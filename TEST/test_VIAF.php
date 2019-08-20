<!DOCTYPE html>
<html>
  <head>
    <title>TEST VIAF</title>
    <meta charset="utf-8" />
  </head>

  <body>
    <?php
    require_once '../VIAF_Service.php';
    $viaf = new VIAF_Service();
    $no = isset($_POST['no']) ? $_POST['no'] : '102333412';
    $response = isset($_POST['response']) ? $_POST['response'] : 'justlinks.json';
    $viaf->response_format = $response;
    $viaf->viaf_get_data($no);
    file_put_contents('../output_examples/viaf_'.$no.'.json',json_encode($viaf->viaf_record,JSON_PRETTY_PRINT));
    ?>

    <form method="post">
        VIAF number : <input name="no" value="<?php echo $no; ?>"/><br/>
        Response format: <br/>

        <input type="radio" id="justlinks.json" name="response" checked="checked" value="justlinks.json"/>
        <label for="justlinks.json">justlinks.json</label>
        <br/>
        <input type="radio" id="viaf.json" name="response" value="viaf.json"/>
        <label for="viaf.json">viaf.json</label>
        <br/>
        <input type="submit"/>
    </form>

    <br/>
    <p>Result of VIAF get data request:
      <pre>
        <?php 
          echo $viaf;
        ?>
      </pre>
    </p>


  </body>

</html>