
//set debug to false in a production environment
var debug = true; 

/*
* url for je_assets 
*
*/
// development
//var assetsURL = window.location.origin+'/oclcAPIs/register/je_assets'; 
// production in Wordpress
//var assetsURL = window.location.origin+'/wp-content/themes/ppl2/je_assets';
// production stand alone
var assetsURL = window.location.origin+'/register/je_assets';

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
// development
//var accountUrl  = window.location.origin + '/oclcAPIs/register/account.php';
// production stand alone
var accountUrl  = window.location.origin + '/register/account.php';

JSONEditor.defaults.options.keep_oneof_values = false; //oneof is not used in the schema's
JSONEditor.defaults.options.theme = 'barebones';
