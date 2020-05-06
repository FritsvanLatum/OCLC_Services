<?php
  //for TWIG templating:
  require_once '../../vendor/autoload.php';
  //for lookups in patron admin of WMS
  require_once '../../IDM_Service.php';
  //for lookups, holds, cancel holds and renewal in WMS
  require_once '../../NCIP_Service.php';

  $debug = FALSE;
  //add &debug to the url for getting output from library classes that use API's:
  if (array_key_exists('debug',$_GET)) $debug = TRUE;
  
  //TWIG templates:
  $id_template_file = './templates/id_template.html';
  $ncip_template_file = './templates/ncip_template.html';
  
  //classes for Patrons and circulation services
  $patron = new IDM_Service('keys_idm.php');
  $ncip = new NCIP_Service('keys_ncip.php');
  
  //if this script is called with an url parameter 'barcode' then get ppid of patron and collect his circulation data
  $barcode = null;
  if (array_key_exists('barcode',$_GET)) {
    //get ppid
    $barcode = $_GET['barcode'];
    $patron->read_patron_barcode($barcode);
    if ($patron->patron && (array_key_exists('id',$patron->patron))) {
      $ppid = $patron->patron['id'];
      $ncip->lookup_patron_ppid($ppid);
    }
  }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/idcard.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/barcodeSchema.js"></script>
    <script>
      <?php if ($barcode) echo "barcode = '$barcode';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Get patron information</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
        //now render with TWIG templating the output
        if ($patron->patron && (array_key_exists('id',$patron->patron))) {
          $loader = new Twig_Loader_Filesystem(__DIR__);
          $twig = new Twig_Environment($loader, array(
          //specify a cache directory only in a production setting
          //'cache' => './compilation_cache',
          ));
          echo $twig->render($id_template_file, $patron->patron);
          echo $twig->render($ncip_template_file, $ncip->patron["NCIPMessage"][0]["LookupUserResponse"][0]);
        }
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php
      //show information from library classes
      //use echo $patron; and/or echo $circulation; for even more info
      if ($debug) { ?>
    <div>
      Patron:
      <pre>
        <?php if ($barcode) echo $patron->patron_str();?>
      </pre>
      NCIP:
      <pre>
        <?php if ($barcode) echo $ncip->ncip_message_str();?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/barcodeForm.js"></script>
  </body>

</html>