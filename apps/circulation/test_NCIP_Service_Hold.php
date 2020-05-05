<!DOCTYPE html>
<html>
  <head>
    <title>TEST NCIP Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
/*
1125981646 De wapens neer! : roman, Bertha von Suttner
1015377410 Herman Rosse : design, art, love, architecture, film, theatre, history

Frits van Latum
Account: urn:mace:oclc:idm:peacepalace 56bb24b2-9f56-41fa-9cdd-53adb5744904
Diagnos: urn:oclc:platform:57439 / b420b91d-b3d9-4501-9ccd-99ed44984908

Judith van Walstijn
Account: urn:mace:oclc:idm:peacepalace 35042283-fb4a-41d8-859e-5a3b47a81d35
Diagnos: urn:oclc:platform:57439 / 79363e42-54a3-46a5-a858-e026641fd9f0

Aad Janson
Account: urn:mace:oclc:idm:peacepalace 836c32aa-801c-45e3-9584-f3382bd72421
Diagnos: urn:oclc:platform:57439 / 9d1edb1d-d051-4fde-8431-169bfab07666
*/    
    require_once '../NCIP_Service.php';
    $service = new NCIP_Service('keys_ncip.php');
    ?>
    <h3>Hold voor Judith</h3>
    <pre>
      <?php
      $ocn = '1125981646';
      $ppid = '79363e42-54a3-46a5-a858-e026641fd9f0';
      $service->request_biblevel($ppid,$ocn);
      echo $service;
      //file_put_contents('test_NCIP_request_response'.$ppid.'.xml',json_encode($service->request,JSON_PRETTY_PRINT));
      ?>
    </pre>
    <h3>Hold voor Aad</h3>
    <pre>
      <?php
      $ocn = '1015377410';
      $ppid = '9d1edb1d-d051-4fde-8431-169bfab07666';
      $service->request_biblevel($ppid,$ocn);
      echo $service;
      //file_put_contents('test_NCIP_request_response'.$ppid.'.xml',json_encode($service->request,JSON_PRETTY_PRINT));
      ?>
    </pre>

  </body>

</html>