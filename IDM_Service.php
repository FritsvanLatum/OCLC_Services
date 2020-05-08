<?php

/* 
The class IDM_Service contains the following functions 

read_patron_ppid: gets patron data with a given ppid
get_barcode: gets the barcode from a patron that is already read from WMS

read_patron_barcode: gets patron data with a given barcode
search_patron: search a patron in WMS using a SCIM search string, used by read_patron_barcode and wms_barcode_exists

wms_update: generates SCIM json with changed user data, calls update_patron
wms_activate: generates SCIM json in order to activate a user, calls update_patron
update_patron: updates a patron in WMS

wms_new_barcode: generates a new and not already existing barcode in WMS, uses new_barcode and wms_barcode_exists
new_barcode: generates a 10 digit barcode
wms_barcode_exists: checks whether a barcode already is used in WMS

wms_create: generates SCIM json with new user data, calls create_patron
create_patron: creates a patron in WMS

*/


require_once __DIR__.'/OCLC_Service.php';
require_once __DIR__.'/vendor/autoload.php';

/**
* A class that represents the IDM Service
*/
class IDM_Service extends OCLC_Service{

  //$read_url and search_url are extended in __construct
  public $idm_url = "share.worldcat.org/idaas/scim/v2/Users";

  public $read_url = "";
  public $read_headers = ['Accept' => 'application/scim+json'];

  public $search_url = "";
  public $search_headers = ['Accept' => 'application/scim+json',
                            'Content-Type' => 'application/scim+json'];
  public $search_method = 'POST';
  public $search_POST = true;

  public $create_url = "";
  public $create_headers = ['Content-Type' => 'application/scim+json'];
  public $create_method = 'POST';
  public $create_POST = true;

  public $update_url = "";
  public $update_headers = ['Content-Type' => 'application/scim+json'];
  public $update_method = 'PUT';

  public $initial_patron_type = 'fromWebsite';
  public $patron = null;
  public $search = null;
  public $create = null;
  public $update = null;

  public function __construct($key_file) {
    parent::__construct($key_file);

    //https://{institution-identifier}.share.worldcat.org/idaas/scim/v2/Users/{id}
    $this->read_url = 'https://'.$this->institution.'.'.$this->idm_url;

    //https://{institution-identifier}.share.worldcat.org/idaas/scim/v2/Users/.search
    $this->search_url = 'https://'.$this->institution.'.'.$this->idm_url.'/.search';

    //https://{institution-identifier}.share.worldcat.org/idaas/scim/v2/Users
    $this->create_url = 'https://'.$this->institution.'.'.$this->idm_url;

    //https://{institution-identifier}.share.worldcat.org/idaas/scim/v2/Users/{id}
    $this->update_url = 'https://'.$this->institution.'.'.$this->idm_url;
  }

  public function __toString(){

    //create an array and return json_encoded string
    $json = parent::__toString();

    $json['idm_url'] = $this->idm_url;
    $json['read_url'] = $this->read_url;
    $json['read_headers'] = $this->read_headers;
    $json['search_url'] = $this->search_url;
    $json['search_headers'] = $this->search_headers;
    $json['search_method'] = $this->search_method;
    $json['search_POST'] = $this->search_POST;
    $json['update_url'] = $this->update_url;
    $json['update_headers'] = $this->update_headers;
    $json['update_method'] = $this->update_method;
    $json['create_url'] = $this->create_url;
    $json['create_headers'] = $this->create_headers;
    $json['create_method'] = $this->create_method;

    $json['patron'] = $this->patron;
    $json['search'] = $this->search;
    $json['create'] = $this->create;
    $json['update'] = $this->update;


    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function patron_str(){
    return json_encode($this->patron, JSON_PRETTY_PRINT);
  }


  /*      public function read_patron_ppid($id)
  *
  * parameter: $id
  *
  * gets from WMS the user data in scim json format of the patron
  * with the ppid of the patron given in the parameter $id
  * uses CURL
  *
  * returns TRUE when the patron data is found, 
  * in that case, the data are stored in $this->patron,
  * 
  * returns FALSE otherwise
  *
  * TODO: should be called wmw_get_patron_ppid($ppid)
  */

  public function read_patron_ppid($id) {
    //authorization
    $this->read_headers['Authorization'] = $this->get_access_token_authorization('SCIM');

    $url = $this->read_url.'/'.$id;

    $header_array = [];
    foreach ($this->read_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','read_patron_ppid','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','read_patron_ppid',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','read_patron_ppid','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','read_patron_ppid',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','read_patron_ppid',"Result but still cUrl error [$error_number]: $error_msg");
        }
        $patron_received = json_decode($result,TRUE);
        $json_errno = json_last_error();
        $json_errmsg = json_last_error_msg();
        if ($json_errno == JSON_ERROR_NONE) {
          //store result in this object as an array
          $this->patron = $patron_received;
          return TRUE;
        }
        else {
          $this->log_entry('Error','read_patron_ppid',"json_decode error [$json_errno]: $json_errmsg");
          return FALSE;
        }
      }
    }
  }

  /*     public function get_barcode()
  *
  * een simpele getter voor de barcode
  * returns a barcode als een patron al is ingelezen, m.b.v. function read_patron_ppid($id)
  * anders een empty string
  *
  */
  public function get_barcode() {
    $barcode = '';
    if ($this->patron && array_key_exists('externalId', $this->patron)) {
      $barcode = $this->patron['externalId'];
    }
    return $barcode;
  }

  /*      public function public function read_patron_barcode($barcode)
  *
  * parameter: $barcode
  *
  * gets from WMS the user data in scim json format of the patron
  * with the barcode of the patron given in the parameter
  * uses public function search_patron($search)
  *
  * returns TRUE when the patron data is found, in that case, the data are stored in $this->patron,
  * returns FALSE otherwise
  *
  * TODO: should be called wmw_get_patron_barcode($barcode)
  */
  public function read_patron_barcode($barcode) {
    $search = '{"schemas": ["urn:ietf:params:scim:api:messages:2.0:SearchRequest"], '.
              ' "filter": "External_ID eq \"'.$barcode.'\""}';
    $result = $this->search_patron($search);
    if ($result) {
      //search has an answer
      if ($this->search["totalResults"] > 0) {
        $this->patron = $this->search["Resources"][0];
        $this->search["Resources"] = [];
      }
    }
    return $result;
  }

  /*     public function search_patron($search)
  *
  * searches patrons in WMS, the SCIM way
  *
  * parameter $search must be a valid SCIM json string for searching
  * see example in read_patron_barcode($barcode)
  *
  * at the moment WMS supports very limited search functionality
  */
  public function search_patron($search) {
    //authorization
    $this->search_headers['Authorization'] = $this->get_access_token_authorization('SCIM');

    $header_array = [];
    foreach ($this->search_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->search_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_POST, $this->search_POST);
    curl_setopt($curl, CURLOPT_POSTFIELDS,$search);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    curl_close($curl);


    if ($error_number) {
      //return info in json format
      $result = '{"Curl_errno": "'.$error_number.'", "Curl_error": "'.curl_error($curl).'"}';
      $this->errors['curl'] = json_decode($result,TRUE);
      return false;
    }
    else {
      //store result in this object as an array
      $this->search = json_decode($result,TRUE);

      return true;
    }
  }


  /*
  * updates a customer in WMS
  * 
  * parameters:
  *   $ppid: a valid ppid of an existing patron
  *   $barcode: the barcode of the patron
  *   $json: the patron data, see function wms_create($barcode,$json) for an example
  *
  * this functions uses TWIG to transform the simple $json in the complicated $scim_json
  * which is used by function update_patron 
  
  * returns TRUE or FALSE
  */
  public function wms_update($ppid, $barcode, $json) {
    $json['extra'] = array(
    'country' => $this->get_countrycode($json['address']['country']),
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

    $this->update_patron($ppid,$scim_json);
    //file_put_contents('form_response.json',json_encode($patron->update, JSON_PRETTY_PRINT));

    return array_key_exists('id',$this->update) ? TRUE : FALSE;
  }

  /*
  * activates a new customer in WMS
  * meaning that blocked is set to FALSE and verified to TRUE
  *
  * returns TRUE or FALSE
  */
  public function wms_activate($ppid, $barcode, $json){
    //calculate expiry date
    $expDate = ($json['services']['membershipPeriod'] == "week") ? date('Y-m-d\TH:i:s\Z', strtotime("+9 days")) : date('Y-m-d\TH:i:s\Z', strtotime("+1 year"));
    $json['extra'] = array(
    'barcode' => $barcode,
    'date' => date("Y-m-d"),
    'expDate' => $expDate,
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

    $this->update_patron($ppid,$scim_json);
    //file_put_contents('form_response.json',json_encode($patron->update, JSON_PRETTY_PRINT));

    return array_key_exists('id',$patron->update) ? TRUE : FALSE;
  }

  public function update_patron($ppid, $scim_json) {
    //$ppid must be the value of the "id" key in scim json

    //authorization
    $this->update_headers['Authorization'] = $this->get_access_token_authorization('SCIM');

    $header_array = [];
    foreach ($this->update_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->update_url.'/'.$ppid);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->update_method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $scim_json);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    curl_close($curl);


    if ($error_number) {
      //return info in json format
      $result = '{"Curl_errno": "'.$error_number.'", "Curl_error": "'.curl_error($curl).'"}';
      $this->errors['curl'] = json_decode($result,TRUE);
      return false;
    }
    else {
      //store result in this object as an array
      $this->update = json_decode($result,TRUE);

      return TRUE;
    }
  }


  /*
  * generates a barcode
  * from a sha26 hash in which letters are replaced by numbers
  * all barcodes start with 90
  *
  */
  private function new_barcode($userName) {
    $hash = substr(hash('sha256',$userName.time()), 0, 20);
    $chars = str_split($hash);
    $newhash = '';
    foreach ($chars as $c) {
      $num = ord($c);
      if (($num > 47) && ($num < 58)) {
        //numbers are ok
        $newhash .= $c;
      }
      else if (($num > 64) && ($num < 91)) {
        //uppercase letters: "translate" to a number
        $newhash .= strval($num - 64);
      }
      else if (($num > 96) && ($num < 123)) {
        //lowercase letters: "translate" to a number
        $newhash .= strval($num - 96);
      }
      //else: skip the others
    }
    //put '90' before the first 8 characters, making it very possible that the resulting barcode is not unique...
    return '90'.substr($newhash, 0, 8);
  }

  /*
  * checks whether the barcode is already used in WMS
  *
  * returns TRUE or FALSE
  */
  private function wms_barcode_exists($barcode){
    $search = '{"schemas": ["urn:ietf:params:scim:api:messages:2.0:SearchRequest"], '.
    '"filter": "External_ID eq \"'.$barcode.'\""}';
    $this->search_patron($search);
    return ($this->search["totalResults"] == 0) ? FALSE : TRUE;
  }

  /*
  * generates a new barcode (with function new_barcode)
  * checks whether it already exists (with function wms_barcode_exists)
  *
  * if not returns the barcode as a unique new barcode
  */
  public function wms_new_barcode($userName) {
    $barcode = $this->new_barcode($userName);
    //check max 20 times in WMS whether the barcode already exists
    $max_repeats = 20;
    $repeat = 0;
    while ($this->wms_barcode_exists($barcode) && ($repeat < $max_repeats)) {
      $repeat++;
      $barcode = $this->new_barcode($json['id']['userName']);
    }
    if ($repeat >= $max_repeats) $barcode = '';
    return $barcode;
  }

  /*   function wms_create($barcode,$json) {
  *
  * creates a new customer in WMS
  * blocked is set to TRUE and verified to FALSE, see function wms_activate
  * patron type is set to "website",

  * parameters:
  *    $barcode: a new barcode in WMS
  *    $json: an associated array containg the data of a person, i.e.

  {
  "person": {
  "firstName": "Frits",
  "lastName": "van Latum",
  "email": "f.a.vanlatum@vlfg.nl",
  "dateBirth": "1991-11-11",
  "gender": "male",
  "tel": "061a345678"
  },
  "id": {
  "userName": "f.a.vanlatum@vlfg.nl",
  "password": "Some9password",
  "confpw": "Some9password"
  },
  "inst": {
  "instType": "court",
  "instName": "VLFG",
  "research": "peace",
  "to_be_defined": "to_be_defined"
  },
  "address": {
  "address1": "Straat 1",
  "postcode": "2806 CG",
  "city": "Gouda",
  "state": "Zuid Holland",
  "country": "Netherlands"
  },
  "services": {
  "membership": "Yes",
  "membershipPeriod": "year",
  "receiveAlerts": "Yes",
  "alertSubjects": {
  "pubIntLaw": ["Food; Health", "something"],
  "privIntLaw": [],
  "munCompLaw": [],
  "special": [],
  "other": []
  }
  }
  }

  *
  * uses function create_patron with the CURL stuff
  *
  * returns the new ppid or an empty string
  */
  public function wms_create($barcode,$json) {
    $ppid = '';
    $json['extra'] = array(
    'barcode' => $barcode,
    'country' => $this->get_countrycode($json['address']['country']),
    'date' => date("Y-m-d"),
    'expDate' => date('Y-m-d\TH:i:s\Z'),
    'patron_type' => $this->initial_patron_type,
    /*
     de gebruiker wordt direct actief, als dat anders moet kan wms_activate gebruikt worden
     om een gebruiker te activeren
    */
    'blocked' => 'false',
    'verified' => 'true'
    );
    //file_put_contents('form.json',json_encode($json, JSON_PRETTY_PRINT));

    $loader = new Twig_Loader_Filesystem(__DIR__);
    $twig = new Twig_Environment($loader, array(
    //specify a cache directory only in a production setting
    //'cache' => './compilation_cache',
    ));
    $scim_json = $twig->render('./idm_templates/scim_create_template.json', $json);
    file_put_contents('form_scim.json',json_encode($scim_json, JSON_PRETTY_PRINT));

    #$this->create_patron($scim_json);
    //file_put_contents('form_response.json',json_encode($patron->create, JSON_PRETTY_PRINT));

    return array_key_exists('id',$this->create) ? $this->create['id'] : '';
  }

  /*
  For a user which wants to use the Circulation portion of WorldShare Management Services we reccomend at least the following fields:

  givenName
  familyName
  email
  circulationInfo section
  barcode
  borrowerCategory
  homeBranch
  */
  private function create_patron($scim_json) {

    //authorization
    $this->create_headers['Authorization'] = $this->get_access_token_authorization('SCIM');

    $header_array = [];
    foreach ($this->create_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }
//echo json_encode($header_array,JSON_PRETTY_PRINT);
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->create_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_POST, $this->create_POST);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $scim_json);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    curl_close($curl);


    if ($error_number) {
      //return info in json format
      $result = '{"Curl_errno": "'.$error_number.'", "Curl_error": "'.curl_error($curl).'"}';
      $this->errors['curl'] = json_decode($result,TRUE);
      return false;
    }
    else {
      //store result in this object as an array
      $this->create = json_decode($result,TRUE);

      return TRUE;

    }
  }

  /*
  * gets a 2 letter country code according to ISO 3166-1 alpha-2
  * WMS requires this code instead of the name of the country
  * codes are in a separate file country2code.php
  */
  private function get_countrycode($country) {
    require(__DIR__.'/country2code.php');
    return array_key_exists($country,$codeOfCountry) ? $codeOfCountry[$country] : '';
  }

}


