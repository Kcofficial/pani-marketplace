<?php
$page_title = 'Detail Produk - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'includes/discount_functions.php';
require_once 'includes/review_functions.php';
require_once 'config/database.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: shop.php');
    exit();
}

try {
    // Get product with seller information
    $stmt = getDBConnection()->prepare("
        SELECT p.*, u.name as seller_name, u.email as seller_email, u.phone as seller_phone, 
               u.address as seller_address, u.avatar as seller_avatar,
               c.name as category_name
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.id = ? AND p.status = 'active'
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: shop.php');
        exit();
    }
    
    // Get product images
    $product_images = [$product['image']]; // Add more images if available
    
    // Get sales count
    $stmt = getDBConnection()->prepare("
        SELECT SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = ? AND o.status != 'cancelled'
    ");
    $stmt->execute([$product_id]);
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_sold = $sales_data['total_sold'] ?? 0;
    
    // Get reviews
    $stmt = getDBConnection()->prepare("
        SELECT rating, review_text, created_at
        FROM reviews
        WHERE product_id = ? AND status = 'approved'
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$product_id]);
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate average rating
    $avg_rating = 0;
    $total_reviews = count($reviews);
    if ($total_reviews > 0) {
        $total_rating = array_sum(array_column($reviews, 'rating'));
        $avg_rating = $total_rating / $total_reviews;
    }
    
    // Get related products
    $stmt = getDBConnection()->prepare("
        SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.category_id = ? AND p.id != ? AND p.status = 'active'
        ORDER BY RAND() 
        LIMIT 8
    ");
    $stmt->execute([$product['category_id'], $product_id]);
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error loading product: " . $e->getMessage());
    header('Location: shop.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Paṇi Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-image-main {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
        .thumbnail-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            border: 2px solid transparent;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .thumbnail-image:hover {
            border-color: #ee4d2d;
            transform: scale(1.05);
        }
        .thumbnail-image.active {
            border-color: #ee4d2d;
            border-width: 3px;
        }
        .star-rating {
            color: #fbbf24;
        }
        .star-empty {
            color: #e5e7eb;
        }
        .btn-add-cart {
            background: white;
            color: #ee4d2d;
            border: 2px solid #ee4d2d;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-add-cart:hover {
            background: #fff5f5;
            transform: translateY(-2px);
        }
        .btn-buy-now {
            background: #ee4d2d;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-buy-now:hover {
            background: #d63820;
            transform: translateY(-2px);
        }
        .btn-buy-now:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .price-box {
            background: linear-gradient(135deg, #fff5f5 0%, #ffe8e6 100%);
            border: 1px solid #ffe8e6;
            border-radius: 8px;
            padding: 20px;
        }
        .store-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
        }
        .chat-button {
            background: #00a86b;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .chat-button:hover {
            background: #0085b3;
        }
        .spec-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .spec-item:last-child {
            border-bottom: none;
        }
        .review-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 12px;
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

    <!-- Breadcrumb -->
    <div class="container mx-auto px-4 py-3">
        <nav class="flex text-sm text-gray-600">
            <a href="index.php" class="hover:text-orange-500">Beranda</a>
            <span class="mx-2">/</span>
            <a href="shop.php" class="hover:text-orange-500">Produk</a>
            <span class="mx-2">/</span>
            <a href="shop.php?category=<?php echo $product['category_id']; ?>" class="hover:text-orange-500"><?php echo htmlspecialchars($product['category_name']); ?></a>
            <span class="mx-2">/</span>
            <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
        </nav>
    </div>

    <!-- Main Product Content -->
    <div class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Left Column - Image Gallery -->
            <div class="space-y-4">
                <!-- Main Image -->
                <div class="bg-white rounded-lg p-4 shadow-sm">
                    <img id="main-image" 
                         src="https://picsum.photos/seed/<?php echo $product['id']; ?>/600/600.jpg" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         class="product-image-main">
                </div>
                
                <!-- Thumbnail Images -->
                <div class="flex space-x-2 overflow-x-auto pb-2">
                    <?php for ($i = 1; $i <= 4; $i++): ?>
                        <img src="https://picsum.photos/seed/<?php echo $product['id'] . '_' . $i; ?>/80/80.jpg" 
                             alt="Thumbnail <?php echo $i; ?>"
                             class="thumbnail-image <?php echo $i === 1 ? 'active' : ''; ?>"
                             onclick="changeMainImage(this)">
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Right Column - Product Information -->
            <div class="space-y-4">
                <!-- Product Title and Rating -->
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <h1 class="text-2xl font-bold text-gray-900 mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <!-- Rating and Reviews -->
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="flex items-center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $avg_rating ? 'star-rating' : 'star-empty'; ?>"></i>
                            <?php endfor; ?>
                            <span class="ml-2 text-gray-600"><?php echo number_format($avg_rating, 1); ?></span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium"><?php echo $total_reviews; ?></span> ulasan
                        </div>
                        <div class="text-sm text-gray-600">
                            <span class="font-medium"><?php echo $total_sold; ?></span> terjual
                        </div>
                    </div>
                    
                    <!-- Price -->
                    <div class="price-box">
                        <?php if ($product['discount_price'] && $product['discount_price'] < $product['price']): ?>
                            <div class="flex items-center space-x-3">
                                <span class="text-gray-500 line-through text-lg">
                                    Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                                </span>
                                <span class="bg-red-500 text-white px-2 py-1 rounded text-sm font-bold">
                                    <?php 
                                    $discount_percentage = round((($product['price'] - $product['discount_price']) / $product['price']) * 100);
                                    echo $discount_percentage . '%';
                                    ?>
                                </span>
                            </div>
                            <div class="text-3xl font-bold text-orange-500">
                                Rp <?php echo number_format($product['discount_price'], 0, ',', '.'); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-3xl font-bold text-orange-500">
                                Rp <?php echo number_format($product['price'], 0, ',', '.'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Shipping Details -->
                    <div class="border-t pt-4">
                        <h3 class="font-semibold text-gray-900 mb-3">Detail Pengiriman</h3>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Biaya Pengiriman:</span>
                                <span class="font-medium">Rp <?php echo number_format(15000, 0, ',', '.'); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Estimasi Tiba:</span>
                                <span class="font-medium">2-3 hari</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Garansi:</span>
                                <span class="font-medium">7 hari pengembalian</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button onclick="addToCart()" 
                            class="btn-add-cart flex-1">
                        <i class="fas fa-shopping-cart mr-2"></i>
                        Tambah ke Keranjang
                    </button>
                    <button onclick="buyNow()" 
                            class="btn-buy-now flex-1"
                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-bolt mr-2"></i>
                        <?php echo $product['stock'] <= 0 ? 'Stok Habis' : 'Beli Sekarang'; ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Store Information -->
        <div class="mt-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex items-start space-x-4">
                    <!-- Seller Avatar -->
                    <div class="text-center">
                        <img src="https://picsum.photos/seed/<?php echo $product['seller_id']; ?>/100/100.jpg" 
                             alt="<?php echo htmlspecialchars($product['seller_name']); ?>"
                             class="w-20 h-20 rounded-full border-2 border-gray-200">
                        <div class="mt-2">
                            <span class="inline-block bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                                Official Store
                            </span>
                        </div>
                    </div>
                    
                    <!-- Store Info -->
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($product['seller_name']); ?></h3>
                        <div class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            <?php echo htmlspecialchars($product['seller_address'] ?? 'Jakarta Selatan, Indonesia'); ?>
                        </div>
                        
                        <!-- Store Statistics -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">95%</div>
                                <div class="text-xs text-gray-600">Chat Respon</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600"><?php echo rand(100, 1000); ?></div>
                                <div class="text-xs text-gray-600">Produk Terjual</div>
                            </div>
                        </div>
                        
                        <!-- Chat Button -->
                        <button onclick="openChat()" class="chat-button w-full">
                            <i class="fas fa-comments mr-2"></i>
                            Chat Penjual
                        </button>
                        <button onclick="window.location.href='seller-profile.php?id=<?php echo $product['seller_id']; ?>'" class="bg-white text-orange-500 px-4 py-2 rounded-lg hover:bg-orange-100 transition-colors">
                            <i class="fas fa-store mr-2"></i>
                            Kunjungi Toko
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Specifications -->
        <div class="mt-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Spesifikasi Produk</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="spec-item">
                        <span class="text-gray-600">Berat</span>
                        <span class="font-medium"><?php echo $product['weight'] ?? '500 gram'; ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="text-gray-600">Kondisi</span>
                        <span class="font-medium">Baru</span>
                    </div>
                    <div class="spec-item">
                        <span class="text-gray-600">Kategori</span>
                        <span class="font-medium"><?php echo htmlspecialchars($product['category_name']); ?></span>
                    </div>
                    <div class="spec-item">
                        <span class="text-gray-600">Stok</span>
                        <span class="font-medium <?php echo $product['stock'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo $product['stock']; ?> tersedia
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Product Ratings -->
        <div class="mt-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Rating Produk</h3>
                <div class="space-y-3">
                    <?php if (empty($reviews)): ?>
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-star text-4xl mb-2"></i>
                            <p>Belum ada ulasan untuk produk ini</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-card">
                                <div class="flex items-start space-x-3">
                                    <img src="https://picsum.photos/seed/user<?php echo $review['id']; ?>/40/40.jpg" 
                                         alt="User" 
                                         class="w-10 h-10 rounded-full">
                                    <div class="flex-1">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <div class="flex">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star text-sm <?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="text-sm text-gray-600"><?php echo date('d M Y', strtotime($review['created_at'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-gray-700 mt-2"><?php echo htmlspecialchars($review['review_text']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        <div class="mt-8">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Produk Terkait</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="group cursor-pointer" onclick="window.location.href='product_shopee.php?id=<?php echo $related['id']; ?>'">
                            <div class="bg-white rounded-lg border hover:shadow-lg transition-all duration-300">
                                <img src="https://picsum.photos/seed/<?php echo $related['id']; ?>/200/200.jpg" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>"
                                     class="w-full h-48 object-cover rounded-t-lg">
                                <div class="p-3">
                                    <h4 class="font-medium text-gray-900 text-sm mb-1"><?php echo htmlspecialchars($related['name']); ?></h4>
                                    <p class="text-orange-500 font-bold">Rp <?php echo number_format($related['price'], 0, ',', '.'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Modal -->
    <div id="chatModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">Chat dengan <?php echo htmlspecialchars($product['seller_name']); ?></h3>
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
        function changeMainImage(thumbnail) {
            const mainImage = document.getElementById('main-image');
            const newSrc = thumbnail.src.replace('/80/80', '/600/600');
            mainImage.src = newSrc;
            
            // Update active thumbnail
            document.querySelectorAll('.thumbnail-image').forEach(img => {
                img.classList.remove('active');
            });
            thumbnail.classList.add('active');
        }
        
        function addToCart() {
            const quantity = 1; // You can add quantity selector later
            const productData = {
                id: <?php echo $product_id; ?>,
                name: '<?php echo addslashes($product['name']); ?>',
                price: <?php echo $product['discount_price'] ?? $product['price']; ?>,
                quantity: quantity
            };
            
            console.log('Adding to cart:', productData);
            
            // Show success notification
            showNotification('Produk berhasil ditambahkan ke keranjang!');
        }
        
        function buyNow() {
            if (<?php echo $product['stock']; ?> <= 0) {
                showNotification('Maaf, produk ini sedang habis!', 'error');
                return;
            }
            
            addToCart();
            setTimeout(() => {
                window.location.href = 'checkout.php';
            }, 1000);
        }
        
        function openChat() {
            document.getElementById('chatModal').classList.remove('hidden');
        }
        
        function visitSellerShop() {
            const sellerId = <?php echo $product['seller_id'] ?? 1; ?>;
            console.log('Seller ID:', sellerId);
            window.location.href = 'seller-shop.php?id=' + sellerId;
        }
        
        function closeChat() {
            document.getElementById('chatModal').classList.add('hidden');
        }
        
        function sendChatMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (message) {
                console.log('Sending message:', message);
                input.value = '';
                
                // Simulate seller response
                setTimeout(() => {
                    showNotification('Penjual telah menerima pesan Anda!');
                }, 1000);
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                    ${message}
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
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
