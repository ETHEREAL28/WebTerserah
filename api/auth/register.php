<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';
    $fullName = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($username) || empty($password) || empty($email) || empty($fullName)) {
        sendResponse(false, 'Semua field wajib diisi!');
    }
    
    $conn = getConnection();
    
    // Check if username exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        sendResponse(false, 'Username sudah digunakan!');
    }
    $checkStmt->close();
    
    // Hash password
    $hashedPassword = md5($password);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, 'customer')");
    $stmt->bind_param("ssssss", $username, $hashedPassword, $email, $fullName, $phone, $address);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Registrasi berhasil! Silakan login.');
    } else {
        sendResponse(false, 'Gagal mendaftar: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>