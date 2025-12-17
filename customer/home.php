<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header('Location: ../index.php');
    exit;
}

require_once '../api/config.php';
$conn = getConnection();


$userId = $_SESSION['user_id'];
$userQuery = $conn->query("SELECT * FROM users WHERE id = $userId");
$user = $userQuery->fetch_assoc();


$cartCount = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = $userId")->fetch_assoc()['count'];


$categories = $conn->query("SELECT * FROM categories ORDER BY name");


$products = $conn->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.name");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - TERSERAHMART</title>
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
            <a href="home.php" class="nav-link active">
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
            <h1>Selamat Datang, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
            <p>Temukan produk kebutuhan Anda di TERSERAHMART</p>
        </div>


        <div class="filter-section" style="margin-bottom: 25px;">
            <select id="categoryFilter" style="padding: 10px 15px; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 1em; cursor: pointer;">
                <option value="">Semua Kategori</option>
                <?php while($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                <?php endwhile; ?>
            </select>
        </div>


        <div class="products-grid" id="productsGrid">
            <?php while($product = $products->fetch_assoc()): ?>
                <div class="product-card" data-category="<?php echo $product['category_id']; ?>" onclick="showProductDetail(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image" onerror="this.src='https://via.placeholder.com/300'">
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                        <p class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                        <p class="product-stock">
                            <span class="iconify" data-icon="mdi:package-variant"></span>
                            Stok: <?php echo $product['stock']; ?>
                        </p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>


    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalProductName">Detail Produk</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                    <div>
                        <img id="modalProductImage" style="width: 100%; border-radius: 12px;" src="">
                    </div>
                    <div>
                        <p style="color: #999; margin-bottom: 10px;" id="modalProductCategory"></p>
                        <h3 style="font-size: 1.8em; margin-bottom: 15px; color: #667eea;" id="modalProductPrice"></h3>
                        <p style="margin-bottom: 15px; color: #666;"><strong>Stok:</strong> <span id="modalProductStock"></span></p>
                        <p style="margin-bottom: 15px; color: #666;"><strong>Barcode:</strong> <span id="modalProductBarcode"></span></p>
                        <p style="margin-bottom: 25px; color: #666; line-height: 1.6;" id="modalProductDescription"></p>
                        
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <label style="font-weight: 600;">Jumlah:</label>
                            <input type="number" id="modalQuantity" value="1" min="1" style="width: 80px; padding: 10px; border: 2px solid #e0e0e0; border-radius: 6px; text-align: center; font-size: 1em;">
                        </div>
                        
                        <button class="btn btn-primary" style="width: 100%;" onclick="addToCart()">
                            <span class="iconify" data-icon="mdi:cart-plus"></span>
                            Tambah ke Keranjang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentProduct = null;
        

        document.getElementById('hamburgerBtn').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('visible');
            mainContent.classList.toggle('expanded');
        });
        

        document.getElementById('categoryFilter').addEventListener('change', function() {
            const categoryId = this.value;
            const products = document.querySelectorAll('.product-card');
            
            products.forEach(product => {
                if (categoryId === '' || product.getAttribute('data-category') === categoryId) {
                    product.style.display = 'block';
                } else {
                    product.style.display = 'none';
                }
            });
        });
        

        function showProductDetail(product) {
            currentProduct = product;
            document.getElementById('modalProductName').textContent = product.name;
            document.getElementById('modalProductImage').src = product.image;
            document.getElementById('modalProductCategory').textContent = product.category_name;
            document.getElementById('modalProductPrice').textContent = 'Rp ' + parseInt(product.price).toLocaleString('id-ID');
            document.getElementById('modalProductStock').textContent = product.stock;
            document.getElementById('modalProductBarcode').textContent = product.barcode;
            document.getElementById('modalProductDescription').textContent = product.description || 'Tidak ada deskripsi';
            document.getElementById('modalQuantity').value = 1;
            document.getElementById('modalQuantity').max = product.stock;
            
            document.getElementById('productModal').classList.add('active');
        }
        

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }
        

        async function addToCart() {
            const quantity = parseInt(document.getElementById('modalQuantity').value);
            
            if (quantity < 1) {
                alert('Jumlah minimal 1!');
                return;
            }
            
            if (quantity > currentProduct.stock) {
                alert('Stok tidak mencukupi!');
                return;
            }
            
            const formData = new FormData();
            formData.append('product_id', currentProduct.id);
            formData.append('quantity', quantity);
            
            try {
                const response = await fetch('../api/cart/add.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                    closeModal();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        }
        

        function logout() {
            if (confirm('Yakin ingin logout?')) {
                window.location.href = '../api/auth/logout.php';
            }
        }
        

        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php closeConnection($conn); ?>