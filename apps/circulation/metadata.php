<?php
//require_once '../../vendor/autoload.php';
require_once '../../Metadata_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$metadatan = new Metadata_Service('keys_collman.php');

$ocn = null;
if (array_key_exists('ocn',$_GET)) {
  $ocn = trim($_GET['ocn']);
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
    <title>Circulation - metadata of publication</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/metadataSchema.js"></script>
    <script>
      <?php if ($ocn) echo "ocn = '$ocn';" ?>
      <?php if ($acc) echo "acc = '$acc';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submitOCN'>Get metadata by OCN</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="expl">
    <p>Conversies naar MARC:</p>
<ol>
  <li>Vul een ocn in</li>
  <li>Kies een Format (xml of json)</li>
  <li>Klik op 'Get metadata by ocn'</li>
</ol>
    </div>
    <script type="text/javascript" src="js/metadataForm.js"></script>
    <div id="res">
      <?php
      if ($acc == 'xml') {$metadatan->metadata_headers = ['Accept' => 'application/atom+xml'];}
      else {$metadatan->metadata_headers = ['Accept' => 'application/atom+json'];}
      
      if ($ocn) {
        $metadatan->get_bib_ocn($ocn);
/*        $marc = $metadatan->json2marc('marc');
        if ($marc) {
          file_put_contents ('LHRs.marc', $marc, FILE_APPEND);
          echo "<h5>MARC format</h5>";
          echo "<pre>$marc</pre>";
        }
        
        $marc = $metadatan->json2marc('xml');
        if ($marc) {
          file_put_contents ('LHRs.xml', $marc, FILE_APPEND);
          $marc = str_replace(array('<','>'), array('&lt;','&gt;'), $marc);
          echo "<h5>MARC XML format</h5>";
          echo "<pre>$marc</pre>";
        }
*/
        echo "<h5>API output</h5>";
        echo $metadatan->metadata_str('html');
      }
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
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