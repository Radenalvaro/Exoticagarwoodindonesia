<?php
session_start();

// Fungsi untuk mencatat log logout
function logLogout($username) {
    $log_file = 'logs/maintenance_access.log';
    $log_dir = dirname($log_file);
    
    // Buat direktori log jika belum ada
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] LOGOUT: User '{$username}' logged out from maintenance system\n";
    
    // Tulis ke file log
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Ambil username sebelum session dihapus
$username = $_SESSION['maintenance_username'] ?? 'Unknown';

// Hapus semua data session
$_SESSION = array();

// Hapus cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Log logout activity
logLogout($username);

// Redirect setelah beberapa detik
$redirect_delay = 3; // dalam detik
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout - Secure Maintenance System</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="<?php echo $redirect_delay; ?>;url=loginmaintainance.php">
    <style>
        body {
            background: linear-gradient(135deg, #1c0c00 0%, #2a1200 50%, #1c0c00 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .logout-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            text-align: center;
            border: 1px solid rgba(175, 123, 0, 0.2);
        }
        
        .logout-icon {
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
        
        .logout-icon i {
            font-size: 35px;
            color: white;
        }
        
        h2 {
            color: #af7b00;
            margin-bottom: 15px;
        }
        
        .countdown {
            font-size: 24px;
            font-weight: bold;
            color: #af7b00;
            margin: 20px 0;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #916700 0%, #af7b00 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(175, 123, 0, 0.3);
        }
        
        .security-note {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
            border-left: 4px solid #28a745;
        }
        
        .progress {
            height: 10px;
            margin: 20px 0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .progress-bar {
            background: linear-gradient(135deg, #af7b00 0%, #d4941a 100%);
            transition: width 1s linear;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h2>Logout Berhasil</h2>
        <p>Anda telah berhasil keluar dari sistem maintenance.</p>
        <p>Semua sesi keamanan telah dihapus.</p>
        
        <div class="progress">
            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
        </div>
        
        <div class="countdown">
            Redirect dalam <span id="countdown"><?php echo $redirect_delay; ?></span> detik
        </div>
        
        <a href="loginmaintainance.php" class="btn btn-login">
            <i class="fas fa-sign-in-alt me-2"></i>Login Kembali
        </a>
        
        <div class="security-note">
            <small>
                <i class="fas fa-shield-alt text-success me-2"></i>
                <strong>Informasi Keamanan:</strong> Semua data sesi telah dihapus dari browser Anda. 
                Untuk keamanan tambahan, disarankan untuk menutup browser setelah selesai menggunakan sistem.
            </small>
        </div>
    </div>
    
    <script>
        // Countdown timer
        let countdown = <?php echo $redirect_delay; ?>;
        const countdownElement = document.getElementById('countdown');
        const progressBar = document.querySelector('.progress-bar');
        
        function updateCountdown() {
            countdown--;
            countdownElement.textContent = countdown;
            
            // Update progress bar
            const progressPercent = (<?php echo $redirect_delay; ?> - countdown) / <?php echo $redirect_delay; ?> * 100;
            progressBar.style.width = progressPercent + '%';
            
            if (countdown <= 0) {
                window.location.href = 'loginmaintainance.php';
            } else {
                setTimeout(updateCountdown, 1000);
            }
        }
        
        // Start countdown
        setTimeout(updateCountdown, 1000);
    </script>
</body>
</html>
