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

function fetchProducts($conn) {
    $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

$products = fetchProducts($conn);
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - TERSERAHMART</title>
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
        tr:hover {
            background: #fcfcfc;
        }
        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            margin: 0 2px;
        }
        .btn-edit { background: #ff9800; }
        .btn-delete { background: #f44336; }
        .product-img-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
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
            <a href="products.php" class="nav-link active">
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
            <div>
                <h1>Kelola Produk</h1>
                <p>Tambah, edit, dan hapus data produk</p>
            </div>
            <button class="btn btn-primary" onclick="openModal()">
                <span class="iconify" data-icon="mdi:plus"></span>
                Tambah Produk
            </button>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th style="width: 70px;">Gambar</th>
                        <th>Nama Produk</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Barcode</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td>#<?php echo $product['id']; ?></td>
                        <td>
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" class="product-img-thumb" onerror="this.src='https://via.placeholder.com/50'">
                        </td>
                        <td>
                            <div style="font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div style="font-size: 0.85em; color: #999; max-width: 200px; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($product['description']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></td>
                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                        <td>
                            <?php if ($product['stock'] < 10): ?>
                                <span style="color: #f44336; font-weight: 600;"><?php echo $product['stock']; ?></span>
                            <?php else: ?>
                                <?php echo $product['stock']; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                        <td style="text-align: right;">
                            <button class="btn-action btn-edit" onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                <span class="iconify" data-icon="mdi:pencil"></span>
                            </button>
                            <button class="btn-action btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                <span class="iconify" data-icon="mdi:delete"></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form -->
    <div class="modal" id="productModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Tambah Produk</h2>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="productForm">
                    <input type="hidden" name="id" id="productId">
                    
                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="name" id="productName" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Kategori</label>
                            <select name="category_id" id="productCategory" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Barcode</label>
                            <input type="text" name="barcode" id="productBarcode" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="form-group">
                            <label>Harga (Rp)</label>
                            <input type="number" name="price" id="productPrice" required>
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stock" id="productStock" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>URL Gambar</label>
                        <input type="url" name="image" id="productImage" required placeholder="https://example.com/image.jpg">
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" id="productDescription" rows="3" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <span class="iconify" data-icon="mdi:content-save"></span>
                        Simpan Produk
                    </button>
                </form>
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

        function openModal() {
            document.getElementById('productForm').reset();
            document.getElementById('productId').value = '';
            document.getElementById('modalTitle').textContent = 'Tambah Produk';
            document.getElementById('productModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('productModal').classList.remove('active');
        }

        function editProduct(product) {
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name;
            document.getElementById('productCategory').value = product.category_id;
            document.getElementById('productBarcode').value = product.barcode;
            document.getElementById('productPrice').value = product.price;
            document.getElementById('productStock').value = product.stock;
            document.getElementById('productImage').value = product.image;
            document.getElementById('productDescription').value = product.description;
            
            document.getElementById('modalTitle').textContent = 'Edit Produk';
            document.getElementById('productModal').classList.add('active');
        }

        async function deleteProduct(id) {
            if (!confirm('Yakin ingin menghapus produk ini?')) return;

            const formData = new FormData();
            formData.append('id', id);

            try {
                const response = await fetch('../api/products/delete.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        }

        document.getElementById('productForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const id = document.getElementById('productId').value;
            const url = id ? '../api/products/update.php' : '../api/products/create.php';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message);
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        });

        // Close modal when clicking outside
        document.getElementById('productModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
<?php closeConnection($conn); ?>