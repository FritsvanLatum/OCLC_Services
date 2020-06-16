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
if (query.includes('ppid=')) {
  editorProperties.startval.ppid = ppid;
}
if (query.includes('ocn=')) {
  editorProperties.startval.ocn = ocn;
}

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

editor.on('ready',function() {


  // Hook up the submit button to log to the console
  $('#submit').on('click',function() {
	  ppid = editor.getEditor('root.ppid').getValue();
	  ocn = editor.getEditor('root.ocn').getValue();
    if ((ppid.length > 0) && (ocn.length > 0))  {
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
        var barcodeURL = document.location.origin + document.location.pathname+'?ppid='+ppid+'&ocn='+ocn;
        window.location.assign(barcodeURL);
      }
    }
  });

  // Hook up the Empty button
  $('#empty').on('click',function() {
    var emptyURL = document.location.origin + document.location.pathname;
    window.location.assign(emptyURL);
  });

});
