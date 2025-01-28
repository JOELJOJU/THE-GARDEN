<?php
session_start(); // Start or resume the session
session_unset(); // Unset all session variables
session_destroy(); // Destroy the session

// Redirect to the logout page after logging out
header("Location: logout.html"); // Your HTML page showing the logout message
exit();
?>
