<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admineai.php');
    exit;
}

// Database connection
require_once 'database.php';

$error_message = '';
$debug_info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        try {
            // Debug: Log the attempt
            $debug_info .= "Login attempt for username: " . htmlspecialchars($username) . "<br>";
            
            // Get admin data from database
            $stmt = $pdo->prepare("SELECT id, username, hashpassword, is_active FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin) {
                $debug_info .= "User found in database<br>";
                $debug_info .= "User active status: " . ($admin['is_active'] ? 'Active' : 'Inactive') . "<br>";
                
                // Hash input password with SHA256 and compare
                $hashed_input_password = hash('sha256', $password);
                $debug_info .= "Input password hash: " . substr($hashed_input_password, 0, 20) . "...<br>";
                $debug_info .= "Database password hash: " . substr($admin['hashpassword'], 0, 20) . "...<br>";
                
                if (!$admin['is_active']) {
                    $error_message = 'Akun tidak aktif! Hubungi administrator.';
                } elseif ($hashed_input_password === $admin['hashpassword']) {
                    $debug_info .= "Password match - Login successful!<br>";
                    
                    // Login successful
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['login_time'] = time();
                    
                    // Update last login time
                    $update_stmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
                    $update_stmt->execute([$admin['id']]);
                    
                    // Redirect to admin panel
                    header('Location: admineai.php');
                    exit;
                } else {
                    $debug_info .= "Password does not match<br>";
                    $error_message = 'Username atau password salah!';
                }
            } else {
                $debug_info .= "User not found in database<br>";
                $error_message = 'Username atau password salah!';
            }
        } catch(PDOException $e) {
            $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
            $debug_info .= "Database error: " . $e->getMessage() . "<br>";
            error_log("Login error: " . $e->getMessage());
        }
    }
}

// Get available users for quick reference
$available_users = [];
try {
    $stmt = $pdo->query("SELECT username, is_active FROM admin_users ORDER BY username");
    $available_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Ignore error for display
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login (Fixed) - Exotic Agarwood Indonesia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1c0c00 0%, #2a1200 50%, #1c0c00 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            border: 1px solid rgba(175, 123, 0, 0.2);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            color: #af7b00;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            margin: 0;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #af7b00;
            box-shadow: 0 0 0 0.2rem rgba(175, 123, 0, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-weight: 600;
            font-size: 16px;
            width: 100%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(175, 123, 0, 0.3);
        }
        
        .btn-login:disabled {
            background: #6c757d;
            transform: none;
            box-shadow: none;
            cursor: not-allowed;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
        }
        
        .brand-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(175, 123, 0, 0.3);
        }
        
        .brand-logo i {
            font-size: 35px;
            color: white;
        }
        
        .security-note {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            border-left: 4px solid #af7b00;
        }
        
        .security-note small {
            color: #666;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .debug-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
            font-size: 12px;
        }
        
        .users-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            border-left: 4px solid #28a745;
        }
        
        .quick-login {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 10px;
            margin-top: 10px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="brand-logo">
                <i class="fas fa-leaf"></i>
            </div>
            <h2>Admin Dashboard</h2>
            <p>Exotic Agarwood Indonesia (Fixed Version)</p>
        </div>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" id="loginForm">
            <div class="form-floating">
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="Username" required autocomplete="username"
                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                <label for="username"><i class="fas fa-user me-2"></i>Username</label>
            </div>
            
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Password" required autocomplete="current-password">
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
            </div>
            
            <button type="submit" class="btn btn-login" id="loginBtn">
                <i class="fas fa-sign-in-alt me-2"></i>
                Masuk ke Dashboard
            </button>
        </form>
        
        <!-- Quick Login for Default Admin -->
        <div class="quick-login">
            <strong>Quick Test:</strong> If you see "admin" user above, try:<br>
            Username: <code>admin</code> | Password: <code>admin123</code>
        </div>
        
        <!-- Debug Info -->
        <?php if ($debug_info && isset($_GET['debug'])): ?>
            <div class="debug-info">
                <strong>Debug Info:</strong><br>
                <?php echo $debug_info; ?>
            </div>
        <?php endif; ?>
        
        <div class="security-note">
            <small>
                <i class="fas fa-shield-alt text-warning"></i>
                Area terbatas untuk administrator. Semua aktivitas akan dicatat untuk keamanan.
            </small>
            <small class="d-block mt-2">
                <a href="?debug=1" class="text-muted">Debug Mode</a> | 
                <a href="debug-admin-system.php" class="text-muted">System Debug</a> |
                <a href="loginmaintainance.php" class="text-muted">Maintenance Login</a>
            </small>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Focus on username field when page loads
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        
        // Auto-hide error message after 8 seconds
        setTimeout(function() {
            const alert = document.querySelector('.alert-danger');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 8000);
    </script>
</body>
</html>
