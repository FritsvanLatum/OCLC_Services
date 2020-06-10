<?php
//TWIG templating:
require_once '../../vendor/autoload.php';
//OCLC Metadata service:
require_once '../../Metadata_Service.php';
//OCLC Collecton Management service:
require_once '../../Collman_Service.php';

session_start();

$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$marcxml_dir = './WMS_export/marcxml';
$mrc_dir = './WMS_export/mrc';
$archive_dir = './WMS_export/marcxml_archive';
$xml_open = '<?xml version="1.0" encoding="UTF-8" ?>'."\n".
'<marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">';
$xml_close = '</marc:collection>';

//check file
//api.export.*.xml
$file_name = '';
$here = getcwd();
$output = '';
$command = '';
if (array_key_exists('action',$_GET) && ($_GET['action'] == 'send')) {
  if (chdir($marcxml_dir)) {
    $files = glob('api.export.*.xml');
    chdir($here);
    if (count($files) == 1) {
      $file_name = $files[0];
      //closexml
      file_put_contents($marcxml_dir.'/'.$file_name, $xml_close, FILE_APPEND);
      $dest_name = preg_replace('/\.xml$/', '.mrc', $file_name);
      //convert: cmarcedit -s .\api.export.D20200609.T100043.xml -d .\test.mrc -xmlmarc
      $command = 'cmarcedit -s '.$marcxml_dir.'/'.$file_name.' -d '.$mrc_dir.'/'.$dest_name.' -xmlmarc';
      $output = shell_exec($command);
      //move to archive
      //./WMS_export/marcxml/api.export.D20200609.T083416.xml
      if (chdir($archive_dir)) {
        $files = glob($file_name.'.*');
        chdir($here);
        $moved = rename($marcxml_dir.'/'.$file_name,$archive_dir.'/'.$file_name.'.'.count($files));
      }
      // remove all session variables
      session_unset();
      // destroy the session
      session_destroy();
    }
  }
}
else {
  if (chdir($marcxml_dir)) {
    $files = glob('api.export.*.xml');
    chdir($here);
    if (count($files) == 0) {
      $file_name = 'api.export.'.date("\DYmd.\THis").'.xml';
      file_put_contents($marcxml_dir.'/'.$file_name, $xml_open);
    }
    else if (count($files) == 1) {
      $file_name = $files[0];
    }
    else {
      //error
    }
  }
}

$bib = new Metadata_Service('keys_collman.php');
//atom+xml reply gives marc xml structured bib record
$bib->metadata_headers = ['Accept' => 'application/atom+xml'];

$lhr = new Collection_Management_Service('keys_collman.php');
//this service does not send marc xml, so atom+json is used in the TWIG template to create marc xml
$lhr->collman_headers = ['Accept' => 'application/atom+json'];

$ocn = null;
$already_exported = FALSE;
if (array_key_exists('ocn',$_GET)) {
  $ocn = trim($_GET['ocn']);
  if (!isset($_SESSION['count'])) {
    $_SESSION['count'] = 1;
    $_SESSION['ocns'] = array($ocn);
  }
  else {
    if (in_array($ocn, $_SESSION['ocns'])) {
      $already_exported = TRUE;
    }
    else {
      $_SESSION['count']++;
      $_SESSION['ocns'][] = $ocn;
    }
  }
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>LHR Export</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/metadataSchema.js"></script>
  </head>

  <body>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submitOCN'>Export LHR data</button>
      <button id='empty'>Empty form</button><br/><br/>
      <button id='send'>Send file to Syndeo</button>
    </div>
    <script type="text/javascript" src="js/metadataForm.js"></script>
    <div id="res">
      <?php
      if ($ocn) {
        //did we do this ocn before?
        $_SESSION['count']--;
        if ($already_exported) {
          echo "<p>OCN: '$ocn' is already exported.</p>";
        }
        else {
          $succeeded = $bib->get_bib_ocn($ocn);
          //check bib record
          if ($succeeded) {
            if (array_key_exists('entry', $bib->metadata)) {
              $ldr = $bib->metadata_json["record"][0]["leader"][0];
              if (substr($ldr, 7, 1) == 'm') {
                //echo "<h5>API output metadata</h5>";
                //echo $bib->metadata_str('marchtml');
                
                //now lhr's
                $succeeded = $lhr->get_lhrs_of_ocn($ocn);
                if ($succeeded) {
                  $marc = $lhr->json2marc('marcxml');
                  if ($marc) {
                    if (array_key_exists("holdingLocation",$lhr->collman["entries"][0]["content"]) && 
                      ($lhr->collman["entries"][0]["content"]["holdingLocation"] == "NLVA")) {
                      file_put_contents($marcxml_dir.'/'.$file_name, $bib->metadata_str('marcxml') , FILE_APPEND);
                      file_put_contents($marcxml_dir.'/'.$file_name, $marc, FILE_APPEND);
                      $_SESSION['count']++;
                      echo "<p>OCN's exported: ".$_SESSION['count']."</p>";
                      echo "<p>OCN's: ".implode(', ',$_SESSION['ocns'])."</p>";
                      echo "<h5>API output LHR</h5>";
                      $html = str_replace(array('<','>'), array('&lt;','&gt;'), $marc);
                      echo "<pre>$html</pre>";


                    }                    
                  }
                  else {
                    echo "<p>Error: Could not get a valid LHR record on ocn '$ocn' from WorldCat. Please try again.</p>";
                  }
                }
                else {
                  echo "<p>Error: Could not get an LHR record on ocn '$ocn' from WorldCat. Please try again.</p>";
                }
              }
              else {
                echo "<p>This OCN is not a monograph. Therefore not exported.</p>";
              }
            }
            else if (array_key_exists('error',$bib->metadata)) {
              echo "<p>Error:</p>";
              foreach ($bib->metadata['error'] as $err) {
                if (array_key_exists('code',$err) && array_key_exists('message',$err)) echo " - ".$err['code'][0].": ".$err['message'][0]."<br/>";
              }
            }
            else {
              echo "<p>Error: Could not get a valid BIB record on ocn '$ocn' from WorldCat. Please try again.</p>";
            }
          }
          else {
            echo "<p>Error: Could not get a BIB record on ocn '$ocn' from WorldCat. Please try again.</p>";
          }
        }
        /*

        }
        //echo $lhr->collman_str('html');
        */
      }
      else if (strlen($output) > 0) {
        echo "<pre>$output</pre>";
      }
      ?>
    </div>
    <?php if ($debug) { ?>
    <div>
      Publication:
      <pre>
        <?php if ($ocn) echo $bib;?>
      </pre>
      <pre>
        <?php if ($ocn) echo $lhr;?>
      </pre>
    </div>
    <?php } ?>

  </body>

</html>