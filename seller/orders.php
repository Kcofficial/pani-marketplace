<?php
$page_title = 'Kelola Pesanan - Paṇi Marketplace';
require_once '../../includes/functions.php';
require_once '../../includes/id_functions.php';
require_once '../../config/database.php';

// Check if user is logged in and is a seller
if (!isLoggedIn() || $_SESSION['user_role'] !== 'seller') {
    redirect('../../login.php');
}

$orders = [];
$error = '';
$success = '';

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    $action = $_POST['action'];
    $notes = isset($_POST['notes']) ? cleanInput($_POST['notes']) : '';
    $tracking_number = isset($_POST['tracking_number']) ? cleanInput($_POST['tracking_number']) : '';
    
    if ($order_id > 0) {
        try {
            $conn = getDBConnection();
            $conn->beginTransaction();
            
            // Update seller order status
            $status = '';
            $timestamp_field = '';
            
            switch ($action) {
                case 'confirm':
                    $status = 'confirmed';
                    $timestamp_field = 'confirmed_at';
                    break;
                case 'prepare':
                    $status = 'preparing';
                    break;
                case 'ready':
                    $status = 'ready_to_ship';
                    break;
                case 'ship':
                    $status = 'shipped';
                    $timestamp_field = 'shipped_at';
                    break;
                case 'deliver':
                    $status = 'delivered';
                    $timestamp_field = 'delivered_at';
                    break;
                case 'cancel':
                    $status = 'cancelled';
                    break;
            }
            
            if ($status) {
                // Update seller order
                $stmt = $conn->prepare("UPDATE seller_orders SET status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP" . 
                                       ($timestamp_field ? ", $timestamp_field = CURRENT_TIMESTAMP" : "") . 
                                       " WHERE order_id = ? AND seller_id = ?");
                $stmt->execute([$status, $notes, $order_id, $_SESSION['user_id']]);
                
                // Update main order status if all seller orders have the same status
                $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as matched 
                                       FROM seller_orders WHERE order_id = ?");
                $stmt->execute([$status, $order_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['total'] == $result['matched']) {
                    // All seller orders have the same status, update main order
                    $main_status = '';
                    switch ($status) {
                        case 'confirmed':
                            $main_status = 'processing';
                            break;
                        case 'shipped':
                            $main_status = 'shipped';
                            break;
                        case 'delivered':
                            $main_status = 'delivered';
                            break;
                        case 'cancelled':
                            $main_status = 'cancelled';
                            break;
                    }
                    
                    if ($main_status) {
                        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                        $stmt->execute([$main_status, $order_id]);
                    }
                }
                
                // Add tracking entry
                $description = '';
                switch ($action) {
                    case 'confirm':
                        $description = 'Pesanan dikonfirmasi oleh penjual';
                        break;
                    case 'prepare':
                        $description = 'Pesanan sedang disiapkan';
                        break;
                    case 'ready':
                        $description = 'Pesanan siap dikirim';
                        break;
                    case 'ship':
                        $description = 'Pesanan telah dikirim';
                        break;
                    case 'deliver':
                        $description = 'Pesanan telah diterima';
                        break;
                    case 'cancel':
                        $description = 'Pesanan dibatalkan oleh penjual';
                        break;
                }
                
                $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description, updated_by) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $status, $description, $_SESSION['user_id']]);
                
                // Update shipping details if tracking number provided
                if ($action === 'ship' && $tracking_number) {
                    $stmt = $conn->prepare("UPDATE shipping_details SET tracking_number = ?, carrier_name = ? WHERE order_id = ?");
                    $stmt->execute([$tracking_number, 'Standard Courier', $order_id]);
                }
                
                $conn->commit();
                $success = 'Status pesanan berhasil diperbarui!';
            }
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $error = "Error updating order: " . $e->getMessage();
        }
    }
}

try {
    $conn = getDBConnection();
    
    // Get seller orders with related data
    $stmt = $conn->prepare("SELECT so.*, o.user_id, o.total_amount, o.created_at as order_date,
                                   o.status as main_status, o.payment_status,
                                   u.name as customer_name, u.email as customer_email,
                                   COUNT(oi.id) as item_count,
                                   sd.tracking_number,
                                   sm.name as shipping_method_name
                           FROM seller_orders so 
                           JOIN orders o ON so.order_id = o.id 
                           JOIN users u ON o.user_id = u.id 
                           LEFT JOIN order_items oi ON o.id = oi.order_id AND oi.product_id IN (SELECT id FROM products WHERE seller_id = ?)
                           LEFT JOIN shipping_details sd ON o.id = sd.order_id 
                           LEFT JOIN shipping_methods sm ON sd.shipping_method_id = sm.id 
                           WHERE so.seller_id = ? 
                           GROUP BY so.id 
                           ORDER BY o.created_at DESC");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error loading orders: " . $e->getMessage();
}

// Function to get status badge
function getStatusBadge($status) {
    $badges = [
        'new' => '<span class="badge bg-secondary">Pesanan Baru</span>',
        'confirmed' => '<span class="badge bg-info">Dikonfirmasi</span>',
        'preparing' => '<span class="badge bg-warning">Disiapkan</span>',
        'ready_to_ship' => '<span class="badge bg-primary">Siap Dikirim</span>',
        'shipped' => '<span class="badge bg-success">Dikirim</span>',
        'delivered' => '<span class="badge bg-success">Terkirim</span>',
        'cancelled' => '<span class="badge bg-danger">Dibatalkan</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
}
?>

<?php require_once '../includes/header.php'; ?>

<!-- Seller Orders Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0"><i class="fas fa-shopping-bag me-2"></i>Kelola Pesanan</h4>
                            <span class="badge bg-light text-dark"><?php echo count($orders); ?> Pesanan</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <?php if (empty($orders)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                                <h4>Belum Ada Pesanan</h4>
                                <p class="text-muted">Anda belum memiliki pesanan dari pelanggan.</p>
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
                                    <button class="nav-link" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab">
                                        Pesanan Baru
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="preparing-tab" data-bs-toggle="tab" data-bs-target="#preparing" type="button" role="tab">
                                        Disiapkan
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="shipped-tab" data-bs-toggle="tab" data-bs-target="#shipped" type="button" role="tab">
                                        Dikirim
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
                                                    <th>Pelanggan</th>
                                                    <th>Tanggal</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($orders as $order): ?>
                                                    <tr>
                                                        <td>
                                                            <strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong>
                                                            <br>
                                                            <small class="text-muted"><?php echo $order['item_count']; ?> item</small>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                                            </div>
                                                        </td>
                                                        <td><?php echo formatDateID($order['order_date']); ?></td>
                                                        <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                        <td><?php echo getStatusBadge($order['status']); ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button class="btn btn-sm btn-outline-primary" 
                                                                        onclick="viewOrderDetails(<?php echo $order['order_id']; ?>)">
                                                                    <i class="fas fa-eye"></i> Detail
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-info" 
                                                                        onclick="updateOrderStatus(<?php echo $order['order_id']; ?>, '<?php echo $order['status']; ?>')">
                                                                    <i class="fas fa-edit"></i> Update
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <!-- New Orders Tab -->
                                <div class="tab-pane fade" id="new" role="tabpanel">
                                    <?php 
                                    $new_orders = array_filter($orders, function($order) {
                                        return $order['status'] === 'new';
                                    });
                                    ?>
                                    <?php if (empty($new_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5>Tidak Ada Pesanan Baru</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No. Pesanan</th>
                                                        <th>Pelanggan</th>
                                                        <th>Tanggal</th>
                                                        <th>Total</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($new_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                            <td><?php echo formatDateID($order['order_date']); ?></td>
                                                            <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-success" 
                                                                        onclick="quickConfirm(<?php echo $order['order_id']; ?>)">
                                                                    <i class="fas fa-check"></i> Konfirmasi
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Preparing Orders Tab -->
                                <div class="tab-pane fade" id="preparing" role="tabpanel">
                                    <?php 
                                    $preparing_orders = array_filter($orders, function($order) {
                                        return in_array($order['status'], ['confirmed', 'preparing', 'ready_to_ship']);
                                    });
                                    ?>
                                    <?php if (empty($preparing_orders)): ?>
                                        <div class="text-center py-4">
                                            <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                                            <h5>Tidak Ada Pesanan Disiapkan</h5>
                                        </div>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>No. Pesanan</th>
                                                        <th>Pelanggan</th>
                                                        <th>Status</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($preparing_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                            <td><?php echo getStatusBadge($order['status']); ?></td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary" 
                                                                        onclick="quickShip(<?php echo $order['order_id']; ?>)">
                                                                    <i class="fas fa-truck"></i> Kirim
                                                                </button>
                                                            </td>
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
                                        return in_array($order['status'], ['shipped', 'delivered']);
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
                                                        <th>Pelanggan</th>
                                                        <th>No. Resi</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($shipped_orders as $order): ?>
                                                        <tr>
                                                            <td><strong>#<?php echo str_pad($order['order_id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                            <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                                            <td>
                                                                <code><?php echo htmlspecialchars($order['tracking_number'] ?? '-'); ?></code>
                                                            </td>
                                                            <td><?php echo getStatusBadge($order['status']); ?></td>
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

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="orderDetailsContent">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Memuat detail pesanan...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Status Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="updateStatusForm">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="updateOrderId">
                    <input type="hidden" name="action" id="updateAction">
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Catatan (Opsional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Tambahkan catatan untuk pelanggan..."></textarea>
                    </div>
                    
                    <div class="mb-3" id="trackingNumberField" style="display: none;">
                        <label for="tracking_number" class="form-label">Nomor Resi</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number" placeholder="Masukkan nomor resi">
                        <small class="text-muted">Masukkan nomor resi jika pesanan akan dikirim</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    const content = document.getElementById('orderDetailsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Memuat detail pesanan...</p>
        </div>
    `;
    
    modal.show();
    
    // Load order details (simulated)
    setTimeout(() => {
        content.innerHTML = `
            <div class="order-details">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>No. Pesanan:</strong> #${orderId.toString().padStart(6, '0')}
                    </div>
                    <div class="col-md-6">
                        <strong>Tanggal:</strong> ${new Date().toLocaleDateString('id-ID')}
                    </div>
                </div>
                
                <h6>Item Pesanan:</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Contoh Produk</td>
                                <td>2</td>
                                <td>Rp 50.000</td>
                                <td>Rp 100.000</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <strong>Alamat Pengiriman:</strong><br>
                        Jl. Contoh No. 123, Jakarta
                    </div>
                    <div class="col-md-6">
                        <strong>Metode Pembayaran:</strong><br>
                        Transfer Bank
                    </div>
                </div>
            </div>
        `;
    }, 1000);
}

function updateOrderStatus(orderId, currentStatus) {
    const modal = new bootstrap.Modal(document.getElementById('updateStatusModal'));
    document.getElementById('updateOrderId').value = orderId;
    
    // Show appropriate options based on current status
    let actionOptions = '';
    
    switch(currentStatus) {
        case 'new':
            actionOptions = `
                <option value="confirm">Konfirmasi Pesanan</option>
                <option value="cancel">Batalkan Pesanan</option>
            `;
            break;
        case 'confirmed':
            actionOptions = `
                <option value="prepare">Mulai Persiapan</option>
                <option value="cancel">Batalkan Pesanan</option>
            `;
            break;
        case 'preparing':
            actionOptions = `
                <option value="ready">Siap untuk Dikirim</option>
                <option value="cancel">Batalkan Pesanan</option>
            `;
            break;
        case 'ready_to_ship':
            actionOptions = `
                <option value="ship">Kirim Pesanan</option>
                <option value="cancel">Batalkan Pesanan</option>
            `;
            break;
        case 'shipped':
            actionOptions = `
                <option value="deliver">Tandai sebagai Terkirim</option>
            `;
            break;
    }
    
    // Update form (simplified for demo)
    document.getElementById('updateAction').value = actionOptions.includes('confirm') ? 'confirm' : 'ship';
    
    // Show tracking number field if shipping
    if (currentStatus === 'ready_to_ship') {
        document.getElementById('trackingNumberField').style.display = 'block';
    } else {
        document.getElementById('trackingNumberField').style.display = 'none';
    }
    
    modal.show();
}

function quickConfirm(orderId) {
    if (confirm('Konfirmasi pesanan ini?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="order_id" value="${orderId}">
            <input type="hidden" name="action" value="confirm">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function quickShip(orderId) {
    const trackingNumber = prompt('Masukkan nomor resi:');
    if (trackingNumber) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="order_id" value="${orderId}">
            <input type="hidden" name="action" value="ship">
            <input type="hidden" name="tracking_number" value="${trackingNumber}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once '../includes/footer.php'; ?>
