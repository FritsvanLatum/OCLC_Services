<?php

function scim2json($scim_json) {
  $loader = new Twig_Loader_Filesystem(__DIR__);
  $twig = new Twig_Environment($loader, array(
  //specify a cache directory only in a production setting
  //'cache' => './compilation_cache',
  ));
  $json = $twig->render('./idm_templates/scim2json_template.json', $scim_json);
  //file_put_contents('form.json',json_encode($scim_json, JSON_PRETTY_PRINT));
  $json = preg_replace('/\s+/', ' ', $json);
  $jsonarr = json_decode($json,TRUE);
  if (array_key_exists('country',$jsonarr))  $jsonarr['country'] = get_countrycode($jsonarr['country'],'name');
  if (array_key_exists('gender',$jsonarr))  $jsonarr['gender'] = strtolower($jsonarr['gender']);
  return json_encode($jsonarr);
}

/*   function json2scim_new($barcode,$json, $patron_type = null, $activate = FALSE) {
*
* converts a json format to scim

* parameters:
*    $barcode: a new barcode in WMS
*    $json: an associative array containg the data of a person, i.e.
*    $activate:
if FALSE (default): blocked is set to TRUE and verified to FALSE,
if TRUE: blocked is set to FALSE and verified to TRUE, see function json2scim_activate
*    $patron_type: if null patron type is set to defauklt
*
* returns scim_json
*/

function json2scim_new($barcode, $json, $patron_type = null, $activate = FALSE) {
  $ppid = '';
  $json['extra'] = array(
  'barcode' => $barcode,
  'country' => get_countrycode($json['country']),
  'date' => date("Y-m-d"),
  'expDate' => date('Y-m-d\TH:i:s\Z'),
  'patron_type' => 'Website',
  /*
  de gebruiker wordt direct actief, als dat anders moet kan json2scim_activate gebruikt worden
  om een gebruiker te activeren
  */
  'blocked' => 'false',
  'verified' => 'true'
  );

  if ($activate) {
    $json['extra']['blocked'] = 'true';
    $json['extra']['verified'] = 'false';
  }
  if ($patron_type !== null) $json['extra']['patron_type'] = $patron_type;

  //file_put_contents('form.json',json_encode($json, JSON_PRETTY_PRINT));

  $loader = new Twig_Loader_Filesystem(__DIR__);
  $twig = new Twig_Environment($loader, array(
  //specify a cache directory only in a production setting
  //'cache' => './compilation_cache',
  ));
  $scim_json = $twig->render('./idm_templates/scim_create_template.json', $json);
  //file_put_contents('form_scim.json',json_encode($scim_json, JSON_PRETTY_PRINT));
  
  return $scim_json;
}

/*
* updates a customer in WMS
*
* parameters:
*   $ppid: a valid ppid of an existing patron
*   $barcode: the barcode of the patron
*   $json: the patron data, see function json2scim_new($barcode,$json) for an example
*
* this functions uses TWIG to transform the simple $json in the complicated $scim_json
* which is used by function update_patron

* returns TRUE or FALSE
*/
function json2scim_update($ppid, $json) {
  $json['extra'] = array(
  'country' => get_countrycode($json['country']),
  'date' => date("Y-m-d")
  );
  //file_put_contents('form.json',json_encode($json, JSON_PRETTY_PRINT));

  $loader = new Twig_Loader_Filesystem(__DIR__);
  $twig = new Twig_Environment($loader, array(
  //specify a cache directory only in a production setting
  //'cache' => './compilation_cache',
  ));
  $scim_json = $twig->render('./idm_templates/scim_update_template.json', $json);
  //file_put_contents('form_scim.json',json_encode($scim_json, JSON_PRETTY_PRINT));

  return $scim_json;
}


/*
* activates a new customer in WMS
* meaning that blocked is set to FALSE and verified to TRUE
*
* returns TRUE or FALSE
*/
function json2scim_activate($ppid, $barcode, $json){
  //calculate expiry date
  //$expDate = ($json['services']['membershipPeriod'] == "week") ? date('Y-m-d\TH:i:s\Z', strtotime("+9 days")) : date('Y-m-d\TH:i:s\Z', strtotime("+1 year"));
  $json['extra'] = array(
  'barcode' => $barcode,
  'date' => date("Y-m-d"),
  //'expDate' => $expDate,
  'blocked' => 'false',
  'verified' => 'true'
  );
  //file_put_contents('form.json',json_encode($json, JSON_PRETTY_PRINT));

  $loader = new Twig_Loader_Filesystem(__DIR__);
  $twig = new Twig_Environment($loader, array(
  //specify a cache directory only in a production setting
  //'cache' => './compilation_cache',
  ));
  $scim_json = $twig->render('./idm_templates/scim_activate_template.json', $json);
  //file_put_contents('form_scim.json',json_encode($scim_json, JSON_PRETTY_PRINT));
  return $scim_json;
}

/*
* gets a 2 letter country code according to ISO 3166-1 alpha-2
* WMS requires this code instead of the name of the country
* codes are in a separate file country2code.php
*/
function get_countrycode($c,$t = 'code') {
  $codeOfCountry = array(
  "Afghanistan" => "AF",
  "Åland Islands" => "AX",
  "Albania" => "AL",
  "Algeria" => "DZ",
  "American Samoa" => "AS",
  "Angola" => "AO",
  "Andorra" => "AD",
  "Anguilla" => "AI",
  "Antarctica" => "AQ",
  "Antigua and Barbuda" => "AG",
  "Argentina" => "AR",
  "Armenia" => "AM",
  "Aruba" => "AW",
  "Australia" => "AU",
  "Austria" => "AT",
  "Azerbaijan" => "AZ",
  "Bahamas" => "BS",
  "Bahrain" => "BH",
  "Bangladesh" => "BD",
  "Barbados" => "BB",
  "Belarus" => "BY",
  "Belgium" => "BE",
  "Belize" => "BZ",
  "Benin" => "BJ",
  "Bermuda" => "BM",
  "Bhutan" => "BT",
  "Bolivia, Plurinational State of" => "BO",
  "Bonaire, Sint Eustatius and Saba" => "BQ",
  "Bosnia and Herzegovina" => "BA",
  "Botswana" => "BW",
  "Bouvet Island" => "BV",
  "Brazil" => "BR",
  "British Indian Ocean Territory" => "IO",
  "Brunei Darussalam" => "BN",
  "Bulgaria" => "BG",
  "Burkina Faso" => "BF",
  "Burundi" => "BI",
  "Cambodia" => "KH",
  "Cameroon" => "CM",
  "Canada" => "CA",
  "Cape Verde" => "CV",
  "Cayman Islands" => "KY",
  "Central African Republic" => "CF",
  "Chad" => "TD",
  "Chile" => "CL",
  "China" => "CN",
  "Christmas Island" => "CX",
  "Cocos (Keeling) Islands" => "CC",
  "Colombia" => "CO",
  "Comoros" => "KM",
  "Congo" => "CG",
  "Congo, the Democratic Republic of the" => "CD",
  "Cook Islands" => "CK",
  "Costa Rica" => "CR",
  "Côte d'Ivoire" => "CI",
  "Croatia" => "HR",
  "Cuba" => "CU",
  "Curaçao" => "CW",
  "Cyprus" => "CY",
  "Czech Republic" => "CZ",
  "Denmark" => "DK",
  "Djibouti" => "DJ",
  "Dominica" => "DM",
  "Dominican Republic" => "DO",
  "Ecuador" => "EC",
  "Egypt" => "EG",
  "El Salvador" => "SV",
  "Equatorial Guinea" => "GQ",
  "Eritrea" => "ER",
  "Estonia" => "EE",
  "Ethiopia" => "ET",
  "Falkland Islands (Malvinas)" => "FK",
  "Faroe Islands" => "FO",
  "Fiji" => "FJ",
  "Finland" => "FI",
  "France" => "FR",
  "French Guiana" => "GF",
  "French Polynesia" => "PF",
  "French Southern Territories" => "TF",
  "Gabon" => "GA",
  "Gambia" => "GM",
  "Georgia" => "GE",
  "Germany" => "DE",
  "Ghana" => "GH",
  "Gibraltar" => "GI",
  "Greece" => "GR",
  "Greenland" => "GL",
  "Grenada" => "GD",
  "Guadeloupe" => "GP",
  "Guam" => "GU",
  "Guatemala" => "GT",
  "Guernsey" => "GG",
  "Guinea" => "GN",
  "Guinea-Bissau" => "GW",
  "Guyana" => "GY",
  "Haiti" => "HT",
  "Heard Island and McDonald Islands" => "HM",
  "Holy See (Vatican City State)" => "VA",
  "Honduras" => "HN",
  "Hong Kong" => "HK",
  "Hungary" => "HU",
  "Iceland" => "IS",
  "India" => "IN",
  "Indonesia" => "ID",
  "Iran, Islamic Republic of" => "IR",
  "Iraq" => "IQ",
  "Ireland" => "IE",
  "Isle of Man" => "IM",
  "Israel" => "IL",
  "Italy" => "IT",
  "Jamaica" => "JM",
  "Japan" => "JP",
  "Jersey" => "JE",
  "Jordan" => "JO",
  "Kazakhstan" => "KZ",
  "Kenya" => "KE",
  "Kiribati" => "KI",
  "Korea, Democratic People's Republic of" => "KP",
  "Korea, Republic of" => "KR",
  "Kuwait" => "KW",
  "Kyrgyzstan" => "KG",
  "Lao People's Democratic Republic" => "LA",
  "Latvia" => "LV",
  "Lebanon" => "LB",
  "Lesotho" => "LS",
  "Liberia" => "LR",
  "Libya" => "LY",
  "Liechtenstein" => "LI",
  "Lithuania" => "LT",
  "Luxembourg" => "LU",
  "Macao" => "MO",
  "Macedonia, the Former Yugoslav Republic of" => "MK",
  "Madagascar" => "MG",
  "Malawi" => "MW",
  "Malaysia" => "MY",
  "Maldives" => "MV",
  "Mali" => "ML",
  "Malta" => "MT",
  "Marshall Islands" => "MH",
  "Martinique" => "MQ",
  "Mauritania" => "MR",
  "Mauritius" => "MU",
  "Mayotte" => "YT",
  "Mexico" => "MX",
  "Micronesia, Federated States of" => "FM",
  "Moldova, Republic of" => "MD",
  "Monaco" => "MC",
  "Mongolia" => "MN",
  "Montenegro" => "ME",
  "Montserrat" => "MS",
  "Morocco" => "MA",
  "Mozambique" => "MZ",
  "Myanmar" => "MM",
  "Namibia" => "NA",
  "Nauru" => "NR",
  "Nepal" => "NP",
  "Netherlands" => "NL",
  "New Caledonia" => "NC",
  "New Zealand" => "NZ",
  "Nicaragua" => "NI",
  "Niger" => "NE",
  "Nigeria" => "NG",
  "Niue" => "NU",
  "Norfolk Island" => "NF",
  "Northern Mariana Islands" => "MP",
  "Norway" => "NO",
  "Oman" => "OM",
  "Pakistan" => "PK",
  "Palau" => "PW",
  "Palestine, State of" => "PS",
  "Panama" => "PA",
  "Papua New Guinea" => "PG",
  "Paraguay" => "PY",
  "Peru" => "PE",
  "Philippines" => "PH",
  "Pitcairn" => "PN",
  "Poland" => "PL",
  "Portugal" => "PT",
  "Puerto Rico" => "PR",
  "Qatar" => "QA",
  "Réunion" => "RE",
  "Romania" => "RO",
  "Russian Federation" => "RU",
  "Rwanda" => "RW",
  "Saint Barthélemy" => "BL",
  "Saint Helena, Ascension and Tristan da Cunha" => "SH",
  "Saint Kitts and Nevis" => "KN",
  "Saint Lucia" => "LC",
  "Saint Martin (French part)" => "MF",
  "Saint Pierre and Miquelon" => "PM",
  "Saint Vincent and the Grenadines" => "VC",
  "Samoa" => "WS",
  "San Marino" => "SM",
  "Sao Tome and Principe" => "ST",
  "Saudi Arabia" => "SA",
  "Senegal" => "SN",
  "Serbia" => "RS",
  "Seychelles" => "SC",
  "Sierra Leone" => "SL",
  "Singapore" => "SG",
  "Sint Maarten (Dutch part)" => "SX",
  "Slovakia" => "SK",
  "Slovenia" => "SI",
  "Solomon Islands" => "SB",
  "Somalia" => "SO",
  "South Africa" => "ZA",
  "South Georgia and the South Sandwich Islands" => "GS",
  "South Sudan" => "SS",
  "Spain" => "ES",
  "Sri Lanka" => "LK",
  "Sudan" => "SD",
  "Suriname" => "SR",
  "Svalbard and Jan Mayen" => "SJ",
  "Swaziland" => "SZ",
  "Sweden" => "SE",
  "Switzerland" => "CH",
  "Syrian Arab Republic" => "SY",
  "Taiwan, Province of China" => "TW",
  "Tajikistan" => "TJ",
  "Tanzania, United Republic of" => "TZ",
  "Thailand" => "TH",
  "Timor-Leste" => "TL",
  "Togo" => "TG",
  "Tokelau" => "TK",
  "Tonga" => "TO",
  "Trinidad and Tobago" => "TT",
  "Tunisia" => "TN",
  "Turkey" => "TR",
  "Turkmenistan" => "TM",
  "Turks and Caicos Islands" => "TC",
  "Tuvalu" => "TV",
  "Uganda" => "UG",
  "Ukraine" => "UA",
  "United Arab Emirates" => "AE",
  "United Kingdom" => "GB",
  "United States" => "US",
  "United States Minor Outlying Islands" => "UM",
  "Uruguay" => "UY",
  "Uzbekistan" => "UZ",
  "Vanuatu" => "VU",
  "Venezuela, Bolivarian Republic of" => "VE",
  "Viet Nam" => "VN",
  "Virgin Islands, British" => "VG",
  "Virgin Islands, U.S." => "VI",
  "Wallis and Futuna" => "WF",
  "Western Sahara" => "EH",
  "Yemen" => "YE",
  "Zambia" => "ZM",
  "Zimbabwe" => "ZW",
  );

  if ($t == 'code') {
    return array_key_exists($c,$codeOfCountry) ? $codeOfCountry[$c] : '';
  }
  else {
    $key = array_search ($c,$codeOfCountry);
    return $key ? $key : '';
  }
}

