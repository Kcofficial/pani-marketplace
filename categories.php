<?php
$page_title = 'Kategori - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Get all categories with type information
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT c.*, ct.name as type_name, ct.icon as type_icon 
        FROM categories c
        LEFT JOIN category_types ct ON c.category_type_id = ct.id
        ORDER BY ct.name, c.name
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get category types
    $stmt = $conn->prepare("SELECT * FROM category_types WHERE is_active = TRUE ORDER BY name");
    $stmt->execute();
    $category_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get product counts per category
    $stmt = $conn->prepare("
        SELECT category_id, COUNT(*) as product_count 
        FROM products 
        WHERE status = 'active' 
        GROUP BY category_id
    ");
    $stmt->execute();
    $product_counts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR, PDO::FETCH_COLUMN, PDO::FETCH_COLUMN);
    
} catch(PDOException $e) {
    $categories = [];
    $category_types = [];
    $product_counts = [];
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Categories Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Kategori Produk</h1>
                <p class="lead mb-4">Jelajahi berbagai kategori produk berkualitas dari UMKM terpercaya. Temukan produk yang sesuai dengan kebutuhan Anda dengan harga terbaik.</p>
                <div class="d-flex gap-3">
                    <a href="#all-categories" class="btn btn-light btn-lg px-4">Lihat Semua Kategori</a>
                    <a href="shop.php" class="btn btn-outline-light btn-lg px-4">Mulai Belanja</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-th-large fa-5x mb-4"></i>
                    <h3 class="mb-3">10+ Kategori</h3>
                    <p class="mb-0">Produk berkualitas dari berbagai industri</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Types Overview -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Jenis Kategori</h2>
            <p class="lead text-muted">Kategori produk kami dirancang untuk memenuhi berbagai kebutuhan Anda</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($category_types as $type): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body p-4 text-center">
                            <div class="text-primary mb-3">
                                <i class="<?php echo $type['icon']; ?> fa-3x"></i>
                            </div>
                            <h5 class="card-title mb-2"><?php echo htmlspecialchars($type['name']); ?></h5>
                            <p class="text-muted small mb-3"><?php echo htmlspecialchars($type['description']); ?></p>
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-primary rounded-pill">
                                    <?php 
                                    $count = 0;
                                    foreach ($categories as $cat) {
                                        if ($cat['type_name'] == $type['name']) {
                                            $count++;
                                        }
                                    }
                                    echo $count . ' Kategori';
                                    ?>
                                </span>
                                <span class="badge bg-success rounded-pill">
                                    <?php 
                                    $product_count = 0;
                                    foreach ($categories as $cat) {
                                        if ($cat['type_name'] == $type['name']) {
                                            $product_count += ($product_counts[$cat['id']] ?? 0);
                                        }
                                    }
                                    echo $product_count . ' Produk';
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- All Categories Section -->
<section class="py-5 bg-light" id="all-categories">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Semua Kategori</h2>
            <p class="lead text-muted">Temukan produk yang Anda cari dari berbagai kategori yang tersedia</p>
        </div>
        
        <div class="row g-4">
            <?php foreach ($categories as $category): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="text-primary me-3">
                                    <i class="<?php echo $category['type_icon']; ?> fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($category['name']); ?></h5>
                                    <small class="text-muted"><?php echo htmlspecialchars($category['type_name']); ?></small>
                                </div>
                            </div>
                            
                            <?php if (!empty($category['description'])): ?>
                                <p class="text-muted small mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                            <?php else: ?>
                                <p class="text-muted small mb-3">Temukan berbagai produk berkualitas dalam kategori ini.</p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-info rounded-pill">
                                    <?php echo ($product_counts[$category['id']] ?? 0); ?> Produk
                                </span>
                                <div class="text-muted small">
                                    <i class="fas fa-star text-warning"></i>
                                    <span>4.5</span>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="shop.php?category=<?php echo $category['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-shopping-cart me-2"></i>Lihat Produk
                                </a>
                                <a href="#" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-heart me-2"></i>Simpan
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Kategori Populer</h2>
            <p class="lead text-muted">Kategori dengan produk terlaris dan rating tertinggi</p>
        </div>
        
        <div class="row g-4">
            <!-- Fashion Category -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded-start h-100" alt="Fashion">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-tshirt text-primary me-2"></i>
                                    <h5 class="card-title mb-0">Fashion</h5>
                                </div>
                                <p class="text-muted small mb-2">Pakaian, aksesoris, dan fashion items</p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-success">15 Produk</span>
                                    <div class="text-warning">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <small>4.5</small>
                                    </div>
                                </div>
                                <a href="shop.php?category=1" class="btn btn-primary btn-sm">Lihat Produk</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Electronics Category -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded-start h-100" alt="Electronics">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-laptop text-info me-2"></i>
                                    <h5 class="card-title mb-0">Electronics</h5>
                                </div>
                                <p class="text-muted small mb-2">Gadget, elektronik, dan aksesoris teknologi</p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-success">12 Produk</span>
                                    <div class="text-warning">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <small>4.5</small>
                                    </div>
                                </div>
                                <a href="shop.php?category=2" class="btn btn-primary btn-sm">Lihat Produk</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Food & Beverage Category -->
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="row g-0">
                        <div class="col-md-4">
                            <img src="https://via.placeholder.com/300x200" class="img-fluid rounded-start h-100" alt="Food & Beverage">
                        </div>
                        <div class="col-md-8">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-utensils text-success me-2"></i>
                                    <h5 class="card-title mb-0">Food & Beverage</h5>
                                </div>
                                <p class="text-muted small mb-2">Makanan, minuman, dan bahan makanan</p>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-success">8 Produk</span>
                                    <div class="text-warning">
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star"></i>
                                        <i class="fas fa-star-half-alt"></i>
                                        <small>4.5</small>
                                    </div>
                                </div>
                                <a href="shop.php?category=3" class="btn btn-primary btn-sm">Lihat Produk</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Stats -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-primary mb-2">10</h3>
                    <p class="text-muted">Kategori Utama</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-success mb-2">40+</h3>
                    <p class="text-muted">Produk Aktif</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-info mb-2">100%</h3>
                    <p class="text-muted">UMMKM Terdaftar</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-warning mb-2">4.5</h3>
                    <p class="text-muted">Rating Rata-rata</p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-item {
    padding: 2rem;
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.rounded-start {
    border-top-left-radius: 0.375rem !important;
    border-bottom-left-radius: 0.375rem !important;
}

.rounded-end {
    border-top-right-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
