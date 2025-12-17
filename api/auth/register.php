<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    $username = $input['username'] ?? '';
    $password = $input['password'] ?? '';
    $email = $input['email'] ?? '';
    $fullName = $input['full_name'] ?? '';
    $phone = $input['phone'] ?? '';
    $address = $input['address'] ?? '';
    
    if (empty($username) || empty($password) || empty($email) || empty($fullName)) {
        sendResponse(false, 'Semua field wajib diisi (username, password, email, full_name)!');
    }
    
    $conn = getConnection();
    

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        sendResponse(false, 'Username sudah digunakan!');
    }
    $checkStmt->close();
    

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