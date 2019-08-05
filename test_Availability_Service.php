<!DOCTYPE html>
<html>
  <head>
    <title>TEST Availability Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once './Availability_Service.php';

    $avail = new Availability_Service('keys_availability.php');
    $avail->get_availabilty_of_ocn('1024083487');
    ?>  
    
    <p>Result of availability request:
      <pre>
<?php echo $avail;?>
      </pre>
    </p>

    
  </body>

</html>