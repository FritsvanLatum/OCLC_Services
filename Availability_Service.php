<?php
require_once './OCLC_Service.php';

/**
* A class that represents availability
*/
class Availability_Service extends OCLC_Service {

  //please note that the calculation of the authorization header uses the "http://..." url
  private $avail_url_auth = "http://worldcat.org/circ/availability/sru/service";
  //the url that the service needs to get the data is the "https://..." url
  private $avail_url = "https://worldcat.org/circ/availability/sru/service";

  private $avail_method = 'GET';
  private $avail_params = ['x-registryId' => '',
  'query' => '',
  'x-return-group-availability' => 0];
  private $avail_headers = ['Accept' => 'text/xml'];
  private $ocn = '';
  public $avail_xml = null;
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
    $json['ocn'] = $this->ocn;
    $json['avail'] = $this->avail;
    $json['avail_xml'] = $this->avail_xml;
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function get_availabilty_of_ocn($ocn) {
    $this->ocn = $ocn;
    return $this->get_availabilty_query('no:ocm'.$ocn);
  }

  public function get_availabilty_query($query) {
    //$query has to be a CQL query
    $this->avail_params['query'] = $query;
    $av_url = $this->avail_url.'?'.http_build_query($this->avail_params);
    $av_url_auth = $this->avail_url_auth.'?'.http_build_query($this->avail_params);

    //authorization
    $this->avail_headers['Authorization'] = $this->get_auth_header($av_url_auth,$this->avail_method);
    $header_array = [];
    foreach ($this->avail_headers as $k => $v) {
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
        $this->avail_xml = $result;
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        $result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->loadXML($result);
        $this->avail = $this->xml2json($xmlDoc,[]);
        return TRUE;
      }
    }
  }

/*
  returns an array of holdings
*/
  public function get_circulation_info($ocn) {
    $holdings = array();
    $found = FALSE;
    if ($this->ocn == $ocn) {
      $found = ($this->avail !== null);
    } 
    else {
      $found = $this->get_availabilty_of_ocn($ocn);
    }
    if ($found) {
      //check?
      $holdings = $this->avail['searchRetrieveResponse'][0]['records'][0]['record'][0]['recordData'][0]['opacRecord'][0]['holdings'];
    }
    return $holdings;
  }
}

