<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'database.php';

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $origin = trim($_POST['origin'] ?? '');
    $grade = trim($_POST['grade'] ?? '');
    $quantity = (int)($_POST['quantity'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    
    // Validation
    if (empty($name) || empty($origin) || empty($grade) || empty($description) || $price <= 0) {
        $error_message = 'Semua field wajib diisi dan harga harus lebih dari 0!';
    } else {
        // Handle file upload
        $image_url = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_info = pathinfo($_FILES['product_image']['name']);
            $file_extension = strtolower($file_info['extension']);
            
            // Validate file type
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($file_extension, $allowed_extensions)) {
                $error_message = 'Format file tidak didukung! Gunakan JPG, PNG, GIF, atau WebP.';
            } else {
                // Generate unique filename
                $new_filename = 'kayu_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                    $image_url = $upload_path;
                } else {
                    $error_message = 'Gagal mengupload gambar!';
                }
            }
        }
        
        if (empty($error_message)) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO products (name, category, grade, origin, price, unit, description, image_url, stock_quantity, is_active) 
                    VALUES (?, 'kayu_gaharu', ?, ?, ?, 'KG', ?, ?, ?, 1)
                ");
                
                $result = $stmt->execute([$name, $grade, $origin, $price, $description, $image_url, $quantity]);
                
                if ($result) {
                    $success_message = 'Produk kayu gaharu berhasil ditambahkan!';
                    // Clear form
                    $name = $origin = $grade = $description = '';
                    $quantity = $price = 0;
                } else {
                    $error_message = 'Gagal menambahkan produk!';
                }
            } catch(PDOException $e) {
                $error_message = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Get unread messages count for sidebar
try {
    $stmt = $pdo->query("SELECT COUNT(*) as unread_count FROM contact_messages WHERE is_read = 0");
    $unread_count = $stmt->fetch()['unread_count'];
} catch(PDOException $e) {
    $unread_count = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kayu Gaharu - Admin Dashboard</title>
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
        
        .form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .form-title {
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
        
        .file-upload-area {
            border: 2px dashed #af7b00;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #fafafa;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            background: #f0f0f0;
            border-color: #916700;
        }
        
        .file-upload-area.dragover {
            background: #e8f5e8;
            border-color: #28a745;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #af7b00;
            margin-bottom: 15px;
        }
        
        .upload-text {
            color: #666;
            margin-bottom: 10px;
        }
        
        .file-info {
            background: #e8f5e8;
            border: 1px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
            display: none;
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
            <a class="nav-link active" href="inputdatakayugaharu.php">
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
                <?php if ($unread_count > 0): ?>
                    <span class="badge bg-danger ms-2"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
            
            <div class="nav-section">Pengaturan</div>
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
            <h3 class="mb-0">Tambah Produk Kayu Gaharu</h3>
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
            
            <div class="form-container">
                <h4 class="form-title">
                    <i class="fas fa-tree me-2"></i>Form Tambah Kayu Gaharu
                </h4>
                
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="origin" class="form-label">Asal Daerah *</label>
                                <input type="text" class="form-control" id="origin" name="origin" 
                                       value="<?php echo htmlspecialchars($origin ?? ''); ?>" 
                                       placeholder="Contoh: Sumatra, Indonesia" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="grade" class="form-label">Grade *</label>
                                <input type="text" class="form-control" id="grade" name="grade" 
                                       value="<?php echo htmlspecialchars($grade ?? ''); ?>" 
                                       placeholder="Contoh: Super A+, Premium Grade, dll" required>
                                <div class="form-text">Tulis grade sesuai keinginan Anda</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Stok (KG)</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       value="<?php echo htmlspecialchars($quantity ?? 0); ?>" min="0">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="price" class="form-label">Harga (USD) *</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo htmlspecialchars($price ?? ''); ?>" 
                                       step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Gambar Produk</label>
                        <div class="file-upload-area" onclick="document.getElementById('product_image').click()">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="upload-text">
                                <strong>Klik untuk upload gambar</strong> atau drag & drop file di sini
                            </div>
                            <small class="text-muted">Format: JPG, PNG, GIF, WebP (Max: 5MB)</small>
                        </div>
                        <input type="file" id="product_image" name="product_image" 
                               accept="image/*" style="display: none;">
                        <div class="file-info" id="file-info">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            <span id="file-name"></span>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeFile()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="description" class="form-label">Deskripsi Produk *</label>
                        <textarea class="form-control" id="description" name="description" rows="5" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                        <div class="form-text">Jelaskan detail produk, kualitas, dan keunggulannya</div>
                    </div>
                    
                    <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-submit">
                            <i class="fas fa-save me-2"></i>Simpan Produk
                        </button>
                        <a href="admineai.php" class="btn btn-back">
                            <i class="fas fa-times me-2"></i>Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // File upload handling
        const fileInput = document.getElementById('product_image');
        const fileInfo = document.getElementById('file-info');
        const fileName = document.getElementById('file-name');
        const uploadArea = document.querySelector('.file-upload-area');
        
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File terlalu besar! Maksimal 5MB.');
                    this.value = '';
                    return;
                }
                
                fileName.textContent = file.name;
                fileInfo.style.display = 'block';
                uploadArea.style.border = '2px solid #28a745';
                uploadArea.style.background = '#e8f5e8';
            }
        });
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
        
        function removeFile() {
            fileInput.value = '';
            fileInfo.style.display = 'none';
            uploadArea.style.border = '2px dashed #af7b00';
            uploadArea.style.background = '#fafafa';
        }
    </script>
</body>
</html>
