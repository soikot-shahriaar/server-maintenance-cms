<?php
/**
 * Search Maintenance Logs Page
 * Server Maintenance Log CMS
 */

require_once 'config/config.php';
require_once 'includes/MaintenanceLog.php';
require_once 'includes/User.php';

// Require login
requireLogin();

$maintenanceLog = new MaintenanceLog();
$user = new User();

$searchResults = [];
$searchTerm = '';
$totalResults = 0;

// Pagination
$page = intval($_GET['page'] ?? 1);
$limit = RECORDS_PER_PAGE;
$offset = ($page - 1) * $limit;

// Handle search
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['search'])) {
    $searchTerm = sanitizeInput($_GET['search']);
    
    // Get search results
    $searchResults = $maintenanceLog->search($searchTerm, $limit, $offset);
    
    // Get total count for pagination (simplified - using same search without limit)
    $allResults = $maintenanceLog->search($searchTerm);
    $totalResults = count($allResults);
}

$totalPages = $totalResults > 0 ? ceil($totalResults / $limit) : 0;

// Get recent searches from session (simple implementation)
if (!isset($_SESSION['recent_searches'])) {
    $_SESSION['recent_searches'] = [];
}

// Add current search to recent searches
if (!empty($searchTerm) && !in_array($searchTerm, $_SESSION['recent_searches'])) {
    array_unshift($_SESSION['recent_searches'], $searchTerm);
    $_SESSION['recent_searches'] = array_slice($_SESSION['recent_searches'], 0, 5); // Keep only 5 recent searches
}

// Get unique server names for suggestions
$serverNames = $maintenanceLog->getServerNames();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Logs - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h1>Search Maintenance Logs</h1>
            <a href="logs.php" class="btn btn-secondary">← Back to All Logs</a>
        </div>
        
        <!-- Search Form -->
        <div class="search-section">
            <form method="GET" action="search.php" class="search-form">
                <div class="search-input-group">
                    <input type="text" name="search" id="search" 
                           value="<?php echo htmlspecialchars($searchTerm); ?>"
                           placeholder="Search by server name, description, outcome, or technician name..."
                           class="search-input" required>
                    <button type="submit" class="btn btn-primary search-btn">🔍 Search</button>
                </div>
            </form>
            
            <!-- Search Tips -->
            <div class="search-tips">
                <h3>Search Tips:</h3>
                <ul>
                    <li>Search by server name (e.g., "WEB-SERVER-01")</li>
                    <li>Search by description keywords (e.g., "security patches")</li>
                    <li>Search by outcome or notes (e.g., "successful")</li>
                    <li>Search by technician name (e.g., "John Doe")</li>
                </ul>
            </div>
            
            <!-- Recent Searches -->
            <?php if (!empty($_SESSION['recent_searches'])): ?>
            <div class="recent-searches">
                <h3>Recent Searches:</h3>
                <div class="search-tags">
                    <?php foreach ($_SESSION['recent_searches'] as $recentSearch): ?>
                    <a href="search.php?search=<?php echo urlencode($recentSearch); ?>" class="search-tag">
                        <?php echo htmlspecialchars($recentSearch); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Quick Search Suggestions -->
            <div class="quick-searches">
                <h3>Quick Searches:</h3>
                <div class="search-tags">
                    <a href="search.php?search=emergency" class="search-tag">Emergency Maintenance</a>
                    <a href="search.php?search=security" class="search-tag">Security Updates</a>
                    <a href="search.php?search=failed" class="search-tag">Failed Maintenance</a>
                    <a href="search.php?search=in_progress" class="search-tag">In Progress</a>
                    <?php foreach (array_slice($serverNames, 0, 3) as $serverName): ?>
                    <a href="search.php?search=<?php echo urlencode($serverName); ?>" class="search-tag">
                        <?php echo htmlspecialchars($serverName); ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Search Results -->
        <?php if (!empty($searchTerm)): ?>
        <div class="search-results">
            <div class="results-header">
                <h2>Search Results for "<?php echo htmlspecialchars($searchTerm); ?>"</h2>
                <p>Found <?php echo $totalResults; ?> result(s)</p>
            </div>
            
            <?php if (empty($searchResults)): ?>
                <div class="empty-state">
                    <p>No maintenance logs found matching your search criteria.</p>
                    <div class="search-suggestions">
                        <h3>Try:</h3>
                        <ul>
                            <li>Using different keywords</li>
                            <li>Checking for typos</li>
                            <li>Using broader search terms</li>
                            <li><a href="logs.php">Browse all logs</a> with filters</li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Server Name</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Performed By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($searchResults as $log): ?>
                            <tr>
                                <td>
                                    <a href="view_log.php?id=<?php echo $log['id']; ?>" class="log-link">
                                        <?php 
                                        $serverName = htmlspecialchars($log['server_name']);
                                        // Highlight search term in server name
                                        if (stripos($serverName, $searchTerm) !== false) {
                                            $serverName = str_ireplace($searchTerm, '<mark>' . $searchTerm . '</mark>', $serverName);
                                        }
                                        echo $serverName;
                                        ?>
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
                                <td class="description-cell">
                                    <?php 
                                    $description = htmlspecialchars($log['description']);
                                    // Highlight search term in description
                                    if (stripos($description, $searchTerm) !== false) {
                                        $description = str_ireplace($searchTerm, '<mark>' . $searchTerm . '</mark>', $description);
                                    }
                                    // Truncate long descriptions
                                    if (strlen(strip_tags($description)) > 100) {
                                        $description = substr(strip_tags($description), 0, 100) . '...';
                                    }
                                    echo $description;
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $performedBy = htmlspecialchars($log['performed_by_name']);
                                    // Highlight search term in performer name
                                    if (stripos($performedBy, $searchTerm) !== false) {
                                        $performedBy = str_ireplace($searchTerm, '<mark>' . $searchTerm . '</mark>', $performedBy);
                                    }
                                    echo $performedBy;
                                    ?>
                                </td>
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
                        <a href="?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page - 1; ?>" class="btn btn-secondary">← Previous</a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="?search=<?php echo urlencode($searchTerm); ?>&page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next →</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Auto-focus search input
        document.getElementById('search').focus();
        
        // Add search suggestions functionality
        const searchInput = document.getElementById('search');
        const serverNames = <?php echo json_encode($serverNames); ?>;
        
        // Simple autocomplete functionality could be added here
        searchInput.addEventListener('input', function() {
            // This could be enhanced with a proper autocomplete dropdown
        });
    </script>
</body>
</html>

