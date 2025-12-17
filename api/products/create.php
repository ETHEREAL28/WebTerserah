<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    $name = $input['name'] ?? '';
    $categoryId = $input['category_id'] ?? 0;
    $price = $input['price'] ?? 0;
    $stock = $input['stock'] ?? 0;
    $image = $input['image'] ?? '';
    $barcode = $input['barcode'] ?? '';
    $description = $input['description'] ?? '';
    
    if (empty($name) || empty($categoryId) || empty($price) || empty($barcode) || empty($image)) {
        sendResponse(false, 'Data tidak lengkap!');
    }
    
    $conn = getConnection();
    

    $checkStmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
    $checkStmt->bind_param("s", $barcode);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        sendResponse(false, 'Barcode sudah digunakan!');
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("INSERT INTO products (name, category_id, price, stock, image, barcode, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siiisss", $name, $categoryId, $price, $stock, $image, $barcode, $description);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Produk berhasil ditambahkan!', ['id' => $conn->insert_id]);
    } else {
        sendResponse(false, 'Gagal menambahkan produk: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>