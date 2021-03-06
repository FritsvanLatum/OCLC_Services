<?php
require_once __DIR__.'/OCLC_Service.php';

/**
* A class that represents a pulllist
*/
class Discovery_Service extends OCLC_Service {


  private $read_url = "https://beta.worldcat.org/discovery/bib/data";
  public $read_headers = ['Accept' => 'application/json'];
  public $record_xml;
  public $record;

  private $search_url = "https://beta.worldcat.org/discovery/bib/search";
  public $search_params = [];
  private $search_method = 'GET';
  private $search_headers = [];
  public $search_result = [];
  public $list = null;

  public function __construct($key_file) {
    parent::__construct($key_file);
  }

  public function __toString(){
    $json = parent::__toString();

    //create an array and return json_encoded string
    $json['read_url'] = $this->read_url;
    $json['read_headers'] = $this->read_headers;
    $json['record'] = $this->record;
    $json['record_xml'] = $this->record_xml;

    $json['search_url'] = $this->search_url;
    $json['search_params'] = $this->search_params;
    $json['search_method'] = $this->search_method;
    $json['search_headers'] = $this->search_headers;
    $json['search_result'] = $this->search_result;

    $json['list'] = $this->list;
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function wcds_read_record($ocn){
    $this->read_headers['Authorization'] = $this->get_access_token_authorization('WorldCatDiscoveryAPI');

    $header_array = [];
    foreach ($this->read_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    $url = $this->read_url.'/'.$ocn;

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
        //which response format
        /*
        application/rdf+xml
        text/plain
        text/turtle
        application/ld+json
        application/json
        */
        if (($this->read_headers['Accept'] == 'application/ld+json') || ($this->read_headers['Accept'] == 'application/json')) {
          $received = json_decode($result,TRUE);
          $json_errno = json_last_error();
          $json_errmsg = json_last_error_msg();
          if ($json_errno == JSON_ERROR_NONE) {
            //store result in this object as an array
            $this->record = $received;
            return TRUE;
          }
          else {
            $this->log_entry('Error','read_patron_ppid',"json_decode error [$json_errno]: $json_errmsg");
            return FALSE;
          }
        }
        else if ($this->read_headers['Accept'] == 'application/rdf+xml') {
          //$result = str_replace(array("\n", "\r", "\t"), '', $result);
          //$result = trim(str_replace('"', "'", $result));  //??
          
          $xmlDoc = new DOMDocument();
          $xmlDoc->preserveWhiteSpace = FALSE;
          $xmlDoc->formatOutput = TRUE;
          $xmlDoc->loadXML($result);
          $this->record_xml = $xmlDoc->saveXML();
          $this->record = $this->xml2json($xmlDoc,[]);
          return TRUE;
        }
        else {
          $result = str_replace(array("\n", "\r", "\t"), '', $result);
          $result = trim(str_replace('"', "'", $result));
          $this->record = $result;
          return TRUE;
        }
      }
    }
  }



  public function wcds_search_request($headers,$params) {

    $this->read_headers['Authorization'] = $this->get_access_token_authorization('WorldCatDiscoveryAPI');
    foreach ($headers as $k => $v) $this->search_headers[$k] = $v;

    $header_array = [];
    foreach ($this->read_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }



    foreach ($params as $k => $v) $this->search_params[$k] = $v;

    $urlparts = array();
    foreach ($this->search_params as $k => $v) {
      if (is_array($v)) {
        foreach ($v as $w) $urlparts[] = $k.'='.urlencode($w);
      }
      else {
        $urlparts[] = $k.'='.urlencode($v);
      }
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $this->search_url.'?'.implode('&',$urlparts));

    //echo '<pre>'.$this->search_url.'?'.implode('&',$urlparts).'</pre>';

    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    //echo 'Result: '.$result;
    $error_number = curl_errno($curl);

    if ($error_number) {
      $result = "Error: ".$error_number.": ".curl_error($curl)."\n".$result;
      echo "Error: $result";
    }
    curl_close($curl);
    //file_put_contents("result.json",$result);
    $result = json_decode($result,TRUE);
    $this->search_result = $result;

    //debug:

  }

  public function wcds_db_list() {

    $this->search_headers['Authorization'] = $this->get_access_token_authorization('WorldCatDiscoveryAPI');

    $header_array = [];
    foreach ($this->search_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'https://beta.worldcat.org/discovery/database/list');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);

    $error_number = curl_errno($curl);

    if ($error_number) {
      $result = "Error: ".$error_number.": ".curl_error($curl)."\n".$result;
      echo "Error: $result";
    }
    curl_close($curl);
    //file_put_contents("result.json",$result);
    $result = json_decode($result,TRUE);
    $this->list = $result;

    //debug:

  }
}

