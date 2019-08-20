<?php
require_once __DIR__.'/OCLC_Service.php';

/**
* A class that represents the VIAF Service
*/
class VIAF_Service extends OCLC_Service {

  //urls are extended in __construct
  private $get_data_url = "http://www.viaf.org/viaf";
  public $response_format = 'justlinks.json'; //'viaf.json' ;
  private $get_data_method = 'GET';

  public $search_headers = [];
  public $search_params = [];

  public $viaf_no = '';
  public $viaf_record = null;

  private $viaf_src_all = [
  'ALL' => 'All source data within VIAF',
  'BAV' => 'Biblioteca Apostolica Vaticana',
  'BIBSYS' => 'BIBSYS',
  'BLBNB' => 'National Library of Brazil',
  'BNC' => 'National Library of Catalonia',
  'BNCHL' => 'National Library of Chile',
  'BNE' => 'Biblioteca Nacional de España',
  'BNF' => 'Bibliothèque Nationale de France',
  'BNL' => 'National Library of Luxembourg',
  'B2Q' => 'National Library and Archives of Quèbec',
  'CYT' => 'National Central Library, Taiwan',
  'DBC' => 'DBC (Danish Bibliographic Center)',
  'DNB' => 'Deutsche Nationalbibliothek',
  'EGAXA' => 'Bibliotheca Alexandrina (Egypt)',
  'ERRR' => 'National Library of Estonia',
  'ICCU' => 'Istituto Centrale per il Catalogo Unico',
  'ISNI' => 'ISNI',
  'JPG' => 'Getty Research Institute',
  'KRNLK' => 'National Library of Korea',
  'LC' => 'Library of Congress/NACO',
  'LAC' => 'Library and Archives Canada',
  'LNB' => 'National Library of Latvia',
  'LNL' => 'Lebanese National Library',
  'MRBNR' => 'National Library of Morocco',
  'NDL' => 'National Diet Library, Japan',
  'NII' => 'National Institute of Informatics (Japan)',
  'NKC' => 'National Library of the Czech Republic',
  'NLA' => 'National Library of Australia',
  'NLB' => 'National Library Board, Singapore',
  'NLI' => 'National Library of Israel',
  'NLIara' => 'National Library of Israel (Arabic)',
  'NLIcyr' => 'National Library of Israel (Cyrillic)',
  'NLIheb' => 'National Library of Israel (Hebrew)',
  'NLIlat' => 'National Library of Israel (Latin)',
  'NLP' => 'National Library of Poland',
  'NLR' => 'National Library of Russia',
  'NSK' => 'National and University Library in Zagreb',
  'NSZL' => 'National Szèchènyi Library, Hungary',
  'NTA' => 'National Library of the Netherlands',
  'NUKAT' => 'NUKAT Center of Warsaw University Library',
  'N6I' => 'National Library of Ireland',
  'PERSEUS' => 'PERSEUS',
  'PTBNP' => 'Biblioteca Nacional de Portugal',
  'RERO' => 'RERO.Library Network of Western Switzerland',
  'SELIBR' => 'National Library of Sweden',
  'SRP' => 'Syriac Reference Portal',
  'SUDOC' => 'Sudoc [ABES], France',
  'SWNL' => 'Swiss National Library',
  'UIY' => 'National and University Library of Iceland (NULI)',
  'VLACC' => 'Flemish Public Libraries',
  'WKP' => 'Wikidata',
  'W2Z' => 'National Library of Norway',
  'XA xA (eXtended Authorities)',
  'XR' => 'xR (eXtended Relationships)',
  'FAST' => 'FAST'
  ];
  private $viaf_src_usefull = [
  'ISNI' => 'ISNI',
  'JPG' => 'Getty Research Institute',
  'LC' => 'Library of Congress/NACO',
  'NTA' => 'National Library of the Netherlands',
  'PERSEUS' => 'PERSEUS',
  'WKP' => 'Wikidata',
  'XA' => 'xA (eXtended Authorities)',
  'XR' => 'xR (eXtended Relationships)',
  'FAST' => 'FAST',
  "viafID" => "viafID",
  "Identities" => "Identities",
  "Wikipedia" =>  "Wikipedia",
  ];
  
  private $wiki_useful = [
            "de.wikipedia.org",
            "en.wikipedia.org",
            "es.wikipedia.org",
            "fr.wikipedia.org",
            "it.wikipedia.org",
            "nl.wikipedia.org",
            "simple.wikipedia.org",
  ];

  public function __construct() {

  }

  public function __toString(){

    //create an array and return json_encoded string
    $json = [];

    $json['get_data_url'] = $this->get_data_url;
    $json['response_format'] = $this->response_format;
    $json['get_data_method'] = $this->get_data_method;

    $json['search_headers'] = $this->search_headers;
    $json['search_params'] = $this->search_params;

    $json['viaf_no'] = $this->viaf_no;
    $json['viaf_record'] = $this->viaf_record;
    return json_encode($json, JSON_PRETTY_PRINT);
  }


  public function viaf_get_data($no) {
    $this->viaf_no = $no;

    $viaf_url = $this->get_data_url.'/'.$no.'/'.$this->response_format;
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $viaf_url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $this->get_data_method);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','viaf_get_data','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','viaf_get_data',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','viaf_get_data','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','viaf_get_data',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','viaf_get_data',"Result but still cUrl error [$error_number]: $error_msg");
        }

        $received = json_decode($result,TRUE);
        $json_errno = json_last_error();
        $json_errmsg = json_last_error_msg();
        if ($json_errno == JSON_ERROR_NONE) {
          //store result in this object as an array
          $this->viaf_record = array();
          
          foreach ($received as $k=>$v) {
            if (array_key_exists($k,$this->viaf_src_usefull) ) {
              if ($k == 'Wikipedia') {
                foreach ($received[$k] as $wiki) {
                  foreach ($this->wiki_useful as $usefull) {
                    if (strpos($wiki,$usefull) !== FALSE) {
                      $this->viaf_record['Wikipedia'][] = $wiki;
                    }
                  }
                }
              }
              else {
                $this->viaf_record[$this->viaf_src_usefull[$k]] = $v;
              }
            }
          }
          //$this->viaf_record = $received;
          return TRUE;
        }
        else {
          $this->log_entry('Error','read_patron_ppid',"json_decode error [$json_errno]: $json_errmsg");
          return FALSE;
        }
      }
    }
  }
}
