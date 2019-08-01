<!DOCTYPE html>
<html>
  <head>
    <title>TEST NCIP Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <pre>
    <?php
    require_once './NCIP_Service.php';
    $service = new NCIP_Service('ncip_keys.php');
    
    
    $service->lookup_patron_ppid('b420b91d-b3d9-4501-9ccd-99ed44984908');
    echo $service;
    echo "\n\nErrors:\n".json_encode($service->errors);
    ?>
    </pre>
  </body>

</html>