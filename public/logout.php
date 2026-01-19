<?php
/**
 * Logout Page
 * Destroys session and redirects to home
 */

require_once __DIR__ . '/../includes/auth.php';

logoutUser();

header('Location: /index.php');
exit();
?>
