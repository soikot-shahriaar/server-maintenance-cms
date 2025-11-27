<?php
/**
 * Index Page - Entry Point
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';

// Redirect based on login status
if (isLoggedIn()) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
?>
