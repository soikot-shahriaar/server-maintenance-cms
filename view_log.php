<?php
/**
 * View Maintenance Log Page
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

// Handle any messages
$message = '';
$messageType = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'updated':
            $message = 'Maintenance log updated successfully!';
            $messageType = 'success';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Log: <?php echo htmlspecialchars($log['server_name']); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Maintenance Log Details</h1>
            <div class="header-actions">
                <a href="logs.php" class="btn btn-secondary">← Back to Logs</a>
                <?php if (isAdmin() || $log['performed_by'] == $_SESSION['user_id']): ?>
                <a href="edit_log.php?id=<?php echo $logId; ?>" class="btn btn-primary">Edit</a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <div class="log-details">
            <div class="detail-card">
                <div class="detail-header">
                    <h2><?php echo htmlspecialchars($log['server_name']); ?></h2>
                    <div class="status-badges">
                        <span class="badge badge-type-<?php echo $log['maintenance_type']; ?>">
                            <?php echo ucfirst($log['maintenance_type']); ?>
                        </span>
                        <span class="badge badge-status-<?php echo $log['status']; ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $log['status'])); ?>
                        </span>
                    </div>
                </div>
                
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Maintenance Date</label>
                        <value><?php echo formatDate($log['maintenance_date']); ?></value>
                    </div>
                    
                    <div class="detail-item">
                        <label>Start Time</label>
                        <value><?php echo $log['start_time'] ? date('H:i', strtotime($log['start_time'])) : 'Not specified'; ?></value>
                    </div>
                    
                    <div class="detail-item">
                        <label>End Time</label>
                        <value><?php echo $log['end_time'] ? date('H:i', strtotime($log['end_time'])) : 'Not specified'; ?></value>
                    </div>
                    
                    <div class="detail-item">
                        <label>Duration</label>
                        <value>
                            <?php 
                            if ($log['start_time'] && $log['end_time']) {
                                $start = new DateTime($log['start_time']);
                                $end = new DateTime($log['end_time']);
                                $duration = $start->diff($end);
                                echo $duration->format('%H:%I');
                            } else {
                                echo 'Not calculated';
                            }
                            ?>
                        </value>
                    </div>
                    
                    <div class="detail-item">
                        <label>Performed By</label>
                        <value><?php echo htmlspecialchars($log['performed_by_name']); ?></value>
                    </div>
                    
                    <div class="detail-item">
                        <label>Created</label>
                        <value><?php echo formatDate($log['created_at'], DISPLAY_DATETIME_FORMAT); ?></value>
                    </div>
                    
                    <div class="detail-item">
                        <label>Last Updated</label>
                        <value><?php echo formatDate($log['updated_at'], DISPLAY_DATETIME_FORMAT); ?></value>
                    </div>
                </div>
                
                <div class="detail-section">
                    <h3>Description</h3>
                    <div class="detail-content">
                        <?php echo nl2br(htmlspecialchars($log['description'])); ?>
                    </div>
                </div>
                
                <?php if ($log['outcome']): ?>
                <div class="detail-section">
                    <h3>Outcome/Notes</h3>
                    <div class="detail-content">
                        <?php echo nl2br(htmlspecialchars($log['outcome'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (isAdmin() || $log['performed_by'] == $_SESSION['user_id']): ?>
        <div class="action-section">
            <h3>Actions</h3>
            <div class="action-buttons">
                <a href="edit_log.php?id=<?php echo $logId; ?>" class="btn btn-primary">
                    <span class="btn-icon">✏️</span>
                    Edit Log
                </a>
                <a href="delete_log.php?id=<?php echo $logId; ?>" class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this maintenance log?')">
                    <span class="btn-icon">🗑️</span>
                    Delete Log
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

