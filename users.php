<?php
/**
 * User Management Page (Admin Only)
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';
require_once 'includes/User.php';

// Require admin access
requireAdmin();

$user = new User();

// Pagination
$page = intval($_GET['page'] ?? 1);
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Get users and total count
$users = $user->getAll($limit, $offset);
$totalUsers = $user->getTotalCount();
$totalPages = ceil($totalUsers / $limit);

// Handle any messages
$message = '';
$messageType = '';
if (isset($_GET['message'])) {
    switch ($_GET['message']) {
        case 'user_created':
            $message = 'User created successfully!';
            $messageType = 'success';
            break;
        case 'user_updated':
            $message = 'User updated successfully!';
            $messageType = 'success';
            break;
        case 'user_deactivated':
            $message = 'User deactivated successfully!';
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
    <title>User Management - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>User Management</h1>
            <a href="register.php" class="btn btn-primary">+ Add New User</a>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <!-- Users Table -->
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <p>No users found.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $userData): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($userData['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($userData['username']); ?></td>
                            <td><?php echo htmlspecialchars($userData['email']); ?></td>
                            <td>
                                <span class="badge <?php echo $userData['role'] === 'admin' ? 'badge-status-completed' : 'badge-status-scheduled'; ?>">
                                    <?php echo ucfirst($userData['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $userData['is_active'] ? 'badge-status-completed' : 'badge-status-failed'; ?>">
                                    <?php echo $userData['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($userData['created_at']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <?php if ($userData['id'] != $_SESSION['user_id']): ?>
                                        <?php if ($userData['is_active']): ?>
                                        <a href="deactivate_user.php?id=<?php echo $userData['id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to deactivate this user?')">
                                           Deactivate
                                        </a>
                                        <?php else: ?>
                                        <a href="activate_user.php?id=<?php echo $userData['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                           Activate
                                        </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Current User</span>
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
                    <a href="?page=<?php echo $page - 1; ?>" class="btn btn-secondary">← Previous</a>
                <?php endif; ?>
                
                <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next →</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <!-- User Statistics -->
        <div class="stats-section">
            <h2>User Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3><?php echo $totalUsers; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <h3>
                            <?php 
                            $activeUsers = array_filter($users, function($u) { return $u['is_active']; });
                            echo count($activeUsers);
                            ?>
                        </h3>
                        <p>Active Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👑</div>
                    <div class="stat-content">
                        <h3>
                            <?php 
                            $adminUsers = array_filter($users, function($u) { return $u['role'] === 'admin'; });
                            echo count($adminUsers);
                            ?>
                        </h3>
                        <p>Administrators</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👤</div>
                    <div class="stat-content">
                        <h3>
                            <?php 
                            $staffUsers = array_filter($users, function($u) { return $u['role'] === 'staff'; });
                            echo count($staffUsers);
                            ?>
                        </h3>
                        <p>Staff Members</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

