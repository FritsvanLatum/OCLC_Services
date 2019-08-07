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
    $service = new NCIP_Service('keys_ncip.php');
    
    
    $service->lookup_patron_ppid('b420b91d-b3d9-4501-9ccd-99ed44984908');
    echo $service;
    file_put_contents('./output_examples/test_NCIP_lookup_response.xml',$service->patron);
    echo "\n\nErrors:\n".json_encode($service->errors);
    ?>
    </pre>
  </body>

</html>