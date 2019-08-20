<!DOCTYPE html>
<html>
  <head>
    <title>TEST SOLR</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    //require_once './searchPage.php';
    //$search = new SearchPage();
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
        //http://localhost:8983/solr/ppl/select?q=*%3A*
          $result = file_get_contents('http://localhost:8983/solr/ppl/select?q='.$userQuery);
          echo $result;//json_encode($result,JSON_PRETTY_PRINT);
        ?>
      </pre>
    </p>


  </body>

</html>