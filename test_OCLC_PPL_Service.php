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
    $service = new OCLC_PPL_Service('keys_some.php');
    $service->get_auth_header('some_url','GET');
    $service->get_access_token_authorization("some_scope");
    echo json_encode($service->__toString(), JSON_PRETTY_PRINT);
   
    ?>
    </pre>
  </body>

</html>