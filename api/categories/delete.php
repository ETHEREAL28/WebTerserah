<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    
    if (empty($id)) {
        sendResponse(false, 'ID kategori harus diisi!');
    }
    
    $conn = getConnection();
    
    // Check if category has products
    $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = ?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result()->fetch_assoc();
    
    if ($result['total'] > 0) {
        sendResponse(false, 'Kategori tidak bisa dihapus karena masih memiliki produk!');
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Kategori berhasil dihapus!');
    } else {
        sendResponse(false, 'Gagal menghapus kategori: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>