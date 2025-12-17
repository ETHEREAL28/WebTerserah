<?php
session_start();


if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}


require_once '../api/config.php';
$conn = getConnection();

$userId = $_SESSION['user_id'];


$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pesanan Saya - TERSERAHMART</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <style>
        body { padding: 20px; font-family: sans-serif; }
        h1 { margin-bottom: 20px; }
        .back-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; color: #333; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f5f5f5; }
        .status-badge { padding: 5px 10px; border-radius: 15px; font-size: 0.9em; font-weight: 600; }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-completed { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <a href="home.php" class="back-btn">
        <span class="iconify" data-icon="mdi:arrow-left"></span>
        Kembali ke Beranda
    </a>

    <h1>Pesanan Saya</h1>
    
    <?php if ($result->num_rows > 0): ?>
    <table>
        <tr>
            <th>Order ID</th>
            <th>Tanggal</th>
            <th>Total</th>
            <th>Metode Pembayaran</th>
            <th>Status</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td>#<?php echo $row['id']; ?></td>
            <td><?php echo date('d M Y H:i', strtotime($row['created_at'])); ?></td>
            <td>Rp <?php echo number_format($row['total_amount'], 0, ',', '.'); ?></td>
            <td><?php echo ucfirst($row['payment_method']); ?></td>
            <td>
                <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                    <?php echo ucfirst($row['status']); ?>
                </span>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>Belum ada pesanan.</p>
    <?php endif; ?>
</body>
</html>

<?php
$conn->close();
?>