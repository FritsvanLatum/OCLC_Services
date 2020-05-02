<?php
require_once './vendor/autoload.php';
require_once '../../IDM_Service.php';
require_once '../../NCIP_Service.php';
$debug = TRUE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;
$patron_barcode = null;
$ppid = null;

$id_template_file = './templates/id_template.html';
$ncip_template_file = './templates/ncip_template.html';

$patron = new IDM_Service('keys_idm.php');
$ncip = new NCIP_Service('keys_ncip.php');
if (array_key_exists('patronBarcode',$_GET)) {
	//get ppid
  $patron_barcode = $_GET['patronBarcode'];
	$patron->read_patron_barcode($patron_barcode);
	if ($patron->search['totalResults'] > 0) {
  	$ppid = $patron->patron['id'];

	  //get ncip data
	  $ncip->lookup_patron_ppid($ppid);
	  
  }
  
      
}

?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/idcard.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/circSchema.js"></script>
    <script>
    	patronBarcode = '';
    	ppid = '';
    	<?php if ($patron_barcode) echo "patronBarcode = '$patron_barcode';" ?>
    	<?php if ($ppid) echo "ppid = '$ppid';" ?>
    </script>
  </head>

  <body>
  	<p>Frits: 090037604</p>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Search patron</button>
      <button id='empty'>Empty form</button>
    </div>
    <!--<div id="res" class="alert">-->
    <div id="res">
      <?php 
      if ($patron->search['totalResults'] > 0) {
      	if ($patron->search['totalResults'] > 1) echo "Please note: more then one patron found with barcode: $patron_barcode.";

        $loader = new Twig_Loader_Filesystem(__DIR__);
        $twig = new Twig_Environment($loader, array(
          //specify a cache directory only in a production setting
          //'cache' => './compilation_cache',
        ));
        echo $twig->render($id_template_file, $patron->patron);
        echo $twig->render($ncip_template_file, $ncip->patron["NCIPMessage"]["LookupUserResponse"]);

      }
      else echo "No patron found with barcode: $patron_barcode.";

      ?>
    </div>

    <?php if ($debug) { ?>
    <div>
      Patron:
      <pre>
        <?php if ($patron_barcode) echo $patron->patron_str();?>
      </pre>
      NCIP:
      <pre>
        <?php if ($patron_barcode) echo $ncip->ncip_message_str();?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/idcardForm.js"></script>
  </body>

</html>