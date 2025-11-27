<?php
/**
 * Logout Script
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';

// Destroy session and redirect to login
session_destroy();
header('Location: login.php?message=logout');
exit();
?>

