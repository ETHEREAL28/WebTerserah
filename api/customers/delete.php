<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    
    if (empty($id)) {
        sendResponse(false, 'ID customer harus diisi!');
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Customer berhasil dihapus!');
    } else {
        sendResponse(false, 'Gagal menghapus customer: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>