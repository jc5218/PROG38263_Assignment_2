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

     // Check if the activation code resides in the database (is proper and expected and hasn't already been used) and is of the correct type (activation)
     $query = pg_execute($dbconn, "check_verify", array($code));
     $type = pg_fetch_result($query, 0, 3);
     if ((pg_num_rows($query) == 1) && ($type == 'activate')) {

          // Retrieve the username and email associated with the code
          $uname = pg_fetch_result($query, 0, 1);
          $email = pg_fetch_result($query, 0, 2);

          if (!empty($_POST["pwd"])) {

               // Retrieve the password hash and salt for the target user and use the salt to hash the user's password confirmation
               $pwd = test_input($_POST["pwd"]);
               $query = pg_execute($dbconn, "check_exist", array($uname));
               $pwd1 = pg_fetch_result($query, 0, 1);
               $salt = pg_fetch_result($query, 0, 6);
               $pwd2 = $pwd.$salt;
               $pwd2 = hash("sha256", $pwd2);

               // Activate the account and remove the verification entry from the database to denote that the activation code has been used
               if ($pwd1 == $pwd2) {
                    $query = pg_execute($dbconn, "activate", array($uname));
                    $query = pg_execute($dbconn, "remove_verify", array($code));
                    $date = date_format(date_create(), 'Y-m-d H:i:s');
                    $log = $date.": ".$uname." - Account successfully activated.\n";
                    file_put_contents($file, $log, FILE_APPEND);
                    $_SESSION['temp_user'] = $uname;
                    header("location: success0.php");
     
               } else {
                    $passErr = "Incorrect password.";
               }

          } else {
               $passErr = "Password field cannot be empty.";
          }
     } else {

               // Log if someone tries to use the code again after account activation or tries to use a code of a different type (i.e. a reset password code) on the activation page
               $date = date_format(date_create(), 'Y-m-d H:i:s');
               $log = $date.": Outdated or incorrect activation code ".$code." used\n";
               file_put_contents($file, $log, FILE_APPEND);
               header("location: login.php");
     }

  ?>
  
  
<p></p>
<div align="center" id="main">
<h1>Account Activation</h1>
</div>
<p></p>
<div align="center" id="profile">
<p></p>
<form action="" method="post">
  Password: <span class="error">* <?php echo $passErr;?></span> <input type="password" name="pwd"><br></br>
  <p></p>
  <input type="submit" name="submit" value="Activate account">
  </form>
<p></p>
<a href="login.php">Return to Login</a>
</div>
</body>
</html>
