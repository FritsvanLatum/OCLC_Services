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
$ocn = null;
if (array_key_exists('ppid',$_GET) && array_key_exists('ocn',$_GET)) {
  //get ppid
  $ppid = $_GET['ppid'];
  $ocn = $_GET['ocn'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - hold</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/holdSchema.js"></script>
    <script>
      <?php if ($ppid) echo "ppid = '$ppid';" ?>
      <?php if ($ocn) echo "ocn = '$ocn';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Place Hold</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
        if (array_key_exists('ppid',$_GET) && array_key_exists('ocn',$_GET)) {
          //get ppid

          $loader = new Twig_Loader_Filesystem(__DIR__);
          $twig = new Twig_Environment($loader, array(
            //specify a cache directory only in a production setting
            //'cache' => './compilation_cache',
          ));
          
          $patron->read_patron_ppid($ppid);
          if ($patron->patron && (array_key_exists('id',$patron->patron))) {
            echo $twig->render($id_template_file, $patron->patron);

            $ncip->request_biblevel($ppid,$ocn);
            if (array_key_exists('Problem',$ncip->response_json['NCIPMessage'][0]['RequestItemResponse'][0])) {
              echo "<pre>".json_encode($ncip->response_json['NCIPMessage'][0]['RequestItemResponse'][0], JSON_PRETTY_PRINT)."</pre>";
            }
            else {
              $ncip->lookup_patron_ppid($ppid);
              echo $twig->render($ncip_template_file, $ncip->response_json["NCIPMessage"][0]["LookupUserResponse"][0]);
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

    <script type="text/javascript" src="js/holdForm.js"></script>
  </body>

</html>