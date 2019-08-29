<!DOCTYPE html>
<html>
  <head>
    <title>TEST SOLR</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once './searchPage.php';
    $search = new SearchPage();
    $userQuery = isset($_POST['userQuery']) ? $_POST['userQuery'] : '*:*';
    ?>

    <form method="post">
        Search : <input name="userQuery" value="<?php echo $userQuery; ?>"/><br/>
        <input type="submit"/>
    </form>

    <br/>
    <p>Search result:
      <pre>
        <?php 
          $search->search($userQuery);
          //echo $search->results["response"]["docs"][0]["listEntry"][0]."<br/>";
          echo json_encode($search,JSON_PRETTY_PRINT);
        ?>
      </pre>
    </p>


  </body>

</html>