<?php

require_once './OCLC_Service.php';
require_once './vendor/autoload.php';

/**
* A class that represents the NCIP Service
*/
class NCIP_Service extends OCLC_Service{

  //urls are extended in __construct
  public $ncip_url = "share.worldcat.org/ncip";

  private $auth_url = 'http://www.worldcat.org/wskey/v2/hmac/v1';
  private $auth_method = 'GET';
  private $auth_headers = ['Accept' => 'application/json'];

  public $lookup_url = "";
  public $lookup_headers = ['Accept: application/xml',
                            'Content-Type: application/xml'
                            ];
  public $lookup_method = 'POST';

  public $patron = null;

  public function __construct($key_file) {
    parent::__construct($key_file);

    //https://{institution-identifier}.share.worldcat.org/idaas/scim/v2/Users/{id}
    $this->lookup_url = 'https://'.$this->institution.'.'.$this->ncip_url.'/circ-patron';
  }

  public function __toString(){
    
    //create an array and return json_encoded string
    $json = parent::__toString();
    
    $json['ncip_url'] = $this->ncip_url;
    $json['lookup_url'] = $this->lookup_url;
    $json['lookup_headers'] = $this->lookup_headers;
    $json['lookup_method'] = $this->lookup_method;
    
    $json['patron'] = $this->patron;


    return json_encode($json, JSON_PRETTY_PRINT);
  }


  public function lookup_patron_ppid($id) {
    //WMS_NCIP
    //authorization
    $this->lookup_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP');

    //doe iets slims met het invullen van het ppid in ./ncip_templates/lookup_template.xml
    //for now:
    $xml = file_get_contents('./ncip_templates/lookup_frits.xml');
    
    $header_array = [];
    foreach ($this->lookup_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->lookup_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->lookup_method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','lookup_patron_ppid','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','lookup_patron_ppid',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','lookup_patron_ppid','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','lookup_patron_ppid',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','lookup_patron_ppid',"Result but still cUrl error [$error_number]: $error_msg");
        }
        
        $this->patron = $result;
      }
    }
  }

  public function renew_item_of_patron($ppid, $itemid) {
    //WMS_NCIP
    
    //test the service with $itemid that not is lent by $ppid, with $itemid that is reserved by somebody else, etc.
    //if the service sends back adequate answers then skip the checks below

      //first check whether $itemid is lent to $ppid
      //then check whether it is allowed to renew the item, if not: message to the user
      // if everything ok: 
    
    //authorization
    $this->lookup_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP');

    //doe iets slims met het invullen van het ppid en ietmid in ./ncip_templates/renew_template.xml
    $xml = '';
        
    $header_array = [];
    foreach ($this->lookup_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->lookup_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->lookup_method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','lookup_patron_ppid','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','lookup_patron_ppid',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','lookup_patron_ppid','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','lookup_patron_ppid',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','lookup_patron_ppid',"Result but still cUrl error [$error_number]: $error_msg");
        }
        
        $this->patron = $result;
      }
    }
  }

  public function renew_all_items_of_patron($ppid) {
     //see remarks in renew_item_of_patron
    
   
  }

/*
TODO: 
Request Item

    Bibliographic Level
    Item Level

Update Request Item
Cancel Request Item
*/
}
