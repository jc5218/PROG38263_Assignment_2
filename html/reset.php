<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css">
</head>  
<body bgcolor="#E6E6FA">

  <?php

     include("config.php");
     session_start();

     $passErr = '';

     // Retrieve the random code from the URL
     $parts = parse_url($_SERVER["REQUEST_URI"]);
     parse_str($parts['query'], $query);
     $code = test_input($query['code']);

     // Generate token for CSRF protection
     $token = bin2hex(openssl_random_pseudo_bytes(10));

     // Make sure the code is in the database (is proper, expected and has not been used) and is of the correct type (password reset)
     $query = pg_execute($dbconn, "check_verify", array($code));
     $type = pg_fetch_result($query, 0, 3);

     if ((pg_num_rows($query) == 1) && ($type == 'reset')) {

          // Retrieve the username associated with that code if it is valid
          $username = pg_fetch_result($query, 0, 1);
     
          if ((empty($_POST["pwd"])) || (!isset($_POST['csrfToken']))) {
               $passErr = "You must input a new password.";
          } else {

               // Sanitize and validate the password input
               $pwd = test_input($_POST["pwd"]);
               if (preg_match("/^[a-zA-Z0-9]*$/", $pwd)) {

                    // Generate a new salt and hash the new password
                    $salt = bin2hex(openssl_random_pseudo_bytes(10));
                    $pwd2 = $pwd.$salt;
                    $pwd2 = hash("sha256", $pwd2);

                    // Reactivate the account and add the new password and salt to that user's account entry
                    $result = pg_execute($dbconn, "activate", array($username));
                    $result = pg_execute($dbconn, "pwd_reset", array($pwd2, $username));
     $result = pg_execute($dbconn, "pwd_reset2", array($salt, $username));

                    // Remove the code from the database after it has been used and add a log entry
                    $query = pg_execute($dbconn, "remove_verify", array($code));
                    $date = date_format(date_create(), 'Y-m-d H:i:s');
                    $log = $date.": ".$username." - Password reset successful\n";
                    file_put_contents($file, $log, FILE_APPEND);
                    $_SESSION['temp_user'] = $username;
                    header("location: success.php");
               } else {
                    $passErr = "Password must only consist of alphabetical characters and numbers.";
               }
          }

     } else {

          // Log if the code is accessed after being used once or is of an incorrect type (i.e. activation)
          $date = date_format(date_create(), 'Y-m-d H:i:s');
          $log = $date.": Outdated or incorrect password reset code ".$code." used\n";
          file_put_contents($file, $log, FILE_APPEND);
          header("location: login.php");
     }
     
  ?>
  
  
<p></p>
<div align="center" id="main">
<h1>Password Reset for <?php echo $username; ?></h1>
</div>
<p></p>
<div align="center" id="profile">
<p></p>
<form action="" method="post">
  <input type="hidden" name="csrfToken" value="<?php echo $token; ?>" />
  New Password: <span class="error">* <?php echo $passErr;?></span> <input type="password" name="pwd"><br></br>
  <p></p>
  <input type="submit" name="submit" value="Reset Password">
  </form>
<p></p>
<a href="login.php">Return to Login</a>
</div>
</body>
</html>
