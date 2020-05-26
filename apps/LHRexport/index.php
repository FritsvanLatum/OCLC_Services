<?php
require_once '../../vendor/autoload.php';
require_once '../../Metadata_Service.php';
require_once '../../CollMan_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$metadatan = new Metadata_Service('keys_collman.php');
$metadatan->metadata_headers = ['Accept' => 'application/atom+xml'];
$collman = new Collection_Management_Service('keys_collman.php');
$collman->collman_headers = ['Accept' => 'application/atom+json'];
$ocn = null;
if (array_key_exists('ocn',$_GET)) {
  $ocn = trim($_GET['ocn']);
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
    <script type="text/javascript" src="schema/metadataSchema.js"></script>
    <script>
      <?php if ($ocn) echo "ocn = '$ocn';" ?>
    </script>
  </head>

  <body>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submitOCN'>Export LHR data</button>
      <button id='empty'>Empty form</button>
    </div>
    <script type="text/javascript" src="js/metadataForm.js"></script>
    <div id="res">
      <?php
      if ($ocn) {
        $metadatan->get_bib_ocn($ocn);
        echo "<h5>API output metadata</h5>";
        echo $metadatan->metadata_str('html');

        $collman->get_lhrs_of_ocn($ocn);
        $marc = $collman->json2marc('xml');
        if ($marc) {
          $marc = str_replace(array('<','>'), array('&lt;','&gt;'), $marc);
          echo "<h5>API output LHR</h5>";
          echo "<pre>$marc</pre>";
        }
        //echo $collman->collman_str('html');
      }
      ?>
    </div>
    <?php if ($debug) { ?>
    <div>
      Publication:
      <pre>
        <?php if ($ocn) echo $metadatan;?>
      </pre>
    </div>
    <?php } ?>

  </body>

</html>