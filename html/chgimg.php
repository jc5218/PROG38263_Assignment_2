<!DOCTYPE html>
<html>
  <head>
    <link href="style.css" rel="stylesheet" type="text/css">
  </head>
<body bgcolor="#E6E6FA">  

<?php
   include("config.php");
   session_start();

   // Retrieve the current username
   $uname = $_SESSION['current_user'];

   // Generate token for CSRF protection
   $token = bin2hex(openssl_random_pseudo_bytes(10));
   
   $imgErr = '';

   // Retrieve the current avatar for the user
   $result = pg_execute($dbconn, "image_query", array($uname));

   $img1 = pg_fetch_result($result, 0, 0);
   
   if (($_SERVER["REQUEST_METHOD"] == "POST") && (isset($_POST['csrfToken']))) {

   // Assemble the URL and retrieve the image type
   $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);   
   $uploadOk = 1;
   $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
   
   // Check if image file is a actual image or fake image
   if(isset($_POST["submit"])) {
        $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
        if($check !== false) {
             $imgErr = "File is an image - " . $check["mime"] . ".";
             $uploadOk = 1;
        } else {
             $imgErr = "File is not an image.";
             $uploadOk = 0;
        }
   }
   
   // Check if file already exists; Update the user's avatar to that file if so, otherwise add a new entry
   if (file_exists($target_file)) {
        if (pg_num_rows($result) == 0) {
             $result = pg_execute($dbconn, "add_avatar", array($uname, $target_file));
        } else {
             $result = pg_execute($dbconn, "update_avatar2", array($target_file, $uname));
        }
        $date = date_format(date_create(), 'Y-m-d H:i:s');
        $log = $date.": ".$uname." - avatar change to ".$target_file."\n";
        file_put_contents($file, $log, FILE_APPEND);
        header("location: profile.php");
   }
   
   // Check file size and disallow files over 500kb
   if ($_FILES["fileToUpload"]["size"] > 500000) {
        $imgErr = "Sorry, your file is too large.";
        $uploadOk = 0;
}

   // Allow certain file formats
   if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        $imgErr = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
   }

   // Check if $uploadOk is set to 0 by an error
   if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
             if (pg_num_rows($result) == 0) {
                  $result = pg_execute($dbconn, "add_avatar", array($uname, $target_file));
             } else {
                  $result = pg_execute($dbconn, "update_avatar2", array($target_file, $uname));
             }
        $date = date_format(date_create(), 'Y-m-d H:i:s');
        $log = $date.": ".$uname." - avatar change to ".$target_file."\n";
        file_put_contents($file, $log, FILE_APPEND);
        header("location: profile.php");

        } else {
             $date = date_format(date_create(), 'Y-m-d H:i:s');
             $log = $date.": ".$uname." - unsuccessful avatar change\n";
             file_put_contents($file, $log, FILE_APPEND);
             $imgErr = "Unsuccessful file upload.";
        }
   }

}

?>

<div id="profile">
  <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrfToken" value="<?php echo $token; ?>" />
    Current Avatar: <span class="error"><?php echo $imgErr;?></span><br></br>
    <img src="<?php echo $img1; ?>" width="120" height="120"><br></br>
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Change Avatar" name="submit">
    <p></p>
    <a href="profile.php">Return to Profile</a>
</form>
</div>

</body>
</html>
