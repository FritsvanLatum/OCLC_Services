<?php
require_once '../../vendor/autoload.php';
require_once '../../Availability_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$avail = new Availability_Service('keys_availability.php');

$ocn = null;
if (array_key_exists('ocn',$_GET)) {
  $ocn = $_GET['ocn'];
  $avail->get_availabilty_of_ocn($ocn);
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - availability of publication</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/availSchema.js"></script>
    <script>
      <?php if ($ocn) echo "ocn = '$ocn';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Get availability</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
      if ($ocn) echo '<pre>'.json_encode($avail->get_circulation_info($ocn), JSON_PRETTY_PRINT).'</pre>';
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php if ($debug) { ?>
    <div>
      Publication:
      <pre>
        <?php if ($ocn) echo $avail->avail_str();?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/availForm.js"></script>
  </body>

</html>