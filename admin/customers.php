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

function fetchCustomers($conn) {
    // Note: Assuming role='customer' to differentiate from admins
    $sql = "SELECT id, full_name, username, email, phone, role FROM users WHERE role = 'customer' ORDER BY full_name";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$customers = fetchCustomers($conn);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Customer - TERSERAHMART</title>
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
        tr:hover {
            background: #fcfcfc;
        }
        .user-avatar-small {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
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
            <a href="categories.php" class="nav-link">
                <span class="iconify" data-icon="mdi:shape"></span>
                Kategori
            </a>
            <a href="orders.php" class="nav-link">
                <span class="iconify" data-icon="mdi:clipboard-list"></span>
                Pesanan
            </a>
            <a href="customers.php" class="nav-link active">
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
                <h1>Kelola Customer</h1>
                <p>Data pelanggan terdaftar</p>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($customers) > 0): ?>
                        <?php foreach ($customers as $customer): ?>
                        <tr>
                            <td>#<?php echo $customer['id']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <div class="user-avatar-small">
                                        <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                                    </div>
                                    <span style="font-weight: 600; color: #333;">
                                        <?php echo htmlspecialchars($customer['full_name']); ?>
                                    </span>
                                </div>
                            </td>
                            <td style="color: #666;"><?php echo htmlspecialchars($customer['username']); ?></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($customer['email']); ?>" style="color: #667eea; text-decoration: none;">
                                    <?php echo htmlspecialchars($customer['email']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999; padding: 30px;">
                                Belum ada customer terdaftar
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