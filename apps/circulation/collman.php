<?php
require_once '../../vendor/autoload.php';
require_once '../../CollMan_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$collman = new Collection_Management_Service('keys_collman.php');

$code = null;
if (array_key_exists('code',$_GET)) {
  $code = trim($_GET['code']);
}
$action = null;
if (array_key_exists('action',$_GET)) {
  $action = trim($_GET['action']);
}
$acc = null;
if (array_key_exists('acc',$_GET)) {
  $acc = trim($_GET['acc']);
}
else {
  $acc = 'xml';
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
    <script type="text/javascript" src="schema/collmanSchema.js"></script>
    <script>
      <?php if ($code) echo "code = '$code';" ?>
      <?php if ($action) echo "action = '$action';" ?>
      <?php if ($acc) echo "acc = '$acc';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submitOCN'>Get LHR by OCN</button>
      <button id='submitBarcode'>Get LHR by barcode</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="expl">
    <p>Conversies naar MARC:</p>
<ol>
  <li>Vul een barcode in</li>
  <li>Kies Format 'atom_json' </li>
  <li>Klik op 'Get LHR by barcode'</li>
</ol>
    </div>
    <script type="text/javascript" src="js/collmanForm.js"></script>
    <div id="res">
      <?php
      if ($acc == 'atom_xml') {$collman->collman_headers = ['Accept' => 'application/atom+xml'];}
      else if ($acc == 'atom_json') {$collman->collman_headers = ['Accept' => 'application/atom+json'];}
      else if ($acc == 'json') {$collman->collman_headers = ['Accept' => 'application/json'];}
      else {$collman->collman_headers = ['Accept' => 'application/xml'];};
      
      if ($code) {
        if ($action == 'ocn') $collman->get_lhrs_of_ocn($code);
        if ($action == 'barcode') $collman->get_lhrs_of_barcode($code);
        $marc = $collman->json2marc('marc');
        if ($marc) {
          echo "<h5>MARC format</h5>";
          echo "<pre>$marc</pre>";
        }
        
        $marc = $collman->json2marc('xml');
        if ($marc) {
          $marc = str_replace(array('<','>'), array('&lt;','&gt;'), $marc);
          echo "<h5>MARC XML format</h5>";
          echo "<pre>$marc</pre>";
        }
        echo "<h5>API output</h5>";
        echo $collman->collman_str('html');
      }
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php if ($debug) { ?>
    <div>
      Publication:
      <pre>
        <?php if ($code) echo $collman;?>
      </pre>
    </div>
    <?php } ?>

  </body>

</html>