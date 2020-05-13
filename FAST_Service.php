<?php
require_once __DIR__.'/OCLC_Service.php';

/**
* A class that represents the fast Service
*/
class FAST_Service extends OCLC_Service {

  //urls are extended in __construct
  private $get_data_url = "http://id.worldcat.org/fast";
  //public $response_format = 'fast.json';//'justlinks.json'; //'fast.json' ;
  private $get_data_method = 'GET';

  public $search_headers = [];
  public $search_params = [];

  public $fast_no = '';
  public $fast_record = null;
  public $fast_record_xml = null;


  public function __construct() {

  }

  public function __toString(){

    //create an array and return json_encoded string
    $json = [];

    $json['get_data_url'] = $this->get_data_url;
    //$json['response_format'] = $this->response_format;
    $json['get_data_method'] = $this->get_data_method;

    $json['search_headers'] = $this->search_headers;
    $json['search_params'] = $this->search_params;

    $json['fast_no'] = $this->fast_no;
    $json['fast_record'] = $this->fast_record;
    //$json['fast_record_xml'] = $this->fast_record_xml;

    return json_encode($json, JSON_PRETTY_PRINT);
  }


  public function fast_get_data($no) {
    $this->fast_no = $no;

    $fast_url = $this->get_data_url.'/'.$no.'/'; //.$this->response_format;
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $fast_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->get_data_method);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','fast_get_data','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','fast_get_data',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','fast_get_data','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','fast_get_data',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','fast_get_data',"Result but still cUrl error [$error_number]: $error_msg");
        }
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        //$result = trim(str_replace('"', "'", $result));  //??
        
        $xmlDoc = new DOMDocument();
        $xmlDoc->preserveWhiteSpace = FALSE;
        $xmlDoc->formatOutput = TRUE;
        $xmlDoc->loadXML($result);
        $this->fast_record_xml = $xmlDoc->saveXML();
        $options = array();
        $this->fast_record = $this->xml2json($xmlDoc,$options);
        return TRUE;
      }
    }
  }
}

