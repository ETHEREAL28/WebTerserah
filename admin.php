<?php
require_once 'api/config.php';

$conn = getConnection();

// Get all products with category
$sqlProducts = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC";
$resultProducts = $conn->query($sqlProducts);

// Get categories
$sqlCategories = "SELECT * FROM categories ORDER BY name";
$resultCategories = $conn->query($sqlCategories);

// Get customers
$sqlCustomers = "SELECT * FROM customers ORDER BY name";
$resultCustomers = $conn->query($sqlCustomers);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - TERSERAHMART</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav class="sidebar-nav">
            <a href="index.php" class="nav-link">BERANDA</a>
            <a href="#" class="nav-link active" data-page="products">KELOLA PRODUK</a>
            <a href="#" class="nav-link" data-page="categories">KELOLA KATEGORI</a>
            <a href="#" class="nav-link" data-page="customers">KELOLA CUSTOMER</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>TERSERAHMART</h1>
            <p class="subtitle">Admin Panel</p>
        </header>

        <!-- Kelola Produk -->
        <div id="products-page" class="page active">
            <h2>KELOLA PRODUK</h2>
            
            <div class="admin-actions">
                <button class="btn-add" id="btn-add-product">+ Tambah Produk Baru</button>
            </div>

            <!-- Form Tambah/Edit Produk -->
            <div class="product-form-container" id="product-form-container" style="display: none;">
                <h3 id="form-title">Tambah Produk Baru</h3>
                <form id="product-form">
                    <input type="hidden" id="product-id" name="id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Produk: *</label>
                            <input type="text" id="product-name" name="name" required>
                        </div>

                        <div class="form-group">
                            <label>Barcode: *</label>
                            <input type="text" id="product-barcode" name="barcode" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Kategori: *</label>
                            <select id="product-category" name="category_id" required>
                                <option value="">Pilih Kategori</option>
                                <?php
                                $resultCategories->data_seek(0);
                                while($category = $resultCategories->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Harga: *</label>
                            <input type="number" id="product-price" name="price" required min="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Stok: *</label>
                            <input type="number" id="product-stock" name="stock" required min="0">
                        </div>

                        <div class="form-group">
                            <label>URL Gambar: *</label>
                            <input type="text" id="product-image" name="image" required placeholder="https://...">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi:</label>
                        <textarea id="product-description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">Simpan</button>
                        <button type="button" class="btn-cancel" id="btn-cancel">Batal</button>
                    </div>
                </form>
            </div>

            <!-- Daftar Produk -->
            <div class="admin-product-list">
                <h3>Daftar Semua Produk</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama</th>
                            <th>Barcode</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultProducts->num_rows > 0): ?>
                            <?php while($product = $resultProducts->fetch_assoc()): ?>
                                <tr data-aos="fade-up">
                                    <td><?php echo $product['id']; ?></td>
                                    <td>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             onerror="this.src='https://via.placeholder.com/60'">
                                    </td>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                    <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                    <td><?php echo $product['stock']; ?></td>
                                    <td>
                                        <button class="btn-edit" onclick='editProduct(<?php echo json_encode($product); ?>)'>Edit</button>
                                        <button class="btn-delete" onclick="deleteProduct(<?php echo $product['id']; ?>)">Hapus</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">Belum ada produk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kelola Kategori -->
        <div id="categories-page" class="page">
            <h2>KELOLA KATEGORI</h2>
            
            <div class="admin-actions">
                <button class="btn-add" id="btn-add-category">+ Tambah Kategori Baru</button>
            </div>

            <!-- Form Kategori -->
            <div class="product-form-container" id="category-form-container" style="display: none;">
                <h3 id="category-form-title">Tambah Kategori Baru</h3>
                <form id="category-form">
                    <input type="hidden" id="category-id" name="id">
                    
                    <div class="form-group">
                        <label>Nama Kategori: *</label>
                        <input type="text" id="category-name" name="name" required>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">Simpan</button>
                        <button type="button" class="btn-cancel" id="btn-cancel-category">Batal</button>
                    </div>
                </form>
            </div>

            <!-- Daftar Kategori -->
            <div class="admin-product-list">
                <h3>Daftar Kategori</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $resultCategories->data_seek(0);
                        if ($resultCategories->num_rows > 0):
                            while($category = $resultCategories->fetch_assoc()):
                        ?>
                            <tr data-aos="fade-up">
                                <td><?php echo $category['id']; ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td>
                                    <button class="btn-edit" onclick='editCategory(<?php echo json_encode($category); ?>)'>Edit</button>
                                    <button class="btn-delete" onclick="deleteCategory(<?php echo $category['id']; ?>)">Hapus</button>
                                </td>
                            </tr>
                        <?php 
                            endwhile;
                        else: 
                        ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">Belum ada kategori.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Kelola Customer -->
        <div id="customers-page" class="page">
            <h2>KELOLA CUSTOMER</h2>
            
            <div class="admin-actions">
                <button class="btn-add" id="btn-add-customer">+ Tambah Customer Baru</button>
            </div>

            <!-- Form Customer -->
            <div class="product-form-container" id="customer-form-container" style="display: none;">
                <h3 id="customer-form-title">Tambah Customer Baru</h3>
                <form id="customer-form">
                    <input type="hidden" id="customer-id" name="id">
                    
                    <div class="form-group">
                        <label>Nama Customer: *</label>
                        <input type="text" id="customer-name" name="name" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>No. Telepon:</label>
                            <input type="text" id="customer-phone" name="phone">
                        </div>

                        <div class="form-group">
                            <label>Alamat:</label>
                            <textarea id="customer-address" name="address" rows="2"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-save">Simpan</button>
                        <button type="button" class="btn-cancel" id="btn-cancel-customer">Batal</button>
                    </div>
                </form>
            </div>

            <!-- Daftar Customer -->
            <div class="admin-product-list">
                <h3>Daftar Customer</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultCustomers->num_rows > 0): ?>
                            <?php while($customer = $resultCustomers->fetch_assoc()): ?>
                                <tr data-aos="fade-up">
                                    <td><?php echo $customer['id']; ?></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['address'] ?? '-'); ?></td>
                                    <td>
                                        <button class="btn-edit" onclick='editCustomer(<?php echo json_encode($customer); ?>)'>Edit</button>
                                        <button class="btn-delete" onclick="deleteCustomer(<?php echo $customer['id']; ?>)">Hapus</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">Belum ada customer.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
<?php closeConnection($conn); ?>