<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $categoryId = $_POST['category_id'] ?? 0;
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $image = $_POST['image'] ?? '';
    $barcode = $_POST['barcode'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($id) || empty($name) || empty($categoryId) || empty($price) || empty($barcode) || empty($image)) {
        sendResponse(false, 'Data tidak lengkap!');
    }
    
    $conn = getConnection();
    
    // Check if barcode exists (except current product)
    $checkStmt = $conn->prepare("SELECT id FROM products WHERE barcode = ? AND id != ?");
    $checkStmt->bind_param("si", $barcode, $id);
    $checkStmt->execute();
    if ($checkStmt->get_result()->num_rows > 0) {
        sendResponse(false, 'Barcode sudah digunakan oleh produk lain!');
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("UPDATE products SET name = ?, category_id = ?, price = ?, stock = ?, image = ?, barcode = ?, description = ? WHERE id = ?");
    $stmt->bind_param("siiisssi", $name, $categoryId, $price, $stock, $image, $barcode, $description, $id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Produk berhasil diupdate!');
    } else {
        sendResponse(false, 'Gagal mengupdate produk: ' . $conn->error);
    }
    
    $stmt->close();
    closeConnection($conn);
} else {
    sendResponse(false, 'Method tidak diizinkan!');
}
?>