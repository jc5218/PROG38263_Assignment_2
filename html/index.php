<?php

include_once __DIR__ .'/libs/csrf/csrfprotector.php';

//Initialise CSRFGuard library
csrfProtector::init();
header('location: login.php');

?>