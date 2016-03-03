<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
    <?php

       // Include the main config file and start session
       include("config.php");
       session_start();

       $log = '';

       // Generate token for CSRF protection
       $token = bin2hex(openssl_random_pseudo_bytes(10));

       // If the current user is still logged in, automatically redirect to the profile page
       if (isset($_SESSION['current_user'])) {
            header("location: profile.php");
       }
       
       $error = '';

       // Retrieve the username and password from the fields
       $username = test_input($_POST['username']);
       $password = test_input($_POST['password']);
       
       if (!empty($username) && !empty($password) && (isset($_POST['csrfToken']))) {

            // Retrieve the desired user's salt to hash the typed password
            $query = pg_execute($dbconn, "check_exist", array($username));
            $salt = pg_fetch_result($query, 0, 6);
            $pwd = $password.$salt;
            $pwd = hash("sha256", $pwd);

            // Then match the username and password hash to the database
            $query = pg_execute($dbconn, "login_query", array($username, $pwd));

            // Set the timestamp for log purposes
            $date = date_format(date_create(), 'Y-m-d H:i:s');

            // If the user exists, check to see if the user:
            // a) has been activated, and
            // b) has not been locked out due to too many logins.
            if (pg_num_rows($query) == 1) {
       
                 $query = pg_execute($dbconn, "check_exist", array($username));
                 $actcheck = pg_fetch_result($query, 0, 3);
                 $lockcheck = pg_fetch_result($query, 0, 4);

                 // If the user is valid (activated and not locked out),
                 // Reset the number of retries and log the user in
                 if (($actcheck == 'Y') && ($lockcheck == 'N')) {
                      $query = pg_execute($dbconn, "set_retries", array(0, $username));
                      $_SESSION['current_user'] = $username;

                      // Add a log entry
                      $log = $date.": ".$username." - login successful\n";
                      file_put_contents($file, $log, FILE_APPEND);
                      header("location: profile.php");
                 } else {

                      // Add log entries if the user attempts and fails to login
                      if ($actcheck == 'N') {
                           $log = $date.": ".$username." - unactivated account login\n";
                           file_put_contents($file, $log, FILE_APPEND);
                           $error = 'Account has not yet been activated';
                      } else {

                           // Log if a locked user tries to log in
                           $log = $date.": ".$username." - locked account login\n";
                           file_put_contents($file, $log, FILE_APPEND);
                           $query = pg_execute($dbconn, "email_query", array($username));
                           $email = pg_fetch_result($query, 0, 0);
                           $_SESSION['lock'] = $email;
                           $_SESSION['temp_user'] = $username;
                           header("location: lock.php");
                      }
                 }

            // Lock the target user and log after too many retries
            } else {
                 $query = pg_execute($dbconn, "check_exist", array($username));
                 if (pg_num_rows($query) == 1) {
                      $query = pg_execute($dbconn, "check_retries", array($username));
                      $tries = pg_fetch_result($query, 0, 0);
                      if ($tries >= 4) {
                           $result = pg_execute($dbconn, "lock", array($username));
                           $log = $date.": ".$username." - account locked\n";
                           file_put_contents($file, $log, FILE_APPEND);
                           $query = pg_execute($dbconn, "email_query", array($username));
                           $email = pg_fetch_result($query, 0, 0);
                           $_SESSION['lock'] = $email;
                           $_SESSION['temp_user'] = $username;
                           header("location: lock.php");
                      } else {
                           $log = $date.": Attempted login using account ".$username."\n";
                           file_put_contents($file, $log, FILE_APPEND);
                           $tries = $tries + 1;
                           $query = pg_execute($dbconn, "set_retries", array($tries, $username));
                           $error = 'Username or password is invalid, attempts made: '.$tries;
                      }
                 } else {
                      $error = 'User does not exist.';
                 }
            }
    
            pg_free_result($query);
            pg_close($dbconn);
       }
    ?>
  </head>
  <body bgcolor="#E6E6FA">
    <p></p>
    <div align="center" id="main">
      <h1>User Login</h1>
    </div>
    <div id="profile">
      <form name="login_form" action="" method="post">
	<input type="hidden" name="csrfToken" value="<?php echo $token; ?>" />
	Username:<input type="text" name="username" size="40" /><br />
	Password:<input type="password" name="password" size="40" /><br />
	<p></p>
	<input type="submit">
	<p></p>
	<a href="newuser2.php">Register as New User</a>
	<br></br>
	<a href="forgot.php">Forgot Password?</a>
	<span><?php echo "<p>".$error."</p>"; ?></span>
    </form>
    </div>
  </body>
</html>
