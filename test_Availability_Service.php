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
    //$avail->get_availabilty_of_ocn('402543568');
    //$avail->get_availabilty_of_ocn('887933119');
    //$avail->get_availabilty_of_ocn('1019254132');
    ?>

    <p>Result of availability request:
      <pre>
        <?php 
          echo $avail;

        ?>
      </pre>
    </p>

    <p>Result of circulations:
      <pre>
        <?php echo json_encode($avail->get_circulation_info(),JSON_PRETTY_PRINT);?>
      </pre>
    </p>

  </body>

</html>