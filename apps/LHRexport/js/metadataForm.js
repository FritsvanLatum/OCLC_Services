var debug = true;
//var schemaFile = "schema/schema.json";

//JSONEditor defaults
JSONEditor.defaults.theme = 'bootstrap2'; //'barebones';
JSONEditor.defaults.iconlib = 'fontawesome3'; //'';
JSONEditor.defaults.options.keep_oneof_values = false;
JSONEditor.plugins.selectize.enable = true;

var editorProperties =
{
  //  show_errors: 'change',  //interaction (default), change, always, never
  //  ajax:true,
  schema: schemaObj,
  //remove_empty_properties:true,
  required_by_default: true,
  no_additional_properties: true,
  disable_edit_json: true,
  disable_properties: true,
  disable_collapse: true
};

// Initialize the editor
var query = document.location.search;

/* hier geen ocn opnieuw invullen
if (query.includes('ocn=')) {
  editorProperties.startval = {ocn:ocn};
}
*/

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

editor.on('ready',function() {


  // Hook up the submit button to log to the console
  $('#submitOCN').on('click',function() {
	  ocn = editor.getEditor('root.ocn').getValue();
    //if (ocn.length > 0)  {
      //empty feedback div
      $('#res').html("");

      //Validate
      var errors = editor.validate();

      if(errors.length) {
        //collect and show error messages
        if (debug) console.log(errors);
        msg = '<p>Your request has NOT been sent. Correct the following fields.</p>';
        errors.forEach(function(err) {
          msg += '<p>' + editor.getEditor(err.path).schema.title + ': ' + err.message + '</p>';
        });
        $('#res').html(msg);
      }
      else {
        var barcodeURL = document.location.origin + document.location.pathname+'?ocn='+ocn;
        window.location.assign(barcodeURL);
      }
    //}
  });


  // Hook up the Empty button
  $('#empty').on('click',function() {
    var emptyURL = document.location.origin + document.location.pathname;
    window.location.assign(emptyURL);
  });

  // Hook up the send button
  $('#send').on('click',function() {
    var emptyURL = document.location.origin + document.location.pathname+'?action=send';
    window.location.assign(emptyURL);
  });

});
