<!DOCTYPE html>
<html>
  <head>
    <title>TEST Dynamic Fields</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once './Availability_Service.php';
    $avail = new Availability_Service('keys_availability.php');
    require_once './WorldCat_KB_Service.php';
    $KB = new WorldCat_KB_Service('keys_worldcat_kb.php');
    $ocns = ['887933119','402543568','887933119','1019254132'];
    
    foreach ($ocns as $ocn) {
      ?>
      <p>OCN: <?php echo $ocn?></p>
      <p>Availability:</p>
      <pre>
        <?php echo json_encode($avail->get_circulation_info($ocn),JSON_PRETTY_PRINT);?>
      </pre>
      <p>Online:</p>
      <pre>
      <?php 
         $href = $KB->getlink($ocn,'canonical');
         echo ($href == '') ? 'NO URL': '<a href="'.$href.'">'.$href."</a></br>\n";
      ?>      </pre>
      <br/><hr/><br/>
      <?php
    }
    ?>

  </body>
</html>