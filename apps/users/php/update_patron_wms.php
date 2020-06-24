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
//file_put_contents("test.json",json_encode(json_decode($scim_json),JSON_PRETTY_PRINT));


if ($idm->update_patron($json['ppid'], $scim_json)) {
  echo $idm;
}
else {
  header('HTTP/1.1 500 Internal Server Error');
  exit("Update failed.");
}


?>
