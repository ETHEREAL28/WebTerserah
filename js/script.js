// Initialize
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    initAnimations();
});

// Setup Event Listeners
function setupEventListeners() {
    // Hamburger Menu
    const hamburger = document.getElementById('hamburger');
    const sidebar = document.getElementById('sidebar');
    
    if (hamburger) {
        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('active');
        });
    }
    
    // Navigation Links
    const navLinks = document.querySelectorAll('.nav-link[data-page]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.page').forEach(page => {
                page.classList.remove('active');
            });
            
            const pageName = this.getAttribute('data-page');
            const targetPage = document.getElementById(pageName + '-page');
            if (targetPage) {
                targetPage.classList.add('active');
                animatePageTransition(targetPage);
            }
            
            if (window.innerWidth <= 768) {
                sidebar.classList.remove('active');
            }
        });
    });
    
    // Product Form
    const btnAddProduct = document.getElementById('btn-add-product');
    const btnCancel = document.getElementById('btn-cancel');
    const productForm = document.getElementById('product-form');
    
    if (btnAddProduct) {
        btnAddProduct.addEventListener('click', showAddProductForm);
    }
    
    if (btnCancel) {
        btnCancel.addEventListener('click', hideForm);
    }
    
    if (productForm) {
        productForm.addEventListener('submit', handleProductSubmit);
    }
    
    // Category Form
    const btnAddCategory = document.getElementById('btn-add-category');
    const btnCancelCategory = document.getElementById('btn-cancel-category');
    const categoryForm = document.getElementById('category-form');
    
    if (btnAddCategory) {
        btnAddCategory.addEventListener('click', showAddCategoryForm);
    }
    
    if (btnCancelCategory) {
        btnCancelCategory.addEventListener('click', hideCategoryForm);
    }
    
    if (categoryForm) {
        categoryForm.addEventListener('submit', handleCategorySubmit);
    }
    
    // Customer Form
    const btnAddCustomer = document.getElementById('btn-add-customer');
    const btnCancelCustomer = document.getElementById('btn-cancel-customer');
    const customerForm = document.getElementById('customer-form');
    
    if (btnAddCustomer) {
        btnAddCustomer.addEventListener('click', showAddCustomerForm);
    }
    
    if (btnCancelCustomer) {
        btnCancelCustomer.addEventListener('click', hideCustomerForm);
    }
    
    if (customerForm) {
        customerForm.addEventListener('submit', handleCustomerSubmit);
    }
}

// Initialize Animations
function initAnimations() {
    const elements = document.querySelectorAll('[data-aos="fade-up"]');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '0';
                    entry.target.style.transform = 'translateY(30px)';
                    entry.target.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, 50);
                }, index * 100);
                
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    elements.forEach(el => observer.observe(el));
}

// Animate Page Transition
function animatePageTransition(page) {
    const elements = page.querySelectorAll('[data-aos]');
    elements.forEach((el, index) => {
        setTimeout(() => {
            el.style.animation = 'none';
            setTimeout(() => {
                el.style.animation = '';
            }, 10);
        }, index * 50);
    });
}

// Filter Products by Category
function filterProducts() {
    const categoryFilter = document.getElementById('category-filter');
    const selectedCategory = categoryFilter.value;
    const products = document.querySelectorAll('#all-products-grid .product-card');
    
    products.forEach(product => {
        const productCategory = product.getAttribute('data-category');
        
        if (selectedCategory === '' || productCategory === selectedCategory) {
            product.style.display = 'block';
            product.style.animation = 'fadeIn 0.5s ease';
        } else {
            product.style.display = 'none';
        }
    });
}

// ===== PRODUCT CRUD =====

// Show Add Product Form
function showAddProductForm() {
    document.getElementById('form-title').textContent = 'Tambah Produk Baru';
    document.getElementById('product-form').reset();
    document.getElementById('product-id').value = '';
    showForm('product-form-container');
}

// Edit Product
function editProduct(product) {
    document.getElementById('form-title').textContent = 'Edit Produk';
    document.getElementById('product-id').value = product.id;
    document.getElementById('product-name').value = product.name;
    document.getElementById('product-barcode').value = product.barcode;
    document.getElementById('product-category').value = product.category_id;
    document.getElementById('product-price').value = product.price;
    document.getElementById('product-stock').value = product.stock;
    document.getElementById('product-image').value = product.image;
    document.getElementById('product-description').value = product.description || '';
    showForm('product-form-container');
}

// Handle Product Submit
async function handleProductSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const apiUrl = id ? 'api/products/update.php' : 'api/products/create.php';
    
    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            hideForm();
            setTimeout(() => fadeOutAndReload(), 1000);
        } else {
            showNotification('Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menyimpan produk!', 'error');
    }
}

// Delete Product
async function deleteProduct(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api/products/delete.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => fadeOutAndReload(), 1000);
        } else {
            showNotification('Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan saat menghapus produk!', 'error');
    }
}

// ===== CATEGORY CRUD =====

// Show Add Category Form
function showAddCategoryForm() {
    document.getElementById('category-form-title').textContent = 'Tambah Kategori Baru';
    document.getElementById('category-form').reset();
    document.getElementById('category-id').value = '';
    showForm('category-form-container');
}

// Edit Category
function editCategory(category) {
    document.getElementById('category-form-title').textContent = 'Edit Kategori';
    document.getElementById('category-id').value = category.id;
    document.getElementById('category-name').value = category.name;
    showForm('category-form-container');
}

// Handle Category Submit
async function handleCategorySubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const apiUrl = id ? 'api/categories/update.php' : 'api/categories/create.php';
    
    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            hideCategoryForm();
            setTimeout(() => fadeOutAndReload(), 1000);
        } else {
            showNotification('Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan!', 'error');
    }
}

// Delete Category
async function deleteCategory(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus kategori ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api/categories/delete.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => fadeOutAndReload(), 1000);
        } else {
            showNotification('Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan!', 'error');
    }
}

// ===== CUSTOMER CRUD =====

// Show Add Customer Form
function showAddCustomerForm() {
    document.getElementById('customer-form-title').textContent = 'Tambah Customer Baru';
    document.getElementById('customer-form').reset();
    document.getElementById('customer-id').value = '';
    showForm('customer-form-container');
}

// Edit Customer
function editCustomer(customer) {
    document.getElementById('customer-form-title').textContent = 'Edit Customer';
    document.getElementById('customer-id').value = customer.id;
    document.getElementById('customer-name').value = customer.name;
    document.getElementById('customer-phone').value = customer.phone || '';
    document.getElementById('customer-address').value = customer.address || '';
    showForm('customer-form-container');
}

// Handle Customer Submit
async function handleCustomerSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const id = formData.get('id');
    const apiUrl = id ? 'api/customers/update.php' : 'api/customers/create.php';
    
    try {
        const response = await fetch(apiUrl, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            hideCustomerForm();
            setTimeout(() => fadeOutAndReload(), 1000);
        } else {
            showNotification('Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan!', 'error');
    }
}

// Delete Customer
async function deleteCustomer(id) {
    if (!confirm('Apakah Anda yakin ingin menghapus customer ini?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', id);
    
    try {
        const response = await fetch('api/customers/delete.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message, 'success');
            setTimeout(() => fadeOutAndReload(), 1000);
        } else {
            showNotification('Error: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan!', 'error');
    }
}

// ===== UTILITY FUNCTIONS =====

// Show Form with Animation
function showForm(containerId) {
    const container = document.getElementById(containerId);
    container.style.display = 'block';
    container.scrollIntoView({ behavior: 'smooth', block: 'start' });
    
    container.style.opacity = '0';
    setTimeout(() => {
        container.style.transition = 'opacity 0.3s ease';
        container.style.opacity = '1';
    }, 10);
}

// Hide Forms
function hideForm() {
    hideFormWithAnimation('product-form-container');
}

function hideCategoryForm() {
    hideFormWithAnimation('category-form-container');
}

function hideCustomerForm() {
    hideFormWithAnimation('customer-form-container');
}

function hideFormWithAnimation(containerId) {
    const container = document.getElementById(containerId);
    container.style.opacity = '0';
    setTimeout(() => {
        container.style.display = 'none';
    }, 300);
}

// View Product Detail
function viewProduct(id) {
    showNotification('Detail produk akan ditampilkan (fitur dalam pengembangan)', 'success');
}

// Add to Cart
function addToCart(productId, productName, price) {
    showNotification(`${productName} ditambahkan ke keranjang!`, 'success');
}

// View Transaction
function viewTransaction(id) {
    showNotification('Detail transaksi akan ditampilkan (fitur dalam pengembangan)', 'success');
}

// Show Notification
function showNotification(message, type = 'success') {
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 25px;
        background: ${type === 'success' ? 'linear-gradient(90deg, #11998e 0%, #38ef7d 100%)' : 'linear-gradient(90deg, #eb3349 0%, #f45c43 100%)'};
        color: white;
        border-radius: 8px;
        font-weight: bold;
        z-index: 10000;
        opacity: 0;
        transform: translateY(-20px);
        transition: all 0.3s ease;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 10);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Fade out and reload
function fadeOutAndReload() {
    document.body.style.transition = 'opacity 0.3s ease';
    document.body.style.opacity = '0';
    setTimeout(() => location.reload(), 300);
}

// Add fadeIn animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);