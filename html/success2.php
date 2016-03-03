<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css">
</head>  
<body bgcolor="#E6E6FA">

  <?php

     include("config.php");
     session_start();
     $username = $_SESSION['temp_user'];
     
     ?>
<p></p>
<div align="center" id="profile">
<p></p>
Account <?php echo $username; ?> successfully unlocked.
<p></p>
<a href="login.php">Return to Login</a>
</div>
</body>
</html>
