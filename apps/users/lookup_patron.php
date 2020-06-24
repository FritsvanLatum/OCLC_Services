<?php
//for TWIG templating:
require_once '../../vendor/autoload.php';
//for lookups in patron admin of WMS
require_once '../../IDM_Service.php';

$debug = TRUE;
//add &debug to the url for getting output from library classes that use API's:
if (array_key_exists('debug',$_GET)) $debug = TRUE;

//TWIG templates:
$id_template_file = './templates/id_template.html';

//class for Patrons
$patron = new IDM_Service('keys_idm.php');

$code = null;
if (array_key_exists('code',$_GET)) {
  $code = trim($_GET['code']);
}
$action = null;
if (array_key_exists('action',$_GET)) {
  $action = trim($_GET['action']);
}
$code = null;
if (array_key_exists('code',$_GET)) {
  //get code
  $code = $_GET['code'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - find patron information</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/lookupPatronSchema.js"></script>
    <script>
      <?php if ($code) echo "code = '$code';" ?>
      <?php if ($action) echo "action = '$action';" ?>
    </script>
  </head>

  <body>
    <a href="index.html">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submitPPID'>Get patron information by ppid</button>
      <button id='submitBarcode'>Get patron information by barcode</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
      if ($code) {
        if ($action == 'ppid') {
          $patron->read_patron_ppid($code);
        }
        if ($action == 'barcode') {
          $patron->read_patron_barcode($code);
        }
        //now render with TWIG templating the output
        if ($patron->patron && (array_key_exists('id',$patron->patron))) {
          $ppid = $patron->patron['id'];
          echo '<p><a href="./existing_patron.php?ppid='.$ppid.'" target="_blank">Edit user data</a>.</p>';
          
          $loader = new Twig_Loader_Filesystem(__DIR__);
          $twig = new Twig_Environment($loader, array(
          //specify a cache directory only in a production setting
          //'cache' => './compilation_cache',
          ));
          echo $twig->render($id_template_file, $patron->patron);
        }
      }
      ?>
      <br/>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php
    //show information from library classes
    //use echo $patron; and/or echo $ncip; for even more info
    if ($debug) { ?>
      <div>
        Patron:
        <pre>
          <?php if ($code) echo $patron;?>
        </pre>
      </div>
      <?php } ?>
      <!-- this script adds processing of the little form, button clicks and shows this page again with url parameters -->
      <script type="text/javascript" src="js/lookupPatronForm.js"></script>
    </body>

  </html>