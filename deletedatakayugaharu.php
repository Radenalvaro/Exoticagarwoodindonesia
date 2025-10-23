<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    
    try {
        // Get product info first to delete image file
        $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ? AND category = 'kayu_gaharu'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Delete the product from database
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND category = 'kayu_gaharu'");
            $result = $stmt->execute([$product_id]);
            
            if ($result) {
                // Delete image file if exists
                if (!empty($product['image_url']) && file_exists($product['image_url'])) {
                    unlink($product['image_url']);
                }
                $_SESSION['success_message'] = 'Produk kayu gaharu berhasil dihapus!';
            } else {
                $_SESSION['error_message'] = 'Gagal menghapus produk!';
            }
        } else {
            $_SESSION['error_message'] = 'Produk tidak ditemukan!';
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'ID produk tidak valid!';
}

header('Location: admineai.php');
exit;
?>