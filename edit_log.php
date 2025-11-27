<?php
/**
 * Edit Maintenance Log Page
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';
require_once 'includes/MaintenanceLog.php';

// Require login
requireLogin();

$error = '';
$success = '';

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

// Check if user can edit this log (admin or owner)
if (!isAdmin() && $log['performed_by'] != $_SESSION['user_id']) {
    header('Location: logs.php?error=access_denied');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $serverName = sanitizeInput($_POST['server_name'] ?? '');
        $maintenanceDate = sanitizeInput($_POST['maintenance_date'] ?? '');
        $startTime = sanitizeInput($_POST['start_time'] ?? '');
        $endTime = sanitizeInput($_POST['end_time'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $maintenanceType = sanitizeInput($_POST['maintenance_type'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? '');
        $outcome = sanitizeInput($_POST['outcome'] ?? '');
        
        // Validation
        if (empty($serverName) || empty($maintenanceDate) || empty($description) || empty($maintenanceType) || empty($status)) {
            $error = 'Please fill in all required fields';
        } else {
            $result = $maintenanceLog->update(
                $logId,
                $serverName,
                $maintenanceDate,
                $startTime ?: null,
                $endTime ?: null,
                $description,
                $maintenanceType,
                $status,
                $outcome ?: null
            );
            
            if ($result['success']) {
                header('Location: view_log.php?id=' . $logId . '&message=updated');
                exit();
            } else {
                $error = $result['message'];
            }
        }
    }
} else {
    // Pre-populate form with existing data
    $serverName = $log['server_name'];
    $maintenanceDate = $log['maintenance_date'];
    $startTime = $log['start_time'];
    $endTime = $log['end_time'];
    $description = $log['description'];
    $maintenanceType = $log['maintenance_type'];
    $status = $log['status'];
    $outcome = $log['outcome'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Maintenance Log - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Edit Maintenance Log</h1>
            <div class="header-actions">
                <a href="view_log.php?id=<?php echo $logId; ?>" class="btn btn-secondary">← Back to Log</a>
                <a href="logs.php" class="btn btn-secondary">All Logs</a>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" action="edit_log.php?id=<?php echo $logId; ?>" class="maintenance-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="server_name">Server Name *</label>
                        <input type="text" id="server_name" name="server_name" required 
                               value="<?php echo htmlspecialchars($serverName); ?>"
                               placeholder="e.g., WEB-SERVER-01">
                    </div>
                    
                    <div class="form-group">
                        <label for="maintenance_date">Maintenance Date *</label>
                        <input type="date" id="maintenance_date" name="maintenance_date" required 
                               value="<?php echo htmlspecialchars($maintenanceDate); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time</label>
                        <input type="time" id="start_time" name="start_time" 
                               value="<?php echo htmlspecialchars($startTime); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="end_time">End Time</label>
                        <input type="time" id="end_time" name="end_time" 
                               value="<?php echo htmlspecialchars($endTime); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="maintenance_type">Maintenance Type *</label>
                        <select id="maintenance_type" name="maintenance_type" required>
                            <option value="">Select Type</option>
                            <option value="routine" <?php echo $maintenanceType === 'routine' ? 'selected' : ''; ?>>Routine</option>
                            <option value="emergency" <?php echo $maintenanceType === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                            <option value="upgrade" <?php echo $maintenanceType === 'upgrade' ? 'selected' : ''; ?>>Upgrade</option>
                            <option value="repair" <?php echo $maintenanceType === 'repair' ? 'selected' : ''; ?>>Repair</option>
                            <option value="security" <?php echo $maintenanceType === 'security' ? 'selected' : ''; ?>>Security</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="">Select Status</option>
                            <option value="scheduled" <?php echo $status === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="in_progress" <?php echo $status === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo $status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" required rows="4" 
                              placeholder="Describe the maintenance activity..."><?php echo htmlspecialchars($description); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="outcome">Outcome/Notes</label>
                    <textarea id="outcome" name="outcome" rows="3" 
                              placeholder="Describe the outcome or any additional notes..."><?php echo htmlspecialchars($outcome); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Log</button>
                    <a href="view_log.php?id=<?php echo $logId; ?>" class="btn btn-secondary">Cancel</a>
                    <?php if (isAdmin() || $log['performed_by'] == $_SESSION['user_id']): ?>
                    <a href="delete_log.php?id=<?php echo $logId; ?>" class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this maintenance log?')">Delete</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

