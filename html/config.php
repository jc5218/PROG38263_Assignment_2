<?php

	#populate these based on your server setup
	$dbname = "postgres";
	$username = "postgres";
	$password = "abcd1024";
	$dbconn = pg_connect("host=localhost dbname=$dbname user=$username password=$password");

        #Prepared statements that will be universal across the web application
	$prepare = pg_prepare($dbconn, 'login_query', 'SELECT * FROM users WHERE username = $1 AND password = $2');
	$prepare2 = pg_prepare($dbconn, 'insert_user', 'INSERT INTO users VALUES ($1, $2, $3, $4, $5, $6, $7)');
	$prepare4 = pg_prepare($dbconn, 'check_exist', 'SELECT * FROM users WHERE username = $1');
	$prepare5 = pg_prepare($dbconn, 'image_query', 'SELECT avatar FROM user_image WHERE username = $1');
	$prepare6 = pg_prepare($dbconn, 'update_username', 'UPDATE users SET username = $1 WHERE username = $2');
	$prepare8 = pg_prepare($dbconn, 'update_email', 'UPDATE users SET email = $1 WHERE username = $2');
	$prepare9 = pg_prepare($dbconn, 'update_avatar', 'UPDATE user_image SET username = $1 WHERE username = $2');
	$prepare10 = pg_prepare($dbconn, 'update_avatar2', 'UPDATE user_image SET avatar = $1 WHERE username = $2');
	$prepare11 = pg_prepare($dbconn, 'email_query', 'SELECT email FROM users WHERE username = $1');
	$prepare12 = pg_prepare($dbconn, 'unactivate', 'UPDATE users SET activated = \'N\' WHERE username = $1');
	$prepare13 = pg_prepare($dbconn, 'activate', 'UPDATE users SET activated = \'Y\' WHERE username = $1');
	$prepare14 = pg_prepare($dbconn, 'pwd_reset', 'UPDATE users SET password = $1 WHERE username = $2');
	$prepare15 = pg_prepare($dbconn, 'pwd_reset2', 'UPDATE users SET salt = $1 WHERE username = $2');
	$prepare16 = pg_prepare($dbconn, 'check_lock', 'SELECT locked FROM users WHERE username = $1');
        $prepare17 = pg_prepare($dbconn, 'unlock', 'UPDATE users SET locked = \'N\' WHERE username = $1');
	$prepare18 = pg_prepare($dbconn, 'lock', 'UPDATE users SET locked = \'Y\' WHERE username = $1');
	$prepare19 = pg_prepare($dbconn, 'check_retries', 'SELECT retries FROM users WHERE username = $1');
	$prepare20 = pg_prepare($dbconn, 'set_retries', 'UPDATE users SET retries = $1 WHERE username = $2');
	$prepare21 = pg_prepare($dbconn, 'add_verify', 'INSERT INTO verify VALUES ($1, $2, $3, $4)');
	$prepare22 = pg_prepare($dbconn, 'check_verify', 'SELECT * FROM verify WHERE code = $1');
	$prepare23 = pg_prepare($dbconn, 'remove_verify', 'DELETE FROM verify WHERE code = $1');
	$prepare24 = pg_prepare($dbconn, 'add_avatar', 'INSERT INTO user_image VALUES ($1, $2)');

        #Retrieves the localhost IP address
        $ip = $_SERVER['SERVER_ADDR'];

	#Log file location. Alter this according to your settings
        $file = '/home/joseph/logme/log.txt';

	#Avatar upload directory. Alter this according to your settings
	$target_dir = "/home/joseph/Uploads/";

# Function to strip white spaces, HTML tags, special characters, and SQL keywords from the string passed to it. Used to process and sanitize all user information fields.
function test_input($data) {
   $data = trim($data);
   $data = stripslashes($data);
   $data = htmlspecialchars($data);
   $data = strip_tags($data);
   $data = pg_escape_string($data);
   return $data;
}

?>