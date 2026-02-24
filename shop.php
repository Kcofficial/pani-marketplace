<?php
// Redirect to product_pani.php
header('Location: product_pani.php');
exit();
?>

<?php require_once 'includes/header.php'; ?>

<!-- Flash Sale Banner -->
<?php 
$flash_sales = getActiveFlashSales();
if (!empty($flash_sales)): 
?>
<section class="promotion-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h3><i class="fas fa-bolt me-2"></i>⚡ Flash Sale Berlangsung!</h3>
                <p>Diskon besar untuk waktu terbatas. Jangan lewatkan kesempatan emas ini!</p>
            </div>
            <div class="col-md-4">
                <a href="#flash-sale-products" class="btn-promotion">
                    <i class="fas fa-shopping-cart me-2"></i>Lihat Semua Flash Sale
                </a>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Shop Header -->
<section class="py-4 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1 class="mb-0">Toko</h1>
                <p class="text-muted mb-0">
                    <?php 
                    if ($total_products > 0) {
                        echo "Menampilkan " . (($page - 1) * $per_page + 1) . "-" . min($page * $per_page, $total_products) . " dari $total_products produk";
                    } else {
                        echo "Tidak ada produk yang ditemukan";
                    }
                    ?>
                </p>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end gap-2">
                    <select class="form-select form-select-sm" id="sort-products" style="width: auto;">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="newest_products" <?php echo $sort === 'newest_products' ? 'selected' : ''; ?>>Produk Terbaru</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Harga: Rendah ke Tinggi</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Harga: Tinggi ke Rendah</option>
                        <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Nama: A ke Z</option>
                        <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Nama: Z ke A</option>
                        <option value="popular" <?php echo $sort === 'popular' ? 'selected' : ''; ?>>Terpopuler</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Shop Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Filters -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Filters</h5>
                        
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label"><?php echo t('search'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                                <input type="text" class="form-control form-control-sm" id="search-input" name="search" 
                                       value="<?php echo htmlspecialchars($search); ?>" required>
                            </div>
                        </div>
                        <?php if (isset($errors['search'])): ?>
                            <div class="text-danger small mt-1"><?php echo $errors['search']; ?></div>
                        <?php endif; ?>
                        
                        <!-- Categories -->
                        <div class="mb-4">
                            <label class="form-label"><?php echo t('categories'); ?></label>
                            <div class="category-filter">
                                <a href="shop.php" class="d-block mb-2 text-decoration-none <?php echo $category_id === 0 ? 'fw-bold text-primary' : 'text-muted'; ?>">
                                    Semua Kategori
                                </a>
                                <?php foreach ($categories as $category): ?>
                                <a href="shop.php?category=<?php echo $category['id']; ?>" 
                                   class="d-block mb-2 text-decoration-none <?php echo $category_id === $category['id'] ? 'fw-bold text-primary' : 'text-muted'; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">Rentang Harga</label>
                            <form method="GET">
                                <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_price" 
                                               placeholder="Minimum" min="0">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" 
                                               placeholder="Maksimum" min="0">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-outline-primary btn-sm mt-2 w-100">Terapkan</button>
                            </form>
                        </div>
                        
                        <!-- Clear Filters -->
                        <a href="shop.php" class="btn btn-outline-secondary btn-sm w-100">Hapus Semua Filter</a>
                    </div>
                </div>
            </div>
            
            <!-- Products Grid -->
            <div class="col-lg-9">
                <?php if (empty($products)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>Tidak ada produk yang ditemukan</h4>
                        <p>Coba sesuaikan filter atau istilah pencarian Anda</p>
                        <a href="shop.php" class="btn btn-primary">Clear Filters</a>
                    </div>
                <?php else: ?>
                    <div class="row g-4" id="products-container">
                        <?php foreach ($products as $product): 
                            $price_info = getProductFinalPrice($product);
                            $rating_summary = getProductRatingSummary($product['id']);
                        ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="product-card product-card-discount clickable-card" onclick="window.location.href='product.php?id=<?php echo $product['id']; ?>'">
                                <div class="position-relative overflow-hidden">
                                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/400/300.jpg" 
                                         class="product-image w-100" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    
                                    <!-- Discount Badges -->
                                    <?php if ($price_info['flash_sale']): ?>
                                        <div class="flash-sale-badge">
                                            <i class="fas fa-bolt me-1"></i>Flash Sale
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($price_info['has_discount']): ?>
                                        <div class="discount-badge">
                                            -<?php echo $price_info['discount_percentage']; ?>%
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <button class="btn btn-sm btn-outline-light wishlist-toggle" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                onclick="event.stopPropagation();">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    </div>
                                    
                                    <!-- Stock Indicator -->
                                    <?php if ($product['stock'] == 0): ?>
                                        <div class="position-absolute top-0 start-0 p-2">
                                            <span class="badge bg-danger">Habis</span>
                                        </div>
                                    <?php elseif ($product['stock'] < 5): ?>
                                        <div class="position-absolute top-0 start-0 p-2">
                                            <span class="badge bg-warning">Stok Terbatas</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="p-3">
                                    <div class="mb-2">
                                        <small class="text-muted"><?php echo htmlspecialchars($product['category_name']); ?></small>
                                    </div>
                                    <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="text-muted small mb-3"><?php echo substr(htmlspecialchars($product['description']), 0, 100); ?>...</p>
                                    
                                    <!-- Price Display -->
                                    <div class="price-display mb-2">
                                        <?php if ($price_info['has_discount']): ?>
                                            <span class="original-price"><?php echo formatPriceIDR($price_info['original_price']); ?></span>
                                            <span class="current-price"><?php echo formatPriceIDR($price_info['final_price']); ?></span>
                                            <span class="discount-percentage">-<?php echo $price_info['discount_percentage']; ?>%</span>
                                        <?php else: ?>
                                            <span class="current-price"><?php echo formatPriceIDR($price_info['final_price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Rating Display -->
                                    <?php if ($rating_summary['total_reviews'] > 0): ?>
                                        <div class="mb-2">
                                            <?php echo generateStarRating($rating_summary['average_rating'], false); ?>
                                            <small class="text-muted ms-1">(<?php echo $rating_summary['total_reviews']; ?>)</small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <small class="text-muted">Stok: <?php echo $product['stock']; ?></small>
                                        <?php if ($price_info['flash_sale']): ?>
                                            <small class="text-danger">
                                                <i class="fas fa-fire me-1"></i>
                                                <?php echo $price_info['flash_sale']['flash_stock'] - $price_info['flash_sale']['sold_count']; ?> tersisa
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-outline-primary btn-sm btn-detail"
                                           onclick="event.stopPropagation();">Lihat Detail</a>
                                        <button class="btn btn-primary btn-sm add-to-cart" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                data-price="<?php echo $price_info['final_price']; ?>"
                                                onclick="event.stopPropagation();"
                                                <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                                            <i class="fas fa-cart-plus me-1"></i><?php echo $product['stock'] <= 0 ? 'Habis' : 'Tambah ke Keranjang'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">Sebelumnya</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <li class="page-item active">
                                        <span class="page-link"><?php echo $i; ?></span>
                                    </li>
                                <?php elseif ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                                    <li class="page-item">
                                        <span class="page-link"><?php echo $i; ?></span>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Lanjut</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Ensure product detail links work properly
$(document).ready(function() {
    $('.btn-detail').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var href = $(this).attr('href');
        if (href) {
            window.location.href = href;
        }
    });
    
    // Make entire product card clickable (except buttons)
    $('.clickable-card').on('click', function(e) {
        // Check if click is on a button or link
        if ($(e.target).closest('a, button').length === 0) {
            // Get product ID from the card
            var productId = $(this).find('.btn-detail').attr('href');
            if (productId) {
                window.location.href = productId;
            }
        }
    });
    
    // Add hover effect for clickable cards
    $('.clickable-card').css('cursor', 'pointer');
});
</script>

<?php require_once 'includes/footer.php'; ?>
