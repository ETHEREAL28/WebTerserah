<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($id) || empty($name)) {
        sendResponse(false, 'Data tidak lengkap!');
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("UPDATE customers SET name = ?, phone = ?, address = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $address, $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Customer berhasil diupdate!');
    } else {
        sendResponse(false, 'Gagal mengupdate customer: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>