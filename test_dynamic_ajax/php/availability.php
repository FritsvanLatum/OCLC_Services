<?php
/*
script called by jQuery.get (AJAX)
*/
require_once '../../Availability_Service.php';
$avail = new Availability_Service('keys_availability.php');

/*
* there is one parameter ocn in the URL
*
*/

if (isset($_GET['ocn'])) {
  $av = $avail->get_circulation_info($ocn);
  echo $av;
  exit(0);
}
else {
  //no parameter ocn, however exit(0), this must be handled in the page itself
  exit(0);
}

?>