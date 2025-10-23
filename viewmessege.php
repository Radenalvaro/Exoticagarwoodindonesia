<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Database connection details
require_once 'database.php';

// First, connect without database to create it if needed
try {
    $conn_create = new mysqli($servername, $username, $password);
    if ($conn_create->connect_error) {
        die("Connection failed: " . $conn_create->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn_create->query($sql_create_db);
    $conn_create->close();
} catch (Exception $e) {
    // Continue if there's an error
}

// Now connect to the database
$pdo = null;
try {
    $dsn = "mysql:host=$servername;dbname=$dbname;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Create contact_messages table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$success_message = '';
$error_message = '';
$message = null;

// Get message ID
$message_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($message_id <= 0) {
    $_SESSION['error_message'] = 'ID pesan tidak valid!';
    header('Location: checkmessegecontact.php');
    exit;
}

// Get message data
try {
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$message_id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        $_SESSION['error_message'] = 'Pesan tidak ditemukan!';
        header('Location: checkmessegecontact.php');
        exit;
    }
    
    // Mark as read if not already read
    if ($message['is_read'] == 0) {
        $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$message_id]);
        $message['is_read'] = 1;
    }
    
} catch(PDOException $e) {
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header('Location: checkmessegecontact.php');
    exit;
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'mark_unread') {
        try {
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 0 WHERE id = ?");
            $result = $stmt->execute([$message_id]);
            
            if ($result) {
                $success_message = 'Pesan berhasil ditandai sebagai belum dibaca!';
                $message['is_read'] = 0;
            } else {
                $error_message = 'Gagal mengupdate status pesan!';
            }
        } catch(PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        try {
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $result = $stmt->execute([$message_id]);
            
            if ($result) {
                $_SESSION['success_message'] = 'Pesan berhasil dihapus!';
                header('Location: checkmessegecontact.php');
                exit;
            } else {
                $error_message = 'Gagal menghapus pesan!';
            }
        } catch(PDOException $e) {
            $error_message = 'Database error: ' . $e->getMessage();
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

// Format date
function formatDate($date) {
    return date('d F Y, H:i', strtotime($date));
}

// Get time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'Baru saja';
    if ($time < 3600) return floor($time/60) . ' menit yang lalu';
    if ($time < 86400) return floor($time/3600) . ' jam yang lalu';
    if ($time < 2592000) return floor($time/86400) . ' hari yang lalu';
    if ($time < 31536000) return floor($time/2592000) . ' bulan yang lalu';
    return floor($time/31536000) . ' tahun yang lalu';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message - Admin Dashboard</title>
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
        
        .message-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .message-header {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            padding: 25px 30px;
            position: relative;
        }
        
        .message-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }
        
        .message-header .content {
            position: relative;
            z-index: 1;
        }
        
        .status-badge {
            position: absolute;
            top: 20px;
            right: 30px;
            z-index: 2;
        }
        
        .message-body {
            padding: 30px;
        }
        
        .message-meta {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #af7b00;
        }
        
        .message-content {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            line-height: 1.8;
            font-size: 16px;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(175, 123, 0, 0.3);
        }
        
        .btn-secondary-custom {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary-custom:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }
        
        .btn-danger-custom {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger-custom:hover {
            background: #c82333;
            transform: translateY(-2px);
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
        
        .email-link {
            color: #af7b00;
            text-decoration: none;
            font-weight: 600;
        }
        
        .email-link:hover {
            color: #916700;
            text-decoration: underline;
        }
        
        .time-info {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .message-id {
            font-family: 'Courier New', monospace;
            background: #e9ecef;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 12px;
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
            
            <div class="nav-section">TAMBAH PRODUK</div>
            <a class="nav-link" href="inputdatakayugaharu.php">
                <i class="fas fa-plus-circle"></i>
                Tambah Kayu Gaharu
            </a>
            <a class="nav-link" href="inputdataminyakgaharu.php">
                <i class="fas fa-plus-circle"></i>
                Tambah Minyak Gaharu
            </a>
            
            <div class="nav-section">EDIT PRODUK</div>
            <a class="nav-link" href="admineai.php#kayu-section">
                <i class="fas fa-edit"></i>
                Edit Kayu Gaharu
            </a>
            <a class="nav-link" href="admineai.php#minyak-section">
                <i class="fas fa-edit"></i>
                Edit Minyak Gaharu
            </a>
            
            <div class="nav-section">HAPUS PRODUK</div>
            <a class="nav-link" href="admineai.php#kayu-section">
                <i class="fas fa-trash"></i>
                Delete Kayu Gaharu
            </a>
            <a class="nav-link" href="admineai.php#minyak-section">
                <i class="fas fa-trash"></i>
                Delete Minyak Gaharu
            </a>
            
            <div class="nav-section">PESAN</div>
            <a class="nav-link active" href="checkmessegecontact.php">
                <i class="fas fa-envelope"></i>
                Pesan Kontak
                <?php if ($unread_messages > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_messages; ?></span>
                <?php endif; ?>
            </a>
        
            <div class="nav-section">PENGATURAN</div>
            <a class="nav-link" href="changeusernpassadmin.php">
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
            <h3 class="mb-0">Detail Pesan Kontak</h3>
            <a href="checkmessegecontact.php" class="btn-back">
                <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar Pesan
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
            
            <div class="message-container">
                <!-- Message Header -->
                <div class="message-header">
                    <div class="status-badge">
                        <?php if ($message['is_read']): ?>
                            <span class="badge bg-success">
                                <i class="fas fa-check-circle me-1"></i>Sudah Dibaca
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning">
                                <i class="fas fa-exclamation-circle me-1"></i>Belum Dibaca
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="content">
                        <h4 class="mb-2">
                            <i class="fas fa-user me-2"></i>
                            <?php echo htmlspecialchars($message['name']); ?>
                        </h4>
                        <p class="mb-1">
                            <i class="fas fa-envelope me-2"></i>
                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="text-white">
                                <?php echo htmlspecialchars($message['email']); ?>
                            </a>
                        </p>
                        <div class="time-info text-white-50">
                            <i class="fas fa-clock me-1"></i>
                            <span><?php echo timeAgo($message['created_at']); ?></span>
                            <span class="mx-2">•</span>
                            <span><?php echo formatDate($message['created_at']); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Message Body -->
                <div class="message-body">
                    <!-- Message Meta Info -->
                    <div class="message-meta">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-info-circle me-2"></i>Informasi Pesan
                                </h6>
                                <p class="mb-1">
                                    <strong>ID Pesan:</strong> 
                                    <span class="message-id">#<?php echo str_pad($message['id'], 6, '0', STR_PAD_LEFT); ?></span>
                                </p>
                                <p class="mb-1">
                                    <strong>Nama Pengirim:</strong> 
                                    <?php echo htmlspecialchars($message['name']); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Email:</strong> 
                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="email-link">
                                        <?php echo htmlspecialchars($message['email']); ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2">
                                    <i class="fas fa-calendar me-2"></i>Waktu & Status
                                </h6>
                                <p class="mb-1">
                                    <strong>Diterima:</strong> 
                                    <?php echo formatDate($message['created_at']); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Waktu Relatif:</strong> 
                                    <?php echo timeAgo($message['created_at']); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Status:</strong> 
                                    <?php if ($message['is_read']): ?>
                                        <span class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>Sudah Dibaca
                                        </span>
                                    <?php else: ?>
                                        <span class="text-warning">
                                            <i class="fas fa-exclamation-circle me-1"></i>Belum Dibaca
                                        </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Message Content -->
                    <div class="message-content">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-comment-alt me-2"></i>Isi Pesan
                        </h6>
                        <div class="message-text">
                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: Pesan dari Website Agarwood&body=Halo <?php echo htmlspecialchars($message['name']); ?>,%0D%0A%0D%0ATerima kasih atas pesan Anda.%0D%0A%0D%0ASalam,%0D%0AAdmin Exotic Agarwood" 
                           class="btn btn-action btn-primary-custom">
                            <i class="fas fa-reply me-2"></i>Balas via Email
                        </a>
                        
                        <?php if ($message['is_read']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="mark_unread">
                                <button type="submit" class="btn btn-action btn-secondary-custom">
                                    <i class="fas fa-eye-slash me-2"></i>Tandai Belum Dibaca
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" style="display: inline;" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus pesan ini? Tindakan ini tidak dapat dibatalkan.')">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-action btn-danger-custom">
                                <i class="fas fa-trash me-2"></i>Hapus Pesan
                            </button>
                        </form>
                        
                        <a href="checkmessegecontact.php" class="btn btn-action btn-secondary-custom">
                            <i class="fas fa-list me-2"></i>Lihat Semua Pesan
                        </a>
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
        
        // Copy message ID to clipboard
        document.addEventListener('DOMContentLoaded', function() {
            const messageId = document.querySelector('.message-id');
            if (messageId) {
                messageId.style.cursor = 'pointer';
                messageId.title = 'Klik untuk copy ID';
                
                messageId.addEventListener('click', function() {
                    navigator.clipboard.writeText(this.textContent).then(function() {
                        // Show temporary feedback
                        const original = messageId.textContent;
                        messageId.textContent = 'Copied!';
                        messageId.style.background = '#28a745';
                        messageId.style.color = 'white';
                        
                        setTimeout(function() {
                            messageId.textContent = original;
                            messageId.style.background = '#e9ecef';
                            messageId.style.color = '';
                        }, 1000);
                    });
                });
            }
        });
    </script>
</body>
</html>
