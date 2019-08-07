<?php

require_once './OCLC_Service.php';

/**
* A class that represents the KB Service
*/
class WorldCat_KB_Service extends OCLC_Service{

  //urls are extended in __construct
  public $search_url = "http://worldcat.org/webservices/kb/rest/entries/search";
  public $search_headers = ['Accept' => 'application/json',
  ];
  public $search_params = ['oclcnum' => '',
  'wskey' => ''
  ];
  public $search_method = 'GET';
  public $ocn = '';
  public $kb_record = null;

  public function __construct($key_file) {
    parent::__construct($key_file);
  }

  public function __toString(){

    //create an array and return json_encoded string
    $json = parent::__toString();

    $json['search_url'] = $this->search_url;
    $json['search_headers'] = $this->search_headers;
    $json['search_params'] = $this->search_params;
    $json['search_method'] = $this->search_method;

    $json['ocn'] = $this->ocn;
    $json['kb_record'] = $this->kb_record;


    return json_encode($json, JSON_PRETTY_PRINT);
  }


  public function search_kb_record($ocn) {
    $this->ocn = $ocn;
    $header_array = [];
    foreach ($this->search_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }
    $this->search_params['oclcnum'] = $ocn;
    $this->search_params['wskey'] = $this->wskey;
    $src_url = $this->search_url.'?'.http_build_query($this->search_params);
    //echo $src_url;
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $src_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->search_method);
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

        $received = json_decode($result,TRUE);
        $json_errno = json_last_error();
        $json_errmsg = json_last_error_msg();
        if ($json_errno == JSON_ERROR_NONE) {
          //store result in this object as an array
          $this->kb_record = $received;
          return TRUE;
        }
        else {
          $this->log_entry('Error','read_patron_ppid',"json_decode error [$json_errno]: $json_errmsg");
          return FALSE;
        }
      }
    }
  }

  public function getlink($ocn,$type = 'via') {
    $result = '';
    if ($this->ocn <> $ocn) $this->search_kb_record($ocn);
    foreach ($this->kb_record['entries'] as $entry) {
      foreach ($entry['links'] as $link) {
        if ($link['rel'] == $type) $result = $link['href'];
      }
    }
    return $result;
  }
}
