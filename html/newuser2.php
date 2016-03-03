<!DOCTYPE html>
<html>
<head>
<link href="style.css" rel="stylesheet" type="text/css">
</head>  
<body bgcolor="#E6E6FA">

<div align="center" id="main">
<p></p>
<h1><font size="6" align="middle">Create New User</font></h1>
<p></p>
</div>

<?php
   include("config.php");
   session_start();

   $token = bin2hex(openssl_random_pseudo_bytes(10));
   
// define variables and set to empty values
$unameErr = $passErr = $emailErr = "";
$uname = $pass = $email = "";

// Reset the number of errors found   
$errFound = false;   

if (($_SERVER["REQUEST_METHOD"] == "POST") && (isset($_POST['csrfToken']))) {

  // Check if the username is empty; If not, check if the username field contains only alphabetical characters and numbers
  if (empty($_POST["uname"])) {
    $errFound = true;
    $unameErr = "Username cannot be empty.";
  } else {
    $uname = test_input($_POST["uname"]);
    if (!preg_match("/^[a-zA-Z0-9]*$/", $uname)) {
     $errFound = true;
     $unameErr = "Username can only consist of alphabetical characters and numbers.";
    }
   }

   // Check if the password is empty; If not, check if the password field contains only alphabetical characters and numbers
  if (empty($_POST["pass"])) {
    $errFound = true;
    $passErr = "Password cannot be empty.";
  } else {
    $pass = test_input($_POST["pass"]);
    if (!preg_match("/^[a-zA-Z0-9]*$/", $pass)) {
     $errFound = true;
     $passErr = "Password can only consist of alphabetical characters and numbers.";
    }
  }

  // Check if the email field is empty; If not, ensure that the email is a valid email address
  if (empty($_POST["email"])) {
    $errFound = true; 
    $emailErr = "E-mail cannot be empty.";
  } else {
    $email = test_input($_POST["email"]);
    if (!preg_match("/^[a-zA-Z0-9\.\-]+[@][a-zA-Z0-9\-]+[\.][a-zA-Z\.]+$/", $email)) {
     $errFound = true;
     $emailErr = "E-mail addresses must be valid.";
    }
  }

  // If there are no erroneous inputs on all of the required fields, initiate a prepared statement to insert the user's info into the database if the username hasn't been taken, and send an activation email to the user's email
  if ($errFound == false) {

   $date = date_format(date_create(), 'Y-m-d H:i:s');

   // Add the user into an initial state (unactivated, unlocked, 0 retries) if the username hasn't been taken
   $result = pg_execute($dbconn, "check_exist", array($uname));
   if (pg_num_rows($result) == 0) {

        // Salt and hash the password before adding the user
        $salt = bin2hex(openssl_random_pseudo_bytes(10));
        $pwd = $pass.$salt;
        $pwd = hash("sha256", $pwd);
        $result = pg_execute($dbconn, "insert_user", array($uname, $pwd, $email, 'N', 'N', 0, $salt));

        // Send temporary session data on username and email, generate a random code, and use that code to send an activation email link to the user
        $_SESSION['temp_user'] = $uname;
        $_SESSION['activation'] = $email;
        $code = bin2hex(openssl_random_pseudo_bytes(20));
        $query = pg_execute($dbconn, "add_verify", array($code, $uname, $email, 'activate'));
        $log = $date.": ".$uname." - New user created, pending activation at https://".$ip."/verify.php?code=".$code."\n";
        file_put_contents($file, $log, FILE_APPEND);
        header("location: activation.php");
   } else {
        $log = $date.": ".$uname." - account creation unsuccessful\n";
        file_put_contents($file, $log, FILE_APPEND);
        $unameErr = "Username already taken.";
   }
   
  }

  // Close connection
   pg_close($dbconn);

}
   
?>

<p><span class="error">* required field.</span></p>

<div id="profile">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"
      method="post">
  <input type="hidden" name="csrfToken" value="<?php echo $token; ?>" />
  Username: <span class="error">* <?php echo $unameErr;?></span> <input type="text" name="uname">
  <p>
  Password: <span class="error">* <?php echo $passErr;?></span> <input type="password" name="pass">
  <p>
  E-mail: <span class="error">* <?php echo $emailErr;?></span> <input type="text" name="email">
  <p>
  <input type="submit" name="submit">
  <p></p>
  <a href="login.php">Existing User Login</a>
  <p></p>
</form>
</div>
</body>
</html>
