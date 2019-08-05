<?php
require_once './OCLC_Service.php';

/**
* A class that represents availability
*/
class Availability_Service extends OCLC_PPL_Service {

  //https://worldcat.org/circ/availability/sru/service?x-registryId=57439&query=no:ocm1024083487
  private $avail_url_auth = "http://worldcat.org/circ/availability/sru/service";

  private $avail_url = "https://worldcat.org/circ/availability/sru/service";
  private $avail_method = 'GET';
  private $avail_params = array();
  private $avail_headers = ['Accept' => 'text/xml'];

  private $ocn = array();
  private $ns = array();
  public $avail = null;
  
  public function __construct($key_file) {
    parent::__construct($key_file);
    $this->avail_params['x-registryId'] = $this->institution;
  }

  public function __toString(){
    //create an array and return json_encoded string
    $json = parent::__toString();

    $json['avail_url_auth'] = $this->avail_url_auth;
    $json['avail_url'] = $this->avail_url;
    $json['avail_headers'] = $this->avail_headers;
    $json['avail_params'] = $this->avail_params;
    $json['avail'] = $this->avail;
    $json['ocn'] = $this->ocn;
    $json['ns'] = $this->ns;

    return json_encode($json, JSON_PRETTY_PRINT);
  }

  private function get_avail_auth_header($url,$method) {
    //get an authorization header
    //  with wskey, secret and if necessary user data from $config
    //  for the $method and $url provided as parameters

    $authorizationHeader = '';
    if ($this->wskey && $this->secret) {
      $user = null;
      $opts = null;
      $wskeyObj = new WSKey($this->wskey, $this->secret,$opts);
      $authorizationHeader = $wskeyObj->getHMACSignature($method, $url);
      $authorizationHeader = 'Authorization: '.$authorizationHeader;
    }
    else {
      $this->log_entry('Error','get_avail_auth_header','No wskey and/or no secret!');
    }
    return $authorizationHeader;
  }

  public function get_avail($ocn) {
    
    $this->ocn = $ocn;
    $this->avail_params['query']='no:ocm'.$ocn;
    $av_url = $this->avail_url.'?'.http_build_query($this->avail_params);
    $av_url_auth = $this->avail_url_auth.'?'.http_build_query($this->avail_params);

    //authorization
    $authorizationHeader = $this->get_avail_auth_header($av_url_auth,$this->avail_method);
    if (strlen($authorizationHeader) > 0) {
      array_push($this->avail_headers,$authorizationHeader);
    }
    else {
      $this->log_entry('Error','get_avail','No authorization header created!');
    }
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $av_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->avail_headers);
    
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    //file_put_contents($ocn.'.xml',$result);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','get_avail','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','get_avail',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','get_avail','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','get_avail',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','get_avail',"Result but still cUrl error [$error_number]: $error_msg");
        }
        
        $result = str_replace(array("\n", "\r", "\t"), '', $result);
        $result = trim(str_replace('"', "'", $result));

        $simpleXml = new SimpleXMLElement($result);
        $this->ns = $simpleXml->getDocNamespaces(TRUE,TRUE);
        foreach ($this->ns as $prefix => $namespace) {
          if (strlen($prefix) == 0) $prefix = 'x';
          $simpleXml->registerXPathNamespace($prefix,$namespace);
        }
        $this->avail = $simpleXml;
        return TRUE;
      }
    }
  }
  
  public function get_element_value($ocn,$element) {
    if (($this->ocn != $ocn) || empty($this->avail)) $this->get_avail($ocn);
    $matches = $this->avail->xpath('//'.$element);
    $result = array();
    foreach ($matches as $match) {
      $result[] = $match->__toString();
    }
    return $result;
  }
  
}

