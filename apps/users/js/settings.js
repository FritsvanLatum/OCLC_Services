
//set debug to false in a production environment
var debug = true; 

/*
* urls of PHP scripts called by jquery ajax calls
*/
var registerUrl = "php/register.php";
var activateUrl = "php/activate.php";
var signInUrl   = "php/signin.php";
var changeUrl   = "php/change_reg.php";

/* 
* url of the account page
*/
var accountUrl  = "account.php";

JSONEditor.defaults.options.keep_oneof_values = false; //oneof is not used in the schema's
JSONEditor.defaults.options.theme = 'barebones';
