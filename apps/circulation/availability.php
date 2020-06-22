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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/ocnSchema.js"></script>
    <script>
      <?php if ($ocn) echo "ocn = '$ocn';" ?>
    </script>
  </head>

  <body>
    <a href="index.html">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Get availability</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
      if ($ocn) echo $avail->avail_str('html'); //echo '<pre>'.json_encode($avail->get_circulation_info($ocn), JSON_PRETTY_PRINT).'</pre>';
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php if ($debug) { ?>
    <div>
      Publication:
      <pre>
        <?php if ($ocn) echo $avail;?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/ocnForm.js"></script>
  </body>

</html>