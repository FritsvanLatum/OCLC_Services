<?php

require_once __DIR__.'/OCLC_Service.php';
require_once __DIR__.'/vendor/autoload.php';

/**
* A class that represents the NCIP Service
*/
class NCIP_Service extends OCLC_Service{

  //urls are extended in __construct
  public $ncip_base_url = 'share.worldcat.org/ncip/circ-patron';

  private $auth_url = 'http://www.worldcat.org/wskey/v2/hmac/v1';
  private $auth_method = 'GET';
  private $auth_headers = ['Accept' => 'application/json'];

  public $ncip_url = "";
  public $ncip_headers = ['Accept' => 'application/xml',
                            'Content-Type'=> 'application/xml'
                            ];
  public $ncip_method = 'POST';

  public $patron = null;
  public $request = null;
  public $cancel = null;
  
  public $patron_xml = null;
  public $request_xml = null;
  public $cancel_xml = null;

  public function __construct($key_file) {
    parent::__construct($key_file);
    $this->ncip_url = 'https://'.$this->institution.'.'.$this->ncip_base_url;
  }

  public function __toString(){
    
    //create an array and return json_encoded string
    $json = parent::__toString();
    
    $json['ncip_base_url'] = $this->ncip_base_url;
    $json['ncip_url'] = $this->ncip_url;
    $json['ncip_headers'] = $this->ncip_headers;
    $json['ncip_method'] = $this->ncip_method;
    
    $json['patron_xml'] = $this->patron_xml;
    $json['request_xml'] = $this->request_xml;
    $json['cancel_xml'] = $this->cancel_xml;

    $json['patron'] = $this->patron;
    $json['request'] = $this->request;
    $json['cancel'] = $this->cancel;


    return json_encode($json, JSON_PRETTY_PRINT);
  }


  public function lookup_patron_ppid($ppid) {
    //WMS_NCIP
    //authorization
    $this->ncip_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP');

    $xml = file_get_contents(__DIR__.'/ncip_templates/lookup_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $header_array = [];
    foreach ($this->ncip_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->ncip_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->ncip_method);
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
        $this->patron_xml = $result;
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        $result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->loadXML($result);
        $options = array();
        $this->patron = $this->xml2json($xmlDoc,$options);
        return TRUE;
      }
    }
  }

  public function request_biblevel($ppid, $ocn) {
    //authorization
    $this->ncip_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP');

    $xml = file_get_contents(__DIR__.'/ncip_templates/request_biblevel_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $xml = str_replace('{{ocn}}', $ocn , $xml);
    file_put_contents(__DIR__.'/output_examples/test_NCIP_request_request.xml',$xml);
    $header_array = [];
    foreach ($this->ncip_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->ncip_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->ncip_method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','request_biblevel','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','request_biblevel',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','request_biblevel','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','request_biblevel',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','request_biblevel',"Result but still cUrl error [$error_number]: $error_msg");
        }
        $this->request_xml = $result;
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        $result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->loadXML($result);
        $options = array();
        $this->request = $this->xml2json($xmlDoc,$options);

        return TRUE;
      }
    }
  }

  public function cancel_request($ppid, $request_id) {
    //authorization
    $this->ncip_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP');

    $xml = file_get_contents(__DIR__.'/ncip_templates/cancel_request_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $xml = str_replace('{{request_id}}', $request_id , $xml);
    file_put_contents(__DIR__.'/output_examples/test_NCIP_cancel_request.xml',$xml);
    $header_array = [];
    foreach ($this->ncip_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->ncip_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->ncip_method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','cancel_request','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','cancel_request',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','cancel_request','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','cancel_request',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','cancel_request',"Result but still cUrl error [$error_number]: $error_msg");
        }
        $this->cancel_xml = $result;
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        $result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->loadXML($result);
        $options = array();
        $this->cancel = $this->xml2json($xmlDoc,$options);
       
        return TRUE;
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
    $this->ncip_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP');

    //doe iets slims met het invullen van het ppid en ietmid in ./ncip_templates/renew_template.xml
    $xml = '';
        
    $header_array = [];
    foreach ($this->ncip_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->ncip_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->ncip_method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','renew_item_of_patron','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','renew_item_of_patron',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','renew_item_of_patron','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','renew_item_of_patron',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','renew_item_of_patron',"Result but still cUrl error [$error_number]: $error_msg");
        }
        
        $this->patron = $result;
      }
    }
  }

  public function renew_all_items_of_patron($ppid) {
     //see remarks in renew_item_of_patron
    
   
  }

}
