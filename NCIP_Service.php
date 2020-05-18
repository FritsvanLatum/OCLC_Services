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

  public $response_json = null;
  public $response_xml = null;

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
    
    $json['response_xml'] = $this->response_xml;
//    $json['request_xml'] = $this->request_xml;
//    $json['cancel_xml'] = $this->cancel_xml;
//    $json['renew_xml'] = $this->renew_xml;

    $json['response_json'] = $this->response_json;
//    $json['request'] = $this->request;
//    $json['cancel'] = $this->cancel;
//    $json['renew'] = $this->renew;


    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function response_str($type = '') {
    
    if ($type == 'json') return json_encode($this->response_json, JSON_PRETTY_PRINT);  
    if ($type == 'xml') return $this->response_xml;
    if ($type == 'html') {
      $str = str_replace(array('<','>'), array('&lt;','&gt;'), $this->response_xml);
      $str = '<pre>'.$str.'</pre>';
      return $str;
    }
    return $this->__toString();
 }

  private function send_ncip_request_ppid($ppid, $xml) {
    $this->response_json = null;
    $this->response_xml = null;
    //authorization
    $this->ncip_headers['Authorization'] = $this->get_access_token_authorization('WMS_NCIP',$ppid);
    //headers
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
      $this->log_entry('Error','send_ncip_request_ppid','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','send_ncip_request_ppid',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','send_ncip_request_ppid','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','send_ncip_request_ppid',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','send_ncip_request_ppid',"Result but still cUrl error [$error_number]: $error_msg");
        }
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        //$result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->formatOutput = TRUE;
        $xmlDoc->loadXML($result);
        $this->response_xml = $xmlDoc->saveXML();
        $options = array();
        $this->response_json = $this->xml2json($xmlDoc,$options);
        return TRUE;
      }
    }
  }


  /*
  Functions from staff Profile, authorization via staff ppid
  */
  public function checkout($user_barcode, $item_barcode) {
    //WMS_NCIP
    $xml = file_get_contents(__DIR__.'/ncip_templates/checkout_request_template.xml');
    $xml = str_replace('{{user_barcode}}', $user_barcode , $xml);
    $xml = str_replace('{{item_barcode}}', $item_barcode , $xml);
    //file_put_contents('test.xml',$xml);
    return $this->send_ncip_request_ppid(NULL, $xml);
  }

  public function checkin_barcode($barcode) {
    //WMS_NCIP
    $xml = file_get_contents(__DIR__.'/ncip_templates/checkin_request_template.xml');
    $xml = str_replace('{{barcode}}', $barcode , $xml);
    //file_put_contents('test.xml',$xml);
    return $this->send_ncip_request_ppid(NULL, $xml);
  }

  /*
  Functions from Patron Profile, authorization via ppid of patron 
  */

  public function lookup_patron_ppid($ppid) {
    //WMS_NCIP
    $xml = file_get_contents(__DIR__.'/ncip_templates/lookup_request_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    //file_put_contents('test_NCIP_lookup.xml',$xml);
    return $this->send_ncip_request_ppid($ppid, $xml);
  }

  public function request_biblevel($ppid, $ocn) {
    $xml = file_get_contents(__DIR__.'/ncip_templates/hold_request_biblevel_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $xml = str_replace('{{ocn}}', $ocn , $xml);
    return $this->send_ncip_request_ppid($ppid, $xml);
  }

  public function request_itemlevel($ppid, $barcode) {
    $xml = file_get_contents(__DIR__.'/ncip_templates/hold_request_itemlevel_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $xml = str_replace('{{barcode}}', $barcode , $xml);
    //file_put_contents('test_NCIP_request_request.xml',$xml);
    return $this->send_ncip_request_ppid($ppid, $xml);
  }

  public function cancel_request($ppid, $request_id) {
    $xml = file_get_contents(__DIR__.'/ncip_templates/cancel_request_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $xml = str_replace('{{request_id}}', $request_id , $xml);
    //file_put_contents('test_NCIP_cancel_request.xml',$xml);
    return $this->send_ncip_request_ppid($ppid, $xml);
  }

  public function renew_item_of_patron($ppid, $itemid) {
    $xml = file_get_contents(__DIR__.'/ncip_templates/renew_item_request_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    $xml = str_replace('{{itemid}}', $itemid , $xml);
    return $this->send_ncip_request_ppid($ppid, $xml);
  }

  public function renew_all_items_of_patron($ppid) {
    $xml = file_get_contents(__DIR__.'/ncip_templates/renew_all_request_template.xml');
    $xml = str_replace('{{ppid}}', $ppid , $xml);
    return $this->send_ncip_request_ppid($ppid, $xml);
  }

}
