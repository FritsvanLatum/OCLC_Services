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
    $ocn = isset($_POST['ocn']) ? $_POST['ocn'] : '887933119';
    $response = isset($_POST['response']) ? $_POST['response'] : 'application/json';
    $discovery->read_headers['Accept'] = $response;
    $discovery->wcds_read_record($ocn);
    $discovery->wcds_db_list();
    ?>

    <form method="post">
        OCLC number (ocn): <input name="ocn" value="<?php echo $ocn; ?>"/><br/>
        Response format: <br/>
        <input type="radio" id="application/json" name="response" checked="checked" value="application/json"/>
        <label for="application/json">application/json</label>
        <br/>
        <input type="radio" id="application/rdf+xml" name="response" value="application/rdf+xml"/>
        <label for="application/rdf+xml">application/rdf+xml</label>
        <br/>
        <input type="radio" id="text/plain" name="response" value="text/plain"/>
        <label for="text/plain">text/plain</label>
        <br/>
        <input type="radio" id="text/turtle" name="response" value="text/turtle"/>
        <label for="text/turtle">text/turtle</label>
        <br/>
        <input type="radio" id="application/ld+json" name="response" value="application/ld+json"/>
        <label for="application/ld+json">application/ld+json</label>
        <br/>
<!--application/rdf+xml
text/plain
text/turtle
application/ld+json
application/json-->  

        <input type="submit"/>
    </form>

    <p>Result:
      <pre>
        <?php 
          echo $discovery;
        ?>
      </pre>
    </p>


  </body>

</html>