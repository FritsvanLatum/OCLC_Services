<!DOCTYPE html>
<html>
  <head>
    <title>TEST Dynamic Fields</title>
    <meta charset="utf-8" />
    <script type="text/javascript" src="js/jquery.min.js"></script>
  </head>

  <body>
    <?php
    require_once '../Availability_Service.php';
    $avail = new Availability_Service('keys_availability.php');
    require_once '../WorldCat_KB_Service.php';
    $KB = new WorldCat_KB_Service('keys_worldcat_kb.php');
    
    /*
    This script collects availability and online availability for 4 ocn's
    In production this should be done with separate ajax calls:
    for each ocn a call to a script that collects physical availability (uses Availability_Service)
    and to a script that collects online availability (uses WorldCat_KB_Service)
    
    */
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