<?php
/*
script called by the registration webpage using jQuery.post (AJAX)
*/

//delete or comment out the 3 lines about errors in a production environment
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../../IDM_Service.php';

/*
* $_POST is an associative array as send by the registration form
* see schema/regSchema.js for its schema
*
*/
$json = $_POST;

//check drupal user admin: is this user name already used? if so give feedback

//if this is a new user: generate a new barcode for this user
$idm = new IDM_Service('keys_idm.php');
//get $username from $json
$barcode = $idm->wms_new_barcode('$username');
//check value of $barcode: length must be > 0; if length == 0:  message and exit("Registration failed.")

//if $barcode ok create the user in WMS, receive his ppid
$ppid = $idm->wms_create($barcode,$json);
//check value of $ppid: length must be > 0; if length == 0: message and exit("Registration failed.")

//if $ppid ok insert the user, with his ppid in Drupal
file_put_contents("$ppid.json",$json);

//send mail

exit(0);

