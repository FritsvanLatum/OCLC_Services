<?php
  //for TWIG templating:
  require_once '../../vendor/autoload.php';
  //for lookups in patron admin of WMS
  require_once '../../IDM_Service.php';
  //for lookups, holds, cancel holds and renewal in WMS
  require_once '../../NCIP_Staff_Service.php';

  $debug = FALSE;
  //add &debug to the url for getting output from library classes that use API's:
  if (array_key_exists('debug',$_GET)) $debug = TRUE;
  
  //classes for Patrons and circulation services
  $ncip = new NCIP_Staff_Service('keys_ncip.php');
  
  //if this script is called with an url parameter 'bc_list' 
  $bc_list = null;
  if (array_key_exists('bc_list',$_GET)) $bc_list = $_GET['bc_list'];
  
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - checkin</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/checkinSchema.js"></script>
    <link rel="stylesheet" href="./css/circ.css">
  </head>

  <body>
    <a href="index.html">Back to menu</a>
    <div id="editor"></div>
    <div id="list"></div>
    
    <div id="buttons">
      <button id='submit'>Check In</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
        if (!is_null($bc_list)) {
          $barcodes = explode(',',$bc_list);
          foreach ($barcodes as $c) { 
            $ncip->checkin_barcode($c);
            echo $ncip->response_str('html');
          }
        }
      ?>
    </div>
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