<?php
require_once '../../../IDM_Service.php';
require_once '../../../SCIM_JSON.php';
$idm = new IDM_Service('keys_idm.php');

/*
* $_POST is an associative array as send by the registration form
*/
$json = $_POST;
//TO DO: check on $json

//generate a new barcode for this user
$barcode = $idm->new_barcode('$username');

if ($barcode) {
  //generate scim_json
  $scim_json = json2scim_new($barcode, $json, 'Website', FALSE);
  //file_put_contents("test.json",json_encode(json_decode($scim_json),JSON_PRETTY_PRINT));

  //create the user in WMS, receive his ppid
  $ppid = $idm->create_patron($scim_json);
  if ($ppid) {
    echo $ppid;
    exit(0);
  }
  else {
    exit('No patron created in WMS ');
  }
}
else {
  exit('No valid barcode');
}
