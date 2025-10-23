<?php
// Comprehensive debug system untuk admin login
require_once 'database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin System Debug Tool - Exotic Agarwood Indonesia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1c0c00 0%, #2a1200 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px 0;
        }
        
        .container {
            max-width: 1200px;
        }
        
        .debug-container {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 20px;
        }
        
        .header {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
            opacity: 0.3;
        }
        
        .header h1 {
            position: relative;
            z-index: 1;
            margin: 0;
            font-size: 2.5rem;
            font-weight: 700;
        }
        
        .header p {
            position: relative;
            z-index: 1;
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        
        .debug-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #af7b00;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .section-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 20px;
        }
        
        .section-title {
            color: #2c3e50;
            font-size: 1.4rem;
            font-weight: 600;
            margin: 0;
        }
        
        .status-success {
            color: #28a745;
            font-weight: 600;
        }
        
        .status-error {
            color: #dc3545;
            font-weight: 600;
        }
        
        .status-warning {
            color: #ffc107;
            font-weight: 600;
        }
        
        .status-info {
            color: #17a2b8;
            font-weight: 600;
        }
        
        .admin-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .admin-table .table {
            margin-bottom: 0;
        }
        
        .admin-table th {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
        }
        
        .admin-table td {
            padding: 12px 15px;
            vertical-align: middle;
            border-color: #f1f3f4;
        }
        
        .hash-display {
            font-family: 'Courier New', monospace;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.9rem;
            border: 1px solid #e9ecef;
        }
        
        .test-form {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 2px dashed #dee2e6;
        }
        
        .test-result {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .test-result.success {
            background: #d4edda;
            border-color: #c3e6cb;
        }
        
        .test-result.error {
            background: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            text-decoration: none;
            color: inherit;
        }
        
        .action-card.primary {
            border-color: #007bff;
        }
        
        .action-card.success {
            border-color: #28a745;
        }
        
        .action-card.warning {
            border-color: #ffc107;
        }
        
        .action-card.danger {
            border-color: #dc3545;
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: white;
        }
        
        .action-card.primary .action-icon {
            background: #007bff;
        }
        
        .action-card.success .action-icon {
            background: #28a745;
        }
        
        .action-card.warning .action-icon {
            background: #ffc107;
        }
        
        .action-card.danger .action-icon {
            background: #dc3545;
        }
        
        .badge-custom {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .badge-active {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        
        .form-control:focus {
            border-color: #af7b00;
            box-shadow: 0 0 0 0.2rem rgba(175, 123, 0, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #af7b00;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .debug-container {
                padding: 20px;
                margin: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="debug-container">
            <!-- Header -->
            <div class="header">
                <h1><i class="fas fa-bug me-3"></i>Admin System Debug Tool</h1>
                <p>Comprehensive debugging interface for Exotic Agarwood Indonesia admin system</p>
            </div>

            <!-- Statistics Overview -->
            <div class="debug-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3 class="section-title">System Overview</h3>
                </div>
                
                <div class="stats-grid">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_users");
                        $total_admins = $stmt->fetch()['total'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as active FROM admin_users WHERE is_active = 1");
                        $active_admins = $stmt->fetch()['active'];
                        
                        $stmt = $pdo->query("SELECT COUNT(*) as recent FROM admin_users WHERE last_login > DATE_SUB(NOW(), INTERVAL 7 DAY)");
                        $recent_logins = $stmt->fetch()['recent'];
                    } catch(PDOException $e) {
                        $total_admins = 0;
                        $active_admins = 0;
                        $recent_logins = 0;
                    }
                    ?>
                    
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $total_admins; ?></div>
                        <div class="stat-label">Total Admin Users</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $active_admins; ?></div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number"><?php echo $recent_logins; ?></div>
                        <div class="stat-label">Recent Logins (7 days)</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-number"><?php echo date('H:i'); ?></div>
                        <div class="stat-label">Current Time</div>
                    </div>
                </div>
            </div>

            <!-- Admin Users Table -->
            <div class="debug-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="section-title">Admin Users Database</h3>
                </div>

                <?php
                try {
                    $stmt = $pdo->query("SELECT * FROM admin_users ORDER BY id");
                    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($admins) > 0) {
                        echo "<p class='status-success'><i class='fas fa-check-circle me-2'></i>Found " . count($admins) . " admin user(s) in database</p>";
                        
                        echo "<div class='admin-table'>";
                        echo "<table class='table table-hover'>";
                        echo "<thead>";
                        echo "<tr>";
                        echo "<th><i class='fas fa-hashtag me-2'></i>ID</th>";
                        echo "<th><i class='fas fa-user me-2'></i>Username</th>";
                        echo "<th><i class='fas fa-key me-2'></i>Password Hash</th>";
                        echo "<th><i class='fas fa-toggle-on me-2'></i>Status</th>";
                        echo "<th><i class='fas fa-calendar me-2'></i>Created</th>";
                        echo "<th><i class='fas fa-sign-in-alt me-2'></i>Last Login</th>";
                        echo "</tr>";
                        echo "</thead>";
                        echo "<tbody>";
                        
                        foreach ($admins as $admin) {
                            $active_badge = $admin['is_active'] 
                                ? '<span class="badge-custom badge-active">Active</span>' 
                                : '<span class="badge-custom badge-inactive">Inactive</span>';
                            
                            echo "<tr>";
                            echo "<td><strong>{$admin['id']}</strong></td>";
                            echo "<td><i class='fas fa-user-circle me-2 text-primary'></i>" . htmlspecialchars($admin['username']) . "</td>";
                            echo "<td><div class='hash-display'>" . substr($admin['hashpassword'], 0, 32) . "...</div></td>";
                            echo "<td>$active_badge</td>";
                            echo "<td>" . ($admin['created_at'] ? date('d M Y H:i', strtotime($admin['created_at'])) : '<span class="text-muted">N/A</span>') . "</td>";
                            echo "<td>" . ($admin['last_login'] ? date('d M Y H:i', strtotime($admin['last_login'])) : '<span class="text-muted">Never</span>') . "</td>";
                            echo "</tr>";
                        }
                        echo "</tbody>";
                        echo "</table>";
                        echo "</div>";
                    } else {
                        echo "<div class='alert alert-warning'>";
                        echo "<i class='fas fa-exclamation-triangle me-2'></i><strong>No admin users found!</strong>";
                        echo "<p class='mb-3'>Creating default admin user for testing...</p>";
                        
                        // Create default admin
                        $default_username = 'admin';
                        $default_password = 'admin123';
                        $default_hash = hash('sha256', $default_password);
                        
                        $stmt = $pdo->prepare("INSERT INTO admin_users (username, hashpassword, is_active) VALUES (?, ?, 1)");
                        $result = $stmt->execute([$default_username, $default_hash]);
                        
                        if ($result) {
                            echo "<div class='alert alert-success'>";
                            echo "<i class='fas fa-check-circle me-2'></i><strong>Default admin created successfully!</strong><br>";
                            echo "<strong>Username:</strong> <code>$default_username</code><br>";
                            echo "<strong>Password:</strong> <code>$default_password</code>";
                            echo "</div>";
                        } else {
                            echo "<div class='alert alert-danger'>";
                            echo "<i class='fas fa-times-circle me-2'></i><strong>Failed to create default admin!</strong>";
                            echo "</div>";
                        }
                        echo "</div>";
                    }
                } catch(PDOException $e) {
                    echo "<div class='alert alert-danger'>";
                    echo "<i class='fas fa-database me-2'></i><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage());
                    echo "</div>";
                }
                ?>
            </div>

            <!-- Password Hash Tester -->
            <div class="debug-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-vial"></i>
                    </div>
                    <h3 class="section-title">Login Credential Tester</h3>
                </div>

                <div class="test-form">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-user me-2"></i>Username</label>
                            <input type="text" class="form-control" name="test_user" 
                                   placeholder="Enter username" 
                                   value="<?php echo htmlspecialchars($_GET['test_user'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label"><i class="fas fa-lock me-2"></i>Password</label>
                            <input type="text" class="form-control" name="test_pass" 
                                   placeholder="Enter password" 
                                   value="<?php echo htmlspecialchars($_GET['test_pass'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-flask me-2"></i>Test Login
                            </button>
                        </div>
                    </form>
                </div>

                <?php
                if (isset($_GET['test_user']) && isset($_GET['test_pass'])) {
                    $test_user = $_GET['test_user'];
                    $test_pass = $_GET['test_pass'];
                    $test_hash = hash('sha256', $test_pass);
                    
                    echo "<div class='test-result'>";
                    echo "<h5><i class='fas fa-microscope me-2'></i>Test Results</h5>";
                    echo "<div class='row'>";
                    echo "<div class='col-md-6'>";
                    echo "<p><strong>Input Data:</strong></p>";
                    echo "<p><i class='fas fa-user me-2'></i><strong>Username:</strong> " . htmlspecialchars($test_user) . "</p>";
                    echo "<p><i class='fas fa-lock me-2'></i><strong>Password:</strong> " . htmlspecialchars($test_pass) . "</p>";
                    echo "<p><i class='fas fa-key me-2'></i><strong>Generated Hash:</strong></p>";
                    echo "<div class='hash-display'>" . $test_hash . "</div>";
                    echo "</div>";
                    
                    echo "<div class='col-md-6'>";
                    
                    // Check against database
                    try {
                        $stmt = $pdo->prepare("SELECT id, username, hashpassword, is_active FROM admin_users WHERE username = ?");
                        $stmt->execute([$test_user]);
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($user) {
                            echo "<p><strong>Database Match:</strong></p>";
                            echo "<p><i class='fas fa-database me-2'></i><strong>User Found:</strong> <span class='status-success'>Yes</span></p>";
                            echo "<p><i class='fas fa-key me-2'></i><strong>Database Hash:</strong></p>";
                            echo "<div class='hash-display'>" . $user['hashpassword'] . "</div>";
                            echo "<p><i class='fas fa-toggle-on me-2'></i><strong>Account Status:</strong> " . 
                                 ($user['is_active'] ? '<span class="status-success">Active</span>' : '<span class="status-error">Inactive</span>') . "</p>";
                            
                            if ($test_hash === $user['hashpassword']) {
                                if ($user['is_active']) {
                                    echo "<div class='alert alert-success mt-3'>";
                                    echo "<i class='fas fa-check-circle me-2'></i><strong>LOGIN WOULD SUCCEED!</strong>";
                                    echo "</div>";
                                } else {
                                    echo "<div class='alert alert-warning mt-3'>";
                                    echo "<i class='fas fa-exclamation-triangle me-2'></i><strong>Password correct but user is inactive!</strong>";
                                    echo "</div>";
                                }
                            } else {
                                echo "<div class='alert alert-danger mt-3'>";
                                echo "<i class='fas fa-times-circle me-2'></i><strong>Password hash does not match!</strong>";
                                echo "</div>";
                            }
                        } else {
                            echo "<div class='alert alert-danger mt-3'>";
                            echo "<i class='fas fa-user-slash me-2'></i><strong>User not found in database!</strong>";
                            echo "</div>";
                        }
                    } catch(PDOException $e) {
                        echo "<div class='alert alert-danger mt-3'>";
                        echo "<i class='fas fa-database me-2'></i><strong>Database error:</strong> " . htmlspecialchars($e->getMessage());
                        echo "</div>";
                    }
                    
                    echo "</div>";
                    echo "</div>";
                    echo "</div>";
                }
                ?>
            </div>

            <!-- Quick Actions -->
            <div class="debug-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h3 class="section-title">Quick Actions</h3>
                </div>

                <div class="quick-actions">
                    <a href="loginmaintainance.php" class="action-card warning">
                        <div class="action-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <h5>Maintenance Login</h5>
                        <p class="text-muted">Access secure maintenance panel</p>
                    </a>

                    <a href="create-admin-user.php" class="action-card success">
                        <div class="action-icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        <h5>Manage Admin Users</h5>
                        <p class="text-muted">Create, edit, and manage admin accounts</p>
                    </a>

                    <a href="login.php" class="action-card primary">
                        <div class="action-icon">
                            <i class="fas fa-sign-in-alt"></i>
                        </div>
                        <h5>Admin Login</h5>
                        <p class="text-muted">Access admin login page</p>
                    </a>

                    <a href="?refresh=1" class="action-card danger">
                        <div class="action-icon">
                            <i class="fas fa-redo"></i>
                        </div>
                        <h5>Refresh Debug Data</h5>
                        <p class="text-muted">Reload all system information</p>
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-4">
                <p class="text-muted">
                    <i class="fas fa-shield-alt me-2"></i>
                    Exotic Agarwood Indonesia - Admin Debug Tool v2.0
                    <br>
                    <small>Last updated: <?php echo date('d M Y H:i:s'); ?></small>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-refresh functionality
        if (window.location.search.includes('refresh=1')) {
            setTimeout(() => {
                window.location.href = window.location.pathname;
            }, 2000);
        }
        
        // Add loading animation to action cards
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function(e) {
                if (!this.href.includes('refresh=1')) return;
                
                e.preventDefault();
                this.innerHTML = '<div class="action-icon"><i class="fas fa-spinner fa-spin"></i></div><h5>Refreshing...</h5><p class="text-muted">Please wait</p>';
                
                setTimeout(() => {
                    window.location.href = this.href;
                }, 1000);
            });
        });
        
        // Highlight current test results
        const testResults = document.querySelectorAll('.test-result');
        testResults.forEach(result => {
            if (result.querySelector('.alert-success')) {
                result.classList.add('success');
            } else if (result.querySelector('.alert-danger')) {
                result.classList.add('error');
            }
        });
    </script>
</body>
</html>
