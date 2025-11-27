<?php
/**
 * Maintenance Logs Listing Page
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';
require_once 'includes/MaintenanceLog.php';

// Require login
requireLogin();

$maintenanceLog = new MaintenanceLog();

// Pagination
$page = intval($_GET['page'] ?? 1);
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Filters
$filters = [];
if (!empty($_GET['server_name'])) {
    $filters['server_name'] = sanitizeInput($_GET['server_name']);
}
if (!empty($_GET['status'])) {
    $filters['status'] = sanitizeInput($_GET['status']);
}
if (!empty($_GET['maintenance_type'])) {
    $filters['maintenance_type'] = sanitizeInput($_GET['maintenance_type']);
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = sanitizeInput($_GET['date_from']);
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = sanitizeInput($_GET['date_to']);
}

// Sorting
$orderBy = sanitizeInput($_GET['order_by'] ?? 'maintenance_date');
$orderDir = sanitizeInput($_GET['order_dir'] ?? 'DESC');

// Get logs and total count
$logs = $maintenanceLog->getAll($filters, $limit, $offset, $orderBy, $orderDir);
$totalLogs = $maintenanceLog->getTotalCount($filters);
$totalPages = ceil($totalLogs / $limit);

// Get unique server names for filter dropdown
$serverNames = $maintenanceLog->getServerNames();

// Handle any messages
$message = '';
$messageType = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'deleted':
            $message = 'Maintenance log deleted successfully!';
            $messageType = 'success';
            break;
    }
}
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_id':
            $message = 'Invalid log ID provided.';
            $messageType = 'error';
            break;
        case 'log_not_found':
            $message = 'Maintenance log not found.';
            $messageType = 'error';
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
    <title>Maintenance Logs - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Maintenance Logs</h1>
            <a href="add_log.php" class="btn btn-primary">+ Add New Log</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Filters -->
        <div class="filters-section">
            <form method="GET" action="logs.php" class="filters-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="server_name">Server Name</label>
                        <select id="server_name" name="server_name">
                            <option value="">All Servers</option>
                            <?php foreach ($serverNames as $serverName): ?>
                            <option value="<?php echo htmlspecialchars($serverName); ?>" 
                                    <?php echo ($filters['server_name'] ?? '') === $serverName ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($serverName); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="scheduled" <?php echo ($filters['status'] ?? '') === 'scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                            <option value="in_progress" <?php echo ($filters['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                            <option value="completed" <?php echo ($filters['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo ($filters['status'] ?? '') === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="maintenance_type">Type</label>
                        <select id="maintenance_type" name="maintenance_type">
                            <option value="">All Types</option>
                            <option value="routine" <?php echo ($filters['maintenance_type'] ?? '') === 'routine' ? 'selected' : ''; ?>>Routine</option>
                            <option value="emergency" <?php echo ($filters['maintenance_type'] ?? '') === 'emergency' ? 'selected' : ''; ?>>Emergency</option>
                            <option value="upgrade" <?php echo ($filters['maintenance_type'] ?? '') === 'upgrade' ? 'selected' : ''; ?>>Upgrade</option>
                            <option value="repair" <?php echo ($filters['maintenance_type'] ?? '') === 'repair' ? 'selected' : ''; ?>>Repair</option>
                            <option value="security" <?php echo ($filters['maintenance_type'] ?? '') === 'security' ? 'selected' : ''; ?>>Security</option>
                        </select>
                    </div>
                </div>
                
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="date_from">Date From</label>
                        <input type="date" id="date_from" name="date_from" 
                               value="<?php echo htmlspecialchars($filters['date_from'] ?? ''); ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="date_to">Date To</label>
                        <input type="date" id="date_to" name="date_to" 
                               value="<?php echo htmlspecialchars($filters['date_to'] ?? ''); ?>">
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="logs.php" class="btn btn-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Summary -->
        <div class="results-summary">
            <p>Showing <?php echo count($logs); ?> of <?php echo $totalLogs; ?> logs</p>
        </div>
        
        <!-- Logs Table -->
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <p>No maintenance logs found. <a href="add_log.php">Create your first log</a></p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'server_name', 'order_dir' => $orderBy === 'server_name' && $orderDir === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Server Name
                                    <?php if ($orderBy === 'server_name'): ?>
                                        <span class="sort-indicator"><?php echo $orderDir === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['order_by' => 'maintenance_date', 'order_dir' => $orderBy === 'maintenance_date' && $orderDir === 'ASC' ? 'DESC' : 'ASC'])); ?>">
                                    Date
                                    <?php if ($orderBy === 'maintenance_date'): ?>
                                        <span class="sort-indicator"><?php echo $orderDir === 'ASC' ? '↑' : '↓'; ?></span>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Performed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <a href="view_log.php?id=<?php echo $log['id']; ?>" class="log-link">
                                    <?php echo htmlspecialchars($log['server_name']); ?>
                                </a>
                            </td>
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
                                    <?php if (isAdmin() || $log['performed_by'] == $_SESSION['user_id']): ?>
                                    <a href="edit_log.php?id=<?php echo $log['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-secondary">← Previous</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-secondary">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

