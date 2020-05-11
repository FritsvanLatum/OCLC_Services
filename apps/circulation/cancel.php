<?php
require_once '../../vendor/autoload.php';
require_once '../../IDM_Service.php';
require_once '../../NCIP_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$id_template_file = './templates/id_template.html';
$ncip_template_file = './templates/ncip_template.html';

$patron = new IDM_Service('keys_idm.php');
$ncip = new NCIP_Service('keys_ncip.php');

$ppid = null;
$req_id = null;
if (array_key_exists('action',$_GET)) {
  if ($_GET['action'] == 'patron') {
    if (array_key_exists('ppid',$_GET)) {
      $ppid = $_GET['ppid'];
      $patron->read_patron_ppid($ppid);
       if ($patron->patron && (array_key_exists('id',$patron->patron))) {
         $ppid = $patron->patron['id'];
         $ncip->lookup_patron_ppid($ppid);
      }
    }
  }
  
  if ($_GET['action'] == 'cancel') {
    if (array_key_exists('ppid',$_GET) && array_key_exists('req_id',$_GET)) {
      //get ppid
      $ppid = $_GET['ppid'];
      $req_id = $_GET['req_id'];
      $ncip->cancel_request($ppid,$req_id);
      $patron->read_patron_ppid($ppid);
       if ($patron->patron && (array_key_exists('id',$patron->patron))) {
         $ppid = $patron->patron['id'];
         $ncip->lookup_patron_ppid($ppid);
      }
    }
  }
}


?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - cancel hold</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/cancelSchema.js"></script>
    <script>
      <?php if ($ppid) echo "ppid = '$ppid';" ?>
      <?php if ($req_id) echo "req_id = '$req_id';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='patron'>Get patron information</button>
      <button id='submit'>Cancel Hold</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php 
        if ($patron->patron && (array_key_exists('id',$patron->patron))) {
          $loader = new Twig_Loader_Filesystem(__DIR__);
          $twig = new Twig_Environment($loader, array(
            //specify a cache directory only in a production setting
            //'cache' => './compilation_cache',
          ));
          if (array_key_exists('Problem',$ncip->cancel['NCIPMessage'][0])) {
            echo "Problem:<br/>";
            if (array_key_exists('ProblemType',$ncip->cancel['NCIPMessage'][0]['Problem'][0])) 
              echo $ncip->cancel['NCIPMessage'][0]['Problem'][0]['ProblemType'][0]."<br/>";
            if (array_key_exists('ProblemElement',$ncip->cancel['NCIPMessage'][0]['Problem'][0])) 
              echo $ncip->cancel['NCIPMessage'][0]['Problem'][0]['ProblemElement'][0]."<br/>";
            if (array_key_exists('ProblemDetail',$ncip->cancel['NCIPMessage'][0]['Problem'][0])) 
              echo $ncip->cancel['NCIPMessage'][0]['Problem'][0]['ProblemDetail'][0]."<br/>";
            if (array_key_exists('ProblemValue',$ncip->cancel['NCIPMessage'][0]['Problem'][0])) 
              echo $ncip->cancel['NCIPMessage'][0]['Problem'][0]['ProblemValue'][0]."<br/>";
            echo "<br/>";
          }
          echo $twig->render($id_template_file, $patron->patron);
          echo $twig->render($ncip_template_file, $ncip->patron["NCIPMessage"][0]["LookupUserResponse"][0]);
  
        }
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php if ($debug) { ?>
    <div>
      Patron:
      <pre>
        <?php if ($ppid) echo $patron->patron_str();?>
      </pre>
      NCIP:
      <pre>
        <?php if ($ppid) echo $ncip;?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/cancelForm.js"></script>
  </body>

</html>