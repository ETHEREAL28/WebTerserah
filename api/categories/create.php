<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    
    if (empty($name)) {
        sendResponse(false, 'Nama kategori harus diisi!');
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Kategori berhasil ditambahkan!', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Gagal menambahkan kategori: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>