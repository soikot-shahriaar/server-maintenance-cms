<?php
/**
 * Delete Maintenance Log Script
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';
require_once 'includes/MaintenanceLog.php';

// Require login
requireLogin();

// Get log ID from URL
$logId = intval($_GET['id'] ?? 0);
if (!$logId) {
    header('Location: logs.php?error=invalid_id');
    exit();
}

$maintenanceLog = new MaintenanceLog();
$log = $maintenanceLog->getById($logId);

if (!$log) {
    header('Location: logs.php?error=log_not_found');
    exit();
}

// Check if user can delete this log (admin or owner)
if (!isAdmin() && $log['performed_by'] != $_SESSION['user_id']) {
    header('Location: logs.php?error=access_denied');
    exit();
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        header('Location: view_log.php?id=' . $logId . '&error=invalid_token');
        exit();
    }
    
    $result = $maintenanceLog->delete($logId);
    
    if ($result['success']) {
        header('Location: logs.php?message=deleted');
        exit();
    } else {
        header('Location: view_log.php?id=' . $logId . '&error=delete_failed');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Maintenance Log - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Delete Maintenance Log</h1>
            <a href="view_log.php?id=<?php echo $logId; ?>" class="btn btn-secondary">← Back to Log</a>
        </div>
        
        <div class="confirmation-card">
            <div class="warning-icon">⚠️</div>
            <h2>Confirm Deletion</h2>
            <p>Are you sure you want to delete this maintenance log? This action cannot be undone.</p>
            
            <div class="log-summary">
                <h3>Log Details:</h3>
                <ul>
                    <li><strong>Server:</strong> <?php echo htmlspecialchars($log['server_name']); ?></li>
                    <li><strong>Date:</strong> <?php echo formatDate($log['maintenance_date']); ?></li>
                    <li><strong>Type:</strong> <?php echo ucfirst($log['maintenance_type']); ?></li>
                    <li><strong>Status:</strong> <?php echo ucfirst(str_replace('_', ' ', $log['status'])); ?></li>
                </ul>
            </div>
            
            <div class="confirmation-actions">
                <form method="POST" action="delete_log.php?id=<?php echo $logId; ?>" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="btn btn-danger">Yes, Delete Log</button>
                </form>
                <a href="view_log.php?id=<?php echo $logId; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

