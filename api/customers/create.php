<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($name)) {
        sendResponse(false, 'Nama customer harus diisi!');
    }
    
    $conn = getConnection();
    
    $stmt = $conn->prepare("INSERT INTO customers (name, phone, address) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $phone, $address);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Customer berhasil ditambahkan!', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Gagal menambahkan customer: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>
