<?php
// Script untuk update hash setiap 10 detik
require_once 'database.php';

// Data asli yang akan di-hash
$original_data = [
    'username' => 'Raden Alvaro',
    'password' => 'alvo$2007*',
    'namaayah' => 'Soejono Badroen',
    'namaibu' => 'Esiria Juita',
    'tanggallahir' => '2007-10-08',
    'seedphrase' => 'apple mirror moon tiger coffee smile happy cloud tree jump water sun'
];

function generateSecureHash($data, $timestamp_salt) {
    // Gabungkan semua data dengan timestamp salt
    $combined_string = $data['username'] . '|' . 
                      $data['password'] . '|' . 
                      $data['namaayah'] . '|' . 
                      $data['namaibu'] . '|' . 
                      $data['tanggallahir'] . '|' . 
                      $data['seedphrase'] . '|' . 
                      $timestamp_salt;
    
    return [
        'username_hash' => hash('sha256', $data['username'] . $timestamp_salt),
        'password_hash' => hash('sha256', $data['password'] . $timestamp_salt),
        'namaayah_hash' => hash('sha256', $data['namaayah'] . $timestamp_salt),
        'namaibu_hash' => hash('sha256', $data['namaibu'] . $timestamp_salt),
        'tanggallahir_hash' => hash('sha256', $data['tanggallahir'] . $timestamp_salt),
        'seedphrase_hash' => hash('sha256', $data['seedphrase'] . $timestamp_salt),
        'combined_hash' => hash('sha256', $combined_string),
        'hash_timestamp' => $timestamp_salt
    ];
}

function updateHashInDatabase($pdo, $hashes) {
    try {
        $stmt = $pdo->prepare("
            UPDATE loginmaintainance 
            SET username_hash = ?, 
                password_hash = ?, 
                namaayah_hash = ?, 
                namaibu_hash = ?, 
                tanggallahir_hash = ?, 
                seedphrase_hash = ?,
                combined_hash = ?,
                hash_timestamp = ?,
                last_updated = NOW()
            WHERE is_active = 1
        ");
        
        return $stmt->execute([
            $hashes['username_hash'],
            $hashes['password_hash'],
            $hashes['namaayah_hash'],
            $hashes['namaibu_hash'],
            $hashes['tanggallahir_hash'],
            $hashes['seedphrase_hash'],
            $hashes['combined_hash'],
            $hashes['hash_timestamp']
        ]);
    } catch(PDOException $e) {
        error_log("Hash update error: " . $e->getMessage());
        return false;
    }
}

// Hitung timestamp salt (interval 10 detik)
$current_time = time();
$timestamp_salt = floor($current_time / 10);

// Generate hash baru
$new_hashes = generateSecureHash($original_data, $timestamp_salt);

// Update database
$success = updateHashInDatabase($pdo, $new_hashes);

// Cek apakah file ini diakses langsung atau di-include
$is_direct_access = (basename($_SERVER['PHP_SELF']) === basename(__FILE__));

// Jika dijalankan via web browser DAN diakses langsung (bukan di-include)
if (isset($_SERVER['HTTP_HOST']) && $is_direct_access) {
    // Tampilkan halaman HTML
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hash Security System - Exotic Agarwood Indonesia</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            body {
                background: linear-gradient(135deg, #1c0c00 0%, #2a1200 50%, #1c0c00 100%);
                min-height: 100vh;
                padding: 40px 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: #333;
            }
            
            .container {
                max-width: 900px;
            }
            
            .hash-container {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(10px);
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
                padding: 40px;
                border: 1px solid rgba(175, 123, 0, 0.2);
                position: relative;
                overflow: hidden;
            }
            
            .hash-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                background: linear-gradient(90deg, #af7b00, #d4941a, #af7b00);
                background-size: 200% 100%;
                animation: shimmer 3s ease-in-out infinite;
            }
            
            @keyframes shimmer {
                0%, 100% { background-position: 200% 0; }
                50% { background-position: -200% 0; }
            }
            
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            
            .brand-logo {
                width: 80px;
                height: 80px;
                background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
                border-radius: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 20px;
                box-shadow: 0 10px 30px rgba(175, 123, 0, 0.3);
                transform: rotate(-5deg);
                transition: transform 0.3s ease;
            }
            
            .brand-logo:hover {
                transform: rotate(0deg) scale(1.05);
            }
            
            .brand-logo i {
                font-size: 35px;
                color: white;
            }
            
            .header h1 {
                color: #af7b00;
                font-weight: 700;
                margin-bottom: 10px;
                font-size: 32px;
            }
            
            .header p {
                color: #666;
                margin: 0;
            }
            
            .status-card {
                background: linear-gradient(135deg, #e7f3ff 0%, #f3e5f5 100%);
                border-radius: 15px;
                padding: 20px;
                margin-bottom: 30px;
                border: 1px solid rgba(175, 123, 0, 0.2);
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
            }
            
            .status-item {
                padding: 10px 15px;
                flex: 1;
                min-width: 200px;
                text-align: center;
            }
            
            .status-label {
                font-size: 14px;
                color: #666;
                margin-bottom: 5px;
            }
            
            .status-value {
                font-size: 18px;
                font-weight: bold;
                color: #af7b00;
            }
            
            .status-success {
                color: #28a745;
            }
            
            .status-error {
                color: #dc3545;
            }
            
            .hash-table {
                background: white;
                border-radius: 15px;
                overflow: hidden;
                box-shadow: 0 5px 15px rgba(0,0,0,0.05);
                margin-bottom: 30px;
            }
            
            .hash-table .table {
                margin-bottom: 0;
            }
            
            .hash-table th {
                background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
                color: white;
                font-weight: 600;
                border: none;
            }
            
            .hash-table td {
                vertical-align: middle;
                border-color: #f0f0f0;
            }
            
            .hash-value {
                font-family: monospace;
                font-size: 14px;
                word-break: break-all;
                color: #333;
                background: #f8f9fa;
                padding: 8px;
                border-radius: 5px;
                border: 1px solid #eee;
            }
            
            .btn-login {
                background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
                border: none;
                border-radius: 10px;
                padding: 15px 30px;
                font-weight: 600;
                font-size: 16px;
                color: white;
                transition: all 0.3s ease;
                display: block;
                margin: 0 auto;
                text-decoration: none;
            }
            
            .btn-login:hover {
                background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(175, 123, 0, 0.3);
                color: white;
            }
            
            .security-note {
                background: #fff3e0;
                border-radius: 10px;
                padding: 15px;
                margin-top: 20px;
                border-left: 4px solid #ff9800;
                font-size: 14px;
            }
            
            .auto-update {
                text-align: center;
                margin-top: 20px;
                font-size: 14px;
                color: #666;
            }
            
            .countdown {
                font-weight: bold;
                color: #af7b00;
            }
            
            .pulse {
                animation: pulse 2s infinite;
            }
            
            @keyframes pulse {
                0% { transform: scale(1); }
                50% { transform: scale(1.05); }
                100% { transform: scale(1); }
            }
            
            @media (max-width: 768px) {
                .hash-container {
                    padding: 30px 20px;
                }
                
                .status-item {
                    min-width: 100%;
                    margin-bottom: 10px;
                }
                
                .hash-value {
                    font-size: 12px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="hash-container">
                <div class="header">
                    <div class="brand-logo pulse">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h1>Hash Security System</h1>
                    <p>Dynamic SHA256 Hash Generator with 10-Second Rotation</p>
                </div>
                
                <div class="status-card">
                    <div class="status-item">
                        <div class="status-label">
                            <i class="fas fa-clock me-1"></i> Current Time
                        </div>
                        <div class="status-value">
                            <?php echo date('Y-m-d H:i:s', $current_time); ?>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-label">
                            <i class="fas fa-fingerprint me-1"></i> Timestamp Salt
                        </div>
                        <div class="status-value">
                            <?php echo $timestamp_salt; ?>
                        </div>
                    </div>
                    
                    <div class="status-item">
                        <div class="status-label">
                            <i class="fas fa-sync-alt me-1"></i> Update Status
                        </div>
                        <div class="status-value <?php echo $success ? 'status-success' : 'status-error'; ?>">
                            <?php echo $success ? 'Success' : 'Failed'; ?>
                        </div>
                    </div>
                </div>
                
                <h5 class="mb-3">
                    <i class="fas fa-key me-2"></i>
                    Generated Hashes
                </h5>
                
                <div class="hash-table">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="25%">Hash Type</th>
                                <th>Hash Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($new_hashes as $key => $hash): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($key); ?></strong></td>
                                    <td><div class="hash-value"><?php echo htmlspecialchars($hash); ?></div></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <a href="loginmaintainance.php" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Go to Maintenance Login
                </a>
                
                <div class="security-note">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Security Information:</strong> This system uses SHA256 hashing with a dynamic salt that changes every 10 seconds.
                    All sensitive data is protected with multiple layers of security.
                </div>
                
                <div class="auto-update">
                    <i class="fas fa-sync-alt fa-spin me-1"></i>
                    This page will auto-update in <span id="countdown" class="countdown">10</span> seconds
                </div>
            </div>
        </div>
        
        <script>
            // Auto-update countdown
            function updateCountdown() {
                const now = Math.floor(Date.now() / 1000);
                const remaining = 10 - (now % 10);
                
                document.getElementById('countdown').textContent = remaining;
                
                if (remaining === 1) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            }
            
            // Update countdown every second
            setInterval(updateCountdown, 1000);
            updateCountdown();
        </script>
    </body>
    </html>
    <?php
} elseif (!isset($_SERVER['HTTP_HOST'])) {
    // Jika dijalankan via command line
    echo "Hash updated: " . ($success ? "Success" : "Failed") . "\n";
    echo "Timestamp: " . date('Y-m-d H:i:s') . "\n";
}

// Return success status untuk file yang meng-include
return $success;
?>
