<!DOCTYPE html>
<html class="js svg rgba backgroundsize boxshadow borderradius" lang="en-US">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>User registration - existing user</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/3.2.1/css/font-awesome.css">
    <link rel="stylesheet" type="text/css" href="css/registration.css">
    <link rel="stylesheet" type="text/css" href="css/circ.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>

    <script type="text/javascript" src="js/countryList.js"></script>
    <script type="text/javascript" src="schema/regSchema.js"></script>
    <script type="text/javascript" src="js/regValidators.js"></script>

  </head>

  <body class="page-template-default page page-id-24 page-parent page-child parent-pageid-463">

    <div id = "editorDiv">
      <div id="editor"></div>
      <?php
      require_once(__DIR__.'/je_assets/php/helpers.php');
      if (isset($_GET['je_ac'])) {
        //only display the form when there is a je_ac parameter
        $record = get_customer_from_code($_GET['je_ac']);
        if ($record){
          //assign the json value from MySql in a javascript variable formValues
          echo '<script>';
          echo "formValues = JSON.parse('".$record['json']."');";
          echo '</script>';
          ?>
          <button id="submit">Send</button>
          <!--<button id="empty">Empty form</button>-->
          <?php
        }
        else {
          //echo ???
        }
      }
      else {
        echo '<p>Please <a href="sign-in.php">sign in</a> first. </p>' ;
      }
      ?>
      <div id="res" class="alert"></div>
    </div>
          <script type="text/javascript" src="je_assets/jsoneditor/accountForm.js"></script>

  </body>
</html>