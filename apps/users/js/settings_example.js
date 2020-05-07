
//set debug to false in a production environment
var debug = true; 

/*
* url for je_assets 
*
*/
var assetsURL = window.location.origin+'/some/path/je_assets';

/*
* urls of PHP scripts called by jquery ajax calls
*/
var registerUrl = assetsURL + "/php/register.php";
var activateUrl = assetsURL + "/php/activate.php";
var signInUrl   = assetsURL + '/php/signin.php';
var changeUrl   = assetsURL + "/php/change_reg.php";

/* 
* url of the account page
*/
var accountUrl  = window.location.origin + '/some/path/account.php';

JSONEditor.defaults.options.keep_oneof_values = false; //oneof is not used in the schema's
JSONEditor.defaults.options.theme = 'barebones'; 
