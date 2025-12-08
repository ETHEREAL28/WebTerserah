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

// Get statistics
$totalProducts = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$totalOrders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM orders")->fetch_assoc()['total'] ?? 0;
$lowStock = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock < 10")->fetch_assoc()['count'];
$totalCustomers = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];

// Recent orders
$recentOrders = $conn->query("SELECT o.*, u.full_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 5");

// Low stock products
$lowStockProducts = $conn->query("SELECT * FROM products WHERE stock < 10 ORDER BY stock ASC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TERSERAHMART</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
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
            <a href="dashboard.php" class="nav-link active">
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
            <a href="orders.php" class="nav-link">
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
            <h1>Dashboard</h1>
            <p>Selamat datang kembali, <?php echo htmlspecialchars($user['full_name']); ?>!</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <span class="iconify" data-icon="mdi:package-variant"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalProducts; ?></h3>
                    <p>Total Produk</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <span class="iconify" data-icon="mdi:clipboard-list"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $totalOrders; ?></h3>
                    <p>Total Pesanan</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <span class="iconify" data-icon="mdi:cash-multiple"></span>
                </div>
                <div class="stat-info">
                    <h3>Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></h3>
                    <p>Total Pendapatan</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <span class="iconify" data-icon="mdi:alert"></span>
                </div>
                <div class="stat-info">
                    <h3><?php echo $lowStock; ?></h3>
                    <p>Stok Rendah</p>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Recent Orders -->
            <div style="background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px;">
                <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span class="iconify" data-icon="mdi:clipboard-list" style="color: #667eea;"></span>
                    Pesanan Terbaru
                </h3>

                <?php if ($recentOrders->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="border: none;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px;">ID</th>
                                    <th style="padding: 10px;">Customer</th>
                                    <th style="padding: 10px;">Total</th>
                                    <th style="padding: 10px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($order = $recentOrders->fetch_assoc()): ?>
                                    <tr>
                                        <td style="padding: 10px;">#<?php echo $order['id']; ?></td>
                                        <td style="padding: 10px;"><?php echo htmlspecialchars($order['full_name']); ?></td>
                                        <td style="padding: 10px;">Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                                        <td style="padding: 10px;">
                                            <span style="padding: 4px 12px; background: #e8f5e9; color: #4caf50; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo $order['status']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">Belum ada pesanan</p>
                <?php endif; ?>
            </div>

            <!-- Low Stock Products -->
            <div style="background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px;">
                <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span class="iconify" data-icon="mdi:alert" style="color: #f44336;"></span>
                    Produk Stok Rendah
                </h3>

                <?php if ($lowStockProducts->num_rows > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="border: none;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px;">Produk</th>
                                    <th style="padding: 10px;">Stok</th>
                                    <th style="padding: 10px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($product = $lowStockProducts->fetch_assoc()): ?>
                                    <tr>
                                        <td style="padding: 10px;"><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td style="padding: 10px;">
                                            <span style="padding: 4px 12px; background: #ffebee; color: #f44336; border-radius: 20px; font-size: 0.85em; font-weight: 600;">
                                                <?php echo $product['stock']; ?> pcs
                                            </span>
                                        </td>
                                        <td style="padding: 10px;">
                                            <a href="products.php" style="color: #667eea; text-decoration: none; font-weight: 600;">
                                                <span class="iconify" data-icon="mdi:pencil"></span>
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #999; padding: 20px;">Semua produk stok aman</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Additional Stats -->
        <div style="margin-top: 20px; background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px;">
            <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <span class="iconify" data-icon="mdi:chart-line" style="color: #667eea;"></span>
                Statistik Lainnya
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div style="text-align: center; padding: 20px; background: #f5f5f5; border-radius: 8px;">
                    <span class="iconify" data-icon="mdi:account-group" style="font-size: 40px; color: #667eea; margin-bottom: 10px;"></span>
                    <h4 style="font-size: 1.8em; margin-bottom: 5px;"><?php echo $totalCustomers; ?></h4>
                    <p style="color: #999; font-size: 0.9em;">Total Customer</p>
                </div>

                <div style="text-align: center; padding: 20px; background: #f5f5f5; border-radius: 8px;">
                    <span class="iconify" data-icon="mdi:shape" style="font-size: 40px; color: #4caf50; margin-bottom: 10px;"></span>
                    <?php $totalCat = $conn->query("SELECT COUNT(*) as count FROM categories")->fetch_assoc()['count']; ?>
                    <h4 style="font-size: 1.8em; margin-bottom: 5px;"><?php echo $totalCat; ?></h4>
                    <p style="color: #999; font-size: 0.9em;">Total Kategori</p>
                </div>

                <div style="text-align: center; padding: 20px; background: #f5f5f5; border-radius: 8px;">
                    <span class="iconify" data-icon="mdi:clock-outline" style="font-size: 40px; color: #ff9800; margin-bottom: 10px;"></span>
                    <?php $pending = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'Pending'")->fetch_assoc()['count']; ?>
                    <h4 style="font-size: 1.8em; margin-bottom: 5px;"><?php echo $pending; ?></h4>
                    <p style="color: #999; font-size: 0.9em;">Pesanan Pending</p>
                </div>
            </div>
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