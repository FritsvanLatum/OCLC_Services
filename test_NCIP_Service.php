<!DOCTYPE html>
<html>
  <head>
    <title>TEST NCIP Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once './NCIP_Service.php';
    $service = new NCIP_Service('keys_ncip.php');
    $ppid = 'b420b91d-b3d9-4501-9ccd-99ed44984908';
    $ocn = '982088863';
    ?>
        <p>Lookup</p>
    <pre>
    <?php
    $service->lookup_patron_ppid($ppid);
    echo $service;
    file_put_contents('./output_examples/test_NCIP_lookup_response'.$ppid.'_1.xml',$service->patron);
    ?>
    </pre>
    <p>Request</p>
    <pre>
    <?php
    $service->request_biblevel($ppid,$ocn);
    echo $service;
    file_put_contents('./output_examples/test_NCIP_request_response'.$ppid.'.xml',$service->request);
    ?>
    </pre>
        <p>Lookup</p>
    <pre>
    <?php
    $service->lookup_patron_ppid($ppid);
    echo $service;
    file_put_contents('./output_examples/test_NCIP_lookup_response'.$ppid.'_2.xml',$service->patron);
    ?>
    </pre>

    
  </body>

</html>