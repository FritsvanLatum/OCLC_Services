<!DOCTYPE html>
<html>
  <head>
    <title>TEST OCLC_PPL_Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <h3>read patron with ppid</h3>
    <pre>
    <?php
    require_once '../IDM_Service.php';
    /*
    $service = new IDM_Service('keys_idm.php');
    $service->read_patron_ppid('b420b91d-b3d9-4501-9ccd-99ed44984908');
    echo $service;
    */
    ?>
    </pre>
    <h3>read patron with barcode</h3>
    <pre>
    <?php
    /*
    $service = new IDM_Service('keys_idm.php');
    $service->read_patron_barcode('090037604');
    echo $service;
    */
    ?>
    </pre>
    <h3>read all patrons</h3>
    <pre>
    <?php
    /*
    $service = new IDM_Service('keys_idm.php');
    $service->read_patrons();
    echo $service;
    */
    ?>
    </pre>
    <h3>create patron in ./idm_templates/json_example.json</h3>
    <pre>
    <?php
    /*
    $service = new IDM_Service('keys_idm.php');
    //get input
    $json = json_decode(file_get_contents('../idm_templates/json_example.json'),TRUE);
    echo json_encode($json,JSON_PRETTY_PRINT);
    //create new barcode
    $barcode = $service->wms_new_barcode($json["id"]["userName"]);
    echo "\n\nBarcode: $barcode\n\n";
    //create in WMS (patron type 'website'
    $service->wms_create($barcode,'website',$json);
    echo $service;
    */
    ?>
    </pre>
    </pre>
    <h3>update patron in ./idm_templates/json_example.json</h3>
    <pre>
    <?php
    $service = new IDM_Service('keys_idm.php');
    /*
    //get input
    $json = json_decode(file_get_contents('../idm_templates/json_example.json'),TRUE);
    echo json_encode($json,JSON_PRETTY_PRINT);

    //update in WMS
    $service->wms_update('00c66177-df54-4f2e-ab03-220b8f32b9cf', '9055062314', $json);
    echo $service;
    */
    //test aanvullen idp correlatie
    $scim_json = file_get_contents('../idm_templates/scim_update_template_test.json');
    $ppid = "b420b91d-b3d9-4501-9ccd-99ed44984908";
    $service->update_patron($ppid, $scim_json);
    echo $service;
    ?>
 </body>

</html>