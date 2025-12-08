<?php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        sendResponse(false, 'Username dan password harus diisi!');
    }
    
    $conn = getConnection();
    
    // Hash password dengan md5 (untuk simplicity, gunakan password_hash untuk production)
    $hashedPassword = md5($password);
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $hashedPassword);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        sendResponse(true, 'Login berhasil!', [
            'role' => $user['role'],
            'full_name' => $user['full_name']
        ]);
    } else {
        sendResponse(false, 'Username atau password salah!');
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>