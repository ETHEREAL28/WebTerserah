<?php
session_start();
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "status" => false,
        "message" => "Unauthorized"
    ]);
    exit;
}

require "../api/config.php";
$conn = getConnection();


$user_id = $_SESSION['user_id'];
$payment_method = trim($_POST['payment_method'] ?? '');

if ($payment_method == '') {
    echo json_encode([
        "status" => false,
        "message" => "Metode pembayaran wajib diisi"
    ]);
    exit;
}

$conn->begin_transaction();

try {

    $stmt = $conn->prepare("
        SELECT c.quantity, p.price
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        throw new Exception("Cart kosong");
    }

    $total_amount = 0;
    while ($row = $result->fetch_assoc()) {
        $total_amount += $row['price'] * $row['quantity'];
    }


    $stmt = $conn->prepare("
        INSERT INTO orders (user_id, total_amount, payment_method, status, created_at)
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->bind_param("ids", $user_id, $total_amount, $payment_method);
    $stmt->execute();

    $order_id = $conn->insert_id;


    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Checkout berhasil",
        "order_id" => $order_id
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        "status" => false,
        "message" => $e->getMessage()
    ]);
}