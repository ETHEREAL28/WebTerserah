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

function fetchCategories($conn) {
    $sql = "SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count FROM categories c ORDER BY c.name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$categories = fetchCategories($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - TERSERAHMART</title>
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
            <a href="categories.php" class="nav-link active">
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
            <div>
                <h1>Kelola Kategori</h1>
                <p>Daftar kategori produk tersedia</p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nama Kategori</th>
                        <th>Jumlah Produk</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $category): ?>
                        <tr>
                            <td>#<?php echo $category['id']; ?></td>
                            <td>
                                <span style="font-weight: 600; font-size: 1.1em; color: #667eea;">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </span>
                            </td>
                            <td>
                                <span style="padding: 5px 15px; background: #e3f2fd; color: #1976d2; border-radius: 20px; font-weight: 600;">
                                    <?php echo $category['product_count']; ?> Produk
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999; padding: 30px;">
                                Belum ada kategori
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