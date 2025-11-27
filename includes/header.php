<?php
/**
 * Header Include
 * Server Maintenance Log CMS
 */

// Ensure user is logged in for protected pages
if (!isLoggedIn() && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'register.php') {
    header('Location: login.php');
    exit();
}
?>
<header class="header">
    <div class="header-content">
        <div class="logo">
            <h1><a href="dashboard.php" style="color: white; text-decoration: none;"><?php echo APP_NAME; ?></a></h1>
        </div>
        
        <?php if (isLoggedIn()): ?>
        <nav class="nav-menu" id="navMenu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="logs.php">All Logs</a></li>
            <li><a href="add_log.php">Add Log</a></li>
            <li><a href="search.php">Search</a></li>
            <?php if (isAdmin()): ?>
            <li><a href="users.php">Users</a></li>
            <?php endif; ?>
        </nav>
        
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <a href="logout.php" class="btn btn-secondary btn-sm">Logout</a>
        </div>
        
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">☰</button>
        <?php endif; ?>
    </div>
</header>

<script>
function toggleMobileMenu() {
    const navMenu = document.getElementById('navMenu');
    navMenu.classList.toggle('active');
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const navMenu = document.getElementById('navMenu');
    const toggleButton = document.querySelector('.mobile-menu-toggle');
    
    if (!navMenu.contains(event.target) && !toggleButton.contains(event.target)) {
        navMenu.classList.remove('active');
    }
});

// Close mobile menu when window is resized to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        document.getElementById('navMenu').classList.remove('active');
    }
});
</script>

