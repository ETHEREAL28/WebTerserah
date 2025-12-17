<?php



session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


require_once '../api/config.php';
$conn = getConnection();




$userId = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userProfile = $result->fetch_assoc();


$cartCount = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = $userId")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - TERSERAHMART</title>
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.iconify.design/3/3.1.0/iconify.min.js"></script>
    <style>
        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            display: flex;
            align-items: center;
            gap: 30px;
            border: 1px solid #e0e0e0;
            max-width: 800px;
        }
        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            font-size: 40px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .profile-info h2 {
            margin-bottom: 5px;
            color: #333;
        }
        .profile-info p {
            color: #666;
            margin-bottom: 5px;
        }
        .info-group {
            margin-top: 20px;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .info-item label {
            display: block;
            color: #999;
            font-size: 0.9em;
            margin-bottom: 5px;
        }
        .info-item span {
            font-weight: 600;
            color: #333;
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
            <a href="home.php" class="nav-link">
                <span class="iconify" data-icon="mdi:home"></span>
                Beranda
            </a>
            <a href="cart.php" class="nav-link">
                <span class="iconify" data-icon="mdi:cart"></span>
                Keranjang
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="orders.php" class="nav-link">
                <span class="iconify" data-icon="mdi:clipboard-list"></span>
                Pesanan Saya
            </a>
            <a href="profile.php" class="nav-link active">
                <span class="iconify" data-icon="mdi:account"></span>
                Profil
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($userProfile['full_name'], 0, 1)); ?></div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($userProfile['full_name']); ?></h4>
                    <p>Customer</p>
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
            <h1>Profil Saya</h1>
            <p>Kelola informasi akun Anda</p>
        </div>

        <div class="profile-card">
            <div class="profile-avatar-large">
                <?php echo strtoupper(substr($userProfile['full_name'], 0, 1)); ?>
            </div>
            <div class="profile-info" style="flex: 1;">
                <h2><?php echo htmlspecialchars($userProfile['full_name']); ?></h2>
                <p>Member Customer</p>
                
                <div class="info-group">
                    <div class="info-item">
                        <label>Username</label>
                        <span><?php echo htmlspecialchars($userProfile['username']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <span><?php echo htmlspecialchars($userProfile['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Nomor Telepon</label>
                        <span><?php echo htmlspecialchars($userProfile['phone'] ?? '-'); ?></span>
                    </div>
                    <div class="info-item">
                        <label>Alamat</label>
                        <span><?php echo htmlspecialchars($userProfile['address'] ?? '-'); ?></span>
                    </div>
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