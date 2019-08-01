<!DOCTYPE html>
<html>
  <head>
    <title>TEST OCLC_PPL_Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <pre>
    <?php
    require_once './IDM_Service.php';
    $service = new patron('idm_keys.php');
    
    $service->read_patron_ppid('1a4f4dbb-375a-4363-8792-3aaa95fd9bad');
    echo $service;
    echo "\n\nErrors:\n".json_encode($service->errors);
    ?>
    </pre>
  </body>

</html>