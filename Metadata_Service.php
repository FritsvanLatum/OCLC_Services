<?php
require_once __DIR__.'/OCLC_Service.php';

/**
* A class that represents metadata (bib and lbd) services
*/
class Metadata_Service extends OCLC_Service {

  //please note that the calculation of the authorization header uses the "http://..." url
  private $metadata_url_auth = "https://worldcat.org/bib/data/";
  //the url that the service needs to get the data is the "https://..." url
  private $metadata_url = "https://worldcat.org/bib/data/";

  private $metadata_method = 'GET';
  private $metadata_params = [];

  /*this API has 2 Accept types:
    application/atom+xml (default)
    application/atom+json
  */
  public $metadata_headers = ['Accept' => 'application/atom+xml'];

  private $ocn = '';

  /*
  <?xml version="1.0" encoding="UTF-8"?>
  <entry xmlns="http://www.w3.org/2005/Atom">
    <content type="application/xml">
      <response xmlns="http://worldcat.org/rb" mimeType="application/vnd.oclc.marc21+xml">
        <record xmlns="http://www.loc.gov/MARC21/slim">
        ...
        </record>
      </response>
    </content>
    <id>http://worldcat.org/oclc/975369365</id>
    <link href="http://worldcat.org/oclc/975369365"/>
  </entry>
  
  $only_marc_record = TRUE means only the <record> subelement is returned
  $only_marc_record = FALSE means the compley xml is returned (<entry> element)
  */
  private $only_marc_record = TRUE;
  public $metadata_xml = null;
  public $metadata_json = null;
  public $metadata = null;

  private $marc_template_file = './metadata_templates/LHR_template.marc';
  private $marcxml_template_file = './metadata_templates/LHR_template_marc.xml';

  public function __construct($key_file) {
    parent::__construct($key_file);
  }

  public function __toString(){
    //create an array and return json_encoded string
    $json = parent::__toString();

    $json['metadata_url_auth'] = $this->metadata_url_auth;
    $json['metadata_url'] = $this->metadata_url;
    $json['metadata_headers'] = $this->metadata_headers;
    $json['metadata_params'] = $this->metadata_params;
    $json['ocn'] = $this->ocn;
    $json['metadata'] = $this->metadata;
    $json['metadata_xml'] = $this->metadata_xml;
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function metadata_str($type) {
    if (strpos($this->metadata_headers['Accept'],'json')) {
      if ($type == 'json') return json_encode($this->metadata, JSON_PRETTY_PRINT);
      if ($type == 'xml') return '';
      if ($type == 'html') {
        $str = '<pre>'.json_encode($this->metadata, JSON_PRETTY_PRINT).'</pre>';
        return $str;
      }
      return $this->__toString();
    }
    else {
      //XML:
      if ($type == 'json') return json_encode($this->metadata, JSON_PRETTY_PRINT);
      if ($type == 'xml') return $this->metadata_xml;
      if ($type == 'html') {
        $str = str_replace(array('<','>'), array('&lt;','&gt;'), $this->metadata_xml);
        $str = '<pre>'.$str.'</pre>';
        return $str;
      }
      if ($type == 'marcxml') return $this->marcxml2namespace();
      if ($type == 'marchtml') {
        $str = str_replace(array('<','>'), array('&lt;','&gt;'), $this->marcxml2namespace());
        $str = '<pre>'.$str.'</pre>';
        return $str;
      }
      return $this->__toString();
    }
  }

  private function marcxml2namespace() {
    /*
    <record xmlns="http://www.loc.gov/MARC21/slim"> => <marc:record>
    </record> => </marc:record>
    leader => marc:leader
    controlfield => marc:controlfield
    datafield => marc:datafield
    subfield => marc:subfield
    */
    return str_replace(
      array('<?xml version="1.0"?>', '<record xmlns="http://www.loc.gov/MARC21/slim">', '</record>', 'leader','controlfield','datafield','subfield'),
      array('', '<marc:record>', '</marc:record>', 'marc:leader','marc:controlfield','marc:datafield','marc:subfield'),
      $this->metadata_xml);
  }
  
  public function get_bib_ocn($ocn) {
    $av_url = $this->metadata_url.$ocn;
    $av_url_auth = $this->metadata_url_auth.$ocn;

    //authorization
    $this->metadata_headers['Authorization'] = $this->get_auth_header($av_url_auth,$this->metadata_method);
    $header_array = [];
    foreach ($this->metadata_headers as $k => $v) {
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
    $curl_info = curl_getinfo($curl);
    curl_close($curl);
    if ($curl_info['http_code'] == '404') {
      $error_number = '404';
      $error_msg = 'http code 404 returned';
      $result = FALSE;
    }

    if ($result === FALSE) {
      $this->log_entry('Error','git_bib_ocn','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','git_bib_ocn',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','git_bib_ocn','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','git_bib_ocn',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','git_bib_ocn',"Result but still cUrl error [$error_number]: $error_msg");
        }
        //$result = str_replace(array("\n", "\r", "\t"), '', $result);
        //$result = trim(str_replace('"', "'", $result));  //??
        
        if (strpos($this->metadata_headers['Accept'],'json')) {
          $received = json_decode($result,TRUE);
          $json_errno = json_last_error();
          $json_errmsg = json_last_error_msg();
          if ($json_errno == JSON_ERROR_NONE) {
            //store result in this object as an array
            $this->metadata = $received;
            $this->metadata_xml = '';
            return TRUE;
          }
          else {
            $this->log_entry('Error','get_bib_ocn',"json_decode error [$json_errno]: $json_errmsg");
            return FALSE;
          }
        }
        else {
          $xmlDoc = new DOMDocument();
          $xmlDoc->preserveWhiteSpace = FALSE;
          $xmlDoc->formatOutput = TRUE;
          $xmlDoc->loadXML($result);
          $this->metadata = $this->xml2json($xmlDoc,[]);

          $xml_rec = new DOMDocument();
          $xml_rec->preserveWhiteSpace = FALSE;
          $xml_rec->formatOutput = TRUE;
          $nodes = $xmlDoc->getElementsByTagName('record');
          if ($nodes->length > 0) {
            $node = $nodes->item(0);
            $xml_rec->appendChild($xml_rec->importNode($node, true));
          }
          if ($this->only_marc_record) {
            $this->metadata_xml = $xml_rec->saveXML();
            $this->metadata_json = $this->xml2json($xml_rec,[]);
          }
          else {
            $this->metadata_xml = $xmlDoc->saveXML();
          }
          return TRUE;
        }
      }
    }
  }

  public function json2marc($type = 'marc') {
    $result = FALSE;
    if (strpos($this->metadata_headers['Accept'],'atom+json') > 0) {
      if (array_key_exists("entries",$this->metadata)) {
        if (count($this->metadata["entries"]) > 0) {
          if (array_key_exists("content",$this->metadata["entries"][0])) {
            if (array_key_exists("id",$this->metadata["entries"][0]["content"])) {
              //this->metadata must be a very kosher atom+xml structure
              $json = $this->metadata["entries"][0]["content"];

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
              
              //"receiptStatus": "RECEIVED_AND_COMPLETE_OR_CEASED",
              if (array_key_exists(strtolower($json["receiptStatus"]), $this->acq_status)) $json["acq_status"] = $this->acq_status[strtolower($json["receiptStatus"])];
              
              /*"holding": [
                    {
                        "pieceDesignation": [
                            "09000006742580"
                        ],
                        "note": [],
                        "useRestriction": [],
                        "cost": [
                            {
                                "currency": "EUR",
                                "amount": 10300,
                                "qualifier": null*/
              
              $loader = new Twig_Loader_Filesystem(__DIR__);
              $twig = new Twig_Environment($loader, array(
              //specify a cache directory only in a production setting
              //'cache' => './compilation_cache',
              ));
              if ($type == 'marc') {
                $result = $twig->render($this->marc_template_file, $json);
              }
              else if ($type == 'xml') {
                $result = $twig->render($this->marcxml_template_file, $json);
              }
              //debug:
              //$this->metadata["entries"][0]["content"] = $json;
            }
          }
        }
      }
      
    }
    return $result;
  }

}

