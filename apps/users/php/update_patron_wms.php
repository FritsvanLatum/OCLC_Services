<?php
require_once '../../../IDM_Service.php';
require_once '../../../SCIM_JSON.php';
$idm = new IDM_Service('keys_idm.php');

/*
* $_POST is an associative array as send by the registration form
*/
$json = $_POST;
//TO DO: check on $json

$scim_json = json2scim_update($json['ppid'], $json);

if ($idm->update_patron($json['ppid'], $scim_json)) {
  echo $json['ppid'];
  exit(0)
}
else {
  header('HTTP/1.1 500 Internal Server Error');
  exit("Update failed.");
}

?>
