<!DOCTYPE html>
<html class="js svg rgba backgroundsize boxshadow borderradius" lang="en-US">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>Activate Library card | Peace Palace Library</title>

    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" href="css/registration.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="js/regValidators.js"></script>
    <script type="text/javascript" src="js/settings.js"></script>

  </head>

  <body class="page-template-default page page-id-24 page-parent page-child parent-pageid-463">

    <div id = "editorDiv">
      <div id="editor"></div>

      <script type="text/javascript">
        // Check if the page has loaded completely
        $(document).ready( function() {
          var urlParams = new URLSearchParams(window.location.search);
          if (urlParams.has('je_ac')) {
            jQuery('#res').html('<p>Your activation request is being processed. Please wait...<span class="spinner" /></p>');
            request = jQuery.get(activateUrl + '?je_ac=' + urlParams.get('je_ac'));

            request.done( function(data, textStatus, jqXHR) {
              if (debug) console.log("Data: "+data+' - textStatus: ' + textStatus);
              jQuery('#res').html(data);
            });

            request.fail(function (jqXHR, textStatus, errorThrown){
              // Log the error to the console
              if (debug) console.log("Error: "+textStatus, errorThrown);
              jQuery('#res').html('<p>Activation failed. Contact the services librarian at the desk.</p>');
            });
          }
          else {
            jQuery('#res').html("<p>You have to use an url with an activation code.</p>");
          }
        });
      </script>



      <div id="res" class="alert"></div>
    </div>

  </body>
</html>