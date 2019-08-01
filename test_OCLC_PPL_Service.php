<!DOCTYPE html>
<html>
  <head>
    <title>TEST OCLC_PPL_Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <pre>
    <?php
    require_once './OCLC_PPL_Service.php';
    require_once './some_keys.php';
    $service = new OCLC_PPL_Service('test_keys.php');
    $service->get_auth_header('some_url','GET');
    $service->get_access_token_authorization("some_scope");
    echo $service;
    echo "\n\nErrors:\n".json_encode($service->errors)
    
    
    ?>
    </pre>
  </body>

</html>