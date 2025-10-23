<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Database connection
require_once 'database.php';

$success_message = '';
$error_message = '';

// Get current admin info
$current_admin = $_SESSION['admin_username'] ?? '';
$admin_id = $_SESSION['admin_id'] ?? 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_username') {
        $new_username = trim($_POST['new_username'] ?? '');
        $current_password = $_POST['current_password'] ?? '';
        
        if (empty($new_username) || empty($current_password)) {
            $error_message = 'Username baru dan password saat ini harus diisi!';
        } elseif (strlen($new_username) < 3) {
            $error_message = 'Username minimal 3 karakter!';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
            $error_message = 'Username hanya boleh mengandung huruf, angka, dan underscore!';
        } else {
            try {
                // Verify current password with SHA256
                $stmt = $pdo->prepare("SELECT hashpassword FROM admin_users WHERE id = ?");
                $stmt->execute([$admin_id]);
                $admin = $stmt->fetch();
                
                $hashed_current_password = hash('sha256', $current_password);
                
                if ($admin && $hashed_current_password === $admin['hashpassword']) {
                    // Check if new username already exists
                    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
                    $stmt->execute([$new_username, $admin_id]);
                    
                    if ($stmt->fetch()) {
                        $error_message = 'Username sudah digunakan oleh admin lain!';
                    } else {
                        // Update username
                        $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, updated_at = NOW() WHERE id = ?");
                        $result = $stmt->execute([$new_username, $admin_id]);
                        
                        if ($result) {
                            $_SESSION['admin_username'] = $new_username;
                            $current_admin = $new_username;
                            $success_message = 'Username berhasil diubah dari "' . htmlspecialchars($current_admin) . '" menjadi "' . htmlspecialchars($new_username) . '"!';
                        } else {
                            $error_message = 'Gagal mengubah username!';
                        }
                    }
                } else {
                    $error_message = 'Password saat ini salah!';
                }
            } catch(PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
                error_log("Change username error: " . $e->getMessage());
            }
        }
    } elseif ($action === 'change_password') {
        $current_password = $_POST['current_password_pwd'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = 'Semua field password harus diisi!';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Password baru dan konfirmasi password tidak sama!';
        } elseif (strlen($new_password) < 8) {
            $error_message = 'Password baru minimal 8 karakter untuk keamanan!';
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $new_password)) {
            $error_message = 'Password harus mengandung minimal 1 huruf kecil, 1 huruf besar, dan 1 angka!';
        } elseif ($current_password === $new_password) {
            $error_message = 'Password baru harus berbeda dengan password saat ini!';
        } else {
            try {
                // Verify current password with SHA256
                $stmt = $pdo->prepare("SELECT hashpassword FROM admin_users WHERE id = ?");
                $stmt->execute([$admin_id]);
                $admin = $stmt->fetch();
                
                $hashed_current_password = hash('sha256', $current_password);
                
                if ($admin && $hashed_current_password === $admin['hashpassword']) {
                    // Hash new password with SHA256
                    $new_password_hash = hash('sha256', $new_password);
                    
                    // Update password
                    $stmt = $pdo->prepare("UPDATE admin_users SET hashpassword = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$new_password_hash, $admin_id]);
                    
                    if ($result) {
                        $success_message = 'Password berhasil diubah! Pastikan Anda mengingat password baru untuk login selanjutnya.';
                    } else {
                        $error_message = 'Gagal mengubah password!';
                    }
                } else {
                    $error_message = 'Password saat ini salah!';
                }
            } catch(PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
                error_log("Change password error: " . $e->getMessage());
            }
        }
    }
}

// Get unread messages count for sidebar
try {
    $stmt = $pdo->query("SELECT COUNT(*) as unread_messages FROM contact_messages WHERE is_read = 0");
    $unread_messages = $stmt->fetch()['unread_messages'];
} catch(PDOException $e) {
    $unread_messages = 0;
}

// Get current admin info for display
try {
    $stmt = $pdo->prepare("SELECT username, created_at, last_login FROM admin_users WHERE id = ?");
    $stmt->execute([$admin_id]);
    $admin_info = $stmt->fetch();
} catch(PDOException $e) {
    $admin_info = ['username' => $current_admin, 'created_at' => null, 'last_login' => null];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Username & Password - Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #1c0c00 0%, #2a1200 100%);
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }
        
        .sidebar .brand {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }
        
        .sidebar .brand h4 {
            color: #af7b00;
            font-weight: 700;
            margin: 10px 0 5px;
        }
        
        .sidebar .brand small {
            color: rgba(255, 255, 255, 0.7);
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 0;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(175, 123, 0, 0.2);
            color: #af7b00;
            border-left-color: #af7b00;
        }
        
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .sidebar .nav-section {
            padding: 10px 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 0;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            margin-bottom: 30px;
        }
        
        .settings-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .settings-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #af7b00;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #af7b00;
            box-shadow: 0 0 0 0.2rem rgba(175, 123, 0, 0.25);
        }
        
        .btn-submit {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(175, 123, 0, 0.3);
        }
        
        .btn-back {
            background: #6c757d;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: #5a6268;
            color: white;
            transform: translateY(-2px);
        }
        
        .logout-btn {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(220, 53, 69, 0.2);
            color: #dc3545;
        }
        
        .card-custom {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            border: none;
        }
        
        .current-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #af7b00;
        }
        
        .password-strength {
            margin-top: 10px;
        }
        
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            background: #e9ecef;
            overflow: hidden;
        }
        
        .strength-fill {
            height: 100%;
            transition: all 0.3s ease;
            width: 0%;
        }
        
        .strength-weak { background: #dc3545; }
        .strength-medium { background: #ffc107; }
        .strength-strong { background: #28a745; }
        
        .security-tips {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="brand">
            <i class="fas fa-leaf fa-2x text-warning"></i>
            <h4>Admin Panel</h4>
            <small>Exotic Agarwood</small>
        </div>
        
        <nav class="nav flex-column">
            <a class="nav-link" href="admineai.php">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
            
            <div class="nav-section">Tambah Produk</div>
            <a class="nav-link" href="inputdatakayugaharu.php">
                <i class="fas fa-plus-circle"></i>
                Tambah Kayu Gaharu
            </a>
            <a class="nav-link" href="inputdataminyakgaharu.php">
                <i class="fas fa-plus-circle"></i>
                Tambah Minyak Gaharu
            </a>
            
            <div class="nav-section">Edit Produk</div>
            <a class="nav-link" href="admineai.php#kayu-section">
                <i class="fas fa-edit"></i>
                Edit Kayu Gaharu
            </a>
            <a class="nav-link" href="admineai.php#minyak-section">
                <i class="fas fa-edit"></i>
                Edit Minyak Gaharu
            </a>
            
            <div class="nav-section">Hapus Produk</div>
            <a class="nav-link" href="admineai.php#kayu-section">
                <i class="fas fa-trash"></i>
                Delete Kayu Gaharu
            </a>
            <a class="nav-link" href="admineai.php#minyak-section">
                <i class="fas fa-trash"></i>
                Delete Minyak Gaharu
            </a>
            
            <div class="nav-section">Pesan</div>
            <a class="nav-link" href="checkmessegecontact.php">
                <i class="fas fa-envelope"></i>
                Pesan Kontak
                <?php if ($unread_messages > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_messages; ?></span>
                <?php endif; ?>
            </a>
            
            <div class="nav-section">Pengaturan</div>
            <a class="nav-link active" href="changeusernpassadmin.php">
                <i class="fas fa-user-cog"></i>
                Change Username & Password
            </a>
            
            <hr style="border-color: rgba(255,255,255,0.2); margin: 20px;">
            <a class="logout-btn" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                Logout
            </a>
        </nav>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar d-flex justify-content-between align-items-center">
            <h3 class="mb-0">Change Username & Password</h3>
            <a href="admineai.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Dashboard
            </a>
        </div>
        
        <div class="container-fluid">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="settings-container">
                <h4 class="settings-title">
                    <i class="fas fa-user-cog me-2"></i>Pengaturan Admin
                </h4>
                
                <!-- Current Info -->
                <div class="current-info">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-info-circle me-2"></i>Informasi Admin Saat Ini
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Username:</strong><br>
                                <span class="text-primary"><?php echo htmlspecialchars($admin_info['username']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-2">
                                <strong>Akun Dibuat:</strong><br>
                                <span class="text-muted"><?php echo $admin_info['created_at'] ? date('d M Y H:i', strtotime($admin_info['created_at'])) : 'N/A'; ?></span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-0">
                                <strong>Login Terakhir:</strong><br>
                                <span class="text-muted"><?php echo $admin_info['last_login'] ? date('d M Y H:i', strtotime($admin_info['last_login'])) : 'N/A'; ?></span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Change Username -->
                    <div class="col-md-6">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Ubah Username
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="usernameForm">
                                    <input type="hidden" name="action" value="change_username">
                                    
                                    <div class="mb-3">
                                        <label for="new_username" class="form-label">Username Baru *</label>
                                        <input type="text" class="form-control" id="new_username" name="new_username" 
                                               placeholder="Masukkan username baru" required minlength="3" maxlength="50"
                                               pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan underscore">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Username minimal 3 karakter, hanya huruf, angka, dan underscore
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="current_password" class="form-label">Password Saat Ini *</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" 
                                               placeholder="Masukkan password saat ini" required>
                                        <div class="form-text">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Konfirmasi dengan password saat ini untuk keamanan
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-submit w-100">
                                        <i class="fas fa-save me-2"></i>Ubah Username
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="col-md-6">
                        <div class="card card-custom">
                            <div class="card-header card-header-custom">
                                <h5 class="mb-0">
                                    <i class="fas fa-lock me-2"></i>Ubah Password
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="" id="passwordForm">
                                    <input type="hidden" name="action" value="change_password">
                                    
                                    <div class="mb-3">
                                        <label for="current_password_pwd" class="form-label">Password Saat Ini *</label>
                                        <input type="password" class="form-control" id="current_password_pwd" name="current_password_pwd" 
                                               placeholder="Masukkan password saat ini" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">Password Baru *</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" 
                                               placeholder="Masukkan password baru" required minlength="8">
                                        <div class="password-strength">
                                            <div class="strength-bar">
                                                <div class="strength-fill" id="strengthFill"></div>
                                            </div>
                                            <small class="text-muted" id="strengthText">Kekuatan password akan ditampilkan di sini</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="confirm_password" class="form-label">Konfirmasi Password Baru *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                               placeholder="Ulangi password baru" required minlength="8">
                                        <div class="form-text">
                                            <i class="fas fa-check-double me-1"></i>
                                            Harus sama dengan password baru
                                        </div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-submit w-100">
                                        <i class="fas fa-key me-2"></i>Ubah Password
                                    </button>
                                </form>
                                
                                <div class="security-tips">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-lightbulb me-2"></i>Tips Keamanan Password:
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li>Minimal 8 karakter</li>
                                        <li>Kombinasi huruf besar, kecil, dan angka</li>
                                        <li>Hindari kata yang mudah ditebak</li>
                                        <li>Gunakan password yang unik</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Password strength checker
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            let feedback = [];
            
            // Length check
            if (password.length >= 8) strength += 25;
            else feedback.push('minimal 8 karakter');
            
            // Lowercase check
            if (/[a-z]/.test(password)) strength += 25;
            else feedback.push('huruf kecil');
            
            // Uppercase check
            if (/[A-Z]/.test(password)) strength += 25;
            else feedback.push('huruf besar');
            
            // Number check
            if (/\d/.test(password)) strength += 25;
            else feedback.push('angka');
            
            // Update strength bar
            strengthFill.style.width = strength + '%';
            
            if (strength < 50) {
                strengthFill.className = 'strength-fill strength-weak';
                strengthText.textContent = 'Lemah - Tambahkan: ' + feedback.join(', ');
                strengthText.className = 'text-danger small';
            } else if (strength < 100) {
                strengthFill.className = 'strength-fill strength-medium';
                strengthText.textContent = 'Sedang - Tambahkan: ' + feedback.join(', ');
                strengthText.className = 'text-warning small';
            } else {
                strengthFill.className = 'strength-fill strength-strong';
                strengthText.textContent = 'Kuat - Password aman!';
                strengthText.className = 'text-success small';
            }
        });
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Password tidak sama');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        });
        
        // Username validation
        document.getElementById('new_username').addEventListener('input', function() {
            const username = this.value;
            const pattern = /^[a-zA-Z0-9_]+$/;
            
            if (!pattern.test(username) && username.length > 0) {
                this.setCustomValidity('Username hanya boleh mengandung huruf, angka, dan underscore');
                this.classList.add('is-invalid');
            } else {
                this.setCustomValidity('');
                this.classList.remove('is-invalid');
                if (username.length >= 3) {
                    this.classList.add('is-valid');
                }
            }
        });
    </script>
</body>
</html>
