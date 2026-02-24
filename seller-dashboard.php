<?php
session_start();
$page_title = 'Seller Dashboard - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    header('Location: login.php');
    exit();
}

$seller_id = $_SESSION['user_id'];

try {
    // Get seller information
    $stmt = getDBConnection()->prepare("
        SELECT u.*, COUNT(p.id) as total_products,
               SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END) as active_products,
               COUNT(DISTINCT p.category_id) as total_categories,
               AVG(r.rating) as avg_rating,
               COUNT(r.id) as total_reviews
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id
        LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
        WHERE u.id = ? AND u.role = 'seller'
        GROUP BY u.id
    ");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent products
    $stmt = getDBConnection()->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT SUM(oi.quantity) FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.product_id = p.id AND o.status != 'cancelled') as total_sold
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.seller_id = ?
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seller_id]);
    $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get sales statistics
    $stmt = getDBConnection()->prepare("
        SELECT COUNT(DISTINCT o.id) as total_orders,
               SUM(oi.quantity) as total_items_sold,
               SUM(oi.price * oi.quantity) as total_revenue,
               AVG(o.total_amount) as avg_order_value
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ? AND o.status != 'cancelled'
    ");
    $stmt->execute([$seller_id]);
    $sales_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders
    $stmt = getDBConnection()->prepare("
        SELECT o.*, COUNT(oi.id) as item_count,
               SUM(oi.quantity) as total_quantity,
               GROUP_CONCAT(p.name SEPARATOR ', ') as product_names
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE p.seller_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$seller_id]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories for product form
    $stmt = getDBConnection()->prepare("SELECT * FROM categories ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error loading seller dashboard: " . $e->getMessage());
    $seller = [];
    $recent_products = [];
    $sales_stats = [];
    $recent_orders = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - Paṇi Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold text-orange-500">Paṇi</h1>
                    <div class="hidden md:flex space-x-6">
                        <a href="index.php" class="text-gray-600 hover:text-orange-500">Beranda</a>
                        <a href="shop.php" class="text-gray-600 hover:text-orange-500">Produk</a>
                        <a href="seller-dashboard.php" class="text-orange-500 font-semibold">Dashboard</a>
                        <a href="seller-shop.php?id=<?php echo $seller_id; ?>" class="text-gray-600 hover:text-orange-500">Toko Saya</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Selamat datang, <?php echo htmlspecialchars($seller['name']); ?>!</span>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Produk</p>
                        <p class="text-3xl font-bold"><?php echo $seller['total_products'] ?? 0; ?></p>
                    </div>
                    <i class="fas fa-box text-3xl text-white/50"></i>
                </div>
            </div>
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Produk Aktif</p>
                        <p class="text-3xl font-bold"><?php echo $seller['active_products'] ?? 0; ?></p>
                    </div>
                    <i class="fas fa-check-circle text-3xl text-white/50"></i>
                </div>
            </div>
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Penjualan</p>
                        <p class="text-3xl font-bold"><?php echo $sales_stats['total_items_sold'] ?? 0; ?></p>
                    </div>
                    <i class="fas fa-shopping-cart text-3xl text-white/50"></i>
                </div>
            </div>
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Pendapatan</p>
                        <p class="text-3xl font-bold">Rp <?php echo number_format($sales_stats['total_revenue'] ?? 0, 0, ',', '.'); ?></p>
                    </div>
                    <i class="fas fa-money-bill-wave text-3xl text-white/50"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Add Product Form -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-plus-circle mr-2 text-orange-500"></i>
                        Tambah Produk
                    </h3>
                    <form id="addProductForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                            <input type="text" name="name" required
                                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select name="category_id" required
                                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga</label>
                            <input type="number" name="price" required min="0" step="0.01"
                                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok</label>
                            <input type="number" name="stock" required min="0"
                                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea name="description" rows="3" required
                                      class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-orange-500 text-white py-2 rounded-lg hover:bg-orange-600 transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Produk
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            <i class="fas fa-box mr-2 text-orange-500"></i>
                            Produk Terbaru
                        </h3>
                        <a href="seller-products.php" class="text-orange-500 hover:text-orange-600">
                            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($recent_products as $product): ?>
                            <div class="product-card border rounded-lg p-4">
                                <div class="flex items-start space-x-4">
                                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/80/80.jpg" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-20 h-20 rounded-lg object-cover">
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['category_name']); ?></p>
                                        <p class="text-orange-500 font-bold">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-sm text-gray-600">
                                                <i class="fas fa-shopping-bag mr-1"></i>
                                                <?php echo $product['total_sold'] ?? 0; ?> terjual
                                            </span>
                                            <div class="space-x-2">
                                                <button onclick="editProduct(<?php echo $product['id']; ?>)" 
                                                        class="text-blue-500 hover:text-blue-600">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button onclick="deleteProduct(<?php echo $product['id']; ?>)" 
                                                        class="text-red-500 hover:text-red-600">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mt-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-shopping-cart mr-2 text-orange-500"></i>
                        Pesanan Terbaru
                    </h3>
                    <a href="seller-orders.php" class="text-orange-500 hover:text-orange-600">
                        Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="space-y-4">
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-card border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">Order #<?php echo $order['id']; ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $order['item_count']; ?> item • 
                                        Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                                           <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($order['status'] === 'processing' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-gray-100 text-gray-800'); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <div id="messageContainer" class="fixed top-4 right-4 z-50"></div>

    <script>
        // Add Product Form
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('seller_id', <?php echo $seller_id; ?>);
            
            fetch('api/add-product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('Produk berhasil ditambahkan!', 'success');
                    this.reset();
                    setTimeout(() => window.location.reload(), 2000);
                } else {
                    showMessage(data.message || 'Gagal menambahkan produk', 'error');
                }
            })
            .catch(error => {
                showMessage('Terjadi kesalahan', 'error');
                console.error('Error:', error);
            });
        });
        
        function showMessage(message, type = 'success') {
            const container = document.getElementById('messageContainer');
            const messageDiv = document.createElement('div');
            messageDiv.className = `p-4 rounded-lg shadow-lg mb-2 ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            messageDiv.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    ${message}
                </div>
            `;
            container.appendChild(messageDiv);
            
            setTimeout(() => {
                messageDiv.remove();
            }, 3000);
        }
        
        function editProduct(productId) {
            window.location.href = `edit-product.php?id=${productId}`;
        }
        
        function deleteProduct(productId) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                fetch('api/delete-product.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        product_id: productId,
                        seller_id: <?php echo $seller_id; ?>
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage('Produk berhasil dihapus!', 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        showMessage(data.message || 'Gagal menghapus produk', 'error');
                    }
                })
                .catch(error => {
                    showMessage('Terjadi kesalahan', 'error');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>
