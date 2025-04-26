<?php
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to buyer login page
header("Location: buyer_login.php");
exit;
?>
