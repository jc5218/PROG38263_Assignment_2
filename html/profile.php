<!DOCTYPE html>
<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
  </head>
<body bgcolor="#E6E6FA">  

<?php
   include("config.php");
   session_start();

   // Retrieve the username for the current logged in user
   $uname = $_SESSION['current_user'];
   if ($uname == '') {
        header("location: login.php");
   }

?>
  
<div align="center" id="main">
<p>
<h1>User Details for <?php echo $uname ?></h1>
</p>
</div>
<p></p>
<?php

   // Generate token for CSRF protection
   $token = bin2hex(openssl_random_pseudo_bytes(10));
   
   $uname1 = $pwd = $av = $email = '';
   $unameErr = $passErr = $passErr2 = $emailErr = $imgErr = '';

   // Retrieve password, email, avatar and salt information for that user
   $result = pg_execute($dbconn, "check_exist", array($uname));
   $result2 = pg_execute($dbconn, "image_query", array($uname));

   $pwd = pg_fetch_result($result, 0, 1);
   $email = pg_fetch_result($result, 0, 2);
   $img1 = pg_fetch_result($result2, 0, 0);
   $salt = pg_fetch_result($result, 0, 6);
   
   $errFound = false;
   $relog = false;

   if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
   if ((isset($_POST['change'])) && (isset($_POST['csrfToken']))) {

        // Sanitize and validate the username to be changed to, and ensure the new username hasn't already been taken
        if (!empty($_POST["username"])) {
             $uname1 = test_input($_POST["username"]);
             if (!preg_match("/^[a-zA-Z0-9]*$/", $uname1)) {
                  $errFound = true;
                  $unameErr = "Username can only consist of alphabetical characters and numbers.";
             }
             $query = pg_execute($dbconn, "check_exist", array($uname1));
             if (pg_num_rows($query) != 0) {
                  $errFound = true;
                  $unameErr = "Username already taken.";
             }
        }

        // Sanitize, salt and hash the desired new password and old passwords, and then ensures that the old password must be entered correctly for a password change to be allowed
        if (!empty($_POST["password"])) {
             $pwd1 = test_input($_POST["password"]);
             $pwd2 = $pwd1.$salt;
             $pwd2 = hash("sha256", $pwd2);
             $oldpwd = test_input($_POST["password2"]);
             $oldpwd2 = $oldpwd.$salt;
             $oldpwd2 = hash("sha256", $oldpwd2);
             if ((empty($_POST["password2"])) || ($oldpwd2 != $pwd)) {
                  $errFound = true;
                  $passErr2 = "You must confirm the old password before changing to a new password.";
             }
             if (!preg_match("/^[a-zA-Z0-9]*$/", $pwd1)) {
                  $errFound = true;
                  $passErr = "Password can only consist of alphabetical characters and numbers.";
             }
        }

        // Sanitize and validate the email to be changed to
        if (!empty($_POST["email"])) {
             $email1 = test_input($_POST["email"]);
             if (!preg_match("/^[a-zA-Z0-9\.\-]+[@][a-zA-Z0-9\-]+[\.][a-zA-Z\.]+$/", $email1)) {
                  $errFound = true;
                  $emailErr = "E-mail addresses must be valid.";
             }
        }

        if ($errFound == false) {             

             // Update the new username if desired and force a logout
             if (!empty($_POST["username"]) && ($uname1 != $uname)) {
                  $result = pg_execute($dbconn, "update_username", array($uname1, $uname));
                  $result = pg_execute($dbconn, "update_avatar", array($uname1, $uname));
                  $date = date_format(date_create(), 'Y-m-d H:i:s');
                  $log = $date.": ".$uname." - username change to ".$uname1."\n";
                  file_put_contents($file, $log, FILE_APPEND);   
                  $uname = $uname1;
                  $relog = true;
             }

             // Generate a new salt and hash the new password before updating the user account with both, then force a logout
             if (!empty($_POST["password"]) && ($pwd2 != $pwd)) {
                  $newsalt = bin2hex(openssl_random_pseudo_bytes(10));
                  $pwd1 = test_input($_POST["password"]);
                  $pwd2 = $pwd1.$newsalt;
                  $pwd2 = hash("sha256", $pwd2);
                  $query = pg_execute($dbconn, "pwd_reset", array($pwd2, $uname));
                  $query = pg_execute($dbconn, "pwd_reset2", array($newsalt, $uname));
                  $date = date_format(date_create(), 'Y-m-d H:i:s');
                  $log = $date.": ".$uname." - password change\n";
                  file_put_contents($file, $log, FILE_APPEND);
                  $relog = true;
             }

             // Update the new email address if desired
             if (!empty($_POST["email"]) && ($email1 != $email)) {
                  $result = pg_execute($dbconn, "update_email", array($email1, $uname));
                  $date = date_format(date_create(), 'Y-m-d H:i:s');
                  $log = $date.": ".$uname." - email change to ".$email."\n";
                  file_put_contents($file, $log, FILE_APPEND); 
             }

             // If the username or password has been changed, force a logout and make the user log back in again before making further changes
             if ($relog == true) {
                  session_destroy();
                  header("location: login.php");
             } else {
                  header("location: profile.php");
             }
        }

   }
   
   // Close connection
   pg_close($dbconn);
        
   
   }
   
?>

<div id="profile">
  <form action="" method="post">
  <input type="hidden" name="csrfToken" value="<?php echo $token; ?>" />
  Username: <span class="error"><?php echo $unameErr;?></span> <input type="text" name="username" placeholder="<?php echo $uname; ?>" size="40" /><p />
  Password: <span class="error"><?php echo $passErr;?></span> <input type="password" name="password" size="40" /><p />
  Old Password: <span class="error"><?php echo $passErr2;?></span> <input type="password" name="password2" size="40" /><p />
  Email: <span class="error"><?php echo $emailErr;?></span> <input type="text" name="email" placeholder="<?php echo $email; ?>" size="40" /><p />
  Avatar:<br></br>
  <img src="<?php echo $img1; ?>" width="120" height="120"><br></br>
  <a href="chgimg.php">Change Avatar</a>
  <p></p>
  <input type="submit" name="change" value="Change Information">

  <p></p>
  </form>
<a href="logout.php">Log Out</a>  
<p></p>
</div>
</body>
</html>
