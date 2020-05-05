<?php
require_once './vendor/autoload.php';
require_once '../../IDM_Service.php';
require_once '../../NCIP_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$id_template_file = './templates/id_template.html';
$ncip_template_file = './templates/ncip_template.html';

$patron = new IDM_Service('keys_idm.php');
$ncip = new NCIP_Service('keys_ncip.php');

$ppid = null;
if (array_key_exists('ppid',$_GET)) {
	//get ppid
  $ppid = $_GET['ppid'];
	$patron->read_patron_ppid($ppid);
 	if ($patron->patron && (array_key_exists('id',$patron->patron))) {
 		$ppid = $patron->patron['id'];
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
    <script type="text/javascript" src="schema/ppidSchema.js"></script>
    <script>
    	<?php if ($ppid) echo "ppid = '$ppid';" ?>
    </script>
  </head>

  <body>
  	<p>
  		Judith<br/>
				Diagnos: urn:oclc:platform:57439 / 79363e42-54a3-46a5-a858-e026641fd9f0<br/>
				Barcode: 090013445<br/>
				Account: urn:mace:oclc:idm:peacepalace 35042283-fb4a-41d8-859e-5a3b47a81d35<br/>
				<br/>
				Aad<br/>
				Diagnos: urn:oclc:platform:57439 / 9d1edb1d-d051-4fde-8431-169bfab07666<br/>
				Barcode: 0900019407<br/>
				Account: urn:mace:oclc:idm:peacepalace 836c32aa-801c-45e3-9584-f3382bd72421<br/>
				<br/>
				Frits<br/>
				Diagnos: urn:oclc:platform:57439 / b420b91d-b3d9-4501-9ccd-99ed44984908<br/>
				Barcode: 090037604<br/>
				Account: urn:mace:oclc:idm:peacepalace 56bb24b2-9f56-41fa-9cdd-53adb5744904
	  </p>
	  <p>
	  	1125981646 De wapens neer! : roman, Bertha von Suttner<br/>
			1015377410 Herman Rosse : design, art, love, architecture, film, theatre, history<br/>
	  </p>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Get patron information</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php 
      	if ($patron->patron && (array_key_exists('id',$patron->patron))) {
	        $loader = new Twig_Loader_Filesystem(__DIR__);
	        $twig = new Twig_Environment($loader, array(
	          //specify a cache directory only in a production setting
	          //'cache' => './compilation_cache',
	        ));
	        echo $twig->render($id_template_file, $patron->patron);
	        echo $twig->render($ncip_template_file, $ncip->patron["NCIPMessage"]["LookupUserResponse"]);
	
      	}
      	else echo "No patron found with ppid: $ppid.";
      ?>
    </div>

    <?php if ($debug) { ?>
    <div>
      Patron:
      <pre>
        <?php if ($ppid) echo $patron->patron_str();?>
      </pre>
      NCIP:
      <pre>
        <?php if ($ppid) echo $ncip->ncip_message_str();?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/ppidForm.js"></script>
  </body>

</html>