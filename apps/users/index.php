<!DOCTYPE html>
<html class="js svg rgba backgroundsize boxshadow borderradius" lang="en-US">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <title>User registration | Peace Palace Library</title>

    <link rel="stylesheet" type="text/css" href="css/bootstrap-combined.min.css" id="theme_stylesheet">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.css" id="icon_stylesheet">
    <link rel="stylesheet" href="css/registration.css">

    <script type="text/javascript" src="js/jquery.min.js"></script>
    <script type="text/javascript" src="js/jsoneditor.min.js"></script>
    <script type="text/javascript" src="js/regValidators.js"></script>
    <script type="text/javascript" src="js/settings.js"></script>

  </head>

  <body>
    <h1>Register</h1>
    <p>If you already registered using this web page, please <a href="sign-in.php">sign in</a></p>
    <p>If you have a library card, but did not register yet on this website, go to <a href="this page">this page</a>.</p>
    
    <div id = "editorDiv">
      <div id="editor"></div>
      <button id="submit">Send</button>
      <button id="empty">Empty form</button>
      <script type="text/javascript" src="js/lists.js"></script>
      <script type="text/javascript" src="schema/regSchema.js"></script>
      <script type="text/javascript" src="js/regForm.js"></script>
      <div id="res" class="alert"></div>
    </div>

  </body>
</html>