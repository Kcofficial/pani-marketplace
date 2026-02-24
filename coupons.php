<?php
$page_title = 'Kupon & Promo - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'includes/discount_functions.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=coupons.php');
}

$active_coupons = [];
$active_discounts = [];
$flash_sales = [];
$success = '';
$error = '';

try {
    $conn = getDBConnection();
    
    // Get active coupons
    $stmt = $conn->prepare("
        SELECT * FROM coupons 
        WHERE is_active = TRUE 
        AND (start_date IS NULL OR start_date <= CURRENT_TIMESTAMP)
        AND (end_date IS NULL OR end_date >= CURRENT_TIMESTAMP)
        AND (usage_limit IS NULL OR usage_count < usage_limit)
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $active_coupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get active discounts
    $active_discounts = getActiveDiscounts();
    
    // Get flash sales
    $flash_sales = getActiveFlashSales();
    
} catch (PDOException $e) {
    $error = "Error loading promotions: " . $e->getMessage();
}

// Handle coupon application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_coupon'])) {
    $coupon_code = isset($_POST['coupon_code']) ? strtoupper(cleanInput($_POST['coupon_code'])) : '';
    
    if (empty($coupon_code)) {
        $error = 'Silakan masukkan kode kupon';
    } else {
        // Get cart total (simplified for demo)
        $cart_total = 100000; // This should come from actual cart
        
        $coupon = validateCoupon($coupon_code, $_SESSION['user_id'], $cart_total);
        
        if ($coupon) {
            $_SESSION['applied_coupon'] = $coupon;
            $success = 'Kupon "' . $coupon['code'] . '" berhasil diterapkan! Diskon ' . formatDiscountDisplay($coupon['discount_type'], $coupon['discount_value']) . ' telah ditambahkan ke keranjang Anda.';
        } else {
            $error = 'Kupon tidak valid atau telah kedaluwarsa';
        }
    }
}

// Handle coupon removal
if (isset($_GET['remove_coupon'])) {
    unset($_SESSION['applied_coupon']);
    $success = 'Kupon telah dihapus dari keranjang';
}

// Get applied coupon from session
$applied_coupon = isset($_SESSION['applied_coupon']) ? $_SESSION['applied_coupon'] : null;
?>

<?php require_once 'includes/header.php'; ?>

<!-- Coupons & Promotions Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Active Coupons -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-ticket-alt me-2"></i>Kupon Tersedia</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (empty($active_coupons)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                                <h5>Belum Ada Kupon</h5>
                                <p class="text-muted">Kembali lagi nanti untuk melihat kupon terbaru!</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($active_coupons as $coupon): ?>
                                    <div class="col-md-6">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div>
                                                        <h6 class="card-title mb-1"><?php echo htmlspecialchars($coupon['name']); ?></h6>
                                                        <span class="badge bg-success"><?php echo formatDiscountDisplay($coupon['discount_type'], $coupon['discount_value']); ?></span>
                                                    </div>
                                                    <span class="text-muted small">Kode: <strong><?php echo htmlspecialchars($coupon['code']); ?></strong></span>
                                                </div>
                                                
                                                <p class="card-text small text-muted mb-2"><?php echo htmlspecialchars($coupon['description']); ?></p>
                                                
                                                <?php if ($coupon['min_purchase_amount'] > 0): ?>
                                                    <p class="card-text small">
                                                        <i class="fas fa-shopping-cart me-1"></i>
                                                        Minimum pembelian: <?php echo formatPriceIDR($coupon['min_purchase_amount']); ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if ($coupon['usage_limit']): ?>
                                                    <p class="card-text small">
                                                        <i class="fas fa-users me-1"></i>
                                                        Tersisa: <?php echo $coupon['usage_limit'] - $coupon['usage_count']; ?> dari <?php echo $coupon['usage_limit']; ?> penggunaan
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <?php if ($coupon['end_date']): ?>
                                                    <p class="card-text small text-danger">
                                                        <i class="fas fa-clock me-1"></i>
                                                        Berlaku hingga: <?php echo formatDateID($coupon['end_date']); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Global Discounts -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-percentage me-2"></i>Diskon Aktif</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($active_discounts)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-percentage fa-3x text-muted mb-3"></i>
                                <h5>Belum Ada Diskon</h5>
                                <p class="text-muted">Kembali lagi nanti untuk melihat penawaran terbaru!</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-3">
                                <?php foreach ($active_discounts as $discount): ?>
                                    <div class="col-md-6">
                                        <div class="card border h-100">
                                            <div class="card-body">
                                                <h6 class="card-title mb-2"><?php echo htmlspecialchars($discount['name']); ?></h6>
                                                <p class="card-text small text-muted mb-2"><?php echo htmlspecialchars($discount['description']); ?></p>
                                                
                                                <div class="discount-details mb-2">
                                                    <span class="badge bg-primary"><?php echo formatDiscountDisplay($discount['discount_type'], $discount['discount_value']); ?></span>
                                                    
                                                    <?php if ($discount['min_purchase_amount'] > 0): ?>
                                                        <small class="text-muted d-block">
                                                            Min. pembelian: <?php echo formatPriceIDR($discount['min_purchase_amount']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <?php if ($discount['end_date']): ?>
                                                    <p class="card-text small text-danger mb-0">
                                                        <i class="fas fa-clock me-1"></i>
                                                        Berlaku hingga: <?php echo formatDateID($discount['end_date']); ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Applied Coupon -->
                <?php if ($applied_coupon): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>Kupon Diterapkan</h6>
                        </div>
                        <div class="card-body applied-coupon">
                            <div class="coupon-info">
                                <div>
                                    <strong><?php echo htmlspecialchars($applied_coupon['name']); ?></strong>
                                    <span class="badge bg-success ms-2"><?php echo formatDiscountDisplay($applied_coupon['discount_type'], $applied_coupon['discount_value']); ?></span>
                                </div>
                                <small class="text-muted d-block"><?php echo htmlspecialchars($applied_coupon['description']); ?></small>
                            </div>
                            <a href="?remove_coupon=1" class="remove-coupon">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Flash Sales -->
                <?php if (!empty($flash_sales)): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-warning text-white">
                            <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Flash Sale Aktif</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($flash_sales as $flash): ?>
                                <div class="flash-sale-item mb-3">
                                    <h6><?php echo htmlspecialchars($flash['name']); ?></h6>
                                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($flash['description']); ?></p>
                                    
                                    <div class="flash-sale-timer">
                                        <div class="timer-text">Berakhir dalam:</div>
                                        <div class="timer-countdown" id="timer-<?php echo $flash['id']; ?>">
                                            <!-- Timer will be set by JavaScript -->
                                        </div>
                                    </div>
                                    
                                    <a href="shop.php?flash_sale=<?php echo $flash['id']; ?>" class="btn btn-warning btn-sm w-100">
                                        <i class="fas fa-shopping-cart me-2"></i>Lihat Produk Flash Sale
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Coupon Application Form -->
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-tag me-2"></i>Masukkan Kupon</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="coupon-input-group">
                            <input type="text" 
                                   name="coupon_code" 
                                   class="coupon-input" 
                                   placeholder="Masukkan kode kupon" 
                                   value="<?php echo isset($_POST['coupon_code']) ? htmlspecialchars($_POST['coupon_code']) : ''; ?>"
                                   required>
                            <button type="submit" name="apply_coupon" class="coupon-button">
                                <i class="fas fa-check"></i> Terapkan
                            </button>
                        </form>
                        
                        <div class="mt-3">
                            <h6 class="text-muted">Tips:</h6>
                            <ul class="small text-muted">
                                <li>• Periksa tanggal kedaluwarsa kupon</li>
                                <li>• Beberapa kupon memiliki minimum pembelian</li>
                                <li>• Satu pengguna hanya bisa menggunakan satu kupon per transaksi</li>
                                <li>• Kupon tidak dapat digabungkan dengan diskon lainnya</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Flash Sale Timer Script -->
<script>
<?php if (!empty($flash_sales)): ?>
    // Set up timers for flash sales
    <?php foreach ($flash_sales as $flash): ?>
        const flashSale<?php echo $flash['id']; ?> = {
            endTime: new Date('<?php echo $flash['end_time']; ?>').getTime(),
            timerElement: document.getElementById('timer-<?php echo $flash['id']; ?>')
        };
        
        function updateTimer<?php echo $flash['id']; ?>() {
            const now = new Date().getTime();
            const distance = flashSale<?php echo $flash['id']; ?>.endTime - now;
            
            if (distance < 0) {
                flashSale<?php echo $flash['id']; ?>.timerElement.innerHTML = 'Flash Sale Berakhir!';
                return;
            }
            
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            
            flashSale<?php echo $flash['id']; ?>.timerElement.innerHTML = 
                hours.toString().padStart(2, '0') + ':' +
                minutes.toString().padStart(2, '0') + ':' +
                seconds.toString().padStart(2, '0');
        }
        
        // Update immediately
        updateTimer<?php echo $flash['id']; ?>();
        
        // Update every second
        setInterval(updateTimer<?php echo $flash['id']; ?>, 1000);
    <?php endforeach; ?>
<?php endif; ?>
</script>

<style>
.flash-sale-item {
    border-left: 4px solid #ffc107;
    padding-left: 15px;
    margin-bottom: 20px;
}

.flash-sale-item:last-child {
    border-left: none;
}

.coupon-input-group {
    display: flex;
    gap: 10px;
}

.coupon-input {
    flex: 1;
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
}

.coupon-button {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
}

.coupon-button:hover {
    background: #0056b3;
}

.applied-coupon {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.remove-coupon:hover {
    color: #dc3545;
}

@media (max-width: 768px) {
    .coupon-input-group {
        flex-direction: column;
        gap: 10px;
    }
    
    .coupon-input,
    .coupon-button {
        width: 100%;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
