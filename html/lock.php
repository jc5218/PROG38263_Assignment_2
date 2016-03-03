<!DOCTYPE html>
<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
  </head>
<body bgcolor="#E6E6FA">

<?php

	include("config.php");
	session_start();
        $email = $_SESSION['lock'];
        $uname = $_SESSION['temp_user'];

        // Generate a random code and use that to send an account unlock email to the user
        $code = bin2hex(openssl_random_pseudo_bytes(20));
        $query = pg_execute($dbconn, "add_verify", array($code, $uname, $email, 'lock'));
        $date = date_format(date_create(), 'Y-m-d H:i:s');

        $log = $date.": ".$uname." - Account unlock email sent at https://".$ip."/unlock.php?code=".$code."\n";
        file_put_contents($file, $log, FILE_APPEND);   

?>

<div align="center" id="profile">
<p></p>
This account has been locked due to too many failed login attempts.<p></p>
An email has been sent to the email associated with this account. Please click on the link provided in this email to begin unlocking your account.
<p></p>

<a href="login.php">Return to Login</a>
</div>
</body>
</html>
