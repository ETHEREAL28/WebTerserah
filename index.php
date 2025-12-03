<?php
require_once 'api/config.php';

// Get products by category
$conn = getConnection();

// Get Categories
$sqlCategories = "SELECT * FROM categories ORDER BY name";
$resultCategories = $conn->query($sqlCategories);

// Get all Products with category name
$sqlProducts = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY c.name, p.name";
$resultProducts = $conn->query($sqlProducts);

// Get Recent Transactions
$sqlTransactions = "SELECT t.*, c.name as customer_name 
                    FROM transactions t 
                    LEFT JOIN customers c ON t.customer_id = c.id 
                    ORDER BY t.created_at DESC LIMIT 10";
$resultTransactions = $conn->query($sqlTransactions);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TERSERAHMART - Minimarket</title>
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
            <a href="#" class="nav-link active" data-page="home">BERANDA</a>
            <a href="#" class="nav-link" data-page="products">PRODUK</a>
            <a href="#" class="nav-link" data-page="transactions">TRANSAKSI</a>
            <a href="admin.php" class="nav-link">ADMIN</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header>
            <h1>TERSERAHMART</h1>
            <p class="subtitle">Minimarket Serba Ada</p>
        </header>

        <!-- Home Page -->
        <div id="home-page" class="page active">
            <!-- Stats Cards -->
            <div class="stats-container">
                <?php
                $totalProducts = $conn->query("SELECT COUNT(*) as total FROM products")->fetch_assoc()['total'];
                $totalTransactions = $conn->query("SELECT COUNT(*) as total FROM transactions")->fetch_assoc()['total'];
                $totalRevenue = $conn->query("SELECT SUM(total_amount) as total FROM transactions")->fetch_assoc()['total'] ?? 0;
                $lowStock = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock < 10")->fetch_assoc()['total'];
                ?>
                <div class="stat-card" data-aos="fade-up">
                    <div class="stat-icon">📦</div>
                    <div class="stat-info">
                        <h3><?php echo $totalProducts; ?></h3>
                        <p>Total Produk</p>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up">
                    <div class="stat-icon">🛒</div>
                    <div class="stat-info">
                        <h3><?php echo $totalTransactions; ?></h3>
                        <p>Transaksi</p>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></h3>
                        <p>Total Pendapatan</p>
                    </div>
                </div>
                <div class="stat-card" data-aos="fade-up">
                    <div class="stat-icon">⚠️</div>
                    <div class="stat-info">
                        <h3><?php echo $lowStock; ?></h3>
                        <p>Stok Rendah</p>
                    </div>
                </div>
            </div>

            <!-- Products by Category -->
            <?php
            $categoriesResult = $conn->query("SELECT * FROM categories ORDER BY name");
            while($category = $categoriesResult->fetch_assoc()):
                $categoryId = $category['id'];
                $categoryProducts = $conn->query("SELECT * FROM products WHERE category_id = $categoryId LIMIT 5");
                
                if ($categoryProducts->num_rows > 0):
            ?>
            <section class="product-section">
                <h2><?php echo strtoupper(htmlspecialchars($category['name'])); ?></h2>
                <div class="product-grid">
                    <?php while($product = $categoryProducts->fetch_assoc()): ?>
                        <div class="product-card" data-aos="fade-up">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image" 
                                 onerror="this.src='https://via.placeholder.com/300'">
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="product-barcode">Barcode: <?php echo htmlspecialchars($product['barcode']); ?></p>
                            </div>
                            <div class="product-info">
                                <div>
                                    <div class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                                    <div class="product-stock">Stok: <?php echo $product['stock']; ?></div>
                                </div>
                                <div class="btn-action">
                                    <button class="btn-icon" onclick="viewProduct(<?php echo $product['id']; ?>)" title="Lihat Detail">👁️</button>
                                    <button class="btn-icon" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>)" title="Tambah ke Keranjang">🛒</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php 
                endif;
            endwhile; 
            ?>
        </div>

        <!-- Products Page -->
        <div id="products-page" class="page">
            <h2>SEMUA PRODUK</h2>
            
            <!-- Filter by Category -->
            <div class="filter-container">
                <label>Filter Kategori:</label>
                <select id="category-filter" onchange="filterProducts()">
                    <option value="">Semua Kategori</option>
                    <?php
                    $categoriesResult->data_seek(0);
                    while($category = $categoriesResult->fetch_assoc()):
                    ?>
                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="product-grid" id="all-products-grid">
                <?php
                $resultProducts->data_seek(0);
                while($product = $resultProducts->fetch_assoc()):
                ?>
                    <div class="product-card" data-category="<?php echo $product['category_id']; ?>" data-aos="fade-up">
                        <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             class="product-image" 
                             onerror="this.src='https://via.placeholder.com/300'">
                        <div class="product-details">
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></p>
                            <p class="product-barcode">Barcode: <?php echo htmlspecialchars($product['barcode']); ?></p>
                        </div>
                        <div class="product-info">
                            <div>
                                <div class="product-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></div>
                                <div class="product-stock">Stok: <?php echo $product['stock']; ?></div>
                            </div>
                            <div class="btn-action">
                                <button class="btn-icon" onclick="viewProduct(<?php echo $product['id']; ?>)" title="Lihat Detail">👁️</button>
                                <button class="btn-icon" onclick="addToCart(<?php echo $product['id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>)" title="Tambah ke Keranjang">🛒</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Transactions Page -->
        <div id="transactions-page" class="page">
            <h2>RIWAYAT TRANSAKSI</h2>
            <div class="transactions-list">
                <?php if ($resultTransactions->num_rows > 0): ?>
                    <?php while($transaction = $resultTransactions->fetch_assoc()): ?>
                        <div class="transaction-card" data-aos="fade-up">
                            <div class="transaction-header">
                                <h3>Transaksi #<?php echo $transaction['id']; ?></h3>
                                <span class="transaction-status"><?php echo htmlspecialchars($transaction['status']); ?></span>
                            </div>
                            <div class="transaction-body">
                                <p><strong>Customer:</strong> <?php echo htmlspecialchars($transaction['customer_name'] ?? 'Guest'); ?></p>
                                <p><strong>Total:</strong> Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></p>
                                <p><strong>Metode:</strong> <?php echo htmlspecialchars($transaction['payment_method']); ?></p>
                                <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></p>
                            </div>
                            <button class="btn-detail" onclick="viewTransaction(<?php echo $transaction['id']; ?>)">Lihat Detail</button>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Belum ada transaksi.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>
<?php closeConnection($conn); ?>