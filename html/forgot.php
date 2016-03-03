<!DOCTYPE html>
<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css">
</head>  
<body bgcolor="#E6E6FA">

  <div align="center" id="main">
    <h1>Forgot Password?</h1>
  </div>

  <?php

     include("config.php");
     session_start();

     // Sanitize inputted username and email
     $uname = test_input($_POST['uname']);
     $email = test_input($_POST['email']);
     $unameErr = $emailErr = "";

     // Generate token for CSRF protection
     $token = bin2hex(openssl_random_pseudo_bytes(10));     
     
     if (!empty($uname) && !empty($email) && isset($_POST['csrfToken'])) {

          // Match up the username and email to the database to see if the username exists and is associated with that email
          $query = pg_execute($dbconn, "check_exist", array($uname));
          if (pg_num_rows($query) != 0) {
     
               $query = pg_execute($dbconn, "email_query", array($uname));
               $email2 = pg_fetch_result($query, 0, 0);

               // Deactivate the account if the username and email matches
               if ($email == $email2) {
                    $date = date_format(date_create(), 'Y-m-d H:i:s');
                    $query = pg_execute($dbconn, "unactivate", array($uname));

                    // Generate a random code and use that to send a password reset email to the user
                    $code = bin2hex(openssl_random_pseudo_bytes(20));
                    $query = pg_execute($dbconn, "add_verify", array($code, $uname, $email, 'reset'));
                    $log = $date.": ".$uname." - Sent password reset email at https://".$ip."/reset.php?code=".$code."\n";
                    file_put_contents($file, $log, FILE_APPEND);
                    header("location: confirm.php");
               } else {
                    $emailErr = "Incorrect or empty email.";
               }
          } else {
               $unameErr = "User does not exist.";
          }
     
     } else {
 
          $unameErr = "Username cannot be empty.";
     
     }

     pg_close($dbconn);
     
  ?>
  

  <div id="profile">
   <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
	 method="post">
     <input type="hidden" name="csrfToken" value="<?php echo $token; ?>" />
     Username: <span class="error">* <?php echo $unameErr;?></span> <input type="text" name="uname">
     Email: <span class="error">* <?php echo $emailErr;?></span> <input type="text" name="email">
     <p></p>
     <input type="submit" name="submit">
     <p></p>
     <a href="login.php">Return to Login</a>
     <br></br>
     <a href="newuser2.php">Register as New User</a>
  </form>
  </div>
  
</body>
</html>
