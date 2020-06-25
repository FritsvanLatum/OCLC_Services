<?php
require_once '../../vendor/autoload.php';
require_once '../../IDM_Service.php';
require_once '../../NCIP_Staff_Service.php';
$debug = FALSE;
if (array_key_exists('debug',$_GET)) $debug = TRUE;

$ncip_template_file = './templates/ncip_template.html';

$ncip = new NCIP_Staff_Service('keys_ncip.php');

$user_barcode = null;
$bc_list = null;
if (array_key_exists('user_barcode',$_GET) && array_key_exists('bc_list',$_GET)) {
  //get user_barcode
  $user_barcode = $_GET['user_barcode'];
  $bc_list = $_GET['bc_list'];
}
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - checkout</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/checkoutSchema.js"></script>
    <script>
      <?php if ($user_barcode) echo "user_barcode = '$user_barcode';" ?>
    </script>
  </head>

  <body>
    <a href="index.html">Back to menu</a>
    <div id="editor"></div>
    <div id="list"></div>

    <div id="buttons">
      <button id='submit'>Check out</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
        if (array_key_exists('user_barcode',$_GET) && array_key_exists('bc_list',$_GET)) {
          //checkout
          $barcodes = explode(',',$bc_list);
          foreach ($barcodes as $c) { 
            $ncip->checkout_barcode($user_barcode, $item_barcode);
            echo $ncip->response_str('html');
          }
        }
      ?>
    </div>
    <?php if ($debug) { ?>
    <div>
      Patron:
      NCIP:
      <pre>
        <?php if ($user_barcode) echo $ncip;?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/checkoutForm.js"></script>
  </body>

</html>