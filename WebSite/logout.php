<?php
// start the session
session_start();

// destroy the session
session_destroy();

// redirect the user to the login page
header("Location: login.php");
exit();
?>