<?php

require_once './OCLC_PPL_Service.php';

/**
* A class that represents the NCIP Service
*/
class NCIP_Service extends OCLC_PPL_Service{

  //urls are extended in __construct
  public $ncip_url = "share.worldcat.org/ncip";

  private $auth_url = 'http://www.worldcat.org/wskey/v2/hmac/v1';
  private $auth_method = 'GET';
  private $auth_headers = ['Accept: application/json'];

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
    $token_authorization = $this->get_access_token_authorization('WMS_NCIP');
    if (strlen($token_authorization) > 0) {
      array_push($this->lookup_headers,$token_authorization);
    }
    else {
      $this->log_entry('Error','lookup_patron_ppid','No token authorization header created!');
    }

    //doe iets slims met het invullen van het ppid in lookup.xml
    //for now:
    $xml = file_get_contents('ncip_templates/frits.xml');
    
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->lookup_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->lookup_headers);
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


}
