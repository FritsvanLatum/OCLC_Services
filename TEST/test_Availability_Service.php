<!DOCTYPE html>
<html>
  <head>
    <title>TEST Availability Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once '../Availability_Service.php';
    $avail = new Availability_Service('keys_availability.php');
    $ocn = isset($_POST['ocn']) ? $_POST['ocn'] : '887933119';
    $avail->get_availabilty_of_ocn($ocn);
    //$avail->get_availabilty_of_ocn('402543568');
    //$avail->get_availabilty_of_ocn('887933119');
    //$avail->get_availabilty_of_ocn('1019254132');
    ?>

    <form method="post">
        OCLC number (ocn): <input name="ocn" value="<?php echo $ocn; ?>"/><br/>
        <input type="submit"/>
    </form>

    <p>Result of availability request:
      <pre>
        <?php 
          echo $avail;

        ?>
      </pre>
    </p>
  </body>
</html>