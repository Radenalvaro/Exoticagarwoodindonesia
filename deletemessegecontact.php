<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'database.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $message_id = (int)$_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $result = $stmt->execute([$message_id]);
        
        if ($result) {
            $_SESSION['success_message'] = 'Pesan berhasil dihapus!';
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus pesan!';
        }
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'ID pesan tidak valid!';
}

header('Location: checkmessegecontact.php');
exit;
?>
