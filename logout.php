<?php
require_once 'includes/functions.php';

// Destroy all session data
session_destroy();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Redirect to home page
header('Location: index.php');
exit();
?>
