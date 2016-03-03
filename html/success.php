<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css">
</head>  
<body bgcolor="#E6E6FA">

  <?php

     include("config.php");
     session_start();
     $uname = $_SESSION['temp_user'];
     
     ?>
<p></p>
<div align="center" id="profile">
<p></p>
Password successfully reset for account <?php echo $uname; ?>.
<p></p>
<a href="login.php">Return to Login</a>
</div>
</body>
</html>
