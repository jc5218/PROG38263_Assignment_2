<!DOCTYPE html>
<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
  </head>
<body bgcolor="#E6E6FA">
<?php
     include("config.php");
     session_start();
     session_destroy();
?>
<p></p>
<div align="center" id="main">
<h1>Logout</h1><p></p>
</div>
<div align="center" id="profile">
<p></p>
You have logged out successfully.
<p></p>
<a href="login.php">Return to Login Page</a><br />
<a href="newuser2.php">Register New User</a><br />
<p></p>
</div>
</body>
</html>
