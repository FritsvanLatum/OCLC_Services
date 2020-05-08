<!DOCTYPE html>
<html class="js svg rgba backgroundsize boxshadow borderradius" lang="en-US">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>Account Library card | Peace Palace Library</title>

    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" href="css/registration.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="js/regValidators.js"></script>
    <script type="text/javascript" src="js/settings.js"></script>
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
          <script type="text/javascript" src="je_assets/jsoneditor/lists.js"></script>
          <script type="text/javascript" src="je_assets/jsoneditor/regSchema.js"></script>
          <script type="text/javascript" src="je_assets/jsoneditor/accountForm.js"></script>
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

  </body>
</html>