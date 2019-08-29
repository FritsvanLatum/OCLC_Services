<?php

class SearchPage {
  public $userQuery = '*.*';
  private $parsedQuery = '';

  private $baseURL = 'http://localhost:8983/solr/ppl/select'; //http://localhost:8983/solr/ppl/select?q=*%3A*
  
  private $solrParams = [
      //'defType' => 'dismax', //voor dismax moet de indexering geconfigureerd zijn 
      'sort' => 'score desc',    //default 
      'start' => 0,
      'rows' => 10,
      
      //etc
  ];
  
  private $facetFields = array(

  );

  public $results = array();


  public function __construct() {

  }
  
  public function __toString(){
    $json = [
    'userQuery' => $this->userQuery,
    'parsedQuery' => $this->parsedQuery,
    'baseURL' => $this->baseURL,
    'solrParams' => $this->solrParams,
    'facetFields' => $this->facetFields,
    'results' => $this->results,
    
    ];
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function parse($userInput) {
    $parsed = $userInput;
    //cleanup
    
    
    return $parsed;
  }
  
  public function search($userinput) {
    //CURL stuff

    $this->solrParams['q'] = $this->parse($userinput);
    $url = $this->baseURL.'?'.http_build_query($this->solrParams);
    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    //file_put_contents('search.json',$result);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      //error handling
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        //error handling
        if ($error_number) {
          //error handling
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          //error handling

        }
        $this->results = json_decode($result,TRUE);
        return TRUE;
      }
    }
  }

  public function nextPage() {
    $this->solrParams['start'] = $this->solrParams['start'] + $this->solrParams['rows'];
    //check op start > numresults
    $this->search($userinput);
  }

  public function prevPage() {
    $this->solrParams['start'] = $this->solrParams['start'] - $this->solrParams['rows'];
    //check op start < 0
    $this->search($userinput);
 
  }

  //etc
}