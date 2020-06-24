<?php

/* 
The class IDM_Service contains the following functions 

read_patron_ppid: gets patron data with a given ppid
search_patron: search a patron in WMS using a SCIM search string, used by read_patron_barcode and barcode_exists
read_patron_barcode: gets patron data with a given barcode
barcode_exists: checks whether a barcode already is used in WMS

generate_barcode: generates a 10 digit barcode
new_barcode: generates a new and not already existing barcode in WMS, uses generate_barcode and barcode_exists


json2scim_new: generates SCIM json with new user data, calls create_patron
create_patron: creates a patron in WMS

json2scim_update: generates SCIM json with changed user data, calls update_patron
json2scim_activate: generates SCIM json in order to activate a user, calls update_patron
update_patron: updates a patron in WMS

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
          /*replace long keys*/
          $result = str_replace([
              "urn:mace:oclc.org:eidm:schema:persona:additionalinfo:20180501",
              "urn:mace:oclc.org:eidm:schema:persona:correlationinfo:20180101",
              "urn:mace:oclc.org:eidm:schema:persona:persona:20180305",
              "urn:mace:oclc.org:eidm:schema:persona:wsillinfo:20180101",
              "urn:mace:oclc.org:eidm:schema:persona:wmscircpatroninfo:20180101",
              "urn:mace:oclc.org:eidm:schema:persona:messages:20180305"],
              ['additionalinfo','correlationinfo','persona','wsillinfo','wmscircpatroninfo','messages'],$result);
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


  /*
  * checks whether the barcode is already used in WMS
  *
  * returns TRUE or FALSE
  */
  private function barcode_exists($barcode){
    $search = '{"schemas": ["urn:ietf:params:scim:api:messages:2.0:SearchRequest"], '.
    '"filter": "External_ID eq \"'.$barcode.'\""}';
    $this->search_patron($search);
    return ($this->search["totalResults"] == 0) ? FALSE : TRUE;
  }


  /*
  * generates a barcode
  * from a sha26 hash in which letters are replaced by numbers
  * all barcodes start with 90
  *
  */
  private function generate_barcode($userName) {
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
  * generates a new barcode (with function generate_barcode)
  * checks whether it already exists (with function barcode_exists)
  *
  * if not returns the barcode as a unique new barcode
  */
  public function new_barcode($userName) {
    $barcode = $this->generate_barcode($userName);
    //check max 20 times in WMS whether the barcode already exists
    $max_repeats = 20;
    $repeat = 0;
    while ($this->barcode_exists($barcode) && ($repeat < $max_repeats)) {
      $repeat++;
      $barcode = $this->generate_barcode($userName);
    }
    if ($repeat >= $max_repeats) {
      return FALSE;
    }
    else {
      return $barcode;
    }
  }


  public function create_patron($scim_json) {

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
      //for debugging:
      //file_put_contents('testError.json',json_encode($this,JSON_PRETTY_PRINT));
      return FALSE;
    }
    else {
      //store result in this object as an array
      $this->create = json_decode($result,TRUE);
      //for debugging: especially because a valid response might have valid error messages
      //file_put_contents('testOk.json',json_encode($this,JSON_PRETTY_PRINT));

      if (array_key_exists('id',$this->create)) {
        return $this->create['id'];
      }
      else {
        return FALSE;
      }
    }
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


}


