<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION["admin_username"])) {
    header('Location: login.php');
    exit();
}

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "agarwood_db";

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
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get success/error messages from session
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Fetch messages from the contact form
$sql = "SELECT id, name, email, message, created_at, is_read FROM contact_messages ORDER BY created_at DESC";
$result = $conn->query($sql);

// Get unread messages count
$unread_sql = "SELECT COUNT(*) as unread_count FROM contact_messages WHERE is_read = 0";
$unread_result = $conn->query($unread_sql);
$unread_count = $unread_result ? $unread_result->fetch_assoc()['unread_count'] : 0;

// Function to format time ago
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
    <title>Pesan Kontak - Admin Dashboard</title>
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
        
        .messages-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .messages-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #af7b00;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .table th {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px;
            font-size: 14px;
        }
        
        .table td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .unread-message {
            background-color: #fff3cd !important;
            border-left: 4px solid #ffc107;
        }
        
        .unread-message:hover {
            background-color: #ffeaa7 !important;
        }
        
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #6c757d;
        }
        
        .unread-indicator {
            width: 8px;
            height: 8px;
            background: #007bff;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .btn-action {
            border-radius: 8px;
            padding: 8px 15px;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.3s ease;
            border: none;
            margin-right: 5px;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
        }
        
        .btn-view:hover {
            background: linear-gradient(135deg, #0056b3 0%, #004085 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
        }
        
        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
            color: white;
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
        
        .stats-card {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
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
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_count; ?></span>
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
            <h3 class="mb-0">Pesan Kontak</h3>
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
            
            <!-- Statistics Card -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stats-number"><?php echo $result ? $result->num_rows : 0; ?></div>
                                <div>Total Pesan</div>
                            </div>
                            <i class="fas fa-envelope fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="stats-number"><?php echo $unread_count; ?></div>
                                <div>Belum Dibaca</div>
                            </div>
                            <i class="fas fa-exclamation-circle fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="messages-container">
                <h4 class="messages-title">
                    <i class="fas fa-inbox me-2"></i>Daftar Pesan Kontak
                </h4>
                
                <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Nama</th>
                                    <th><i class="fas fa-envelope me-2"></i>Email</th>
                                    <th><i class="fas fa-comment me-2"></i>Pesan</th>
                                    <th><i class="fas fa-clock me-2"></i>Waktu</th>
                                    <th><i class="fas fa-cogs me-2"></i>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="<?php echo ($row['is_read'] == 0) ? 'unread-message' : ''; ?>">
                                        <td>
                                            <?php if ($row['is_read'] == 0): ?>
                                                <span class="unread-indicator"></span>
                                            <?php endif; ?>
                                            <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($row['email']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="message-preview">
                                                <?php echo htmlspecialchars(substr($row['message'], 0, 100)); ?>
                                                <?php if (strlen($row['message']) > 100): ?>...<?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-muted small">
                                                <?php echo timeAgo($row['created_at']); ?>
                                            </div>
                                            <div class="text-muted" style="font-size: 11px;">
                                                <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="viewmessege.php?id=<?php echo $row['id']; ?>" class="btn-action btn-view">
                                                <i class="fas fa-eye me-1"></i>Lihat
                                            </a>
                                            <a href="deletemessegecontact.php?id=<?php echo $row['id']; ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus pesan dari <?php echo htmlspecialchars($row['name']); ?>?');">
                                                <i class="fas fa-trash me-1"></i>Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <h5>Belum Ada Pesan</h5>
                        <p class="text-muted">Belum ada pesan kontak yang masuk. Pesan akan muncul di sini ketika ada pengunjung yang mengirim pesan melalui form kontak.</p>
                    </div>
                <?php endif; ?>
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
    </script>
</body>
</html>

<?php
// Close connection
$conn->close();
?>
