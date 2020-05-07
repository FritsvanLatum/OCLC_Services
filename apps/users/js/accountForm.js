/*
* Processing of the account form. Uses the JSON schema 'regSchemaObj' defined in regSchema.js
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
  disable_collapse: true
  //remove_empty_properties:true,
};

//formValues contains the earlier provided user data
editorProperties.startval = formValues;

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

function watchMembership() {
  em = editor.getEditor('root.services.membership').getValue();
  if (em == 'Yes') {
    jQuery('div[data-schemapath="root.services.membershipPeriod"]').css("display","block");
  }
  else {
    jQuery('div[data-schemapath="root.services.membershipPeriod"]').css("display","none");
  }
}
function watchAlerts() {
  em = editor.getEditor('root.services.receiveAlerts').getValue();
  if (em == 'Yes') {
    jQuery('div[data-schemapath="root.services.alertSubjects"]').css("display","block");
  }
  else {
    jQuery('div[data-schemapath="root.services.alertSubjects"]').css("display","none");
  }
}

editor.on('ready',function() {
  editor.show_errors = 'change';  //interaction (default), change, always, never
  this.getEditor("root.person.email").disable();
  this.getEditor("root.id.userName").disable();

  //membership: display dependent fields
  watchMembership();
  editor.watch('root.services.membership',watchMembership);

  watchAlerts();
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
      msg = '<p>Your request has NOT been sent. Correct the following fields.<br/>';
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
      msg = '<p>Your registration is being processed. Please wait...<span class="spinner" /></p>';
      jQuery('#res').html(msg);

      // Get the values from the editor
      values = editor.getValue();
      if (debug) console.log(values);

      request = jQuery.post(changeUrl,values);

      request.done( function(data, textStatus, jqXHR) {
        if (debug) console.log("Data: "+data+' - textStatus: ' + textStatus);
        if (!debug) editor.disable();
        if (!debug) jQuery('#submit').attr("disabled", true);
        msg = '<p>Thank you for changing your account.</p>';
        jQuery('#res').html(msg);
      });

      request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        if (debug) console.log("Error: "+textStatus, errorThrown);
        if (!debug) editor.disable();
        if (!debug) jQuery('#submit').attr("disabled", true);
        msg = '<p>Your changes are not updated. Please sign in again.</p>';
        jQuery('#res').html(msg);
      });
    }
  });


  // Hook up the Empty button
  jQuery('#empty').on('click',function() {
    var emptyURL = document.location.origin + document.location.pathname;
    //alert(emptyURL);
    window.location.assign(emptyURL);
  });
});
