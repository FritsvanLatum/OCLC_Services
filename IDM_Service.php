<?php

/* TODO

Zie in : /je_assets/php:

helpers.php: de wms_ functions voor zoeken, aanmaken en wijzigen van patrons kunnen hierin worden opgenomen
deze functies maken gebruik van Twig en de templates: scim_activate_template.json, scim_create_template.json, scim_update_template.json
voor zoeken op barcode staat de scim code in helpers.php zelf

de drie functies hebben een $json parameter. de data daarin worden eerst opgehaald uit de gebruikers database. Dat moet dus de 
Drupal user database worden.



*/


require_once './OCLC_PPL_Service.php';

/**
* A class that represents the IDM Service
*/
class IDM_Service extends OCLC_PPL_Service{

  //$read_url and search_url are extended in __construct
  public $idm_url = "share.worldcat.org/idaas/scim/v2/Users";

  public $read_url = "";
  public $read_headers = ['Accept: application/scim+json'];

  public $search_url = "";
  public $search_headers = ['Accept: application/scim+json',
  'Content-Type: application/scim+json'];
  public $search_method = 'POST';
  public $search_POST = true;

  public $create_url = "";
  public $create_headers = [
  'Content-Type: application/scim+json'];
  public $create_method = 'POST';
  public $create_POST = true;
  
  public $update_url = "";
  public $update_headers = [
  'Content-Type: application/scim+json'];
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
    
    $json['patron'] = $this->patron;
    $json['search'] = $this->search;
    $json['create'] = $this->create;
    $json['update'] = $this->update;


    return json_encode($json, JSON_PRETTY_PRINT);
  }


  public function read_patron_ppid($id) {
    //authorization
    $token_authorization = $this->get_access_token_authorization('SCIM');
    if (strlen($token_authorization) > 0) {
      array_push($this->read_headers,$token_authorization);
    }
    else {
      $this->log_entry('Error','read_patron_ppid','No token authorization header created!');
    }
    
    
    $url = $this->read_url.'/'.$id;
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->read_headers);
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

  public function get_barcode() {
    $barcode = '';
    if ($this->patron && array_key_exists('externalId', $this->patron)) {
      $barcode = $this->patron['externalId'];
    }
    return $barcode;
  }

  public function search_patron($search) {
    //authorization
    $token_authorization = $this->get_access_token_authorization('SCIM');
    array_push($this->search_headers,$token_authorization);

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->search_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->search_headers);
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
  For a user which wants to use the Circulation portion of WorldShare Management Services we reccomend at least the following fields:

  givenName
  familyName
  email
  circulationInfo section
  barcode
  borrowerCategory
  homeBranch
  */

  public function create_patron($scim_json) {

    //authorization
    $token_authorization = $this->get_access_token_authorization('SCIM');
    //echo $token_authorization;
    array_push($this->create_headers,$token_authorization);

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->create_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->create_headers);
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

      return $result;

    }
  }

  public function update_patron($ppid, $scim_json) {
    //$ppid must be the value of the "id" key in scim json
    
    //authorization
    $token_authorization = $this->get_access_token_authorization('SCIM');
    //echo $token_authorization;
    array_push($this->update_headers,$token_authorization);

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->update_url.'/'.$ppid);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->update_headers);
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

      return $result;
    }
  }
}
