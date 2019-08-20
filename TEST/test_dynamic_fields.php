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
<script type="text/javascript">
  // Check if the page has loaded completely
  function get_av(ocn) {
      request = jQuery.get(activateUrl + '?je_ac=' + urlParams.get('je_ac'));

      request.done( function(data, textStatus, jqXHR) {
        if (debug) console.log("Data: "+data+' - textStatus: ' + textStatus);
        jQuery('#res').html(data);
      });

      request.fail(function (jqXHR, textStatus, errorThrown){
        // Log the error to the console
        if (debug) console.log("Error: "+textStatus, errorThrown);
        jQuery('#res').html('<p></p>');
      });
    }
</script>
                    