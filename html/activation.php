<!DOCTYPE html>
<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
  </head>
<body bgcolor="#E6E6FA">

<?php

	include("config.php");
	session_start();
        $email = $_SESSION['activation'];
        $uname = $_SESSION['temp_user'];

?>

<div align="center" id="profile">
<p></p>
An activation email for the account <?php echo $uname; ?> has been sent to your email account at <?php echo $email; ?>; Please click on the verification link to activate the email.
<p></p>

<a href="login.php">Return to Login</a>
</div>
</body>
</html>
