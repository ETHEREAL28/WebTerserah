<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../index.php');
    exit;
}

require_once '../api/config.php';
$conn = getConnection();

$userId = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

function fetchOrders($conn) {
    // Note: Joined with users to get customer name
    $sql = "SELECT o.*, u.full_name as customer_name 
            FROM orders o 
            LEFT JOIN users u ON o.user_id = u.id 
            ORDER BY o.created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$orders = fetchOrders($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - TERSERAHMART</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <style>
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #444;
        }
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }
        .status-pending { background: #fff3e0; color: #ff9800; }
        .status-completed { background: #e8f5e9; color: #4caf50; }
        .status-cancelled { background: #ffebee; color: #f44336; }
    </style>
</head>
<body>
    <button class="hamburger-menu" id="hamburgerBtn">
        <span class="iconify" data-icon="mdi:menu"></span>
    </button>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <span class="iconify" data-icon="mdi:store"></span>
                <h2>TERSERAHMART</h2>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <span class="iconify" data-icon="mdi:view-dashboard"></span>
                Dashboard
            </a>
            <a href="products.php" class="nav-link">
                <span class="iconify" data-icon="mdi:package-variant"></span>
                Produk
            </a>
            <a href="categories.php" class="nav-link">
                <span class="iconify" data-icon="mdi:shape"></span>
                Kategori
            </a>
            <a href="orders.php" class="nav-link active">
                <span class="iconify" data-icon="mdi:clipboard-list"></span>
                Pesanan
            </a>
            <a href="customers.php" class="nav-link">
                <span class="iconify" data-icon="mdi:account-group"></span>
                Customer
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar" style="background: #f44336;">A</div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            <button class="btn-logout" onclick="logout()">
                <span class="iconify" data-icon="mdi:logout"></span>
                Logout
            </button>
        </div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="page-header">
            <div>
                <h1>Kelola Pesanan</h1>
                <p>Daftar transaksi pelanggan</p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total Amount</th>
                        <th>Metode Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>
                                <span style="font-weight: 600; color: #667eea;">#<?php echo $order['id']; ?></span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 30px; height: 30px; background: #eee; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #666;">
                                        <?php echo strtoupper(substr($order['customer_name'] ?? 'Guest', 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?>
                                </div>
                            </td>
                            <td>
                                <span style="color: #666;">
                                    <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                </span>
                            </td>
                            <td style="font-weight: 600;">
                                Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                            </td>
                            <td>
                                <?php echo ucfirst($order['payment_method'] ?? '-'); ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; color: #999; padding: 30px;">
                                Belum ada pesanan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.getElementById('hamburgerBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('visible');
            document.getElementById('mainContent').classList.toggle('expanded');
        });

        function logout() {
            if (confirm('Yakin ingin logout?')) {
                window.location.href = '../api/auth/logout.php';
            }
        }
    </script>
</body>
</html>
<?php closeConnection($conn); ?>