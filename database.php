<?php
// Database configuration
$host = 'localhost';
$dbname = 'agarwood_db';
$username = 'root'; // Sesuaikan dengan username database Anda
$password = '';     // Sesuaikan dengan password database Anda
try {
    // Create PDO connection with more options
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
    // Log detailed error
    error_log("Database connection failed: " . $e->getMessage());
    error_log("Connection details - Host: $host, Database: $dbname, User: $username");
    
    // Check if it's a database not found error
    if (strpos($e->getMessage(), 'Unknown database') !== false) {
        die(json_encode([
            'success' => false,
            'message' => "Database '$dbname' tidak ditemukan. Pastikan database sudah dibuat."
        ]));
    }
    
    // Check if it's an access denied error
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        die(json_encode([
            'success' => false,
            'message' => "Akses database ditolak. Periksa username dan password database."
        ]));
    }
    
    // Generic database error
    die(json_encode([
        'success' => false,
        'message' => "Koneksi database gagal: " . $e->getMessage()
    ]));
}
?>
