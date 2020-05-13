<?php
require_once './OCLC/Auth/WSKey.php';
require_once './OCLC/User.php';
require_once __DIR__.'/../patron/patron.php';
require_once __DIR__.'/../vendor/autoload.php';

/**
* A class that represents a pulllist
*/
class Pulllist {
  //error handling: TODO move logfiles to somewhere else
  private $errors = [];
  private $error_log = __DIR__.'/../pulllist_error';
  private $logging = 'all'; //'none','errors','all' (not yet implemented

  //must be provided as parameters in new Pulllist($wskey,$secret,$ppid), see __construct
  private $wskey = null;
  private $secret = null;
  private $ppid = null;

  //must be provided as parameters in new Patron($this->idm_wskey,$this->idm_secret,$this->idm_ppid), see __construct
  private $idm_wskey = null;
  private $idm_secret = null;
  private $idm_ppid = null;

  //PPL info:
  private $institution = "57439";
  private $defaultBranch = "262638";

  //$ppid_namespace is extended in __construct
  private $ppid_namespace = "urn:oclc:platform:";

  //OCLC key stuff
  private $auth_url = 'http://www.worldcat.org/wskey/v2/hmac/v1';
  private $auth_method = 'GET';
  private $auth_headers = ['Accept: application/json'];

  //$pulllist_url is extended in __construct
  private $pulllist_url = "share.worldcat.org/circ/pulllist";

  //pulllist
  public $list = null;
  public $no_of_items = null;

  //directory and file names
  private $pullist_dir = null;
  private $tickets_dir = 'tickets';
  private $tobeprinted_dir = 'tobeprinted';
  private $printed_dir = 'printed';
  private $pdf_dir = 'temp_printer';
  private $pulllist_filename = 'actual_pulllist.json';
  private $previous_pulllist_filename = 'previous_pulllist.json';

  //will be initialized as an object of class Patron
  private $patron = null;

  //twig stuff
  private $twig = null;
  private $template = 'ticket_template.html';

  public function __construct($wskey,$secret,$ppid,$idm_wskey,$idm_secret,$idm_ppid) {
    //oclc business
    $this->idm_wskey = $idm_wskey;
    $this->idm_secret = $idm_secret;
    $this->idm_ppid = $idm_ppid;

    $this->wskey = $wskey;
    $this->secret = $secret;
    $this->ppid = $ppid;
    $this->ppid_namespace = $this->ppid_namespace.$this->institution;
    $this->pulllist_url = 'https://'.$this->institution.'.'.$this->pulllist_url.'/'.$this->defaultBranch;

    //directory structure
    $this->pullist_dir = __DIR__;  //might be another directory, the web server has to have write access

    $this->tickets_dir = $this->pullist_dir.'/'.$this->tickets_dir;
    $this->pulllist_filename = $this->tickets_dir.'/'.$this->pulllist_filename;
    $this->previous_pulllist_filename = $this->tickets_dir.'/'.$this->previous_pulllist_filename;
    $this->tobeprinted_dir = $this->tickets_dir.'/'.$this->tobeprinted_dir;
    $this->printed_dir = $this->tickets_dir.'/'.$this->printed_dir;
    $this->pdf_dir = $this->tickets_dir.'/'.$this->pdf_dir;

    //Twig
    $loader = new Twig_Loader_Filesystem(__DIR__);
    $this->twig = new Twig_Environment($loader, array(
    //specify a cache directory only in a production setting
    //'cache' => './compilation_cache',
    ));

    //logging

  }

  public function __toString(){
    //create an array with all the class members and its values ...
    $json = [
    'wskey' =>$this->wskey,
    'secret' => $this->secret,
    'ppid' => $this->ppid,

    'institution' => $this->institution,
    'defaultBranch' => $this->defaultBranch,

    'ppid_namespace' => $this->ppid_namespace,

    'auth_url' => $this->auth_url,
    'auth_method' => $this->auth_method,
    'auth_headers' => $this->auth_headers,

    'pulllist_url' => $this->pulllist_url,

    'list' => $this->list,
    'no_of_items' => $this->no_of_items,

    'pullist_dir' => $this->pullist_dir,
    'tickets_dir' => $this->tickets_dir,
    'tobeprinted_dir' => $this->tobeprinted_dir,
    'printed_dir' => $this->printed_dir,
    'pdf_dir' => $this->pdf_dir,
    'pulllist_filename' => $this->pulllist_filename,
    'previous_pulllist_filename' => $this->previous_pulllist_filename,
    'patron' => ($this->patron === null) ? null : get_object_vars($this->patron),
    'twig' => ($this->twig === null) ? null : 'is initiated',
    'template' => $this->template,
    ];
    
    //... and return as a json_encoded string for printing
    return json_encode($json, JSON_PRETTY_PRINT);
  }

  public function log_entry($t,$c,$m) {
    $this->errors[] = date("Y-m-d H:i:s")." $t [$c] $m";
    $name = $this->error_log.'.'.date("Y-W").'.log';
    return file_put_contents($name, date("Y-m-d H:i:s")." $t [$c] $m\n", FILE_APPEND);
  }

  private function get_pulllist_auth_header($url,$method) {
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
        //echo "User: ".$user->getPrincipalID();
      }

      if (count($options) > 0) {
        $wskeyObj = new WSKey($this->wskey, $this->secret, $options);
        $authorizationHeader = $wskeyObj->getHMACSignature($method, $url, $options);
      }
      else {
        $wskeyObj = new WSKey($this->wskey, $this->secret,null);
        $authorizationHeader = $wskeyObj->getHMACSignature($method, $url, null);
      }
      $authorizationHeader = 'Authorization: '.$authorizationHeader;
    }
    else {
      $this->log_entry('Error','get_pulllist_auth_header','No wskey and/or no secret!');
    }
    return $authorizationHeader;
  }

  public function get_pulllist() {
    //authorization
    $authorizationHeader = $this->get_pulllist_auth_header($this->auth_url,$this->auth_method);
    if (strlen($authorizationHeader) > 0) {
      array_push($this->auth_headers,$authorizationHeader);
    }
    else {
      $this->log_entry('Error','get_pulllist','No authorization header created!');
    }

    //CURL
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $this->pulllist_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $this->auth_headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    $error_number = curl_errno($curl);
    $error_msg = curl_error($curl);
    curl_close($curl);

    if ($result === FALSE) {
      $this->log_entry('Error','get_pulllist','No result on cUrl request!');
      if ($error_number) $this->log_entry('Error','get_pulllist',"No result, cUrl error [$error_number]: $error_msg");
      return FALSE;
    }
    else {
      if (strlen($result) == 0) {
        $this->log_entry('Error','get_pulllist','Empty result on cUrl request!');
        if ($error_number) {
          $this->log_entry('Error','get_pulllist',"Empty result, cUrl error [$error_number]: $error_msg");
        }
        return FALSE;
      }
      else {
        if ($error_number) {
          $this->log_entry('Error','get_pulllist',"Result but still cUrl error [$error_number]: $error_msg");
        }

        $list = json_decode($result,TRUE);
        $json_errno = json_last_error();
        $json_errmsg = json_last_error_msg();

        if ($json_errno == JSON_ERROR_NONE) {
          //store result in this object as an array
          $this->list = $list;

          //number of items
          if (array_key_exists('entries',$this->list)) {
            $this->no_of_items = count($this->list['entries']);
          }

          $this->savePullist();
          return TRUE;
        }
        else {
          $this->log_entry('Error','get_pulllist',"json_decode error [$json_errno]: $json_errmsg");
          return FALSE;
        }
      }
    }
  }

  private function savePullist(){
    //save but not after renaming the previous one
    if (file_exists($this->pulllist_filename)) {
      $renamed = rename($this->pulllist_filename,$this->previous_pulllist_filename);
      if (!$renamed) $this->log_entry('Warning','get_pulllist',"Existing pullist file could not be renamed.");
    }
    $written = file_put_contents($this->pulllist_filename,json_encode($this->list, JSON_PRETTY_PRINT));
    if (!$written) $this->log_entry('Warning','get_pulllist',"New pullist file could not be saved.");
  }
  
  public function get_item($i) {
    $result = null;
    if ($this->list && array_key_exists('entries',$this->list) && ($i < $this->no_of_items)) {
      $result = $this->list['entries'][$i];
    }
    return $result;
  }

/*
 Uses Twig to generate HTML
 Uses Mpdf to generate PDF from HTML to send to a printer
 Uses Patron class to look up a patron's barcode
 */
  public function items2html() {
    if ($this->list && array_key_exists('entries',$this->list)) {
      $tel = 0;
      foreach ($this->list['entries'] as $entry) {
        $tel++;

        //try to get the patron barcode
        $patronIdentifier = $entry['content']['patronIdentifier']['ppid'];
        $this->patron = new Patron($this->idm_wskey,$this->idm_secret,$this->idm_ppid);
        $this->patron->read_patron_ppid($patronIdentifier);
        $barcode = $this->patron->get_barcode();

        if (strlen($barcode) == 0) {
          $this->log_entry('Warning','items2html',"Entry $tel: No barcode returned from ppid $patronIdentifier");
        }
        $entry['content']['lenerbarcode']=$barcode;
        
        //generate HTML and PDF and store to files
        try {
          $html_filename = $this->tobeprinted_dir.'/'.$entry['content']['requestId'].'.html';
          $printed_filename = $this->printed_dir.'/'.$entry['content']['requestId'].'.html';
          $pdf_filename = $this->pdf_dir.'/'.$entry['content']['requestId'].'.pdf';

          $html = '';
          if (file_exists($printed_filename)) {
            //already printed: do nothing
            $this->log_entry('Warning','items2html',"file already printed: $printed_filename\n\t".$entry['content']['callNumber']['description']." -- ".$entry['content']['pieceDesignation']." -- ".$entry['content']['patronName']);
          }
          else {
            if (file_exists($html_filename)) {
              if (!file_exists($pdf_filename)) $html = file_get_contents($html_filename);
            }
            else {
              //generate html
              $html = $this->twig->render($this->template, $entry);
              $written = file_put_contents($html_filename,$html);
              if (!$written) $this->log_entry('Error','items2html',"File could not be written: $filename");
            }

            if (file_exists($pdf_filename)) {
              $this->log_entry('Warning','items2html',"pdf file already exists: $pdf_filename");
            }
            else {
              //generate pdf from html
              $mpdf = new \Mpdf\Mpdf();
              $mpdf->WriteHTML($html);
              //save pdf to $printer_as_dir
              $mpdf->Output($pdf_filename);
            }
          }
        }
        catch (Exception $e) {
          $this->log_entry('Error','items2html',"Twig/Mpdf exception: ".$e->getMessage());
        }
      }
    }
    else {
      $this->log_entry('Warning','items2html',"No list or no entries in list");
    }
  }
}

