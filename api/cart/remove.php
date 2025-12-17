<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Silakan login terlebih dahulu!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $input = getJsonInput();
    $cartId = $input['cart_id'] ?? 0;
    
    if (empty($cartId)) {
        sendResponse(false, 'ID tidak valid!');
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cartId, $userId);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Item berhasil dihapus dari keranjang!');
    } else {
        sendResponse(false, 'Gagal menghapus item!');
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>