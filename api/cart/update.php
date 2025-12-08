<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    sendResponse(false, 'Silakan login terlebih dahulu!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $cartId = $_POST['cart_id'] ?? 0;
    $quantity = $_POST['quantity'] ?? 1;
    
    if (empty($cartId) || $quantity < 1) {
        sendResponse(false, 'Data tidak valid!');
    }
    
    $conn = getConnection();
    
    // Get product stock
    $checkStmt = $conn->prepare("SELECT p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
    $checkStmt->bind_param("ii", $cartId, $userId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Item tidak ditemukan!');
    }
    
    $item = $result->fetch_assoc();
    if ($quantity > $item['stock']) {
        sendResponse(false, 'Jumlah melebihi stok!');
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cartId, $userId);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Keranjang berhasil diupdate!');
    } else {
        sendResponse(false, 'Gagal mengupdate keranjang!');
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>