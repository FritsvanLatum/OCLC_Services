<?php
/*
script called by the activation webpage using jQuery.get (AJAX)
*/

//delete or comment out the 3 lines about errors in a production environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('./helpers.php');

/*
* there is one parameter je_ac in the URL
*
*/

if (isset($_GET['je_ac'])) {
  $record = get_customer_from_code($_GET['je_ac']);
  if ($record){
    if ($record['activated']) {
      echo '<p>Registration already activated.</p>';
      exit(0);
    }
    else {
      $json = json_decode($record['json'], TRUE);
      $activated = activate_customer($record['ppid'], $record['userName'], $record['barcode'], $json );
      if ($activated) {
        echo '<p>Registration is activated.</p>';
        exit(0);
      }
      else {
        echo '<p>Registration has NOT been activated. Please contact the services librarian at the desk.</p>';
        exit(0);
      }
    }
  }
  else {
    echo 'The activation key is no longer valid, register again.';
    exit|(0);
  }
}
else {
  //no parameter je_ac, however exit(0), this must be handled in the page itself
  exit(0);
}
?>