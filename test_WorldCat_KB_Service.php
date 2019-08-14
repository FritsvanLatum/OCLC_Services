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
    $ocn = isset($_POST['ocn']) ? $_POST['ocn'] : '887933119';

    $KB->search_kb_record($ocn);
    //$avail->get_availabilty_of_ocn('402543568');
    //$avail->get_availabilty_of_ocn('887933119');
    //$avail->get_availabilty_of_ocn('1019254132');
    ?>

    <form method="post">
        OCLC number (ocn): <input name="ocn" value="<?php echo $ocn; ?>"/><br/>
        <input type="submit"/>
    </form>

    <p>Link to digital publication:<br/>
      <?php 
         $href = $KB->getlink($ocn);
         echo '<a href="'.$href.'">'.$href."</a></br>\n";
      ?>
    </p>
    <br/>
    <p>Other links:<br/>
      <b>alternate</b>: 
      <?php 
         $href = $KB->getlink($ocn,'alternate');
         echo '<a href="'.$href.'">'.$href."</a></br>\n";
      ?><br/>
      <b>self</b>: 
      <?php 
         $href = $KB->getlink($ocn,'self');
         echo '<a href="'.$href.'">'.$href."</a></br>\n";
      ?><br/>
      <b>canonical</b>: 
      <?php 
         $href = $KB->getlink($ocn,'canonical');
         echo '<a href="'.$href.'">'.$href."</a></br>\n";
      ?><br/>
    </p>
    <br/>
    <p>Result of KB request:
      <pre>
        <?php 
          echo $KB;
        ?>
      </pre>
    </p>


  </body>

</html>