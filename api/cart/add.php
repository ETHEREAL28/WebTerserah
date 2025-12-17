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
    $productId = $input['product_id'] ?? 0;
    $quantity = $input['quantity'] ?? 1;
    
    if (empty($productId) || $quantity < 1) {
        sendResponse(false, 'Data tidak valid!');
    }
    
    $conn = getConnection();
    

    $productStmt = $conn->prepare("SELECT stock FROM products WHERE id = ?");
    $productStmt->bind_param("i", $productId);
    $productStmt->execute();
    $productResult = $productStmt->get_result();
    
    if ($productResult->num_rows === 0) {
        sendResponse(false, 'Produk tidak ditemukan!');
    }
    
    $product = $productResult->fetch_assoc();
    if ($product['stock'] < $quantity) {
        sendResponse(false, 'Stok tidak mencukupi!');
    }
    $productStmt->close();
    

    $checkStmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $checkStmt->bind_param("ii", $userId, $productId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {

        $cart = $checkResult->fetch_assoc();
        $newQuantity = $cart['quantity'] + $quantity;
        
        if ($newQuantity > $product['stock']) {
            sendResponse(false, 'Total jumlah melebihi stok!');
        }
        
        $updateStmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $updateStmt->bind_param("ii", $newQuantity, $cart['id']);
        $updateStmt->execute();
        $updateStmt->close();
    } else {

        $insertStmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
        $insertStmt->bind_param("iii", $userId, $productId, $quantity);
        $insertStmt->execute();
        $insertStmt->close();
    }
    
    $checkStmt->close();
    closeConnection($conn);
    
    sendResponse(true, 'Produk berhasil ditambahkan ke keranjang!');
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>