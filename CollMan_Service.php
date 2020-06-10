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
  public $collman_headers = ['Accept' => 'application/xml'];
  private $code = '';
  public $collman_xml = null;
  public $collman = null;
  
  private $acq_status = [
  'unknown' => 0,
  'other_receipt_or_acquisition_status' => 1,
  'received_and_complete_or_ceased' => 2,
  'on_order' => 3,
  'currently_received' => 4,
  'not_currently_received' => 5,
  'external_access' => 6
  ];
  private $marc_template_file = './collman_templates/LHR_template.marc';
  private $marcxml_template_file = './collman_templates/LHR_template_marc.xml';

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
    $json['code'] = $this->code;
    $json['collman'] = $this->collman;
    $json['collman_xml'] = $this->collman_xml;
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function collman_str($type) {
    if (strpos($this->collman_headers['Accept'],'json')) {
      //header is application/atom+json or application/json
      if ($type == 'json') return json_encode($this->collman, JSON_PRETTY_PRINT);
      if ($type == 'xml') return '';
      if ($type == 'html') {
        $str = '<pre>'.json_encode($this->collman, JSON_PRETTY_PRINT).'</pre>';
        return $str;
      }
      return $this->__toString();
    }
    else {
      //header is application/atom+xml or application/xml
      if ($type == 'json') return json_encode($this->collman, JSON_PRETTY_PRINT);
      if ($type == 'xml') return $this->collman_xml;
      if ($type == 'html') {
        $str = str_replace(array('<','>'), array('&lt;','&gt;'), $this->collman_xml);
        $str = '<pre>'.$str.'</pre>';
        return $str;
      }
      return $this->__toString();
    }
  }

  public function get_lhrs_of_ocn($code) {
    //?q=oclc:1125981646
    $this->code = $code;
    return $this->get_lhrs_query('oclc:'.$code);
  }
  public function get_lhrs_of_barcode($code) {
    //?q=oclc:1125981646
    $this->code = $code;
    return $this->get_lhrs_query('barcode:'.$code);
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
    //file_put_contents($code.'.xml',$result);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    $curl_info = curl_getinfo($curl);
    curl_close($curl);
    if ($curl_info['http_code'] == '404') {
      $error_number = '404';
      $error_msg = 'http code 404 returned';
      $result = FALSE;
    }

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
        
        if (strpos($this->collman_headers['Accept'],'json')) {
          //header is application/atom+json or application/json
          $received = json_decode($result,TRUE);
          $json_errno = json_last_error();
          $json_errmsg = json_last_error_msg();
          if ($json_errno == JSON_ERROR_NONE) {
            //store result in this object as an array
            $this->collman = $received;
            $this->collman_xml = '';
            return TRUE;
          }
          else {
            $this->log_entry('Error','read_patron_ppid',"json_decode error [$json_errno]: $json_errmsg");
            return FALSE;
          }
        }
        else {
          //header is application/atom+xml or application/xml
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

  public function json2marc($type = 'mrk') {
    $result = FALSE;
    if (strpos($this->collman_headers['Accept'],'atom+json') > 0) {
      if (array_key_exists("entries",$this->collman)) {
        if (count($this->collman["entries"]) > 0) {
          if (array_key_exists("content",$this->collman["entries"][0])) {
            if (array_key_exists("id",$this->collman["entries"][0]["content"])) {
              //this->collman must be a very kosher atom+xml structure
              $json = $this->collman["entries"][0]["content"];

              //first find the LHR number
              //"id": "https:\/\/circ.sd00.worldcat.org\/LHR\/320076920?inst=57439"
              $parts = explode('?',$json["id"]);
              $parts = explode('/',$parts[0]);
              $json["lhr_id"] = end($parts);
              
              //"bib": "\/bibs\/975369365",
              $parts = explode('/',$json["bib"]);
              $json["ocn_id"] = end($parts);
              
              //"lastUpdateDate": "2020-05-08T05:17:29.700-04:00",
              $parts = explode('T',$json["lastUpdateDate"]);
              $date = $parts[0];
              $time = $parts[1];
              $parts = explode('-',$date);
              $json["yyyymmdd"] = implode('',$parts);
              $json["yymmdd"]=substr($json["yyyymmdd"], 2);
              
              
              //leader
              $json['leader'] = "00000nx  a2200121zi 4500";
              
              //008 00-05
              $json["f008"] = $json["yymmdd"];
              
              //008 06
              //"receiptStatus": "RECEIVED_AND_COMPLETE_OR_CEASED",
              if (array_key_exists(strtolower($json["receiptStatus"]), $this->acq_status)) { 
                $json["f008"] .= $this->acq_status[strtolower($json["receiptStatus"])];
              }
              else {
                $json["f008"] .= 'u';
              }
              
              //008 07
              $json["f008"] .= 'u';
              
              //008 08-11
              $json["f008"] .= '    ';
              
              //008 12
              $json["f008"] .= '8';
              
              //008 13-15
              $json["f008"] .= '   ';

              //008 16
              $json["f008"] .= '4';
              
              //008 17-19
              $json["f008"] .= '001';
              
              //008 20
              $json["f008"] .= 'u';
              
              //008 21
              $json["f008"] .= 'u';
              
              //008 22-24
              $json["f008"] .= 'und';

              //008 25
              $json["f008"] .= '0';
              
              //008 26-31
              $json["f008"] .= $json["yymmdd"];
              
              $loader = new Twig_Loader_Filesystem(__DIR__);
              $twig = new Twig_Environment($loader, array(
              //specify a cache directory only in a production setting
              //'cache' => './compilation_cache',
              ));
              if ($type == 'mrk') {
                $result = $twig->render($this->marc_template_file, $json);
              }
              else if ($type == 'marcxml') {
                $result = $twig->render($this->marcxml_template_file, $json);
              }
              else {
                $result = "No valid type, choose mrk or marcxml.";
              }
              //debug:
              //$this->collman["entries"][0]["content"] = $json;
            }
          }
        }
      }
      
    }
    return $result;
  }

}

