<?php
$page_title = 'Toko Penjual - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'includes/discount_functions.php';
require_once 'config/database.php';

// Get seller ID from URL
$seller_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($seller_id <= 0) {
    header('Location: shop.php');
    exit();
}

try {
    // Get seller information
    $stmt = getDBConnection()->prepare("
        SELECT u.*, COUNT(p.id) as total_products,
               AVG(r.rating) as seller_rating,
               COUNT(DISTINCT p.id) as product_count
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id AND p.status = 'active'
        LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
        WHERE u.id = ? AND u.role = 'seller'
        GROUP BY u.id
    ");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        header('Location: shop.php');
        exit();
    }
    
    // Get seller's products with pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $per_page = 12;
    $offset = ($page - 1) * $per_page;
    
    // Get filter parameters
    $search = isset($_GET['search']) ? cleanInput($_GET['search']) : '';
    $sort = isset($_GET['sort']) ? cleanInput($_GET['sort']) : 'newest';
    $category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
    
    // Build query
    $where_conditions = ["p.seller_id = ? AND p.status = 'active'"];
    $params = [$seller_id];
    
    if (!empty($search)) {
        $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    if ($category > 0) {
        $where_conditions[] = "p.category_id = ?";
        $params[] = $category;
    }
    
    $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    
    // Sort options
    $sort_options = [
        'newest' => 'p.created_at DESC',
        'price_low' => 'p.price ASC',
        'price_high' => 'p.price DESC',
        'name_asc' => 'p.name ASC',
        'name_desc' => 'p.name DESC',
        'popular' => 'total_sold DESC'
    ];
    
    $order_by = $sort_options[$sort] ?? 'p.created_at DESC';
    
    // Get products
    $stmt = getDBConnection()->prepare("
        SELECT p.*, c.name as category_name,
               (SELECT SUM(oi.quantity) FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.product_id = p.id AND o.status != 'cancelled') as total_sold
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $where_clause
        ORDER BY $order_by
        LIMIT ? OFFSET ?
    ");
    
    $stmt->execute($params);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get total products count for pagination
    $stmt = getDBConnection()->prepare("
        SELECT COUNT(*) as total
        FROM products p
        $where_clause
    ");
    $stmt->execute($params);
    $total_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_products = $total_result['total'];
    $total_pages = ceil($total_products / $per_page);
    
    // Get seller's categories
    $stmt = getDBConnection()->prepare("
        SELECT DISTINCT c.id, c.name, COUNT(p.id) as product_count
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.seller_id = ? AND p.status = 'active'
        WHERE c.id IN (SELECT category_id FROM products WHERE seller_id = ? AND status = 'active')
        GROUP BY c.id, c.name
        ORDER BY product_count DESC
    ");
    $stmt->execute([$seller_id, $seller_id]);
    $seller_categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error loading seller shop: " . $e->getMessage());
    header('Location: shop.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($seller['name']); ?> - Paṇi Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .store-header {
            background: linear-gradient(135deg, #ee4d2d 0%, #f79e1b 100%);
        }
        .store-avatar {
            width: 80px;
            height: 80px;
            border: 4px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }
        .category-pill {
            transition: all 0.3s ease;
        }
        .category-pill:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(237, 100, 100, 0.15);
        }
        .stat-card {
            background: linear-gradient(135deg, #f8fafc 0%, #e0e7ff 100%);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
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
                        <a href="categories.php" class="text-gray-600 hover:text-orange-500">Kategori</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Cari produk..." class="border rounded-lg px-4 py-2 pr-10 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                    </div>
                    <button class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                        <i class="fas fa-shopping-cart mr-2"></i>Keranjang
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Store Header -->
    <div class="store-header text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex items-center space-x-6">
                <!-- Store Avatar -->
                <div class="text-center">
                    <img src="https://picsum.photos/seed/<?php echo $seller['id']; ?>/150/150.jpg" 
                         alt="<?php echo htmlspecialchars($seller['name']); ?>"
                         class="store-avatar rounded-full">
                    <div class="mt-3">
                        <span class="inline-block bg-yellow-400 text-orange-900 px-3 py-1 rounded-full text-sm font-bold">
                            <i class="fas fa-check-circle mr-1"></i>
                            Official Store
                        </span>
                    </div>
                </div>
                
                <!-- Store Info -->
                <div class="flex-1 text-center md:text-left">
                    <h1 class="text-3xl font-bold mb-2"><?php echo htmlspecialchars($seller['name']); ?></h1>
                    <p class="text-orange-100 mb-4"><?php echo htmlspecialchars($seller['description'] ?? 'Menjual berbagai produk berkualitas dengan harga terbaik'); ?></p>
                    
                    <!-- Store Statistics -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white"><?php echo $seller['total_products'] ?? 0; ?></div>
                            <div class="text-sm text-orange-100">Produk</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white"><?php echo $seller['product_count'] ?? 0; ?></div>
                            <div class="text-sm text-orange-100">Terjual</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white"><?php echo number_format($seller['seller_rating'] ?? 0, 1); ?></div>
                            <div class="text-sm text-orange-100">Rating</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-white">95%</div>
                            <div class="text-sm text-orange-100">Respon Chat</div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="text-center">
                    <button onclick="visitSellerShop()" class="chat-button w-full">
                        <i class="fas fa-comments mr-2"></i>
                        Chat Penjual
                    </button>
                    <button onclick="window.location.href='product_pani.php?id=<?php echo $product['id']; ?>'" class="bg-white text-orange-500 px-4 py-2 rounded-lg hover:bg-orange-100 transition-colors">
                            <i class="fas fa-store mr-2"></i>
                            Lihat Produk
                        </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Store Content -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Left Sidebar - Categories -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-4">Kategori Toko</h3>
                    <div class="space-y-2">
                        <a href="?id=<?php echo $seller_id; ?>" class="category-pill block w-full text-left px-3 py-2 rounded-lg bg-orange-500 text-white hover:bg-orange-600">
                            <i class="fas fa-th mr-2"></i>
                            Semua Produk
                        </a>
                        <?php foreach ($seller_categories as $cat): ?>
                            <a href="?id=<?php echo $seller_id; ?>&category=<?php echo $cat['id']; ?>" 
                               class="category-pill block w-full text-left px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200">
                                <i class="fas fa-tag mr-2"></i>
                                <?php echo htmlspecialchars($cat['name']); ?>
                                <span class="text-xs text-gray-500">(<?php echo $cat['product_count']; ?>)</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Store Stats -->
                <div class="mt-4 bg-white rounded-lg p-4 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-4">Statistik Toko</h3>
                    <div class="space-y-3">
                        <div class="stat-card p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Produk Aktif</span>
                                <span class="font-bold text-gray-900"><?php echo $seller['total_products'] ?? 0; ?></span>
                            </div>
                        </div>
                        <div class="stat-card p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Total Terjual</span>
                                <span class="font-bold text-gray-900"><?php echo $seller['product_count'] ?? 0; ?></span>
                            </div>
                        </div>
                        <div class="stat-card p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Rating Rata-rata</span>
                                <span class="font-bold text-gray-900"><?php echo number_format($seller['seller_rating'] ?? 0, 1); ?> ⭐</span>
                            </div>
                        </div>
                        <div class="stat-card p-3 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-600">Bergabung Sejak</span>
                                <span class="font-bold text-gray-900"><?php echo date('M Y', strtotime($seller['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content - Products Grid -->
            <div class="lg:col-span-3">
                <!-- Search and Filter Bar -->
                <div class="bg-white rounded-lg p-4 shadow-sm mb-6">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" 
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   placeholder="Cari produk di toko ini..." 
                                   class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                   onchange="window.location.href='?id=<?php echo $seller_id; ?>&search=' + encodeURIComponent(this.value)">
                        </div>
                        <select class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                onchange="window.location.href='?id=<?php echo $seller_id; ?>&category=' + this.value + (search ? '&search=<?php echo urlencode($search); ?>' : '')">
                            <option value="0">Semua Kategori</option>
                            <?php foreach ($seller_categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <select class="border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"
                                onchange="window.location.href='?id=<?php echo $seller_id; ?>&sort=' + this.value + (search ? '&search=<?php echo urlencode($search); ?>' : '') + ($category ? '&category=<?php echo $category; ?>' : '')">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                            <option value="popular" <?php echo $sort == 'popular' ? 'selected' : ''; ?>>Terlaris</option>
                        </select>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <?php if (empty($products)): ?>
                    <div class="bg-white rounded-lg p-8 shadow-sm text-center">
                        <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-600 mb-2">Tidak ada produk di toko ini</h3>
                        <p class="text-gray-500">Penjual belum menambahkan produk apa pun</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($products as $product): ?>
                            <div class="product-card bg-white rounded-lg shadow-sm overflow-hidden cursor-pointer" 
                                 onclick="window.location.href='product_pani.php?id=<?php echo $product['id']; ?>'">
                                <!-- Product Image -->
                                <div class="relative">
                                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/300/300.jpg" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-full h-48 object-cover">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <div class="absolute top-2 left-2 bg-red-500 text-white px-2 py-1 rounded text-xs font-bold">
                                            Habis
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Product Info -->
                                <div class="p-4">
                                    <h4 class="font-semibold text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="text-gray-600 text-xs mb-2 line-clamp-2"><?php echo htmlspecialchars($product['description']); ?></p>
                                    
                                    <!-- Price and Rating -->
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                                                <span class="text-gray-500 line-through text-xs">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                                <span class="text-orange-500 font-bold text-sm">Rp <?php echo number_format($product['discount_price'], 0, ',', '.'); ?></span>
                                            <?php else: ?>
                                                <span class="text-orange-500 font-bold text-sm">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center text-xs text-gray-600">
                                            <i class="fas fa-star text-yellow-400 mr-1"></i>
                                            <?php echo number_format($product['seller_rating'] ?? 0, 1); ?>
                                            <span class="ml-1">(<?php echo $product['total_reviews'] ?? 0; ?>)</span>
                                        </div>
                                    </div>
                                    
                                    <!-- Sales Count -->
                                    <div class="text-xs text-gray-600">
                                        <i class="fas fa-shopping-bag mr-1"></i>
                                        <?php echo $product['total_sold'] ?? 0; ?> terjual
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center mt-8 space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="?id=<?php echo $seller_id; ?>&page=<?php echo $page - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>" 
                               class="px-3 py-2 bg-white border rounded-lg hover:bg-gray-50">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <?php if ($i == $page): ?>
                                <span class="px-3 py-2 bg-orange-500 text-white rounded-lg"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?id=<?php echo $seller_id; ?>&page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>" 
                                   class="px-3 py-2 bg-white border rounded-lg hover:bg-gray-50">
                                    <?php echo $i; ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                            <a href="?id=<?php echo $seller_id; ?>&page=<?php echo $page + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $category ? '&category=' . $category : ''; ?><?php echo $sort ? '&sort=' . $sort : ''; ?>" 
                                   class="px-3 py-2 bg-white border rounded-lg hover:bg-gray-50">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">Chat dengan <?php echo htmlspecialchars($seller['name']); ?></h3>
                    <button onclick="closeChat()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-4">
                    <div class="space-y-3" style="height: 300px; overflow-y: auto;">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-comments text-2xl mb-2"></i>
                            <p>Mulai percakapan dengan penjual</p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <input type="text" id="chatInput" placeholder="Ketik pesan Anda..." 
                               class="flex-1 border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <button onclick="sendChatMessage()" class="bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openChat() {
            document.getElementById('chatModal').classList.remove('hidden');
        }
        
        function closeChat() {
            document.getElementById('chatModal').classList.add('hidden');
        }
        
        function sendChatMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (message) {
                console.log('Sending message to seller:', message);
                input.value = '';
                
                // Simulate seller response
                setTimeout(() => {
                    showNotification('Penjual telah menerima pesan Anda!');
                }, 1000);
            }
        }
        
        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg bg-green-500 text-white';
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    ${message}
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        function visitSellerShop() {
    window.location.href = 'seller-shop.php?id=<?php echo $seller_id; ?>';
}

        // Close modal when clicking outside
        document.getElementById('chatModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeChat();
            }
        });
    </script>
</body>
</html>
