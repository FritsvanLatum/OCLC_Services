
//JSONEditor defaults
JSONEditor.defaults.theme = 'bootstrap3'; //'barebones';
JSONEditor.defaults.iconlib = 'fontawesome3'; //'';

var editorProperties =
{
  //  show_errors: 'change',  //interaction (default), change, always, never
  //  ajax:true,
  schema: schemaObj,
  //remove_empty_properties:true,
  required_by_default: true,
  keep_oneof_values: false,
  no_additional_properties: true,
  disable_array_reorder: true,
  disable_edit_json: true,
  disable_properties: true,
  disable_collapse: true
};

// Initialize the editor
var query = document.location.search;

editorProperties.startval ={};
if (query.includes('code=')) {
  editorProperties.startval.code = code;
}

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

editor.on('ready',function() {


  // Hook up the submit button to log to the console
  $('#submitPPID').on('click',function() {
	  code = editor.getEditor('root.code').getValue();
    if (code.length > 0)  {
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
        var newURL = document.location.origin + document.location.pathname+'?action=ppid&code='+code;
        window.location.assign(newURL);
      }
    }
  });

  $('#submitBarcode').on('click',function() {
	  code = editor.getEditor('root.code').getValue();
    if (code.length > 0)  {
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
        var newURL = document.location.origin + document.location.pathname+'?action=barcode&code='+code;
        window.location.assign(newURL);
      }
    }
  });

  // Hook up the Empty button
  $('#empty').on('click',function() {
    var emptyURL = document.location.origin + document.location.pathname;
    window.location.assign(emptyURL);
  });

});
