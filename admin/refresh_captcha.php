<?php
// Start session
session_start();

// Generate new captcha
$_SESSION['captcha'] = rand(1000, 9999);

// Return the new captcha as plain text
echo $_SESSION['captcha'];
?> 