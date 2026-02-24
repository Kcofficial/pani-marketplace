<?php
$page_title = 'Detail Produk - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'includes/discount_functions.php';
require_once 'includes/review_functions.php';
require_once 'includes/currency_functions.php';
require_once 'config/database.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: shop.php');
    exit();
}

try {
    // Get product with all options and sales count
    $product = getProductWithOptions($product_id);
    
    if (!$product) {
        header('Location: shop.php');
        exit();
    }
    
    // Get sales count for this product
    $stmt = getDBConnection()->prepare("
        SELECT SUM(oi.quantity) as total_sold
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.id
        WHERE oi.product_id = ? AND o.status != 'cancelled'
    ");
    $stmt->execute([$product_id]);
    $sales_data = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_sold = $sales_data['total_sold'] ?? 0;
    
    // Get price info
    $price_info = getProductFinalPrice($product);
    
    // Get rating summary
    $rating_summary = getProductRatingSummary($product_id);
    
    // Get reviews
    $reviews = getProductReviews($product_id, 1, 5);
    
    // Get seller information
    $stmt = getDBConnection()->prepare("
        SELECT u.id, u.name, u.email, u.phone, u.address, 
               COUNT(p.id) as total_products,
               AVG(r.rating) as seller_rating
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id AND p.status = 'active'
        LEFT JOIN reviews r ON p.id = r.product_id
        WHERE u.id = ? AND u.role = 'seller'
        GROUP BY u.id
    ");
    $stmt->execute([$product['seller_id']]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
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
    
    // Get product images (assuming multiple images)
    $product_images = [$product['image']]; // Add more images if available
    
    // Get shipping options
    $shipping_options = [
        ['name' => 'Reguler', 'cost' => 9000, 'days' => '2-3 hari'],
        ['name' => 'Express', 'cost' => 15000, 'days' => '1-2 hari'],
        ['name' => 'Same Day', 'cost' => 25000, 'days' => 'Hari ini']
    ];
    
} catch(PDOException $e) {
    error_log("Error loading product: " . $e->getMessage());
    header('Location: shop.php');
    exit();
}
?>

<?php if (!$product): ?>
    <?php require_once 'includes/header.php'; ?>
    <div class="container py-5">
        <div class="alert alert-danger">Produk tidak ditemukan.</div>
    </div>
    <?php require_once 'includes/footer.php'; ?>
    <?php exit(); ?>
<?php endif; ?>

<?php require_once 'includes/header.php'; ?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="py-3 bg-light">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="shop.php" class="text-decoration-none">Katalog</a></li>
            <li class="breadcrumb-item"><a href="shop.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </div>
</nav>

<!-- Product Details -->
<section class="py-4">
    <div class="container">
        <div class="row">
            <!-- Product Images -->
            <div class="col-lg-6 mb-4">
                <div class="product-gallery">
                    <!-- Main Image -->
                    <div class="main-image-container mb-3 position-relative">
                        <img id="main-product-image" 
                             src="https://picsum.photos/seed/<?php echo $product['id']; ?>/600/600.jpg" 
                             class="img-fluid rounded-3 shadow-sm w-100" 
                             style="max-height: 600px; object-fit: cover;"
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        
                        <!-- Discount Badge -->
                        <?php if ($price_info['has_discount']): ?>
                            <div class="position-absolute top-0 start-0 m-3">
                                <span class="badge bg-danger fs-6">-<?php echo $price_info['discount_percentage']; ?>%</span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Zoom Icon -->
                        <div class="position-absolute bottom-0 end-0 m-3">
                            <button class="btn btn-light btn-sm" onclick="zoomImage()">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <div class="thumbnail-images d-flex gap-2 overflow-auto">
                        <?php foreach ($product_images as $index => $image): ?>
                            <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/100x100.jpg" 
                                 class="img-thumbnail border-primary <?php echo $index === 0 ? 'border-3' : ''; ?>" 
                                 style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                 onclick="changeMainImage(this)"
                                 alt="Thumbnail <?php echo $index + 1; ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="product-info">
                    <!-- Product Title & Category -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-info">
                                <i class="fas fa-tag me-1"></i>
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>
                            <?php if ($product['is_preorder_product']): ?>
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>
                                    Pre-order
                                </span>
                            <?php endif; ?>
                        </div>
                        <h1 class="h3 fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>
                    </div>
                    
                    <!-- Rating & Reviews & Sales -->
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="d-flex align-items-center">
                            <?php echo generateStarRating($rating_summary['average_rating'], false); ?>
                            <span class="ms-2 text-muted"><?php echo number_format($rating_summary['average_rating'], 1); ?></span>
                        </div>
                        <span class="text-muted">|</span>
                        <span class="text-success fw-bold"><?php echo $total_sold; ?> terjual</span>
                        <span class="text-muted">|</span>
                        <a href="#reviews" class="text-decoration-none"><?php echo $rating_summary['total_reviews']; ?> ulasan</a>
                    </div>
                    
                    <!-- Price Section -->
                    <div class="price-section mb-4 p-3 bg-light rounded-3">
                        <?php if ($price_info['has_discount']): ?>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <h2 class="text-danger fw-bold mb-0"><?php echo formatCurrency($price_info['final_price']); ?></h2>
                                <span class="text-muted text-decoration-line-through"><?php echo formatCurrency($price_info['original_price']); ?></span>
                                <span class="badge bg-danger">-<?php echo $price_info['discount_percentage']; ?>%</span>
                            </div>
                        <?php else: ?>
                            <h2 class="text-primary fw-bold mb-2"><?php echo formatCurrency($price_info['final_price']); ?></h2>
                        <?php endif; ?>
                        
                        <!-- Stock Status -->
                        <div class="d-flex align-items-center gap-2">
                            <?php if ($product['is_preorder_product']): ?>
                                <span class="badge bg-info">
                                    <i class="fas fa-clock me-1"></i>
                                    Pre-order
                                </span>
                                <small class="text-muted"><?php echo formatPreorderMessage($product['preorder_settings']); ?></small>
                            <?php elseif ($product['stock'] > 0): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Tersedia
                                </span>
                                <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Habis
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Product Variants -->
                    <?php if (!empty($product['variants'])): ?>
                        <div class="product-variants mb-4">
                            <?php foreach ($product['variants'] as $variant_name => $variant_options): ?>
                                <div class="mb-3">
                                    <label class="form-label fw-bold"><?php echo htmlspecialchars($variant_name); ?>:</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php foreach ($variant_options as $option): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" 
                                                       name="variants[<?php echo $variant_name; ?>]" 
                                                       value="<?php echo htmlspecialchars($option['variant_value']); ?>"
                                                       id="variant-<?php echo $option['id']; ?>"
                                                       data-price-adjustment="<?php echo $option['price_adjustment']; ?>"
                                                       data-stock-adjustment="<?php echo $option['stock_adjustment']; ?>">
                                                <label class="form-check-label border rounded px-3 py-2" for="variant-<?php echo $option['id']; ?>">
                                                    <?php echo htmlspecialchars($option['variant_value']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Flavors -->
                    <?php if (!empty($product['flavors'])): ?>
                        <div class="product-flavors mb-4">
                            <label class="form-label fw-bold">Varian:</label>
                            <select class="form-select" name="flavor" id="flavor-select">
                                <option value="">Pilih Varian</option>
                                <?php foreach ($product['flavors'] as $flavor): ?>
                                    <option value="<?php echo $flavor['id']; ?>"
                                            data-price-adjustment="<?php echo $flavor['price_adjustment']; ?>"
                                            data-stock-adjustment="<?php echo $flavor['stock_adjustment']; ?>">
                                        <?php echo htmlspecialchars($flavor['flavor_name']); ?>
                                        <?php if ($flavor['price_adjustment'] != 0): ?>
                                            (+<?php echo formatCurrency(abs($flavor['price_adjustment'])); ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Quantity & Add to Cart -->
                    <div class="quantity-section mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="input-group" style="width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity()">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="form-control text-center" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <!-- Selected Info Display -->
                            <div id="selected-info" class="ms-auto"></div>
                        </div>
                        <button class="btn btn-primary btn-lg flex-fill" onclick="addToCart()">
                            <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                        </button>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons mb-4">
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-danger flex-fill" onclick="buyNow()">
                                <i class="fas fa-bolt me-2"></i>Beli Sekarang
                            </button>
                            <button class="btn btn-outline-secondary" onclick="addToWishlist()">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="btn btn-outline-secondary" onclick="shareProduct()">
                                <i class="fas fa-share-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Product Info Tabs -->
                    <div class="product-info-tabs">
                        <ul class="nav nav-tabs mb-3" id="productTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button">
                                    Deskripsi
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs" type="button">
                                    Spesifikasi
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button">
                                    Pengiriman
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content" id="productTabsContent">
                            <div class="tab-pane fade show active" id="description" role="tabpanel">
                                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                            </div>
                            <div class="tab-pane fade" id="specs" role="tabpanel">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Berat:</strong></td>
                                        <td><?php echo formatWeight($product['weight'], 'g'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Dimensi:</strong></td>
                                        <td><?php echo htmlspecialchars($product['dimensions'] ?? '-'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>SKU:</strong></td>
                                        <td><?php echo htmlspecialchars($product['sku'] ?? '-'); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="shipping" role="tabpanel">
                                <div class="shipping-options">
                                    <?php foreach ($shipping_options as $option): ?>
                                        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                            <div>
                                                <strong><?php echo $option['name']; ?></strong>
                                                <small class="text-muted d-block"><?php echo $option['days']; ?></small>
                                            </div>
                                            <span class="text-primary fw-bold"><?php echo formatCurrency($option['cost']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Seller Info -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fas fa-store fa-2x"></i>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="mb-1"><?php echo htmlspecialchars($seller['name'] ?? 'Penjual'); ?></h5>
                                    <div class="d-flex align-items-center gap-2">
                                        <?php echo generateStarRating($seller['seller_rating'] ?? 0, false); ?>
                                        <small class="text-muted"><?php echo number_format($seller['seller_rating'] ?? 0, 1); ?></small>
                                    </div>
                                    <small class="text-muted"><?php echo $seller['total_products'] ?? 0; ?> produk</small>
                                    <?php if ($seller['address']): ?>
                                        <div class="text-muted small">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($seller['address']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <button class="btn btn-success" onclick="openChatModal()">
                                    <i class="fas fa-comments me-2"></i>Chat Penjual
                                </button>
                                <button class="btn btn-primary ms-2" onclick="visitSellerShop()">
                                    <i class="fas fa-store me-2"></i>Kunjungi Toko
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chat Modal -->
<div class="modal fade" id="chatModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-comments me-2"></i>
                    Chat dengan <?php echo htmlspecialchars($seller['name'] ?? 'Penjual'); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="chat-messages" id="chatMessages" style="height: 300px; overflow-y: auto; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                    <div class="text-center text-muted mb-3">
                        <small>Mulai percakapan dengan penjual</small>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="input-group">
                        <input type="text" class="form-control" id="chatInput" placeholder="Ketik pesan Anda..." onkeypress="handleChatKeyPress(event)">
                        <button class="btn btn-success" onclick="sendMessage()">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    Penjual biasanya merespons dalam beberapa menit
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Product Reviews -->
<section class="py-4" id="reviews">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Ulasan Pembeli</h5>
                    </div>
                    <div class="card-body">
                        <!-- Rating Summary -->
                        <div class="row mb-4">
                            <div class="col-md-4 text-center">
                                <h2 class="display-4 fw-bold"><?php echo number_format($rating_summary['average_rating'], 1); ?></h2>
                                <?php echo generateStarRating($rating_summary['average_rating'], false); ?>
                                <p class="text-muted"><?php echo $rating_summary['total_reviews']; ?> ulasan</p>
                            </div>
                            <div class="col-md-8">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="me-2" style="width: 20px;"><?php echo $i; ?></span>
                                        <div class="progress flex-fill me-2" style="height: 8px;">
                                            <div class="progress-bar bg-warning" style="width: <?php echo ($rating_summary['rating_distribution'][$i] ?? 0) * 100; ?>%"></div>
                                        </div>
                                        <span style="width: 40px;"><?php echo $rating_summary['rating_distribution'][$i] ?? 0; ?></span>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Review List -->
                        <div class="reviews-list">
                            <?php foreach ($reviews as $review): ?>
                                <div class="review-item border-bottom pb-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong><?php echo htmlspecialchars($review['user_name'] ?? 'Anonymous'); ?></strong>
                                            <div class="d-flex align-items-center gap-2">
                                                <?php echo generateStarRating($review['rating'], false); ?>
                                                <small class="text-muted"><?php echo date('d M Y', strtotime($review['created_at'])); ?></small>
                                            </div>
                                        </div>
                                        <?php if ($review['is_verified_purchase']): ?>
                                            <span class="badge bg-success">Verified Purchase</span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="mb-1"><?php echo htmlspecialchars($review['review_text']); ?></p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-thumbs-up me-1"></i>Membantu (<?php echo $review['helpful_count']; ?>)
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-flag me-1"></i>Laporkan
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Products -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="text-center mb-4">
            <h3>Produk Terkait</h3>
            <p class="text-muted">Produk serupa yang mungkin Anda suka</p>
        </div>
        <div class="row g-4">
            <?php foreach ($related_products as $related): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="card h-100">
                        <img src="https://picsum.photos/seed/<?php echo $related['id']; ?>/200x200.jpg" class="card-img-top" alt="<?php echo htmlspecialchars($related['name']); ?>">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h6>
                            <p class="text-primary fw-bold"><?php echo formatCurrency($related['price']); ?></p>
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary btn-sm">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<style>
.form-check-label {
    cursor: pointer;
    transition: all 0.3s ease;
}

.form-check-input:checked + .form-check-label {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.thumbnail-images img:hover {
    border-color: #007bff !important;
}

.progress {
    background-color: #e9ecef;
}

.nav-tabs .nav-link {
    color: #6c757d;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-color: #007bff;
}

.review-item:last-child {
    border-bottom: none !important;
}

.card {
    transition: transform 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
}
</style>

<script>
// Global variables for selected options
let selectedVariant = '';
let selectedFlavor = '';
let selectedWeight = '';

function changeMainImage(thumbnail) {
    const mainImage = document.getElementById('main-product-image');
    const thumbnailSrc = thumbnail.src.replace('100x100', '600x600');
    mainImage.src = thumbnailSrc;
    
    // Update thumbnail borders
    document.querySelectorAll('.thumbnail-images img').forEach(img => {
        img.classList.remove('border-3');
    });
    thumbnail.classList.add('border-3');
}

function updatePriceAndStock() {
    // Get selected options
    const variantSelect = document.getElementById('variant-select');
    const flavorSelect = document.getElementById('flavor-select');
    const weightSelect = document.getElementById('weight-select');
    
    selectedVariant = variantSelect ? variantSelect.value : '';
    selectedFlavor = flavorSelect ? flavorSelect.value : '';
    selectedWeight = weightSelect ? weightSelect.value : '';
    
    // Update display info
    updateSelectedInfo();
    
    // Here you would typically make an AJAX call to get updated price
    // For now, we'll just update the display
    console.log('Selected:', {
        variant: selectedVariant,
        flavor: selectedFlavor,
        weight: selectedWeight
    });
}

function updateSelectedInfo() {
    const infoDiv = document.getElementById('selected-info');
    if (infoDiv) {
        let info = [];
        if (selectedVariant) info.push(`Varian: ${selectedVariant}`);
        if (selectedFlavor) info.push(`Rasa: ${selectedFlavor}`);
        if (selectedWeight) info.push(`Ukuran: ${selectedWeight}`);
        
        if (info.length > 0) {
            infoDiv.innerHTML = `<small class="text-success"><i class="fas fa-check-circle me-1"></i>${info.join(' | ')}</small>`;
        } else {
            infoDiv.innerHTML = '';
        }
    }
}

function increaseQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max);
    if (input.value < max) {
        input.value = parseInt(input.value) + 1;
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function zoomImage() {
    const mainImage = document.getElementById('main-product-image');
    const modal = document.createElement('div');
    modal.className = 'modal fade show';
    modal.style.display = 'block';
    modal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <img src="${mainImage.src}" class="img-fluid" alt="Product Zoom">
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    
    // Close on click
    modal.addEventListener('click', function() {
        modal.remove();
    });
}

// Chat functionality
function openChatModal() {
    const modal = new bootstrap.Modal(document.getElementById('chatModal'));
    modal.show();
}

function sendMessage() {
    const input = document.getElementById('chatInput');
    const messagesDiv = document.getElementById('chatMessages');
    const message = input.value.trim();
    
    if (message) {
        // Add user message
        const userMessage = `
            <div class="mb-2">
                <div class="d-flex justify-content-end">
                    <div class="bg-primary text-white rounded-3 p-2" style="max-width: 70%;">
                                                        <small class="d-block text-muted">Anda</small>
                                                        ${message}
                                                    </div>
                                                </div>
                                            </div>
                                        `;
        messagesDiv.innerHTML += userMessage;
        
        // Simulate seller response
        setTimeout(() => {
            const sellerResponse = `
                <div class="mb-2">
                    <div class="d-flex justify-content-start">
                        <div class="bg-light rounded-3 p-2" style="max-width: 70%;">
                                                            <small class="d-block text-muted">Penjual</small>
                                                            Terima kasih telah menghubungi kami! Produk ini ready stock. Ada yang bisa kami bantu?
                                                        </div>
                                                    </div>
                                                </div>
                                            `;
            messagesDiv.innerHTML += sellerResponse;
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }, 1000);
        
        input.value = '';
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }
}

function handleChatKeyPress(event) {
    if (event.key === 'Enter') {
        sendMessage();
    }
}

function visitSellerShop() {
    // Redirect to seller's shop (would need seller shop page)
    alert('Mengarah ke toko penjual...');
    // window.location.href = 'seller-shop.php?id=<?php echo $seller['id']; ?>';
}

function addToCart() {
    const quantity = document.getElementById('quantity').value;
    const productInfo = {
        id: <?php echo $product_id; ?>,
        name: '<?php echo addslashes($product['name']); ?>',
        price: '<?php echo $price_info['final_price']; ?>',
        quantity: quantity,
        variant: selectedVariant,
        flavor: selectedFlavor,
        weight: selectedWeight
    };
    
    console.log('Adding to cart:', productInfo);
    
    // Show success message
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success position-fixed top-0 start-50 translate-middle-x m-3';
    alertDiv.style.zIndex = '9999';
    alertDiv.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        ${productInfo.name} (${productInfo.quantity}x) berhasil ditambahkan ke keranjang!
        ${selectedVariant || selectedFlavor || selectedWeight ? '<br><small>Varian: ' + [selectedVariant, selectedFlavor, selectedWeight].filter(Boolean).join(', ') + '</small>' : ''}
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}

function buyNow() {
    addToCart();
    // Redirect to checkout
    setTimeout(() => {
        window.location.href = 'checkout.php';
    }, 1000);
}

function addToWishlist() {
    const productInfo = {
        id: <?php echo $product_id; ?>,
        name: '<?php echo addslashes($product['name']); ?>'
    };
    
    console.log('Adding to wishlist:', productInfo);
    
    // Toggle wishlist button
    const btn = event.target.closest('button');
    const icon = btn.querySelector('i');
    
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas', 'text-danger');
        showNotification('Produk ditambahkan ke wishlist!');
    } else {
        icon.classList.remove('fas', 'text-danger');
        icon.classList.add('far');
        showNotification('Produk dihapus dari wishlist!');
    }
}

function shareProduct() {
    if (navigator.share) {
        navigator.share({
            title: '<?php echo addslashes($product['name']); ?>',
            text: 'Lihat produk menarik ini di Paṇi Marketplace',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        showNotification('Link produk disalin!');
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-info position-fixed top-0 start-50 translate-middle-x m-3';
    notification.style.zIndex = '9999';
    notification.innerHTML = `<i class="fas fa-info-circle me-2"></i>${message}`;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSelectedInfo();
});

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    if (input.value > 1) {
        input.value = parseInt(input.value) - 1;
    }
}

function addToCart() {
    const quantity = document.getElementById('quantity').value;
    // Add to cart logic here
    alert('Produk ditambahkan ke keranjang!');
}

function buyNow() {
    const quantity = document.getElementById('quantity').value;
    // Buy now logic here
    alert('Lanjut ke pembayaran!');
}

function addToWishlist() {
    // Add to wishlist logic here
    alert('Produk ditambahkan ke wishlist!');
}

function shareProduct() {
    // Share logic here
    if (navigator.share) {
        navigator.share({
            title: '<?php echo htmlspecialchars($product["name"]); ?>',
            text: '<?php echo htmlspecialchars($product["description"]); ?>',
            url: window.location.href
        });
    } else {
        alert('Link produk disalin!');
    }
}

function zoomImage() {
    // Zoom image logic here
    alert('Zoom gambar!');
}
</script>

<?php require_once 'includes/footer.php'; ?>
