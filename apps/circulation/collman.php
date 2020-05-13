<?php
require_once '../../vendor/autoload.php';
require_once '../../CollMan_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$collman = new Collection_Management_Service('keys_collman.php');

$ocn = null;
if (array_key_exists('ocn',$_GET)) {
  $ocn = $_GET['ocn'];
  $collman->get_lhrs_of_ocn($ocn);
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - collection management of publication</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/ocnSchema.js"></script>
    <script>
      <?php if ($ocn) echo "ocn = '$ocn';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Get collection management</button>
      <button id='empty'>Empty form</button>
    </div>
    <script type="text/javascript" src="js/ocnForm.js"></script>
    <div id="res">
      <?php
      if ($ocn) echo $collman->collman_str('html'); 
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php if ($debug) { ?>
    <div>
      Publication:
      <pre>
        <?php if ($ocn) echo $collman;?>
      </pre>
    </div>
    <?php } ?>

  </body>

</html>