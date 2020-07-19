<?php

require_once __DIR__.'/OCLC/Auth/WSKey.php';
require_once __DIR__.'/OCLC/User.php';

/**
* A class that represents a patron
*/
class OCLC_Service {

  public $errors = [];
  private $error_log = __DIR__.'/service_errors';
  private $logging = 'all'; //'none','errors','all' (not yet implemented

  public $wskey = null;
  private $secret = null;
  private $ppid = null;

  private $token_authorization = '';
  private $authorizationHeader = '';

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
    if (array_key_exists('wskey',$config)) $this->wskey = $config['wskey'];
    if (array_key_exists('secret',$config)) $this->secret = $config['secret'];
    if (array_key_exists('ppid',$config)) $this->ppid = $config['ppid'];
    if (array_key_exists('ppid_ns',$config)) {
      $this->ppid_namespace = $config['ppid_ns'];
    }
    else {
      $this->ppid_namespace = $this->ppid_namespace.$this->institution;
    }
  }

  public function __toString(){
    //create an array and return json_encoded string
    $json = [
    'errors' => $this->errors,

    'institution' => $this->institution,
    'defaultBranch' => $this->defaultBranch,
    'ppid' => $this->ppid,

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

  public function get_auth_header($url,$method, $ppid = NULL) {
    //use the argument $ppid when given, else $ppid in $config when given
    if (is_null($ppid)  && (!is_null($this->ppid))) {
        $ppid = $this->ppid;
    }
    
    //get an authorization header
    //  with wskey, secret and if necessary user data from $config
    //  for the $method and $url provided as parameters

    $authorizationHeader = '';
    if ($this->wskey && $this->secret) {
      $options = array();
      if ($this->institution && $ppid && $this->ppid_namespace) {
        //uses OCLC provided programming to get an autorization header
        $user = new User($this->institution, $ppid, $this->ppid_namespace);
        $options['user'] = $user;
      }
      //echo "Options: ".json_encode($options, JSON_PRETTY_PRINT);
      if (count($options) > 0) {
        $wskeyObj = new WSKey($this->wskey, $this->secret, $options);
        $authorizationHeader = $wskeyObj->getHMACSignature($method, $url, $options);
      }
      else {
        $wskeyObj = new WSKey($this->wskey, $this->secret,null);
        $authorizationHeader = $wskeyObj->getHMACSignature($method, $url, null);
      }
    }
    else {
      $this->log_entry('Error','get_pulllist_auth_header','No wskey and/or no secret!');
    }
    $this->authorizationHeader = $authorizationHeader;
    return $authorizationHeader;
  }

  public function get_access_token_authorization($scope, $ppid = NULL) {
    //use the argument $ppid when given, else $ppid in $config when given
    if (is_null($ppid)  && (!is_null($this->ppid))) {
        $ppid = $this->ppid;
    }
    $this->token_params['scope'] = $scope;

    $token_authorization = "";
    $authorizationHeader = $this->get_auth_header($this->token_url,$this->token_method, $ppid);
    if (strlen($authorizationHeader) > 0) {
      $this->token_headers['Authorization'] = $authorizationHeader;
    }
    else {
      $this->log_entry('Error','get_access_token_authorization','No authorization header created!');
    }
     return $this->get_access_token_authorization_curl();
  }
  private function get_access_token_authorization_curl() {

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

  /*
  * parameters:
  * $node : must be a DOMNode
  * 
  * $options : associative array, with zero, one, two or all three of these elements:
  * [
  * 'remove_namespaces' => TRUE,
  * 'remove_attributes' => TRUE,
  * 'remove_arrays_one_element' => FALSE,
  * ]
  * the default values are as shown
  * 
  * returns an associative array.
  * 
  * remove_arrays_one_element:
  * Please note that with the default value of remove_arrays_one_element (FALSE) all values in the resulting associative array
  * are itself arrays. This is because this is valid XML:
  * ...
  * <author>Jack</author>
  * <author>John</author>
  * ...
  * But this is not allowed in an associative array:
  * [
  * ...
  * 'author' => 'Jack',
  * 'author' => 'John',
  * ...
  * ]
  * 
  * Should be:
  * [
  * ...
  * 'author' => ['Jack', 'John'],
  * ...
  * ]
  * So each element becomes an array, also when there is only one value...
  * If remove_arrays_one_element is set to TRUE: always check whether a value is an array or a string
  * 
  * remove_attributes:
  * If remove_attributes is set to FALSE, then for each XML element an extra layer is added. The attribute key - value pairs are added and
  * the content of the XML element is added as:
  * '_content_' => ...
  * 
  * remove_namespaces:
  * Set remove_namespaces to FALSE when there will be element name confusions otherwise.
  * 
  */

  public function xml2json($node,$options) {
    //echo '<pre>'.$node->nodeName." : ".$node->getNodePath()."</pre>\n";
    $remove_namespaces = array_key_exists('remove_namespaces',$options) ? $options['remove_namespaces'] : TRUE;
    $remove_attributes = array_key_exists('remove_attributes',$options) ? $options['remove_attributes'] : TRUE;
    $remove_arrays_one_element = array_key_exists('remove_arrays_one_element',$options) ? $options['remove_arrays_one_element'] : FALSE;
    $options = [
    'remove_namespaces' => $remove_namespaces,
    'remove_attributes' => $remove_attributes,
    'remove_arrays_one_element' => $remove_arrays_one_element,
    ];

    $result = array();
    if (($node->nodeType == XML_ELEMENT_NODE) || ($node->nodeType == XML_DOCUMENT_NODE)) {
      if ($node->hasChildNodes()) {
        foreach ($node->childNodes as $child) {
          if ($child->nodeType == XML_ELEMENT_NODE) {
            $ckey = $child->nodeName;
            if ($remove_namespaces) {
              $parts = explode(':',$ckey);
              if (count($parts) > 1) $ckey = $parts[1];
            }

            if ($remove_attributes) {
              $result[$ckey][] = $this->xml2json($child,$options);
            }
            else {
              $sub = array();
              if ($child->hasAttributes()){
                foreach ($child->attributes as $attribute) {
                  $attr_key = $attribute->nodeName;
                  if ($remove_namespaces) {
                    $parts = explode(':',$attr_key);
                    if (count($parts) > 1) $attr_key = $parts[1];
                  }
                  $sub[$attr_key] = $attribute->nodeValue;
                }
              }
              $sub['_content_'] =  $this->xml2json($child,$options);

              $result[$ckey][] = $sub;
            }
          }
          else if ($child->nodeType == XML_TEXT_NODE) $result = $child->textContent;
        }
        if (is_array($result)) {
          if ($remove_arrays_one_element) {
            if (array_keys($result) === range(0, count($result) - 1)) {
              if (count($result) == 1) $result = $result[0];
            }
            else {
              foreach ($result as $k=>$v) {
                if (count($v) == 1) $result[$k] = $v[0];
              }
            }
          }
          }
      }
    }
    return $result;
  }



}
