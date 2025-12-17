<?php
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = getJsonInput();
    $id = $input['id'] ?? 0;
    $name = $input['name'] ?? '';
    $categoryId = $input['category_id'] ?? 0;
    $price = $input['price'] ?? 0;
    $stock = $input['stock'] ?? 0;
    $image = $input['image'] ?? '';
    $barcode = $input['barcode'] ?? '';
    $description = $input['description'] ?? '';
    
    if (empty($id) || empty($name) || empty($categoryId) || empty($price) || empty($barcode) || empty($image)) {
        sendResponse(false, 'Data tidak lengkap!');
    }
    
    $conn = getConnection();
    

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
