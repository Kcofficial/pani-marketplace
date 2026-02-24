<?php
$page_title = 'Pesanan Saya - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=user_orders.php');
}

$orders = [];
$error = '';

try {
    $conn = getDBConnection();
    
    // Get user orders with related data
    $stmt = $conn->prepare("SELECT o.*, 
                                   COUNT(oi.id) as item_count,
                                   sd.tracking_number,
                                   sm.name as shipping_method_name,
                                   pm.name as payment_method_name
                           FROM orders o 
                           LEFT JOIN order_items oi ON o.id = oi.order_id 
                           LEFT JOIN shipping_details sd ON o.id = sd.order_id 
                           LEFT JOIN shipping_methods sm ON sd.shipping_method_id = sm.id 
                           LEFT JOIN payment_details pd ON o.id = pd.order_id 
                           LEFT JOIN payment_methods pm ON pd.payment_method_id = pm.id 
                           WHERE o.user_id = ? 
                           GROUP BY o.id 
                           ORDER BY o.created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error loading orders: " . $e->getMessage();
}

// Function to get status badge
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Menunggu Pembayaran</span>',
        'processing' => '<span class="badge bg-info">Diproses</span>',
        'shipped' => '<span class="badge bg-primary">Dikirim</span>',
        'delivered' => '<span class="badge bg-success">Terkirim</span>',
        'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}

// Function to get payment status badge
function getPaymentStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Menunggu</span>',
        'paid' => '<span class="badge bg-success">Dibayar</span>',
        'failed' => '<span class="badge bg-danger">Gagal</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- User Orders Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Pesanan Saya</h4>
                            <span class="badge bg-light text-dark"><?php echo count($orders); ?> Pesanan</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                <h4>Belum Ada Pesanan</h4>
                                <p class="text-muted">Anda belum memiliki pesanan. Mulai belanja sekarang!</p>
                                <a href="shop.php" class="btn btn-primary">
                                    <i class="fas fa-shopping-cart me-2"></i>Mulai Belanja
                                </a>
                            </div>
                        <?php else: ?>
                            <!-- Filter Tabs -->
                            <ul class="nav nav-tabs mb-4" id="orderTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">
                                        Semua Pesanan
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                                        Menunggu Pembayaran
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab">
                                        Diproses
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped" type="button" role="tab">
                                        Dikirim
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="delivered-tab" data-bs-toggle="tab" data-bs-target="#delivered" type="button" role="tab">
                                        Selesai
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content" id="orderTabContent">
                                <!-- All Orders Tab -->
                                <div class="tab-pane fade show active" id="all" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No. Pesanan</th>
                                                    <th>Tanggal</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Pembayaran</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td>
                                                            <strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo $order['item_count']; ?> item</small>
                                                        </td>
                                                        <td><?php echo formatDateID($order['created_at']); ?></td>
                                                        <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                        <td><?php echo getStatusBadge($order['status']); ?></td>
                                                        <td><?php echo getPaymentStatusBadge($order['payment_status']); ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary">
                                                                    <i class="fas fa-eye"></i> Detail
                                                                </a>
                                                                <?php if ($order['tracking_number']): ?>
                                                                    <button class="btn btn-sm btn-outline-info" 
                                                                            onclick="trackOrder('<?php echo $order['tracking_number']; ?>')">
                                                                        <i class="fas fa-truck"></i> Lacak
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- Pending Orders Tab -->
                                <div class="tab-pane fade" id="pending" role="tabpanel">
                                    <?php 
                                    $pending_orders = array_filter($orders, function($order) {
                                        return $order['status'] === 'pending';
                                    });
                                    ?>
                                    <?php if (empty($pending_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                                            <h5>Tidak Ada Pesanan Menunggu</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No. Pesanan</th>
                                                        <th>Tanggal</th>
                                                        <th>Total</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($pending_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo formatDateID($order['created_at']); ?></td>
                                                            <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                            <td>
                                                                <a href="checkout.php?order_id=<?php echo $order['id']; ?>" 
                                                                   class="btn btn-sm btn-success">
                                                                    <i class="fas fa-credit-card"></i> Bayar Sekarang
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Processing Orders Tab -->
                                <div class="tab-pane fade" id="processing" role="tabpanel">
                                    <?php 
                                    $processing_orders = array_filter($orders, function($order) {
                                        return $order['status'] === 'processing';
                                    });
                                    ?>
                                    <?php if (empty($processing_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                            <h5>Tidak Ada Pesanan Diproses</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No. Pesanan</th>
                                                        <th>Tanggal</th>
                                                        <th>Total</th>
                                                        <th>Metode Pengiriman</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($processing_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo formatDateID($order['created_at']); ?></td>
                                                            <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($order['shipping_method_name'] ?? '-'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Shipped Orders Tab -->
                                <div class="tab-pane fade" id="shipped" role="tabpanel">
                                    <?php 
                                    $shipped_orders = array_filter($orders, function($order) {
                                        return $order['status'] === 'shipped';
                                    });
                                    ?>
                                    <?php if (empty($shipped_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                                            <h5>Tidak Ada Pesanan Dikirim</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No. Pesanan</th>
                                                        <th>Tanggal</th>
                                                        <th>No. Resi</th>
                                                        <th>Kurir</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($shipped_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo formatDateID($order['created_at']); ?></td>
                                                            <td>
                                                                <code><?php echo htmlspecialchars($order['tracking_number'] ?? '-'); ?></code>
                                                            </td>
                                                            <td><?php echo htmlspecialchars($order['shipping_method_name'] ?? '-'); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-info" 
                                                                        onclick="trackOrder('<?php echo $order['tracking_number']; ?>')">
                                                                    <i class="fas fa-search-location"></i> Lacak
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Delivered Orders Tab -->
                                <div class="tab-pane fade" id="delivered" role="tabpanel">
                                    <?php 
                                    $delivered_orders = array_filter($orders, function($order) {
                                        return $order['status'] === 'delivered';
                                    });
                                    ?>
                                    <?php if (empty($delivered_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                            <h5>Belum Ada Pesanan Selesai</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No. Pesanan</th>
                                                        <th>Tanggal</th>
                                                        <th>Total</th>
                                                        <th>Metode Pembayaran</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($delivered_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo formatDateID($order['created_at']); ?></td>
                                                            <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($order['payment_method_name'] ?? '-'); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-warning" 
                                                                        onclick="reorderItems(<?php echo $order['id']; ?>)">
                                                                    <i class="fas fa-redo"></i> Beli Lagi
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Tracking Modal -->
<div class="modal fade" id="trackingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lacak Pengiriman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="trackingContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat informasi pelacakan...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function trackOrder(trackingNumber) {
    const modal = new bootstrap.Modal(document.getElementById('trackingModal'));
    const content = document.getElementById('trackingContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat informasi pelacakan...</p>
        </div>
    `;
    
    modal.show();
    
    // Simulate tracking API call
    setTimeout(() => {
        content.innerHTML = `
            <div class="tracking-info">
                <div class="alert alert-info">
                    <strong>Nomor Resi:</strong> <code>${trackingNumber}</code>
                </div>
                
                <div class="timeline">
                    <div class="timeline-item active">
                        <div class="timeline-marker"></div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong>Paket Sedang Dikirim</strong>
                                                                    <p class="mb-1">Paket Anda dalam perjalanan ke alamat tujuan</p>
                                                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Jakarta Pusat</small>
                                                                </div>
                                                                <small class="text-muted">Hari ini, 14:30</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="timeline-item">
                                                        <div class="timeline-marker"></div>
                                                        <div class="timeline-content">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong>Paket Diproses</strong>
                                                                    <p class="mb-1">Paket telah diproses di gudang</p>
                                                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Jakarta Utara</small>
                                                                </div>
                                                                <small class="text-muted">Kemarin, 10:15</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="timeline-item">
                                                        <div class="timeline-marker"></div>
                                                        <div class="timeline-content">
                                                            <div class="d-flex justify-content-between align-items-start">
                                                                <div>
                                                                    <strong>Paket Diterima</strong>
                                                                    <p class="mb-1">Paket telah diterima dari pengirim</p>
                                                                    <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>Tangerang</small>
                                                                </div>
                                                                <small class="text-muted">2 hari lalu, 16:45</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        `;
                                    }, 1500);
                                }
                                
                                function reorderItems(orderId) {
                                    if (confirm('Apakah Anda ingin membeli kembali item dari pesanan ini?')) {
                                        window.location.href = `reorder.php?order_id=${orderId}`;
                                    }
                                }
                                </script>
                                
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
                                    background: #007bff;
                                    box-shadow: 0 0 0 2px #007bff;
                                }
                                
                                .timeline-content {
                                    background: #f8f9fa;
                                    padding: 15px;
                                    border-radius: 8px;
                                    border-left: 3px solid #dee2e6;
                                }
                                
                                .timeline-item.active .timeline-content {
                                    border-left-color: #007bff;
                                    background: #e7f3ff;
                                }
                                </style>
                                
                                <?php require_once 'includes/footer.php'; ?>
