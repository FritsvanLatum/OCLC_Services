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

$user_barcode = null;
$item_barcode = null;
if (array_key_exists('user_barcode',$_GET) && array_key_exists('item_barcode',$_GET)) {
  //get user_barcode
  $user_barcode = trim($_GET['user_barcode']);
  $item_barcode = trim($_GET['item_barcode']);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>Circulation - checkout (one)</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="schema/checkout_oneSchema.js"></script>
    <script>
      <?php if ($user_barcode) echo "user_barcode = '$user_barcode';" ?>
      <?php if ($item_barcode) echo "item_barcode = '$item_barcode';" ?>
    </script>
  </head>

  <body>
    <a href="index.php">Back to menu</a>
    <div id="editor"></div>
    <div id="buttons">
      <button id='submit'>Check out</button>
      <button id='empty'>Empty form</button>
    </div>
    <div id="res">
      <?php
      if (array_key_exists('user_barcode',$_GET) && array_key_exists('item_barcode',$_GET)) {
        //checkout_one
        $ncip->checkout($user_barcode, $item_barcode);
        echo $ncip->response_str('html');

        /*
        $loader = new Twig_Loader_Filesystem(__DIR__);
        $twig = new Twig_Environment($loader, array(
        //specify a cache directory only in a production setting
        //'cache' => './compilation_cache',
        ));


        $ncip->checkout($user_barcode,$item_barcode);
        if (array_key_exists('Problem',$ncip->response_json['NCIPMessage'][0]['RequestItemResponse'][0])) {
        echo "<pre>".json_encode($ncip->response_json['NCIPMessage'][0]['RequestItemResponse'][0], JSON_PRETTY_PRINT)."</pre>";
        }
        else {
        $ncip->lookup_patron_ppid($user_barcode);
        echo $twig->render($ncip_template_file, $ncip->response_json["NCIPMessage"][0]["LookupUserResponse"][0]);
        }*/
      }
      ?>
    </div>
    <?php if ($debug) { ?>
    <div>
      Patron:
      NCIP:
      <pre>
        <?php echo $ncip;?>
      </pre>
    </div>
    <?php } ?>

    <script type="text/javascript" src="js/checkout_oneForm.js"></script>
  </body>

</html>