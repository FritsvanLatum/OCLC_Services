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
  $circulation = new NCIP_Service('keys_ncip.php');
  
  //if this script is called with an url parameter 'ppid' then check existence of patron and collect his circulation data
  $ppid = null;
  if (array_key_exists('ppid',$_GET)) {
    //get ppid
    $ppid = $_GET['ppid'];
    $patron->read_patron_ppid($ppid);
    if ($patron->patron && (array_key_exists('id',$patron->patron))) $circulation->lookup_patron_ppid($ppid);
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
    <script type="text/javascript" src="schema/ppidSchema.js"></script>
    <script>
      <?php if ($ppid) echo "ppid = '$ppid';" ?>
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
          echo $twig->render($ncip_template_file, $circulation->patron["NCIPMessage"][0]["LookupUserResponse"][0]);
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
        <?php if ($ppid) echo $patron->patron_str();?>
      </pre>
      Circulation:
      <pre>
        <?php if ($ppid) echo $circulation->ncip_message_str();?>
      </pre>
    </div>
    <?php } ?>
    <!-- this script adds processing of the little form, button clicks and shows this page again with url parameters -->
    <script type="text/javascript" src="js/ppidForm.js"></script>
  </body>

</html>