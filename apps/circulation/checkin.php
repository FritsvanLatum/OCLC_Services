<?php
  //for TWIG templating:
  require_once '../../vendor/autoload.php';
  //for lookups in patron admin of WMS
  require_once '../../IDM_Service.php';
  //for lookups, holds, cancel holds and renewal in WMS
  require_once '../../NCIP_Service.php';

  $debug = TRUE;
  //add &debug to the url for getting output from library classes that use API's:
  if (array_key_exists('debug',$_GET)) $debug = TRUE;
  
  //TWIG templates:
  $ncip_template_file = './templates/ncip_template.html';
  
  //classes for Patrons and circulation services
  $ncip = new NCIP_Service('keys_ncip.php');
  
  //if this script is called with an url parameter 'item_barcode' 
  $item_barcode = null;
  if (array_key_exists('item_barcode',$_GET)) $item_barcode = $_GET['item_barcode'];
  
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - checkin</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/checkinSchema.js"></script>
    <script>
      <?php if ($item_barcode) echo "item_barcode = '$item_barcode';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Check In</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
        if (!is_null($item_barcode)) {
          $loader = new Twig_Loader_Filesystem(__DIR__);
          $twig = new Twig_Environment($loader, array(
          //specify a cache directory only in a production setting
          //'cache' => './compilation_cache',
          ));
          $ncip->checkin_barcode($item_barcode);
          if (array_key_exists('Problem',$ncip->response_json['NCIPMessage'][0]['CheckInItemResponse'][0])) {
            echo "<pre>".json_encode($ncip->response_json['NCIPMessage'][0]['CheckInItemResponse'][0], JSON_PRETTY_PRINT)."</pre>";
          }
          
          //echo $twig->render($id_template_file, $patron->patron);
          //echo $twig->render($ncip_template_file, $ncip->response_json["NCIPMessage"][0]["LookupUserResponse"][0]);
        }
      ?>
    </div>
    <p>Add &debug to the url in order to see the output of the API's</p>
    <?php
      //show information from library classes
      //use echo $patron; and/or echo $circulation; for even more info
      if ($debug) { ?>
    <div>
      NCIP:
      <pre>
        <?php echo $ncip;?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/checkinForm.js"></script>
  </body>

</html>