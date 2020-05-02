<!DOCTYPE html>
<html>
  <head>
    <title>TEST NCIP Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
/*
API NCIP
Account: urn:mace:oclc:idm:peacepalace cd0f5349-d9a5-4558-9ec4-cf9ff3a15cfb
Diagnos: urn:oclc:platform:57439 / f0153a8d-7f87-4aa7-934d-b4f2f20bd10f

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
    $ppid = '79363e42-54a3-46a5-a858-e026641fd9f0';
    ?>
    <h3>Lookup Judith</h3>
    <pre>
      <?php
      $service->lookup_patron_ppid($ppid);
      echo $service;
      //file_put_contents('../output_examples/test_NCIP_lookup_response'.$ppid.'_1.xml',json_encode($service->patron,JSON_PRETTY_PRINT));
      ?>
    </pre>
    
	  <?php
    $ppid = '9d1edb1d-d051-4fde-8431-169bfab07666';
    ?>
    <h3>Lookup Aad</h3>
    <pre>
      <?php
      $service->lookup_patron_ppid($ppid);
      echo $service;
      //file_put_contents('../output_examples/test_NCIP_lookup_response'.$ppid.'_1.xml',json_encode($service->patron,JSON_PRETTY_PRINT));
      ?>
    </pre>

	  <?php
    $ppid = 'b420b91d-b3d9-4501-9ccd-99ed44984908';
    ?>
    <h3>Lookup Frits</h3>
    <pre>
      <?php
      $service->lookup_patron_ppid($ppid);
      echo $service;
      //file_put_contents('../output_examples/test_NCIP_lookup_response'.$ppid.'_1.xml',json_encode($service->patron,JSON_PRETTY_PRINT));
      ?>
    </pre>
  </body>
</html>