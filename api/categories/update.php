<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    
    if (empty($id) || empty($name)) {
        sendResponse(false, 'Data tidak lengkap!');
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Kategori berhasil diupdate!');
    } else {
        sendResponse(false, 'Gagal mengupdate kategori: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>