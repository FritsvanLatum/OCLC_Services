/*
* Processing of the sign-in form. Uses the JSON schema 'siSchemaObj' defined in siSchema.js
*
*/

// Initialize the editor
var editorProperties =
{
  schema: siSchemaObj,
  required_by_default: true,
  no_additional_properties: true,
  disable_edit_json: true,
  disable_properties: true,
  disable_collapse: true
  //remove_empty_properties:true,
};

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

editor.on('ready',function() {
  editor.show_errors = 'always';  //interaction (default), change, always, never

  // Hook up the submit button to log to the console
  jQuery('#submitSignIn').on('click',function() {
    //empty feedback div
    jQuery('#res').html("");
    //get validation errors
    var errors = editor.validate();
    if(errors.length) {
      //Collect and show error messages
      if (debug) console.log(errors);
      msg = '<p>Your request has NOT been sent. Correct the following fields:<br/>';
      errors.forEach(function(err) {
        msg += '- ' + editor.getEditor(err.path).schema.title + ': ' + err.message + '<br/>';
      });
      msg += '</p>';
      jQuery('#res').html(msg);
    }
    else {
      // Get the values from the editor
      values = editor.getValue();
      if (debug) console.log(values);

      request = jQuery.post(signInUrl,values);

      request.done( function(data, textStatus, jqXHR) {
        if (debug) console.log("Data: "+data+' - textStatus: ' + textStatus);
        window.location.href = accountUrl + '?je_ac='+data;

      });
      // Callback handler that will be called on failure
      request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        if (debug) console.log("Error: "+textStatus + ' - ' + errorThrown + ' - ' + jqXHR.responseText);
        msg = '<p>Username or password not valid!</p>';
        jQuery('#res').html(msg);
      });

    }
  });

/*
  jQuery('#forgot').on('click',function() {
    window.location.href = window.location.protocol + "//" + window.location.host + "/wordpress498/change-password";
  });
*/

});