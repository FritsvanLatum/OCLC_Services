var debug = true;
//var schemaFile = "schema/schema.json";

//JSONEditor defaults

/*   // theme
    const themeMap = {
      barebones: '',
      bootstrap3: 'https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css',
      bootstrap4: 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
      html: '',
      spectre: 'https://unpkg.com/spectre.css/dist/spectre.min.css',
      tailwind: 'https://unpkg.com/tailwindcss@^1.0/dist/tailwind.min.css'
    }
    themeLink.href = themeMap[data.options.theme]
    themeSelect.value = data.options.theme

    // iconlLib
    const iconLibMap = {
      fontawesome3: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css',
      fontawesome4: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css',
      fontawesome5: 'https://use.fontawesome.com/releases/v5.6.1/css/all.css',
      jqueryui: 'https://code.jquery.com/ui/1.10.3/themes/south-street/jquery-ui.css',
      spectre: 'https://unpkg.com/spectre.css/dist/spectre-icons.min.css'
    }
*/
JSONEditor.defaults.theme = 'bootstrap3'; //'barebones';
JSONEditor.defaults.iconlib = 'fontawesome3'; //'';

//JSONEditor.plugins.selectize.enable = true;

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

if (query.length > 0) {
  editorProperties.startval = {item_barcode:item_barcode};
}

var editor = new JSONEditor(document.getElementById('editor'),editorProperties);

editor.on('ready',function() {


  // Hook up the submit button to log to the console
  $('#submit').on('click',function() {
	  item_barcode = editor.getEditor('root.item_barcode').getValue();
    if (item_barcode.length > 0)  {
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
        var barcodeURL = document.location.origin + document.location.pathname+'?item_barcode='+item_barcode;
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

