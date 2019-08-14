<?php

class SearchPage {
  public $userQuery = ''; //of "*.*"
  private $parsedQuery = '';
  public $start = 0;
  public $numRows = 10;

  private $solrOptions = array (
  'hostname' => SOLR_SERVER_HOSTNAME,
  'login'    => SOLR_SERVER_USERNAME,
  'password' => SOLR_SERVER_PASSWORD,
  'port'     => SOLR_SERVER_PORT,
  );
  private $facetFields = array(

  );
  private $solrClient = nulll //of type SolrClient
  private $solrQuery = null; //of type SolrQuery

  public $searchResults = array();


  public function __construct() {
    $client = new SolrClient($this->solrOptions);
    $query = new SolrQuery();
  }

  public function prepareSearch($userInput) {
    $this->userQuery = $userInput;
    $this->parsedQuery = $this->parse($userInput);

    $this->query->setQuery($this->parsedQuery);
    $this->query->setStart($this->start);
    $this->query->setRows($this->numRows);
    //etc
    
    $this->search();
  }
  
  private function search() {
    $query_response = $this->client->query($this->query);
    $query_response->setParseMode(SolrQueryResponse::PARSE_SOLR_DOC);  //??
    $this->searchResults = $query_response->getResponse();
    $this->
  }

  public function nextPage() {
    $this->start = $this->start + $this->numRows;
    if ($this->start < $this->searchResults['response']['numFound']) $this->search();
  }

  public function prevPage() {
    $this->start = $this->start - $this->numRows;
    if ($this->start > 0) $this->search();
  }

  //etc
}