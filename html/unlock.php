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

     // Check the code against the database to ensure it is valid (is proper, expected and has not been used) and is of the correct type (account unlock)
     $query = pg_execute($dbconn, "check_verify", array($code));
     $type = pg_fetch_result($query, 0, 3);

     if ((pg_num_rows($query) == 1) && ($type == 'lock')) {

          // Retrieve the username associated with the code if it is valid
          $username = pg_fetch_result($query, 0, 1);
     
          if (!empty($_POST["pwd"])) {

               // Retrieve the password hash and salt for that user, then use that salt to salt and hash the password confirmation entry
               $pwd = test_input($_POST["pwd"]);
               $query = pg_execute($dbconn, "check_exist", array($username));
               $pwd1 = pg_fetch_result($query, 0, 1);
               $salt = pg_fetch_result($query, 0, 6);
               $pwd2 = $pwd.$salt;
               $pwd2 = hash("sha256", $pwd2);

               // Unlock account and reset retries if the two hashes match
               if ($pwd1 == $pwd2) {

                    $result = pg_execute($dbconn, "unlock", array($username));
                    $result = pg_execute($dbconn, "set_retries", array(0, $username));

                    // Remove the account unlock code from the database after it has been used once
                    $query = pg_execute($dbconn, "remove_verify", array($code));
                    $date = date_format(date_create(), 'Y-m-d H:i:s');
                    $log = $date.": ".$username." - Account unlock successful\n";
                    file_put_contents($file, $log, FILE_APPEND);
                    $_SESSION['temp_user'] = $username;
                    header("location: success2.php");
               } else {
                    $passErr = "Incorrect password.";
               }
     
          } else {
               $passErr = "You must enter the password for account unlock.";
          }

     } else {
          // Log if the code is reused after being used once or is of the incorrect type (i.e. activation)
          $date = date_format(date_create(), 'Y-m-d H:i:s');
          $log = $date.": Outdated or incorrect account unlock code ".$code." used\n";
          file_put_contents($file, $log, FILE_APPEND);
          header("location: login.php");
     }
     
  ?>
  
  
<p></p>
<div align="center" id="main">
<h1>Account unlock</h1>
</div>
<p></p>
<div align="center" id="profile">
<p></p>
<form action="" method="post">
  Password: <span class="error">* <?php echo $passErr;?></span> <input type="password" name="pwd"><br></br>
  <p></p>
  <input type="submit" name="submit" value="Unlock account">
  </form>
<p></p>
<a href="login.php">Return to Login</a>
</div>
</body>
</html>
