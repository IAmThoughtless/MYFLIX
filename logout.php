<?php
// Start session to access session variables
session_start();
// Clear all session data
$_SESSION = array(); 
// Destroy the session cookie
session_destroy();  
// Redirect to the login page
header("Location: login.php"); 
exit;
?>