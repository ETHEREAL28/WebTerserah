<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit;
}

require_once '../api/config.php';
$conn = getConnection();

$userId = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

// Get cart items
$cartQuery = "SELECT c.id as cart_id, c.quantity, p.* 
              FROM cart c 
              JOIN products p ON c.product_id = p.id 
              WHERE c.user_id = $userId";
$cartItems = $conn->query($cartQuery);

// Calculate total
$total = 0;
$items = [];
while ($item = $cartItems->fetch_assoc()) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
    $items[] = $item;
}

$cartCount = count($items);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - TERSERAHMART</title>
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
            <a href="home.php" class="nav-link">
                <span class="iconify" data-icon="mdi:home"></span>
                Beranda
            </a>
            <a href="cart.php" class="nav-link active">
                <span class="iconify" data-icon="mdi:cart"></span>
                Keranjang
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge" style="position: relative;"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            <a href="orders.php" class="nav-link">
                <span class="iconify" data-icon="mdi:clipboard-list"></span>
                Pesanan Saya
            </a>
            <a href="profile.php" class="nav-link">
                <span class="iconify" data-icon="mdi:account"></span>
                Profil
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($user['full_name']); ?></h4>
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
            <h1>Keranjang Belanja</h1>
            <p>Kelola produk yang akan Anda beli</p>
        </div>

        <?php if (count($items) > 0): ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
                <!-- Cart Items -->
                <div>
                    <?php foreach ($items as $item): ?>
                        <div style="background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; margin-bottom: 15px; display: flex; gap: 20px;">
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" style="width: 120px; height: 120px; object-fit: cover; border-radius: 8px;" onerror="this.src='https://via.placeholder.com/120'">
                            
                            <div style="flex: 1;">
                                <h3 style="margin-bottom: 8px;"><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p style="color: #999; font-size: 0.9em; margin-bottom: 10px;">Stok: <?php echo $item['stock']; ?></p>
                                <p style="color: #667eea; font-size: 1.3em; font-weight: 700; margin-bottom: 15px;">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <div style="display: flex; align-items: center; gap: 10px; border: 1px solid #e0e0e0; border-radius: 8px; padding: 5px;">
                                        <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>, <?php echo $item['stock']; ?>)" style="width: 32px; height: 32px; border: none; background: #f5f5f5; border-radius: 6px; cursor: pointer; font-size: 1.2em;">-</button>
                                        <span style="width: 40px; text-align: center; font-weight: 600;"><?php echo $item['quantity']; ?></span>
                                        <button onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>, <?php echo $item['stock']; ?>)" style="width: 32px; height: 32px; border: none; background: #f5f5f5; border-radius: 6px; cursor: pointer; font-size: 1.2em;">+</button>
                                    </div>
                                    
                                    <button onclick="removeItem(<?php echo $item['cart_id']; ?>)" style="padding: 8px 15px; background: #ffebee; color: #f44336; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                        <span class="iconify" data-icon="mdi:delete"></span>
                                        Hapus
                                    </button>
                                </div>
                            </div>
                            
                            <div style="text-align: right;">
                                <p style="color: #999; font-size: 0.9em; margin-bottom: 5px;">Subtotal</p>
                                <p style="font-size: 1.4em; font-weight: 700; color: #333;">Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div>
                    <div style="background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px; position: sticky; top: 20px;">
                        <h3 style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #e0e0e0;">Ringkasan Belanja</h3>
                        
                        <div style="margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666;">Total Item</span>
                                <span style="font-weight: 600;"><?php echo $cartCount; ?> produk</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                                <span style="color: #666;">Total Harga</span>
                                <span style="font-weight: 600;">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                        
                        <div style="padding-top: 20px; border-top: 2px solid #e0e0e0; margin-bottom: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 1.1em; font-weight: 600;">Total Pembayaran</span>
                                <span style="font-size: 1.5em; font-weight: 700; color: #667eea;">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
                            </div>
                        </div>
                        
                        <button onclick="checkout()" style="width: 100%; padding: 15px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 1.1em; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span class="iconify" data-icon="mdi:cart-check"></span>
                            Checkout
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px;">
                <span class="iconify" data-icon="mdi:cart-outline" style="font-size: 80px; color: #e0e0e0; margin-bottom: 20px;"></span>
                <h3 style="margin-bottom: 10px;">Keranjang Kosong</h3>
                <p style="color: #999; margin-bottom: 25px;">Belum ada produk di keranjang Anda</p>
                <a href="home.php" style="display: inline-flex; align-items: center; gap: 8px; padding: 12px 25px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">
                    <span class="iconify" data-icon="mdi:arrow-left"></span>
                    Mulai Belanja
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('hamburgerBtn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('visible');
            document.getElementById('mainContent').classList.toggle('expanded');
        });
        
        async function updateQuantity(cartId, newQuantity, stock) {
            if (newQuantity < 1) {
                if (!confirm('Hapus item dari keranjang?')) return;
                removeItem(cartId);
                return;
            }
            
            if (newQuantity > stock) {
                alert('Jumlah melebihi stok!');
                return;
            }
            
            const formData = new FormData();
            formData.append('cart_id', cartId);
            formData.append('quantity', newQuantity);
            
            try {
                const response = await fetch('../api/cart/update.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        }
        
        async function removeItem(cartId) {
            if (!confirm('Hapus item dari keranjang?')) return;
            
            const formData = new FormData();
            formData.append('cart_id', cartId);
            
            try {
                const response = await fetch('../api/cart/remove.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        }
        
        function checkout() {
            if (confirm('Lanjutkan ke pembayaran?')) {
                window.location.href = 'checkout.php';
            }
        }
        
        function logout() {
            if (confirm('Yakin ingin logout?')) {
                window.location.href = '../api/auth/logout.php';
            }
        }
    </script>
</body>
</html>
<?php closeConnection($conn); ?>