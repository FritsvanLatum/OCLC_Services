<?php
//for TWIG templating:
require_once '../../vendor/autoload.php';
//for lookups in patron admin of WMS
require_once '../../IDM_Service.php';
require_once '../../SCIM_JSON.php';

$debug = TRUE;
//add &debug to the url for getting output from library classes that use API's:
if (array_key_exists('debug',$_GET)) $debug = TRUE;

//TWIG templates:
$id_template_file = './templates/id_template.html';

//class for Patrons
$patron = new IDM_Service('keys_idm.php');

$ppid = null;
if (array_key_exists('ppid',$_GET)) {
  //get ppid
  $ppid = $_GET['ppid'];
  $patron->read_patron_ppid($ppid);
  $json = scim2json($patron->patron);
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>User registration - existing user</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/registration.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>

    <script type="text/javascript" src="js/countryList.js"></script>
    <script type="text/javascript" src="schema/existingPatronSchema.js"></script>
    <script type="text/javascript" src="js/validators.js"></script>

    <script type="text/javascript">
      ppid = "<?php echo $ppid ?>";
      vals = {
      <?php
      $jsonarr = json_decode($json,TRUE);
      foreach ($jsonarr as $k => $v) {
        echo $k.': "'.$v.'",'."\n";
      }
      ?>
      };
    </script>
  </head>

  <body>
    <a href="index.html">Back to menu</a>
    <div id = "editorDiv">
      <div id="editor"></div>
      <button id="submit">Send</button>
      <button id="empty">Empty form</button>
      <div id="res" class="alert">
      </div>
    </div>
    <script type="text/javascript" src="js/existingPatronForm.js"></script>
  </body>
</html>