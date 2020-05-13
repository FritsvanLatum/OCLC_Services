<?php
require_once __DIR__.'/OCLC_Service.php';

/**
* A class that represents collection management
*/
class Collection_Management_Service extends OCLC_Service {

  //please note that the calculation of the authorization header uses the "http://..." url
  private $collman_url_auth = "https://circ.sd00.worldcat.org/LHR/";
  //the url that the service needs to get the data is the "https://..." url
  private $collman_url = "https://circ.sd00.worldcat.org/LHR/";

  private $collman_method = 'GET';
  private $collman_params = ['q' => ''];
  /*application/atom+xml
application/atom+json
application/xml
application/json*/
  private $collman_headers = ['Accept' => 'application/xml'];
  private $ocn = '';
  public $collman_xml = null;
  public $collman = null;

  public function __construct($key_file) {
    parent::__construct($key_file);
  }

  public function __toString(){
    //create an array and return json_encoded string
    $json = parent::__toString();

    $json['collman_url_auth'] = $this->collman_url_auth;
    $json['collman_url'] = $this->collman_url;
    $json['collman_headers'] = $this->collman_headers;
    $json['collman_params'] = $this->collman_params;
    $json['ocn'] = $this->ocn;
    $json['collman'] = $this->collman;
    $json['collman_xml'] = $this->collman_xml;
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function collman_str($type) {
    
    if ($type == 'json') return $this->__toString();
    if ($type == 'xml') return $this->collman_xml;
    if ($type == 'html') {
      $str = str_replace(array('<','>'), array('&lt;','&gt;'), $this->collman_xml);
      $str = '<pre>'.$str.'</pre>';
      return $str;
    }
    return json_encode($this->collman, JSON_PRETTY_PRINT);
  }

  public function get_lhrs_of_ocn($ocn) {
    //?q=oclc:1125981646
    $this->ocn = $ocn;
    return $this->get_lhrs_query('oclc:'.$ocn);
  }

  public function get_lhrs_query($query) {
    //$query has to be a CQL query
    $this->collman_params['q'] = $query;
    $av_url = $this->collman_url.'?'.http_build_query($this->collman_params);
    $av_url_auth = $this->collman_url_auth.'?'.http_build_query($this->collman_params);

    //authorization
    $this->collman_headers['Authorization'] = $this->get_auth_header($av_url_auth,$this->collman_method);
    $header_array = [];
    foreach ($this->collman_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $av_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    //file_put_contents($ocn.'.xml',$result);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','get_lhrs_query','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','get_lhrs_query',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','get_lhrs_query','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','get_lhrs_query',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','get_lhrs_query',"Result but still cUrl error [$error_number]: $error_msg");
        }
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        //$result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->formatOutput = TRUE;
        $xmlDoc->loadXML($result);
        $this->collman_xml = $xmlDoc->saveXML();
        $this->collman = $this->xml2json($xmlDoc,[]);
        return TRUE;
      }
    }
  }

}

