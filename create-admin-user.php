<?php
// Enhanced create-admin-user.php dengan CRUD operations
session_start();

// Check if maintenance user is logged in
if (!isset($_SESSION['maintenance_logged_in']) || $_SESSION['maintenance_logged_in'] !== true) {
    header('Location: loginmaintainance.php');
    exit;
}

require_once 'database.php';

$success_message = '';
$error_message = '';

// Handle different actions
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Preserve form data
$form_data = [
    'username' => $_POST['username'] ?? '',
    'password' => $_POST['password'] ?? '',
    'admin_id' => $_POST['admin_id'] ?? ''
];

// CREATE - Add new admin
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password harus diisi!';
    } else {
        try {
            // Check if username already exists
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($stmt->fetch()) {
                $error_message = 'Username sudah ada!';
            } else {
                // Hash password with SHA256
                $hashed_password = hash('sha256', $password);
                
                // Insert new admin
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, hashpassword, is_active) VALUES (?, ?, 1)");
                $result = $stmt->execute([$username, $hashed_password]);
                
                if ($result) {
                    $success_message = 'Admin user berhasil dibuat!';
                    // Clear form data on success
                    $form_data = ['username' => '', 'password' => '', 'admin_id' => ''];
                } else {
                    $error_message = 'Gagal membuat admin user!';
                }
            }
        } catch(PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// UPDATE - Edit admin
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = (int)($_POST['admin_id'] ?? 0);
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if ($admin_id <= 0 || empty($username)) {
        $error_message = 'ID admin dan username harus diisi!';
    } else {
        try {
            // Check if username already exists (exclude current admin)
            $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $admin_id]);
            
            if ($stmt->fetch()) {
                $error_message = 'Username sudah digunakan oleh admin lain!';
            } else {
                // Update admin
                if (!empty($password)) {
                    // Update with new password
                    $hashed_password = hash('sha256', $password);
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, hashpassword = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$username, $hashed_password, $admin_id]);
                } else {
                    // Update without changing password
                    $stmt = $pdo->prepare("UPDATE admin_users SET username = ?, updated_at = NOW() WHERE id = ?");
                    $result = $stmt->execute([$username, $admin_id]);
                }
                
                if ($result) {
                    $success_message = 'Admin user berhasil diupdate!';
                    // Clear form data on success
                    $form_data = ['username' => '', 'password' => '', 'admin_id' => ''];
                } else {
                    $error_message = 'Gagal mengupdate admin user!';
                }
            }
        } catch(PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// DELETE - Remove admin (HARD DELETE by ID) with ID reordering
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = (int)($_POST['admin_id'] ?? 0);
    
    if ($admin_id <= 0) {
        $error_message = 'ID admin tidak valid!';
    } else {
        try {
            // Check if this is the last active admin
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM admin_users WHERE is_active = 1");
            $total_admins = $stmt->fetch()['total'];
            
            if ($total_admins <= 1) {
                $error_message = 'Tidak dapat menghapus admin terakhir!';
            } else {
                // Start a transaction for the delete and reordering operations
                $pdo->beginTransaction();
                
                // 1. Delete the admin record
                $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ?");
                $result = $stmt->execute([$admin_id]);
                
                if ($result) {
                    // 2. Get all remaining admin IDs that are greater than the deleted ID
                    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE id > ? ORDER BY id ASC");
                    $stmt->execute([$admin_id]);
                    $remaining_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    
                    // 3. Reorder the IDs to fill the gap
                    $new_id = $admin_id; // Start with the deleted ID
                    foreach ($remaining_ids as $old_id) {
                        // Update each record to have the new sequential ID
                        $stmt = $pdo->prepare("UPDATE admin_users SET id = ? WHERE id = ?");
                        $stmt->execute([$new_id, $old_id]);
                        $new_id++; // Increment for the next record
                    }
                    
                    // 4. Reset the auto-increment value to the next available ID
                    $stmt = $pdo->query("SELECT MAX(id) as max_id FROM admin_users");
                    $max_id = $stmt->fetch()['max_id'];
                    $next_id = ($max_id ?? 0) + 1;
                    
                    // Reset auto-increment to the next available ID
                    $pdo->exec("ALTER TABLE admin_users AUTO_INCREMENT = $next_id");
                    
                    // Commit all changes
                    $pdo->commit();
                    
                    $success_message = 'Admin user berhasil dihapus dan ID diurutkan ulang!';
                } else {
                    $pdo->rollBack();
                    $error_message = 'Gagal menghapus admin user!';
                }
            }
        } catch(PDOException $e) {
            // Roll back the transaction if there's an error
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error_message = 'Database error: ' . $e->getMessage();
        }
    }
}

// Get admin for editing
$edit_admin = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$edit_id]);
        $edit_admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($edit_admin) {
            $form_data['username'] = $edit_admin['username'];
            $form_data['admin_id'] = $edit_admin['id'];
        }
    } catch(PDOException $e) {
        $error_message = 'Error fetching admin data: ' . $e->getMessage();
    }
}

// Get all admins
try {
    $stmt = $pdo->query("SELECT * FROM admin_users ORDER BY is_active DESC, id ASC");
    $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $admins = [];
    $error_message = 'Error fetching admins: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin User Management - Secure Maintenance Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1c0c00 0%, #2a1200 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            max-width: 1200px;
        }
        
        .header {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
            border: none;
            color: #000;
        }
        
        .table {
            background: white;
        }
        
        .maintenance-info {
            background: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .card-header {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            border: none;
        }
        
        .hash-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #af7b00;
        }
        
        .admin-inactive {
            opacity: 0.6;
            background-color: #f8f9fa;
        }
        
        .status-badge {
            font-size: 0.8em;
        }
        
        .realtime-info {
            font-weight: bold;
            color: #af7b00;
        }
        
        .countdown-timer {
            font-size: 16px;
            font-weight: bold;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header text-center">
            <h1><i class="fas fa-users-cog me-2"></i>Admin User Management</h1>
            <p class="mb-0">Secure Maintenance Panel - Logged in as: <?php echo htmlspecialchars($_SESSION['maintenance_username']); ?></p>
        </div>

        <!-- Maintenance Session Info -->
        <div class="maintenance-info">
            <h6><i class="fas fa-shield-alt me-2"></i>Secure Session Info:</h6>
            <div class="row">
                <div class="col-md-4">
                    <p class="mb-1"><strong>User:</strong> <?php echo htmlspecialchars($_SESSION['maintenance_username']); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Login Time:</strong> <?php echo date('d M Y H:i:s', $_SESSION['maintenance_login_time']); ?></p>
                </div>
                <div class="col-md-4">
                    <p class="mb-1"><strong>Session Duration:</strong> <span id="sessionDuration" class="realtime-info"><?php echo gmdate('H:i:s', time() - $_SESSION['maintenance_login_time']); ?></span></p>
                </div>
            </div>
        </div>

        <!-- Hash Security Info -->
        <div class="hash-info">
            <h6><i class="fas fa-lock me-2"></i>Security Status:</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1">Hash Salt: <?php echo $_SESSION['maintenance_hash_salt'] ?? 'N/A'; ?></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-0">Next hash update: <span id="nextUpdate" class="countdown-timer">10 seconds</span></p>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Admin List -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Admin Users</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($admins) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Last Login</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($admins as $admin): ?>
                                            <tr class="<?php echo $admin['is_active'] ? '' : 'admin-inactive'; ?>">
                                                <td><?php echo $admin['id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($admin['username']); ?></strong></td>
                                                <td>
                                                    <?php if ($admin['is_active']): ?>
                                                        <span class="badge bg-success status-badge">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger status-badge">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $admin['created_at'] ? date('d M Y', strtotime($admin['created_at'])) : 'N/A'; ?></td>
                                                <td><?php echo $admin['last_login'] ? date('d M Y H:i', strtotime($admin['last_login'])) : '<span class="text-muted">Never</span>'; ?></td>
                                                <td>
                                                    <?php if ($admin['is_active']): ?>
                                                        <a href="?action=edit&id=<?php echo $admin['id']; ?>" class="btn btn-warning btn-sm me-1">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $admin['id']; ?>, '<?php echo htmlspecialchars($admin['username']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">Deleted</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>No admin users found.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Create/Edit Form -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?php echo $edit_admin ? 'edit' : 'plus'; ?> me-2"></i>
                            <?php echo $edit_admin ? 'Edit Admin' : 'Create New Admin'; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" id="adminForm">
                            <input type="hidden" name="action" value="<?php echo $edit_admin ? 'update' : 'create'; ?>">
                            <?php if ($edit_admin): ?>
                                <input type="hidden" name="admin_id" value="<?php echo $edit_admin['id']; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       required minlength="3" maxlength="50"
                                       value="<?php echo htmlspecialchars($form_data['username']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Password: <?php echo $edit_admin ? '<small class="text-muted">(kosongkan jika tidak ingin mengubah)</small>' : ''; ?>
                                </label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       <?php echo $edit_admin ? '' : 'required'; ?> minlength="6"
                                       value="<?php echo htmlspecialchars($form_data['password']); ?>">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-<?php echo $edit_admin ? 'save' : 'plus'; ?> me-2"></i>
                                    <?php echo $edit_admin ? 'Update Admin' : 'Create Admin'; ?>
                                </button>
                                
                                <?php if ($edit_admin): ?>
                                    <a href="create-admin-user.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel Edit
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Password Hash Generator -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-key me-2"></i>Hash Generator</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="mb-3">
                                <input type="text" class="form-control" name="test_password" 
                                       placeholder="Enter password to hash" 
                                       value="<?php echo htmlspecialchars($_GET['test_password'] ?? ''); ?>">
                            </div>
                            <button type="submit" class="btn btn-secondary btn-sm w-100">
                                <i class="fas fa-key me-2"></i>Generate SHA256
                            </button>
                        </form>

                        <?php if (isset($_GET['test_password']) && !empty($_GET['test_password'])): ?>
                            <div class="mt-3 p-2 bg-light rounded">
                                <small><strong>Hash:</strong></small><br>
                                <code style="font-size: 10px; word-break: break-all;">
                                    <?php echo hash('sha256', $_GET['test_password']); ?>
                                </code>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="text-center mt-4">
            <a href="logout-maintenance.php" class="btn btn-danger me-2">
                <i class="fas fa-sign-out-alt me-2"></i>Logout Maintenance
            </a>
            <a href="loginmaintainance.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to <strong>permanently delete</strong> admin user: <strong id="deleteUsername"></strong>?</p>
                    <p class="text-danger"><small><i class="fas fa-warning me-1"></i>This action will completely remove the record from database and cannot be undone.</small></p>
                    <p class="text-info"><small><i class="fas fa-info-circle me-1"></i>ID akan diurutkan ulang secara otomatis setelah penghapusan.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="admin_id" id="deleteAdminId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Delete Permanently
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Login time dari PHP session
        const loginTime = <?php echo $_SESSION['maintenance_login_time']; ?>;
        
        // Save form data to localStorage to persist during hash updates
        function saveFormData() {
            const formData = {
                username: document.getElementById('username').value,
                password: document.getElementById('password').value,
                action: document.querySelector('input[name="action"]').value,
                admin_id: document.querySelector('input[name="admin_id"]')?.value || ''
            };
            localStorage.setItem('adminFormData', JSON.stringify(formData));
        }
        
        function loadFormData() {
            const savedData = localStorage.getItem('adminFormData');
            if (savedData) {
                const formData = JSON.parse(savedData);
                
                // Only load if form is empty (to prevent overwriting server data)
                if (!document.getElementById('username').value && formData.username) {
                    document.getElementById('username').value = formData.username;
                }
                if (!document.getElementById('password').value && formData.password) {
                    document.getElementById('password').value = formData.password;
                }
            }
        }
        
        // Delete confirmation
        function confirmDelete(adminId, username) {
            document.getElementById('deleteAdminId').value = adminId;
            document.getElementById('deleteUsername').textContent = username;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Hash update countdown (10 detik) - TIDAK RESET FORM
        function updateHashCountdown() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = 10 - (now % 10);
            
            document.getElementById('nextUpdate').textContent = remaining + ' seconds';
            
            // Save form data before potential refresh
            if (remaining <= 2) {
                saveFormData();
            }
            
            // Refresh page tapi preserve form data
            if (remaining === 1) {
                setTimeout(() => {
                    // Only reload if no form interaction in last 5 seconds
                    const lastInteraction = localStorage.getItem('lastFormInteraction');
                    const now = Date.now();
                    
                    if (!lastInteraction || (now - parseInt(lastInteraction)) > 5000) {
                        location.reload();
                    }
                }, 1000);
            }
        }

        // Session duration realtime update
        function updateSessionDuration() {
            const now = Math.floor(Date.now() / 1000);
            const duration = now - loginTime;
            
            const hours = Math.floor(duration / 3600);
            const minutes = Math.floor((duration % 3600) / 60);
            const seconds = duration % 60;
            
            const formattedDuration = 
                String(hours).padStart(2, '0') + ':' +
                String(minutes).padStart(2, '0') + ':' +
                String(seconds).padStart(2, '0');
            
            document.getElementById('sessionDuration').textContent = formattedDuration;
        }

        // Track form interactions
        document.querySelectorAll('#adminForm input, #adminForm textarea').forEach(input => {
            input.addEventListener('input', function() {
                localStorage.setItem('lastFormInteraction', Date.now().toString());
                saveFormData();
            });
        });

        // Clear saved data on successful form submission
        document.getElementById('adminForm').addEventListener('submit', function() {
            localStorage.removeItem('adminFormData');
            localStorage.removeItem('lastFormInteraction');
        });

        // Update setiap detik
        setInterval(updateHashCountdown, 1000);
        setInterval(updateSessionDuration, 1000);
        
        // Initial calls
        updateHashCountdown();
        updateSessionDuration();
        
        // Load saved form data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadFormData();
        });
    </script>
</body>
</html>
