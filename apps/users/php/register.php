<?php
/*
script called by the registration webpage using jQuery.post (AJAX)
*/

require_once '../../../IDM_Service.php';

/*
* $_POST is an associative array as send by the registration form
* see schema/regSchema.js for its schema
*
*/
$json = $_POST;

$idm = new IDM_Service('keys_idm.php');

//generate a new barcode for this user
$barcode = $idm->wms_new_barcode('$username');

//if $barcode ok create the user in WMS, receive his ppid
$ppid = $idm->wms_create($barcode,$json);

//file_put_contents("$ppid.json",json_encode($json,JSON_PRETTY_PRINT));

echo $ppid;
exit(0);

