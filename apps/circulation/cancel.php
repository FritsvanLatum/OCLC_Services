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
$action = null;
if (array_key_exists('ppid',$_GET)) $ppid = trim($_GET['ppid']);
if (array_key_exists('req_id',$_GET)) $req_id = trim($_GET['req_id']);
if (array_key_exists('action',$_GET)) $action = $_GET['action'];
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - cancel hold</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
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
          $loader = new Twig_Loader_Filesystem(__DIR__);
          $twig = new Twig_Environment($loader, array(
            //specify a cache directory only in a production setting
            //'cache' => './compilation_cache',
          ));

          if ($action == 'patron') {
            if (!is_null($ppid)) {
              $patron->read_patron_ppid($ppid);
              if ($patron->patron && (array_key_exists('id',$patron->patron))) {
                $ppid = $patron->patron['id'];
                $ncip->lookup_patron_ppid($ppid);
                echo $twig->render($id_template_file, $patron->patron);
                echo $twig->render($ncip_template_file, $ncip->response_json["NCIPMessage"][0]["LookupUserResponse"][0]);
              }
            }
          }

          if ($action == 'cancel') {
            if (!is_null($ppid) && !is_null($req_id)) {
              $patron->read_patron_ppid($ppid);
              if ($patron->patron && (array_key_exists('id',$patron->patron))) {
                echo $twig->render($id_template_file, $patron->patron);
                $ncip->cancel_request($ppid,$req_id);
                if (array_key_exists('Problem',$ncip->response_json['NCIPMessage'][0])) {
                  echo "<pre>".json_encode($ncip->response_json['NCIPMessage'][0], JSON_PRETTY_PRINT)."</pre>";
                }
                else {
                  $ncip->lookup_patron_ppid($ppid);
                  echo $twig->render($ncip_template_file, $ncip->response_json["NCIPMessage"][0]["LookupUserResponse"][0]);
                }
              }
            }
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