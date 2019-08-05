<?php

require_once './OCLC/Auth/WSKey.php';
require_once './OCLC/User.php';

/**
* A class that represents a patron
*/
class OCLC_PPL_Service {

  public $errors = [];
  private $error_log = __DIR__.'/service_errors';
  private $logging = 'all'; //'none','errors','all' (not yet implemented

  //must be provided as parameters in $patron = new Patron($wskey,$secret,$ppid), see __construct
  private $wskey = null;
  private $secret = null;
  private $ppid = null;
  
  private $authorizationHeader = '';
  private $token_authorization = '';
  
  public $institution = "57439";
  public $defaultBranch = "262638";

  //$ppid_namespace is extended in __construct
  public $ppid_namespace = "urn:oclc:platform:";

  public $token_url = 'https://authn.sd00.worldcat.org/oauth2/accessToken';
  public $token_method = 'POST';
  public $token_POST = true;
  public $token_headers = ['Accept' => 'application/json'];

  public $token_params = [
  'grant_type' => 'client_credentials',
  'authenticatingInstitutionId' => '57439',
  'contextInstitutionId' => '57439',
  'scope' => ''
  ];


  public function __construct($key_file) {

    require(__DIR__.'/'.$key_file);
    $this->wskey = $config['wskey'];
    $this->secret = $config['secret'];
    $this->ppid = $config['ppid'];

    $this->ppid_namespace = $this->ppid_namespace.$this->institution;
  }

  public function __toString(){
    //create an array and return json_encoded string
    $json = [
    'errors' => $this->errors,

    'institution' => $this->institution,
    'defaultBranch' => $this->defaultBranch,

    'ppid_namespace' => $this->ppid_namespace,
    'authorizationHeader' => $this->authorizationHeader,
    'token_url' => $this->token_url,
    'token_method' => $this->token_method,
    'token_POST' => $this->token_POST,
    'token_headers' => $this->token_headers,
    'token_params' => $this->token_params,
    'token_authorization' => $this->token_authorization,
    ];
    return $json;
  }

  public function log_entry($t,$c,$m) {
    $this->errors[] = date("Y-m-d H:i:s")." $t [$c] $m";
    $name = $this->error_log.'.'.date("Y-W").'.log';
    return file_put_contents($name, date("Y-m-d H:i:s")." $t [$c] $m\n", FILE_APPEND);
  }

  public function get_auth_header($url,$method) {
    //get an authorization header
    //  with wskey, secret and if necessary user data from $config
    //  for the $method and $url provided as parameters

    $authorizationHeader = '';
    if ($this->wskey && $this->secret) {
      $options = array();
      if ($this->institution && $this->ppid && $this->ppid_namespace) {
        //uses OCLC provided programming to get an autorization header
        $user = new User($this->institution, $this->ppid, $this->ppid_namespace);
        $options['user'] = $user;
      }
      //echo "Options: ".json_encode($options, JSON_PRETTY_PRINT);
      if (count($options) > 0) {
        $wskeyObj = new WSKey($this->wskey, $this->secret, $options);
        $authorizationHeader = $wskeyObj->getHMACSignature($method, $url, $options);
      }
      else {
        $wskeyObj = new WSKey($config['wskey'], $config['secret'],null);
        $authorizationHeader = $wskeyObj->getHMACSignature($method, $url, null);
      }
    }
    else {
      $this->log_entry('Error','get_pulllist_auth_header','No wskey and/or no secret!');
    }
    $this->authorizationHeader = $authorizationHeader;
    return $authorizationHeader;
  }

  public function get_access_token_authorization($scope) {
    $this->token_params['scope'] = $scope;
    
    $token_authorization = "";
    $authorizationHeader = $this->get_auth_header($this->token_url,$this->token_method);

    if (strlen($authorizationHeader) > 0) {
      $this->token_headers['Authorization'] = $authorizationHeader;
    }
    else {
      $this->log_entry('Error','get_access_token_authorization','No authorization header created!');
    }

    $curl = curl_init();
    
    $header_array = [];
    foreach ($this->token_headers as $k => $v) {
      $header_array[] = "$k: $v";
    }
    
    curl_setopt($curl, CURLOPT_URL, $this->token_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header_array);
    curl_setopt($curl, CURLOPT_POST, $this->token_POST);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->token_params));
    //echo http_build_query($this->token_params);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_, );
    //curl_setopt($curl, CURLOPT_, );

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','get_access_token_authorization','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','get_access_token_authorization',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','get_access_token_authorization','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','get_access_token_authorization',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','get_access_token_authorization',"Result but still cUrl error [$error_number]: $error_msg");
        }
        $token_array = json_decode($result,TRUE);
        $json_errno = json_last_error();
        $json_errmsg = json_last_error_msg();
        if ($json_errno == JSON_ERROR_NONE) {
          if (array_key_exists('access_token',$token_array)){
            $token_authorization = 'Bearer '.$token_array['access_token'];
          }
          else {
            $this->log_entry('Error','get_access_token_authorization',"No access_token returned (curl result: ".$result.")");
            return FALSE;
          }
        }
        else {
          $this->log_entry('Error','get_access_token_authorization',"json_decode error [$json_errno]: $json_errmsg");
          return FALSE;
        }
      }
    }
    $this->token_authorization = $token_authorization;
    return $token_authorization;
  }



}
