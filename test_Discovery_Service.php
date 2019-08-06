<!DOCTYPE html>
<html>
  <head>
    <title>TEST WorldCat Discovery Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once './Discovery_Service.php';
    $discovery = new Discovery_Service('keys_discovery.php');
    $discovery->read_record('887933119');
    ?>

    <p>Result of KB request:
      <pre>
        <?php 
          echo $discovery;
        ?>
      </pre>
    </p>


  </body>

</html>