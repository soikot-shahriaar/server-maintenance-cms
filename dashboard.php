<?php
/**
 * Dashboard Page
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';
require_once 'includes/MaintenanceLog.php';

// Require login
requireLogin();

$maintenanceLog = new MaintenanceLog();
$stats = $maintenanceLog->getStatistics();
$recentLogs = $maintenanceLog->getAll([], 5, 0, 'created_at', 'DESC');

// Handle any messages
$message = '';
$messageType = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'log_created':
            $message = 'Maintenance log created successfully!';
            $messageType = 'success';
            break;
        case 'log_updated':
            $message = 'Maintenance log updated successfully!';
            $messageType = 'success';
            break;
        case 'log_deleted':
            $message = 'Maintenance log deleted successfully!';
            $messageType = 'success';
            break;
        case 'access_denied':
            $message = 'Access denied. You do not have permission to perform this action.';
            $messageType = 'error';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?php echo $stats['total_logs'] ?? 0; ?></h3>
                    <p>Total Logs</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <h3><?php echo $stats['recent_logs'] ?? 0; ?></h3>
                    <p>Last 30 Days</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3>
                        <?php 
                        $completedCount = 0;
                        if (isset($stats['by_status'])) {
                            foreach ($stats['by_status'] as $status) {
                                if ($status['status'] === 'completed') {
                                    $completedCount = $status['count'];
                                    break;
                                }
                            }
                        }
                        echo $completedCount;
                        ?>
                    </h3>
                    <p>Completed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <h3>
                        <?php 
                        $inProgressCount = 0;
                        if (isset($stats['by_status'])) {
                            foreach ($stats['by_status'] as $status) {
                                if ($status['status'] === 'in_progress') {
                                    $inProgressCount = $status['count'];
                                    break;
                                }
                            }
                        }
                        echo $inProgressCount;
                        ?>
                    </h3>
                    <p>In Progress</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h2>Quick Actions</h2>
            <div class="action-buttons">
                <a href="add_log.php" class="btn btn-primary">
                    <span class="btn-icon">➕</span>
                    Add New Log
                </a>
                <a href="logs.php" class="btn btn-secondary">
                    <span class="btn-icon">📋</span>
                    View All Logs
                </a>
                <a href="search.php" class="btn btn-secondary">
                    <span class="btn-icon">🔍</span>
                    Search Logs
                </a>
                <?php if (isAdmin()): ?>
                <a href="users.php" class="btn btn-secondary">
                    <span class="btn-icon">👥</span>
                    Manage Users
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Maintenance Logs -->
        <div class="recent-logs">
            <div class="section-header">
                <h2>Recent Maintenance Logs</h2>
                <a href="logs.php" class="btn btn-link">View All</a>
            </div>
            
            <?php if (empty($recentLogs)): ?>
                <div class="empty-state">
                    <p>No maintenance logs found. <a href="add_log.php">Create your first log</a></p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Server</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Performed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['server_name']); ?></td>
                                <td><?php echo formatDate($log['maintenance_date']); ?></td>
                                <td>
                                    <span class="badge badge-type-<?php echo $log['maintenance_type']; ?>">
                                        <?php echo ucfirst($log['maintenance_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-status-<?php echo $log['status']; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $log['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($log['performed_by_name']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="view_log.php?id=<?php echo $log['id']; ?>" class="btn btn-sm btn-secondary">View</a>
                                        <a href="edit_log.php?id=<?php echo $log['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

