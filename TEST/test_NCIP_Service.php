<!DOCTYPE html>
<html>
  <head>
    <title>TEST NCIP Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once '../NCIP_Service.php';
    $service = new NCIP_Service('keys_ncip.php');
    $ppid = 'b420b91d-b3d9-4501-9ccd-99ed44984908';
    $ppid_Judith = '35042283-fb4a-41d8-859e-5a3b47a81d35';
    $ocn = '982088863';
    $ocn_Judith = '986237324';
    ?>
    <p>Lookup</p>
    <pre>
      <?php
      $service->lookup_patron_ppid($ppid);
      echo $service;
      //file_put_contents('../output_examples/test_NCIP_lookup_response'.$ppid.'_1.xml',json_encode($service->patron,JSON_PRETTY_PRINT));
      ?>
    </pre>
    <p>Request voor Judith</p>
    <pre>
      <?php
      $service->request_biblevel($ppid_Judith,$ocn_Judith);
      echo $service;
      file_put_contents('../output_examples/test_NCIP_request_response'.$ppid.'.xml',json_encode($service->request,JSON_PRETTY_PRINT));
      ?>
    </pre>
    <p>Lookup</p>
    <pre>
      <?php
      $service->lookup_patron_ppid($ppid);
      echo $service;
      //file_put_contents('../output_examples/test_NCIP_lookup_response'.$ppid.'_2.xml',json_encode($service->patron,JSON_PRETTY_PRINT));
      ?>
    </pre>


  </body>

</html>