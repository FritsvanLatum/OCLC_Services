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

if (query.length > 0) {
  editorProperties.startval = {user_barcode:user_barcode,item_barcode:item_barcode};
}

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

editor.on('ready',function() {


  // Hook up the submit button to log to the console
  $('#submit').on('click',function() {
	  user_barcode = editor.getEditor('root.user_barcode').getValue();
	  item_barcode = editor.getEditor('root.item_barcode').getValue();
    if ((user_barcode.length > 0) && (item_barcode.length > 0))  {
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
        var barcodeURL = document.location.origin + document.location.pathname+'?user_barcode='+user_barcode+'&item_barcode='+item_barcode;
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
