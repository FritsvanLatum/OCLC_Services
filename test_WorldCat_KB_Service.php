<!DOCTYPE html>
<html>
  <head>
    <title>TEST WorldCat KB Service</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once './WorldCat_KB_Service.php';
    $KB = new WorldCat_KB_Service('keys_worldcat_kb.php');
    $KB->search_kb_record('887933119');
    //$avail->get_availabilty_of_ocn('402543568');
    //$avail->get_availabilty_of_ocn('887933119');
    //$avail->get_availabilty_of_ocn('1019254132');
    ?>

    <p>Result of KB request:
      <pre>
        <?php 
          echo $KB;
        ?>
      </pre>
      <?php 
         $href = $KB->getlink('887933119');
         echo '<a href="'.$href.'">'.$href."</a></br>\n";
      ?>
    </p>


  </body>

</html>