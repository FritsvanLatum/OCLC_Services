<!DOCTYPE html>
<html>
  <head>
    <title>TEST FAST</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once '../FAST_Service.php';
    $fast = new FAST_Service();
    $no = isset($_POST['no']) ? $_POST['no'] : '12345';
    //$response = isset($_POST['response']) ? $_POST['response'] : 'justlinks.json';
    //$fast->response_format = $response;
    $fast->fast_get_data($no);
    //file_put_contents('fast_'.$no.'.json',json_encode($fast->fast_record,JSON_PRETTY_PRINT));
    ?>

    <form method="post">
        FAST number : <input name="no" value="<?php echo $no; ?>"/><br/>
        <!--Response format: <br/>

        <input type="radio" id="justlinks.json" name="response" checked="checked" value="justlinks.json"/>
        <label for="justlinks.json">justlinks.json</label>
        <br/>
        <input type="radio" id="fast.json" name="response" value="fast.json"/>
        <label for="fast.json">fast.json</label>
        <br/>-->
        <input type="submit"/>
    </form>

    <br/>
    <p>Result of fast get data request:
      <pre>
        <?php 
          echo $fast;
        ?>
      </pre>
    </p>


  </body>

</html>