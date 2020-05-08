/*
* Processing of the registration form. Uses the JSON schema 'regSchemaObj' defined in regSchema.js
*
*/

// Initialize the editor
var editorProperties =
{
  schema: regSchemaObj,
  required_by_default: true,
  no_additional_properties: true,
  disable_edit_json: true,
  disable_properties: true,
  disable_collapse: true,
  //show_errors: 'never'
  //remove_empty_properties:true,
};
var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

//watch functions
function watchAlerts() {
  em = editor.getEditor('root.services.receiveAlerts').getValue();
  if (em == 'Yes') {
    jQuery('div[data-schemapath="root.services.alertSubjects"]').css("display","block");
  }
  else {
    jQuery('div[data-schemapath="root.services.alertSubjects"]').css("display","none");
  }
}

//further initialization after the form is generated
editor.on('ready',function() {
  //editor.options.show_errors = 'never';  //interaction (default), change, always, never

  //hide dependent fields
  jQuery('div[data-schemapath="root.services.alertSubjects"]').css("display","none");
  
  //membership: display/hide dependent fields
  editor.watch('root.services.receiveAlerts',watchAlerts);

  // Hook up the submit button to log to the console
  jQuery('#submit').on('click',function() {
    //empty feedback div
    jQuery('#res').html("");

    //Validate
    var errors = editor.validate();

    if(errors.length) {
      //collect and show error messages
      if (debug) console.log(errors);
      msg = '<p>Your request has NOT been sent. Correct the following fields:<br/>';
      errors.forEach(function(err) {
        var fname = editor.getEditor(err.path).schema.title;
        var parts = err.path.split('.');
        if ((parts.length > 2) && (parts[1] == 'services')) {
          fname = 'Services';
        }
        msg += '- ' + fname + ': ' + err.message + '<br/>';
      });
      msg += '</p>'
      jQuery('#res').html(msg);
    }
    else {
      //send to ajax script
      msg = '<p>Your registration is being processed. Please wait...<span class="spinner" /></p>';
      jQuery('#res').html(msg);

      // Get the values from the editor
      values = editor.getValue();
      if (debug) console.log(values);

      request = jQuery.post(registerUrl,values);

      request.done( function(data, textStatus, jqXHR) {
        if (debug) console.log("Data: "+data+' - textStatus: ' + textStatus);
        if (data.indexOf('already registered') == -1) {
          if (!debug) editor.disable();
          if (!debug) jQuery('#submit').attr("disabled", true);
          msg = '<p>Thank you for registering. You will receive an email with a confirmation link.</p>';
          jQuery('#res').html(msg);
        }
        else {
          msg = '<p>You are NOT registered. Try another email address or contact the services librarian at the desk.</p>';
          jQuery('#res').html(msg);
        }
      });

      request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        if (debug) console.log("Error: "+textStatus, errorThrown);
        msg = '<p>You are NOT registered. Try another email address or contact the services librarian at the desk.</p>';
        jQuery('#res').html(msg);
     });
    }
  });


  // Hook up the Empty button
  jQuery('#empty').on('click',function() {
    var emptyURL = document.location.origin + document.location.pathname;
    window.location.assign(emptyURL);
  });
});
