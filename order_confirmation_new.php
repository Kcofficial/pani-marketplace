<?php
$page_title = 'Konfirmasi Pesanan - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=order_confirmation.php');
}

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    redirect('index.php');
}

// Get order details
$order = null;
$order_items = [];
$shipping_details = null;
$payment_details = null;
$tracking_history = [];

try {
    $conn = getDBConnection();
    
    // Get order
    $stmt = $conn->prepare("SELECT o.*, u.name as customer_name, u.email as customer_email 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.id 
                           WHERE o.id = ? AND o.user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        redirect('index.php');
    }
    
    // Get order items
    $stmt = $conn->prepare("SELECT oi.*, p.name, p.image, c.name as category_name 
                           FROM order_items oi 
                           JOIN products p ON oi.product_id = p.id 
                           JOIN categories c ON p.category_id = c.id 
                           WHERE oi.order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get shipping details
    $stmt = $conn->prepare("SELECT sd.*, sm.name as shipping_method_name, sm.estimated_delivery_days, sm.cost as shipping_cost
                           FROM shipping_details sd 
                           JOIN shipping_methods sm ON sd.shipping_method_id = sm.id 
                           WHERE sd.order_id = ?");
    $stmt->execute([$order_id]);
    $shipping_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get payment details
    $stmt = $conn->prepare("SELECT pd.*, pm.name as payment_method_name, pm.description as payment_description, pm.processing_fee
                           FROM payment_details pd 
                           JOIN payment_methods pm ON pd.payment_method_id = pm.id 
                           WHERE pd.order_id = ?");
    $stmt->execute([$order_id]);
    $payment_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get tracking history
    $stmt = $conn->prepare("SELECT ot.*, u.name as updated_by_name 
                           FROM order_tracking ot 
                           LEFT JOIN users u ON ot.updated_by = u.id 
                           WHERE ot.order_id = ? 
                           ORDER BY ot.timestamp DESC");
    $stmt->execute([$order_id]);
    $tracking_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error loading order details: " . $e->getMessage();
}

// Function to get status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Menunggu Pembayaran</span>',
        'processing' => '<span class="badge bg-info">Diproses</span>',
        'shipped' => '<span class="badge bg-primary">Dikirim</span>',
        'delivered' => '<span class="badge bg-success">Terkirim</span>',
        'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>',
        'order_placed' => '<span class="badge bg-secondary">Pesanan Dibuat</span>',
        'payment_pending' => '<span class="badge bg-warning">Menunggu Pembayaran</span>',
        'payment_confirmed' => '<span class="badge bg-info">Pembayaran Dikonfirmasi</span>',
        'in_transit' => '<span class="badge bg-primary">Dalam Perjalanan</span>',
        'out_for_delivery' => '<span class="badge bg-primary">Akan Dikirim</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Order Confirmation Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Order Details -->
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Pesanan Berhasil!</h4>
                        <p class="mb-0 mt-2">Terima kasih telah berbelanja di toko kami. Pesanan Anda telah diterima dan sedang diproses.</p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php else: ?>
                            <!-- Order Information -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Nomor Pesanan</h6>
                                    <p class="fw-bold">#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Tanggal Pesanan</h6>
                                    <p><?php echo formatDateID($order['created_at']); ?></p>
                                </div>
                            </div>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Status Pesanan</h6>
                                    <p><?php echo getStatusBadge($order['status']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Status Pembayaran</h6>
                                    <p><?php echo getStatusBadge($order['payment_status']); ?></p>
                                </div>
                            </div>
                            
                            <!-- Order Items -->
                            <h6 class="mb-3">Detail Produk</h6>
                            <div class="table-responsive mb-4">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Produk</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Jumlah</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order_items as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="uploads/products/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                                             alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                             class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                                <td><?php echo formatPriceIDR($item['price']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><strong><?php echo formatPriceIDR($item['price'] * $item['quantity']); ?></strong></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Shipping Information -->
                            <?php if ($shipping_details): ?>
                                <h6 class="mb-3">Informasi Pengiriman</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Metode Pengiriman:</strong><br>
                                        <?php echo htmlspecialchars($shipping_details['shipping_method_name']); ?></p>
                                        <p><strong>Nama Penerima:</strong><br>
                                        <?php echo htmlspecialchars($shipping_details['recipient_name']); ?></p>
                                        <p><strong>Telepon:</strong><br>
                                        <?php echo htmlspecialchars($shipping_details['recipient_phone']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Alamat Pengiriman:</strong><br>
                                        <?php echo nl2br(htmlspecialchars($shipping_details['shipping_address'])); ?></p>
                                        <?php if ($shipping_details['estimated_delivery_date']): ?>
                                            <p><strong>Estimasi Pengiriman:</strong><br>
                                            <?php echo formatDateID($shipping_details['estimated_delivery_date']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($shipping_details['tracking_number']): ?>
                                            <p><strong>Nomor Resi:</strong><br>
                                            <code><?php echo htmlspecialchars($shipping_details['tracking_number']); ?></code>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Payment Information -->
                            <?php if ($payment_details): ?>
                                <h6 class="mb-3">Informasi Pembayaran</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <p><strong>Metode Pembayaran:</strong><br>
                                        <?php echo htmlspecialchars($payment_details['payment_method_name']); ?></p>
                                        <p><strong>Total Pembayaran:</strong><br>
                                        <span class="text-primary fs-5"><?php echo formatPriceIDR($payment_details['amount']); ?></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <?php if ($payment_details['transaction_id']): ?>
                                            <p><strong>ID Transaksi:</strong><br>
                                            <code><?php echo htmlspecialchars($payment_details['transaction_id']); ?></code>
                                        </p>
                                        <?php endif; ?>
                                        <?php if ($payment_details['paid_at']): ?>
                                            <p><strong>Tanggal Pembayaran:</strong><br>
                                            <?php echo formatDateID($payment_details['paid_at']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <!-- Tracking History -->
                            <?php if (!empty($tracking_history)): ?>
                                <h6 class="mb-3">Riwayat Pelacakan</h6>
                                <div class="timeline">
                                    <?php foreach ($tracking_history as $index => $track): ?>
                                        <div class="timeline-item <?php echo $index === 0 ? 'active' : ''; ?>">
                                            <div class="timeline-marker"></div>
                                            <div class="timeline-content">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?php echo getStatusBadge($track['status']); ?></strong>
                                                        <p class="mb-1"><?php echo htmlspecialchars($track['description']); ?></p>
                                                        <?php if ($track['location']): ?>
                                                            <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($track['location']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted"><?php echo formatDateID($track['timestamp']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Order Summary -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="fas fa-receipt me-2"></i>Ringkasan Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?php echo formatPriceIDR($order['total_amount']); ?></span>
                            </div>
                            <?php if ($shipping_details): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Biaya Pengiriman:</span>
                                    <span><?php echo formatPriceIDR($shipping_details['shipping_cost'] ?? 0); ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($payment_details && $payment_details['processing_fee'] > 0): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Biaya Admin:</span>
                                    <span><?php echo formatPriceIDR($payment_details['processing_fee']); ?></span>
                                </div>
                            <?php endif; ?>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong class="text-primary fs-5"><?php echo formatPriceIDR($order['total_amount']); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="user_orders.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>Lihat Semua Pesanan
                            </a>
                            <a href="index.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Lanjut Belanja
                            </a>
                            <button onclick="window.print()" class="btn btn-outline-secondary">
                                <i class="fas fa-print me-2"></i>Cetak Pesanan
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Customer Support -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="mb-3"><i class="fas fa-headset me-2"></i>Butuh Bantuan?</h6>
                        <p class="small text-muted mb-2">Jika Anda memiliki pertanyaan tentang pesanan Anda, hubungi kami:</p>
                        <ul class="list-unstyled small">
                            <li><i class="fas fa-phone me-2"></i>WhatsApp: 081260952112</li>
                            <li><i class="fas fa-envelope me-2"></i>Email: ict.jinarakkhita@gmail.com</li>
                            <li><i class="fas fa-clock me-2"></i>Senin - Sabtu: 09:00 - 18:00</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -25px;
    top: 20px;
    width: 2px;
    height: calc(100% + 10px);
    background: #dee2e6;
}

.timeline-item:last-child:before {
    display: none;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #dee2e6;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-item.active .timeline-marker {
    background: #28a745;
    box-shadow: 0 0 0 2px #28a745;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #dee2e6;
}

.timeline-item.active .timeline-content {
    border-left-color: #28a745;
    background: #d4edda;
}

@media print {
    .btn, .card-header {
        display: none !important;
    }
    
    .timeline-item:before {
        background: #000 !important;
    }
    
    .timeline-marker {
        background: #000 !important;
        box-shadow: 0 0 0 2px #000 !important;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
