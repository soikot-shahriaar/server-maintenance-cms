<?php
/**
 * Installation Script
 * Server Maintenance Log CMS
 */

// Prevent running if already installed
if (file_exists('config/installed.lock')) {
    die('System is already installed. Delete config/installed.lock to reinstall.');
}

$step = $_GET['step'] ?? 1;
$errors = [];
$success = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 2:
            // Database configuration
            $dbHost = $_POST['db_host'] ?? '';
            $dbName = $_POST['db_name'] ?? '';
            $dbUser = $_POST['db_user'] ?? '';
            $dbPass = $_POST['db_pass'] ?? '';
            
            // Test database connection
            try {
                $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                // Save configuration
                $configContent = "<?php\n";
                $configContent .= "define('DB_HOST', '$dbHost');\n";
                $configContent .= "define('DB_NAME', '$dbName');\n";
                $configContent .= "define('DB_USER', '$dbUser');\n";
                $configContent .= "define('DB_PASS', '$dbPass');\n";
                $configContent .= "define('DB_CHARSET', 'utf8mb4');\n";
                
                file_put_contents('config/database_config.php', $configContent);
                $success[] = 'Database configuration saved successfully.';
                $step = 3;
            } catch (PDOException $e) {
                $errors[] = 'Database connection failed: ' . $e->getMessage();
            }
            break;
            
        case 3:
            // Import database schema
            try {
                include 'config/database_config.php';
                $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                
                $sql = file_get_contents('sql/database_setup.sql');
                $pdo->exec($sql);
                
                $success[] = 'Database schema imported successfully.';
                $step = 4;
            } catch (Exception $e) {
                $errors[] = 'Database import failed: ' . $e->getMessage();
            }
            break;
            
        case 4:
            // Create admin user
            $username = $_POST['admin_username'] ?? '';
            $email = $_POST['admin_email'] ?? '';
            $password = $_POST['admin_password'] ?? '';
            $fullName = $_POST['admin_name'] ?? '';
            
            if (empty($username) || empty($email) || empty($password) || empty($fullName)) {
                $errors[] = 'All fields are required.';
            } else {
                try {
                    include 'config/database_config.php';
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
                    $stmt->execute([$username, $email, $passwordHash, $fullName]);
                    
                    $success[] = 'Admin user created successfully.';
                    $step = 5;
                } catch (Exception $e) {
                    $errors[] = 'Failed to create admin user: ' . $e->getMessage();
                }
            }
            break;
            
        case 5:
            // Finalize installation
            file_put_contents('config/installed.lock', date('Y-m-d H:i:s'));
            $success[] = 'Installation completed successfully!';
            $step = 6;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Server Maintenance Log CMS</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .installer {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        h1 { color: #2c3e50; text-align: center; }
        .step { background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; }
        input { width: 100%; padding: 0.75rem; border: 2px solid #e9ecef; border-radius: 8px; }
        .btn { background: #3498db; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 8px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
        .progress { background: #e9ecef; height: 10px; border-radius: 5px; margin-bottom: 2rem; }
        .progress-bar { background: #3498db; height: 100%; border-radius: 5px; transition: width 0.3s; }
    </style>
</head>
<body>
    <div class="installer">
        <h1>Server Maintenance Log CMS</h1>
        <h2>Installation Wizard</h2>
        
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo ($step / 6) * 100; ?>%"></div>
        </div>
        
        <?php foreach ($errors as $error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endforeach; ?>
        
        <?php foreach ($success as $msg): ?>
            <div class="success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endforeach; ?>
        
        <?php if ($step == 1): ?>
            <div class="step">
                <h3>Step 1: Welcome</h3>
                <p>Welcome to the Server Maintenance Log CMS installation wizard. This will guide you through setting up your maintenance tracking system.</p>
                <p><strong>Requirements:</strong></p>
                <ul>
                    <li>PHP 7.4 or higher</li>
                    <li>MySQL 5.7 or higher</li>
                    <li>Web server (Apache/Nginx)</li>
                </ul>
                <a href="?step=2" class="btn">Continue</a>
            </div>
        <?php elseif ($step == 2): ?>
            <div class="step">
                <h3>Step 2: Database Configuration</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" value="server_maintenance_cms" required>
                    </div>
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass">
                    </div>
                    <button type="submit" class="btn">Test Connection</button>
                </form>
            </div>
        <?php elseif ($step == 3): ?>
            <div class="step">
                <h3>Step 3: Import Database Schema</h3>
                <p>The database schema will be imported automatically.</p>
                <form method="POST">
                    <button type="submit" class="btn">Import Schema</button>
                </form>
            </div>
        <?php elseif ($step == 4): ?>
            <div class="step">
                <h3>Step 4: Create Admin User</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="admin_name">Full Name</label>
                        <input type="text" id="admin_name" name="admin_name" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_username">Username</label>
                        <input type="text" id="admin_username" name="admin_username" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_email">Email</label>
                        <input type="email" id="admin_email" name="admin_email" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Password</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn">Create Admin User</button>
                </form>
            </div>
        <?php elseif ($step == 5): ?>
            <div class="step">
                <h3>Step 5: Finalize Installation</h3>
                <p>Complete the installation process.</p>
                <form method="POST">
                    <button type="submit" class="btn">Finish Installation</button>
                </form>
            </div>
        <?php elseif ($step == 6): ?>
            <div class="step">
                <h3>Installation Complete!</h3>
                <p>Your Server Maintenance Log CMS has been installed successfully.</p>
                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>Delete this install.php file for security</li>
                    <li>Login with your admin credentials</li>
                    <li>Start adding maintenance logs</li>
                </ul>
                <a href="index.php" class="btn">Go to Application</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

