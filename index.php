<?php
$page_title = 'Beranda - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Get featured products
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.status = 'active' ORDER BY p.created_at DESC LIMIT 8");
    $stmt->execute();
    $featured_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get categories
    $stmt = $conn->prepare("SELECT * FROM categories ORDER BY name LIMIT 6");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get new products (last 30 days)
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.status = 'active' AND p.created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                           ORDER BY p.created_at DESC LIMIT 4");
    $stmt->execute();
    $new_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get best-selling products
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, SUM(oi.quantity) as total_sold
                           FROM products p
                           LEFT JOIN categories c ON p.category_id = c.id
                           LEFT JOIN order_items oi ON p.id = oi.product_id
                           WHERE p.status = 'active'
                           GROUP BY p.id
                           HAVING total_sold > 0
                           ORDER BY total_sold DESC, p.created_at DESC LIMIT 4");
    $stmt->execute();
    $best_selling_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recommended products (random selection)
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.status = 'active' AND p.stock > 0
                           ORDER BY RAND() LIMIT 4");
    $stmt->execute();
    $recommended_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $featured_products = [];
    $categories = [];
    $new_products = [];
    $best_selling_products = [];
    $recommended_products = [];
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/carousel.css">
</head>

<!-- Hero Section with Search and Categories -->
<section class="hero-section-tokopedia">
    <div class="container-fluid px-4">
        <div class="row">
            <!-- Left Side - Logo and Search -->
            <div class="col-lg-8">
                <div class="hero-content">
                    <!-- Logo and Welcome -->
                    <div class="text-center mb-4">
                        <h1 class="hero-title">Paṇi Marketplace</h1>
                        <p class="hero-subtitle">Temukan kebutuhanmu dengan harga terbaik</p>
                    </div>
                    
                    <!-- Search Bar -->
                    <div class="search-container mb-4">
                        <div class="search-box">
                            <div class="search-input-group">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" 
                                       class="search-input" 
                                       placeholder="Cari produk, merek, atau kategori..."
                                       id="hero-search">
                                <button class="search-btn" onclick="performSearch()">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Popular Searches -->
                    <div class="popular-searches">
                        <span class="popular-label">Populer:</span>
                        <a href="shop.php?search=baju" class="popular-tag">Baju</a>
                        <a href="shop.php?search=sepatu" class="popular-tag">Sepatu</a>
                        <a href="shop.php?search=elektronik" class="popular-tag">Elektronik</a>
                        <a href="shop.php?search=makanan" class="popular-tag">Makanan</a>
                        <a href="shop.php?search=kecantikan" class="popular-tag">Kecantikan</a>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Banner -->
            <div class="col-lg-4">
                <div class="hero-banner">
                    <img src="https://picsum.photos/seed/banner1/400/300.jpg" 
                         class="banner-image" 
                         alt="Promo Banner">
                    <div class="banner-overlay">
                        <h4>Flash Sale!</h4>
                        <p>Diskon hingga 70%</p>
                        <a href="shop.php?flash_sale=1" class="btn btn-light btn-sm">Lihat Semua</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Grid Section -->
<section class="categories-section">
    <div class="container">
        <div class="section-header">
            <h3 class="section-title">Kategori Pilihan</h3>
            <a href="categories.php" class="see-all-link">Lihat Semua</a>
        </div>
        
        <div class="categories-grid">
            <?php foreach ($categories as $category): ?>
                <div class="category-card" onclick="window.location.href='shop.php?category=<?php echo $category['id']; ?>'">
                    <div class="category-icon">
                        <?php 
                        $icon_map = [
                            'Elektronik' => 'fas fa-laptop',
                            'Fashion' => 'fas fa-tshirt',
                            'Makanan' => 'fas fa-utensils',
                            'Kesehatan' => 'fas fa-heartbeat',
                            'Olahraga' => 'fas fa-dumbbell',
                            'Rumah Tangga' => 'fas fa-home'
                        ];
                        $icon = $icon_map[$category['name']] ?? 'fas fa-box';
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    <div class="category-info">
                        <h5 class="category-name"><?php echo htmlspecialchars($category['name']); ?></h5>
                        <p class="category-count">+<?php echo rand(100, 999); ?> produk</p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- More Categories Card -->
            <div class="category-card category-more" onclick="window.location.href='categories.php'">
                <div class="category-icon">
                    <i class="fas fa-ellipsis-h"></i>
                </div>
                <div class="category-info">
                    <h5 class="category-name">Lihat Semua</h5>
                    <p class="category-count">Kategori Lainnya</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flash Sale Banner -->
<section class="flash-sale-banner">
    <div class="container">
        <div class="flash-content">
            <div class="flash-icon">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="flash-text">
                <h4>Flash Sale Hari Ini!</h4>
                <p>Diskon hingga 80% - Waktu Terbatas</p>
            </div>
            <div class="flash-timer" id="flash-timer">
                <span class="timer-unit">23</span>
                <span class="timer-separator">:</span>
                <span class="timer-unit">59</span>
                <span class="timer-separator">:</span>
                <span class="timer-unit">59</span>
            </div>
            <a href="shop.php?flash_sale=1" class="btn btn-danger">Belanja Sekarang</a>
        </div>
    </div>
</section>

<style>
/* Hero Section Styles */
.hero-section-tokopedia {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 40px 0;
    color: white;
}

.hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.hero-subtitle {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

/* Search Bar Styles */
.search-container {
    max-width: 600px;
    margin: 0 auto;
}

.search-box {
    background: white;
    border-radius: 50px;
    padding: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.search-input-group {
    display: flex;
    align-items: center;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 20px;
    color: #6c757d;
    font-size: 18px;
    z-index: 2;
}

.search-input {
    flex: 1;
    border: none;
    padding: 15px 20px 15px 50px;
    font-size: 16px;
    outline: none;
    border-radius: 50px;
}

.search-btn {
    background: #42b549;
    border: none;
    padding: 15px 25px;
    border-radius: 50px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: #35a042;
    transform: translateX(2px);
}

/* Popular Searches */
.popular-searches {
    text-align: center;
    margin-top: 20px;
}

.popular-label {
    color: rgba(255,255,255,0.8);
    margin-right: 15px;
    font-size: 14px;
}

.popular-tag {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    text-decoration: none;
    margin: 0 5px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.popular-tag:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

/* Hero Banner */
.hero-banner {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    height: 300px;
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    padding: 30px 20px 20px;
    text-align: center;
}

.banner-overlay h4 {
    color: white;
    font-weight: 700;
    margin-bottom: 5px;
}

.banner-overlay p {
    color: rgba(255,255,255,0.9);
    margin-bottom: 15px;
}

/* Categories Section */
.categories-section {
    padding: 60px 0;
    background: #f8f9fa;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.see-all-link {
    color: #42b549;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.see-all-link:hover {
    color: #35a042;
    transform: translateX(5px);
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    border-radius: 15px;
    padding: 25px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    border-color: #42b549;
}

.category-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}

.category-icon i {
    font-size: 24px;
    color: white;
}

.category-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.category-count {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

.category-more {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6;
}

.category-more .category-icon {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Flash Sale Banner */
.flash-sale-banner {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    padding: 40px 0;
    color: white;
}

.flash-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
}

.flash-icon {
    font-size: 3rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.flash-text h4 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.flash-text p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.flash-timer {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 2rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.timer-unit {
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 8px;
    min-width: 50px;
    text-align: center;
}

.timer-separator {
    font-size: 1.5rem;
    opacity: 0.7;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .flash-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .popular-searches {
        text-align: left;
        overflow-x: auto;
        white-space: nowrap;
    }
}

.hero-subtitle {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

/* Search Bar Styles */
.search-container {
    max-width: 600px;
    margin: 0 auto;
}

.search-box {
    background: white;
    border-radius: 50px;
    padding: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.search-input-group {
    display: flex;
    align-items: center;
    position: relative;
}

.search-icon {
    position: absolute;
    left: 20px;
    color: #6c757d;
    font-size: 18px;
    z-index: 2;
}

.search-input {
    flex: 1;
    border: none;
    padding: 15px 20px 15px 50px;
    font-size: 16px;
    outline: none;
    border-radius: 50px;
}

.search-btn {
    background: #42b549;
    border: none;
    padding: 15px 25px;
    border-radius: 50px;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-btn:hover {
    background: #35a042;
    transform: translateX(2px);
}

/* Popular Searches */
.popular-searches {
    text-align: center;
    margin-top: 20px;
}

.popular-label {
    color: rgba(255,255,255,0.8);
    margin-right: 15px;
    font-size: 14px;
}

.popular-tag {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 6px 15px;
    border-radius: 20px;
    text-decoration: none;
    margin: 0 5px;
    font-size: 13px;
    transition: all 0.3s ease;
}

.popular-tag:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

/* Hero Banner */
.hero-banner {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    height: 300px;
}

.banner-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.banner-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    padding: 30px 20px 20px;
    text-align: center;
}

.banner-overlay h4 {
    color: white;
    font-weight: 700;
    margin-bottom: 5px;
}

.banner-overlay p {
    color: rgba(255,255,255,0.9);
    margin-bottom: 15px;
}

/* Categories Section */
.categories-section {
    padding: 60px 0;
    background: #f8f9fa;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.section-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin: 0;
}

.see-all-link {
    color: #42b549;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.see-all-link:hover {
    color: #35a042;
    transform: translateX(5px);
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.category-card {
    background: white;
    border-radius: 15px;
    padding: 25px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    border: 1px solid #e9ecef;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    border-color: #42b549;
}

.category-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
}

.category-icon i {
    font-size: 24px;
    color: white;
}

.category-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.category-count {
    color: #6c757d;
    font-size: 0.9rem;
    margin: 0;
}

.category-more {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px dashed #dee2e6;
}

.category-more .category-icon {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Flash Sale Banner */
.flash-sale-banner {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    padding: 40px 0;
    color: white;
}

.flash-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    max-width: 1200px;
    margin: 0 auto;
}

.flash-icon {
    font-size: 3rem;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.flash-text h4 {
    font-size: 1.8rem;
    font-weight: 700;
    margin-bottom: 5px;
}

.flash-text p {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0;
}

.flash-timer {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 2rem;
    font-weight: 700;
    font-family: 'Courier New', monospace;
}

.timer-unit {
    background: rgba(255,255,255,0.2);
    padding: 8px 12px;
    border-radius: 8px;
    min-width: 50px;
    text-align: center;
}

.timer-separator {
    font-size: 1.5rem;
    opacity: 0.7;
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2rem;
    }
    
    .categories-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .flash-content {
        flex-direction: column;
        text-align: center;
        gap: 20px;
    }
    
    .popular-searches {
        text-align: left;
        overflow-x: auto;
        white-space: nowrap;
    }
}
</style>

<style>
/* Additional fixes for product images */
.product-image-container {
    position: relative;
    overflow: hidden;
    height: 200px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    min-height: 200px;
}

.product-image-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%, #f0f0f0),
                linear-gradient(45deg, #f0f0f0 25%, transparent 25%, transparent 75%, #f0f0f0 75%, #f0f0f0);
    background-size: 20px 20px;
    background-position: 0 0, 10px 10px;
    opacity: 0.3;
    z-index: 0;
}

.product-image {
    position: relative;
    z-index: 1;
}

.product-card-carousel:hover .product-image {
    transform: scale(1.05);
}

/* Ensure images load properly */
img {
    display: block;
    max-width: 100%;
    height: auto;
}
</style>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shipping-fast fa-3x text-primary"></i>
                    </div>
                    <h5>Pengiriman Gratis</h5>
                    <p class="text-muted">Gratis ongkir untuk pembelian di atas Rp 500.000</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-success"></i>
                    </div>
                    <h5>Pembayaran Aman</h5>
                    <p class="text-muted">100% proses pembayaran yang aman</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-undo fa-3x text-warning"></i>
                    </div>
                    <h5>Pengembalian Mudah</h5>
                    <p class="text-muted">Kebijakan pengembalian 30 hari</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-info"></i>
                    </div>
                    <h5>Dukungan 24/7</h5>
                    <p class="text-muted">Layanan pelanggan yang berdedikasi</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Belanja berdasarkan Kategori</h2>
            <p class="lead text-muted">Jelajahi berbagai macam produk kategori</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
            <div class="col-md-4 col-lg-2">
                <a href="shop.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="category-card">
                        <div class="category-icon">
                            <i class="fas fa-<?php echo getCategoryIcon($category['name']); ?>"></i>
                        </div>
                        <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Carousel -->
<section id="featured" class="product-carousel-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produk Unggulan</h2>
            <p class="section-subtitle">Lihat produk terbaru dan terbaik kami</p>
        </div>
        
        <div class="product-swiper featured-swiper">
            <div class="swiper-wrapper">
                <?php if (empty($featured_products)): ?>
                    <div class="swiper-slide">
                        <div class="product-carousel-empty">
                            <i class="fas fa-box-open"></i>
                            <h4>Tidak ada produk tersedia saat ini.</h4>
                            <p>Periksa kembali nanti untuk penawaran menarik!</p>
                            <a href="shop.php" class="btn btn-primary">Lihat Toko</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($featured_products as $product): ?>
                    <div class="swiper-slide">
                        <div class="product-card-carousel">
                            <div class="product-image-container">
                                <img src="https://picsum.photos/seed/product<?php echo $product['id']; ?>/300/300.jpg" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 200px; object-fit: cover; display: block;"
                                     onerror="this.src='https://via.placeholder.com/300x300/cccccc/666666?text=Product';">
                                <div class="product-badges">
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="badge bg-danger">Habis</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="wishlist-btn wishlist-toggle" 
                                            data-product-id="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-content">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="product-meta">
                                    <span class="product-price"><?php echo formatPriceIDR($product['price']); ?></span>
                                    <span class="product-stock">Stok: <?php echo $product['stock']; ?></span>
                                </div>
                                <div class="product-buttons">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-detail">Lihat Detail</a>
                                    <button class="btn-cart add-to-cart" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $product['stock'] <= 0 ? 'Habis' : 'Tambah ke Keranjang'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        
        <div class="text-center mt-4">
            <a href="shop.php" class="btn btn-primary btn-lg">Lihat Semua Produk</a>
        </div>
    </div>
</section>

<!-- New Products Carousel -->
<section class="product-carousel-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produk Baru</h2>
            <p class="section-subtitle">Produk terbaru yang ditambahkan ke toko kami</p>
        </div>
        
        <div class="product-swiper new-products-swiper">
            <div class="swiper-wrapper">
                <?php if (empty($new_products)): ?>
                    <div class="swiper-slide">
                        <div class="product-carousel-empty">
                            <i class="fas fa-box-open"></i>
                            <h4>Tidak ada produk baru saat ini.</h4>
                            <p>Periksa kembali nanti untuk produk terbaru!</p>
                            <a href="shop.php?sort=newest" class="btn btn-primary">Lihat Toko</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($new_products as $product): ?>
                    <div class="swiper-slide">
                        <div class="product-card-carousel">
                            <div class="product-image-container">
                                <img src="https://picsum.photos/seed/product<?php echo $product['id']; ?>/300/300.jpg" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 200px; object-fit: cover; display: block;"
                                     onerror="this.src='https://via.placeholder.com/300x300/cccccc/666666?text=Product';">
                                <div class="product-badges">
                                    <span class="badge bg-danger">Baru</span>
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="badge bg-dark">Habis</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="wishlist-btn wishlist-toggle" 
                                            data-product-id="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-content">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="product-meta">
                                    <span class="product-price"><?php echo formatPriceIDR($product['price']); ?></span>
                                    <span class="product-stock">Stok: <?php echo $product['stock']; ?></span>
                                </div>
                                <div class="product-buttons">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-detail">Lihat Detail</a>
                                    <button class="btn-cart add-to-cart" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $product['stock'] <= 0 ? 'Habis' : 'Tambah ke Keranjang'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        
        <div class="text-center mt-4">
            <a href="shop.php?sort=newest" class="btn btn-outline-primary btn-lg">Lihat Semua Produk Baru</a>
        </div>
    </div>
</section>

<!-- Best Selling Products Carousel -->
<section class="product-carousel-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produk Terlaris</h2>
            <p class="section-subtitle">Produk paling populer yang banyak dibeli pelanggan</p>
        </div>
        
        <div class="product-swiper best-selling-swiper">
            <div class="swiper-wrapper">
                <?php if (empty($best_selling_products)): ?>
                    <div class="swiper-slide">
                        <div class="product-carousel-empty">
                            <i class="fas fa-chart-line"></i>
                            <h4>Belum ada produk terlaris.</h4>
                            <p>Produk terlaris akan muncul setelah ada penjualan!</p>
                            <a href="shop.php?sort=popular" class="btn btn-primary">Lihat Toko</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($best_selling_products as $product): ?>
                    <div class="swiper-slide">
                        <div class="product-card-carousel">
                            <div class="product-image-container">
                                <img src="https://picsum.photos/seed/product<?php echo $product['id']; ?>/300/300.jpg" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 200px; object-fit: cover; display: block;"
                                     onerror="this.src='https://via.placeholder.com/300x300/cccccc/666666?text=Product';">
                                <div class="product-badges">
                                    <span class="badge bg-success">Terlaris</span>
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="badge bg-dark">Habis</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="wishlist-btn wishlist-toggle" 
                                            data-product-id="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-content">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="product-meta">
                                    <span class="product-price"><?php echo formatPriceIDR($product['price']); ?></span>
                                    <span class="product-stock">Terjual: <?php echo $product['total_sold']; ?></span>
                                </div>
                                <div class="product-buttons">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-detail">Lihat Detail</a>
                                    <button class="btn-cart add-to-cart" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $product['stock'] <= 0 ? 'Habis' : 'Tambah ke Keranjang'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        
        <div class="text-center mt-4">
            <a href="shop.php?sort=popular" class="btn btn-outline-primary btn-lg">Lihat Semua Produk Terlaris</a>
        </div>
    </div>
</section>

<!-- Recommended Products Carousel -->
<section class="product-carousel-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Produk Rekomendasi</h2>
            <p class="section-subtitle">Pilihan produk yang direkomendasikan untuk Anda</p>
        </div>
        
        <div class="product-swiper recommended-swiper">
            <div class="swiper-wrapper">
                <?php if (empty($recommended_products)): ?>
                    <div class="swiper-slide">
                        <div class="product-carousel-empty">
                            <i class="fas fa-star"></i>
                            <h4>Tidak ada produk rekomendasi saat ini.</h4>
                            <p>Periksa kembali nanti untuk rekomendasi produk!</p>
                            <a href="shop.php" class="btn btn-primary">Lihat Toko</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($recommended_products as $product): ?>
                    <div class="swiper-slide">
                        <div class="product-card-carousel">
                            <div class="product-image-container">
                                <img src="https://picsum.photos/seed/product<?php echo $product['id']; ?>/300/300.jpg" 
                                     class="product-image" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     style="width: 100%; height: 200px; object-fit: cover; display: block;"
                                     onerror="this.src='https://via.placeholder.com/300x300/cccccc/666666?text=Product';">
                                <div class="product-badges">
                                    <span class="badge bg-info">Rekomendasi</span>
                                    <?php if ($product['stock'] <= 0): ?>
                                        <span class="badge bg-dark">Habis</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="wishlist-btn wishlist-toggle" 
                                            data-product-id="<?php echo $product['id']; ?>">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="product-content">
                                <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                                <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <div class="product-meta">
                                    <span class="product-price"><?php echo formatPriceIDR($product['price']); ?></span>
                                    <span class="product-stock">Stok: <?php echo $product['stock']; ?></span>
                                </div>
                                <div class="product-buttons">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" 
                                       class="btn-detail">Lihat Detail</a>
                                    <button class="btn-cart add-to-cart" 
                                            data-product-id="<?php echo $product['id']; ?>"
                                            <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                        <?php echo $product['stock'] <= 0 ? 'Habis' : 'Tambah ke Keranjang'; ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <div class="swiper-pagination"></div>
        </div>
        
        <div class="text-center mt-4">
            <a href="shop.php" class="btn btn-outline-primary btn-lg">Lihat Semua Produk Rekomendasi</a>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-3">Berlangganan Newsletter Kami</h3>
                <p>Dapatkan pembaruan terbaru tentang produk baru dan penawaran eksklusif!</p>
            </div>
            <div class="col-md-6">
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Masukkan email Anda" required>
                    <button type="submit" class="btn btn-light">Berlangganan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Apa Kata Pelanggan Kami</h2>
            <p class="lead text-muted">Ulasan nyata dari pelanggan yang puas</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"Produk luar biasa dan layanan pelanggan yang sangat baik. Pasti akan berbelanja lagi!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40x40" class="rounded-circle me-2" alt="Customer">
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Pembelian Terverifikasi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"Pengiriman cepat dan produk berkualitas tinggi. Website juga mudah digunakan."</p>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40x40" class="rounded-circle me-2" alt="Customer">
                            <div>
                                <h6 class="mb-0">Mike Chen</h6>
                                <small class="text-muted">Pembelian Terverifikasi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="far fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"Variasi produk yang bagus dan harga kompetitif. Sangat direkomendasikan!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40x40" class="rounded-circle me-2" alt="Customer">
                            <div>
                                <h6 class="mb-0">Emily Davis</h6>
                                <small class="text-muted">Pembelian Terverifikasi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Helper function for category icons
function getCategoryIcon($category_name) {
    $icons = [
        'Electronics' => 'laptop',
        'Clothing' => 'tshirt',
        'Books' => 'book',
        'Home' => 'home',
        'Sports' => 'football-ball',
        'Toys' => 'gamepad',
        'Food' => 'utensils',
        'Beauty' => 'spa',
        'Health' => 'heart-pulse',
        'Automotive' => 'car',
        'Garden' => 'seedling',
        'Pet' => 'paw',
        'Baby' => 'baby',
        'Office' => 'briefcase',
        'Tools' => 'hammer',
        'Music' => 'music',
        'Art' => 'palette',
        'Photography' => 'camera',
        'Travel' => 'plane',
        'Jewelry' => 'gem',
        'Shoes' => 'shoe-prints',
        'Bags' => 'shopping-bag',
        'Watches' => 'clock',
        'Phones' => 'mobile-alt',
        'Computers' => 'desktop',
        'Gaming' => 'gamepad',
        'Fitness' => 'dumbbell',
        'Outdoor' => 'mountain',
        'Kitchen' => 'blender',
        'Furniture' => 'couch'
    ];
    
    return $icons[$category_name] ?? 'box';
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Featured Products Swiper
    const featuredSwiper = new Swiper('.featured-swiper', {
        slidesPerView: 1.5,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.featured-swiper .swiper-button-next',
            prevEl: '.featured-swiper .swiper-button-prev',
        },
        pagination: {
            el: '.featured-swiper .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 25,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 30,
            },
            1280: {
                slidesPerView: 5,
                spaceBetween: 30,
            }
        }
    });

    // Initialize New Products Swiper
    const newProductsSwiper = new Swiper('.new-products-swiper', {
        slidesPerView: 1.5,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.new-products-swiper .swiper-button-next',
            prevEl: '.new-products-swiper .swiper-button-prev',
        },
        pagination: {
            el: '.new-products-swiper .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 25,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 30,
            },
            1280: {
                slidesPerView: 5,
                spaceBetween: 30,
            }
        }
    });

    // Initialize Best Selling Products Swiper
    const bestSellingSwiper = new Swiper('.best-selling-swiper', {
        slidesPerView: 1.5,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 4500,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.best-selling-swiper .swiper-button-next',
            prevEl: '.best-selling-swiper .swiper-button-prev',
        },
        pagination: {
            el: '.best-selling-swiper .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 25,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 30,
            },
            1280: {
                slidesPerView: 5,
                spaceBetween: 30,
            }
        }
    });

    // Initialize Recommended Products Swiper
    const recommendedSwiper = new Swiper('.recommended-swiper', {
        slidesPerView: 1.5,
        spaceBetween: 20,
        loop: true,
        autoplay: {
            delay: 5500,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.recommended-swiper .swiper-button-next',
            prevEl: '.recommended-swiper .swiper-button-prev',
        },
        pagination: {
            el: '.recommended-swiper .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 20,
            },
            768: {
                slidesPerView: 3,
                spaceBetween: 25,
            },
            1024: {
                slidesPerView: 4,
                spaceBetween: 30,
            },
            1280: {
                slidesPerView: 5,
                spaceBetween: 30,
            }
        }
    });

    // Fix product detail links
    document.querySelectorAll('.btn-detail').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const productId = this.getAttribute('href').split('id=')[1];
            console.log('Navigating to product:', productId);
            window.location.href = 'product.php?id=' + productId;
        });
    });

    // Prevent any other click handlers on product cards
    document.querySelectorAll('.product-card-carousel').forEach(card => {
        card.addEventListener('click', function(e) {
            // Only handle clicks on buttons, not the entire card
            if (!e.target.closest('.btn-detail') && !e.target.closest('.btn-cart') && !e.target.closest('.wishlist-btn')) {
                // Find the detail button and click it
                const detailBtn = this.querySelector('.btn-detail');
                if (detailBtn) {
                    detailBtn.click();
                }
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
