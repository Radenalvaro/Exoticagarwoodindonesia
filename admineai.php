<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'database.php';

// Handle success/error messages
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

// Get statistics
try {
    // Count products
    $stmt = $pdo->query("SELECT COUNT(*) as total_products FROM products WHERE is_active = 1");
    $total_products = $stmt->fetch()['total_products'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as kayu_count FROM products WHERE category = 'kayu_gaharu' AND is_active = 1");
    $kayu_count = $stmt->fetch()['kayu_count'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as minyak_count FROM products WHERE category = 'minyak_gaharu' AND is_active = 1");
    $minyak_count = $stmt->fetch()['minyak_count'];
    
    // Count messages
    $stmt = $pdo->query("SELECT COUNT(*) as total_messages FROM contact_messages");
    $total_messages = $stmt->fetch()['total_messages'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as unread_messages FROM contact_messages WHERE is_read = 0");
    $unread_messages = $stmt->fetch()['unread_messages'];
    
    // Get all products for management
    $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
    $all_products = $stmt->fetchAll();
    
    // Separate products by category
    $kayu_products = array_filter($all_products, function($product) {
        return $product['category'] === 'kayu_gaharu';
    });
    
    $minyak_products = array_filter($all_products, function($product) {
        return $product['category'] === 'minyak_gaharu';
    });
    
    // Recent messages
    $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
    $recent_messages = $stmt->fetchAll();
    
} catch(PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Exotic Agarwood Indonesia</title>
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
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            border: none;
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            margin-bottom: 15px;
        }
        
        .stats-icon.products {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
        }
        
        .stats-icon.messages {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .stats-icon.kayu {
            background: linear-gradient(135deg, #6f42c1 0%, #8e44ad 100%);
        }
        
        .stats-icon.minyak {
            background: linear-gradient(135deg, #fd7e14 0%, #e83e8c 100%);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin: 0;
        }
        
        .stats-label {
            color: #6c757d;
            font-weight: 500;
            margin: 0;
        }
        
        .recent-section {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }
        
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #af7b00;
        }
        
        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            border: none;
            padding: 15px;
            font-weight: 600;
        }
        
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #eee;
        }
        
        .table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .btn-action {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 0 2px;
        }
        
        .btn-edit {
            background: #17a2b8;
            color: white;
        }
        
        .btn-edit:hover {
            background: #138496;
            color: white;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        
        .btn-delete:hover {
            background: #c82333;
            color: white;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border: none;
            color: white;
            border-radius: 8px;
            padding: 8px 16px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
            transform: translateY(-2px);
            color: white;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
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
        
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
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
            <a class="nav-link active" href="admineai.php">
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
            <a class="nav-link" href="#kayu-section" onclick="scrollToSection('kayu-section')">
                <i class="fas fa-edit"></i>
                Edit Kayu Gaharu
            </a>
            <a class="nav-link" href="#minyak-section" onclick="scrollToSection('minyak-section')">
                <i class="fas fa-edit"></i>
                Edit Minyak Gaharu
            </a>
            
            <div class="nav-section">HAPUS PRODUK</div>
            <a class="nav-link" href="#kayu-section" onclick="scrollToSection('kayu-section')">
                <i class="fas fa-trash"></i>
                Delete Kayu Gaharu
            </a>
            <a class="nav-link" href="#minyak-section" onclick="scrollToSection('minyak-section')">
                <i class="fas fa-trash"></i>
                Delete Minyak Gaharu
            </a>
            
            <div class="nav-section">PESAN</div>
            <a class="nav-link" href="checkmessegecontact.php">
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
            <h3 class="mb-0">Dashboard</h3>
            <div class="d-flex align-items-center">
                <span class="me-3">Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></span>
                <span class="badge bg-success"><?php echo date('d M Y, H:i'); ?></span>
            </div>
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
            
            <!-- Welcome Card -->
            <div class="welcome-card">
                <h2><i class="fas fa-chart-line me-2"></i>Selamat Datang di Admin Dashboard</h2>
                <p class="mb-0">Kelola produk agarwood dan pesan pelanggan dengan mudah dan efisien.</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon products">
                            <i class="fas fa-box"></i>
                        </div>
                        <h3 class="stats-number"><?php echo $total_products; ?></h3>
                        <p class="stats-label">Total Produk</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon kayu">
                            <i class="fas fa-tree"></i>
                        </div>
                        <h3 class="stats-number"><?php echo $kayu_count; ?></h3>
                        <p class="stats-label">Kayu Gaharu</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon minyak">
                            <i class="fas fa-tint"></i>
                        </div>
                        <h3 class="stats-number"><?php echo $minyak_count; ?></h3>
                        <p class="stats-label">Minyak Gaharu</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-icon messages">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3 class="stats-number"><?php echo $unread_messages; ?></h3>
                        <p class="stats-label">Pesan Baru</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="recent-section">
                <h5 class="section-title">
                    <i class="fas fa-bolt me-2"></i>Aksi Cepat
                </h5>
                <div class="row">
                    <div class="col-md-6">
                        <a href="inputdatakayugaharu.php" class="btn btn-primary-custom w-100 mb-2">
                            <i class="fas fa-plus me-2"></i>Tambah Kayu Gaharu
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="inputdataminyakgaharu.php" class="btn btn-primary-custom w-100 mb-2">
                            <i class="fas fa-plus me-2"></i>Tambah Minyak Gaharu
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Products Management -->
            <div class="row">
                <div class="col-md-8">
                    <!-- Kayu Gaharu Section -->
                    <div id="kayu-section" class="recent-section">
                        <h5 class="section-title">
                            <i class="fas fa-tree me-2"></i>Kelola Kayu Gaharu
                        </h5>
                        
                        <?php if (!empty($kayu_products)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Grade</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kayu_products as $product): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($product['image_url']) && file_exists($product['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                             alt="Product" class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($product['origin']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['grade']); ?></td>
                                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?> KG</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="editdatakayugaharu.php?id=<?php echo $product['id']; ?>" 
                                                           class="btn-action btn-edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="deletedatakayugaharu.php?id=<?php echo $product['id']; ?>" 
                                                           class="btn-action btn-delete" title="Hapus"
                                                           onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tree"></i>
                                <h6>Belum ada produk kayu gaharu</h6>
                                <a href="inputdatakayugaharu.php" class="btn btn-primary-custom mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Kayu Gaharu
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Minyak Gaharu Section -->
                    <div id="minyak-section" class="recent-section">
                        <h5 class="section-title">
                            <i class="fas fa-tint me-2"></i>Kelola Minyak Gaharu
                        </h5>
                        
                        <?php if (!empty($minyak_products)): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Gambar</th>
                                            <th>Nama Produk</th>
                                            <th>Grade</th>
                                            <th>Harga</th>
                                            <th>Stok</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($minyak_products as $product): ?>
                                            <tr>
                                                <td>
                                                    <?php if (!empty($product['image_url']) && file_exists($product['image_url'])): ?>
                                                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                             alt="Product" class="product-image">
                                                    <?php else: ?>
                                                        <div class="product-image bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($product['origin']); ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['grade']); ?></td>
                                                <td>$<?php echo number_format($product['price'], 2); ?></td>
                                                <td><?php echo $product['stock_quantity']; ?> mL</td>
                                                <td>
                                                    <div class="d-flex">
                                                        <a href="editdataminyakgaharu.php?id=<?php echo $product['id']; ?>" 
                                                           class="btn-action btn-edit" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="deletedataminyakgaharu.php?id=<?php echo $product['id']; ?>" 
                                                           class="btn-action btn-delete" title="Hapus"
                                                           onclick="return confirm('Yakin ingin menghapus produk ini?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tint"></i>
                                <h6>Belum ada produk minyak gaharu</h6>
                                <a href="inputdataminyakgaharu.php" class="btn btn-primary-custom mt-2">
                                    <i class="fas fa-plus me-1"></i>Tambah Minyak Gaharu
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="recent-section">
                        <h5 class="section-title">
                            <i class="fas fa-comments me-2"></i>Pesan Terbaru
                        </h5>
                        <?php if (!empty($recent_messages)): ?>
                            <?php foreach ($recent_messages as $message): ?>
                                <div class="border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($message['name']); ?></h6>
                                            <small class="text-muted"><?php echo htmlspecialchars($message['email']); ?></small>
                                            <p class="mb-1 mt-1"><?php echo htmlspecialchars(substr($message['message'], 0, 100)) . '...'; ?></p>
                                            <small class="text-muted"><?php echo date('d M Y H:i', strtotime($message['created_at'])); ?></small>
                                        </div>
                                        <?php if ($message['is_read'] == 0): ?>
                                            <span class="badge bg-warning">Baru</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">Belum ada pesan.</p>
                        <?php endif; ?>
                        
                        <div class="text-center mt-3">
                            <a href="checkmessegecontact.php" class="btn btn-primary-custom">
                                <i class="fas fa-eye me-1"></i>Lihat Semua Pesan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({
                behavior: 'smooth'
            });
        }
    </script>
</body>
</html>
