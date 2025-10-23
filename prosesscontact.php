<?php
// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header immediately
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');

// Clear any output buffer that might exist
if (ob_get_level()) {
    ob_end_clean();
}

// Start output buffering to catch any unwanted output
ob_start();

// Function to send JSON response
function sendJsonResponse($success, $message, $data = null) {
    // Clear any output buffer
    if (ob_get_length()) {
        ob_clean();
    }
    
    $response = [
        'success' => $success,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if ($data !== null) {
        $response['data'] = $data;
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed');
}

// Check if POST data exists
if (empty($_POST)) {
    sendJsonResponse(false, 'No data received');
}

require_once 'database.php';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            PDO::ATTR_TIMEOUT => 30
        ]
    );
    
    // Test connection
    $pdo->query("SELECT 1");
    
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        sendJsonResponse(false, "Database '$dbname' tidak ditemukan. Pastikan database sudah dibuat.");
    }
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        sendJsonResponse(false, "Akses database ditolak. Periksa username dan password database.");
    }
    
    sendJsonResponse(false, "Koneksi database gagal. Silakan coba lagi nanti.");
}

try {
    // Get and sanitize form data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validation
    if (empty($name)) {
        sendJsonResponse(false, 'Nama harus diisi');
    }
    
    if (empty($email)) {
        sendJsonResponse(false, 'Email harus diisi');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        sendJsonResponse(false, 'Format email tidak valid');
    }
    
    if (empty($message)) {
        sendJsonResponse(false, 'Pesan harus diisi');
    }
    
    if (strlen($message) < 10) {
        sendJsonResponse(false, 'Pesan terlalu pendek (minimal 10 karakter)');
    }
    
    // Check for spam (same email and message in last 5 minutes)
    $stmt = $pdo->prepare("
        SELECT id FROM contact_messages 
        WHERE email = ? AND message = ? 
        AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
    ");
    $stmt->execute([$email, $message]);
    
    if ($stmt->fetch()) {
        sendJsonResponse(false, 'Pesan yang sama sudah dikirim dalam 5 menit terakhir');
    }
    
    // Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, message, is_read, created_at) 
        VALUES (?, ?, ?, ?, 0, NOW())
    ");
    
    $result = $stmt->execute([$name, $email, $phone, $message]);
    
    if ($result) {
        $messageId = $pdo->lastInsertId();
        
        // Log successful submission
        error_log("Contact form submitted successfully - ID: $messageId, Email: $email");
        
        sendJsonResponse(true, 'Terima kasih! Pesan Anda berhasil dikirim. Tim kami akan menghubungi Anda segera.', [
            'message_id' => $messageId
        ]);
    } else {
        sendJsonResponse(false, 'Gagal menyimpan pesan ke database');
    }
    
} catch (PDOException $e) {
    error_log("Database error in contact form: " . $e->getMessage());
    sendJsonResponse(false, 'Terjadi kesalahan database. Silakan coba lagi nanti.');
} catch (Exception $e) {
    error_log("General error in contact form: " . $e->getMessage());
    sendJsonResponse(false, 'Terjadi kesalahan sistem. Silakan coba lagi nanti.');
}

// Clear output buffer and ensure clean exit
if (ob_get_length()) {
    ob_end_clean();
}
?>
