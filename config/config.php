<?php
/**
 * General Application Configuration
 * Server Maintenance Log CMS
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Dynamically detect project folder and base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Remove port if present (e.g., :8080)
    $host = preg_replace('/:\d+$/', '', $host);
    
    // Get the document root and script file paths
    $documentRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $scriptFile = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    
    // Calculate the base path (project folder)
    $basePath = str_replace($documentRoot, '', dirname($scriptFile));
    $basePath = str_replace('\\', '/', $basePath);
    $basePath = trim($basePath, '/');
    
    // Construct the base URL
    $baseUrl = $protocol . $host;
    if (!empty($basePath)) {
        $baseUrl .= '/' . $basePath;
    }
    
    return rtrim($baseUrl, '/');
}

// Application settings
define('APP_NAME', 'Server Maintenance Log CMS');
define('APP_VERSION', '1.0.0');
define('APP_URL', getBaseUrl());

// Security settings
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// Pagination settings
define('RECORDS_PER_PAGE', 10);

// Date and time settings
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'M d, Y');
define('DISPLAY_DATETIME_FORMAT', 'M d, Y H:i');

// Include database configuration
require_once 'database.php';

/**
 * Utility functions
 */

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get full URL for a path
 */
function getUrl($path = '') {
    $path = ltrim($path, '/');
    if (empty($path)) {
        return APP_URL;
    }
    return APP_URL . '/' . $path;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php?error=access_denied');
        exit();
    }
}

/**
 * Format date for display
 */
function formatDate($date, $format = DISPLAY_DATE_FORMAT) {
    return date($format, strtotime($date));
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>

