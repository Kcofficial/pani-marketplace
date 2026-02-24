<?php
$page_title = 'Test Purchase Tracking - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Check if user is logged in as admin for testing
if (!isLoggedIn() || $_SESSION['user_role'] !== 'admin') {
    redirect('login.php');
}

$message = '';
$error = '';

// Handle test actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_action'])) {
    try {
        $conn = getDBConnection();
        $conn->beginTransaction();
        
        switch ($_POST['test_action']) {
            case 'create_test_order':
                // Create a test order with realistic data
                $user_id = 3; // John Doe user
                $total_amount = 150000.00;
                
                // Create order
                $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method, payment_status) VALUES (?, ?, 'pending', 'Jl. Test No. 123, Jakarta', 'Bank Transfer', 'pending')");
                $stmt->execute([$user_id, $total_amount]);
                $order_id = $conn->lastInsertId();
                
                // Add order items
                $test_items = [
                    ['product_id' => 1, 'quantity' => 1, 'price' => 99999.00],
                    ['product_id' => 3, 'quantity' => 2, 'price' => 29999.00]
                ];
                
                foreach ($test_items as $item) {
                    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                }
                
                // Create shipping details
                $stmt = $conn->prepare("INSERT INTO shipping_details (order_id, shipping_method_id, shipping_address, recipient_name, recipient_phone, estimated_delivery_date) VALUES (?, 1, 'Jl. Test No. 123, Jakarta', 'Test User', '08123456789', DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY))");
                $stmt->execute([$order_id]);
                
                // Create payment details
                $stmt = $conn->prepare("INSERT INTO payment_details (order_id, payment_method_id, amount, currency) VALUES (?, 1, ?, 'IDR')");
                $stmt->execute([$order_id, $total_amount]);
                
                // Create tracking entries
                $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description, updated_by) VALUES (?, 'order_placed', 'Pesanan berhasil dibuat', ?)");
                $stmt->execute([$order_id, $_SESSION['user_id']]);
                
                // Create seller order
                $stmt = $conn->prepare("INSERT INTO seller_orders (order_id, seller_id, status) VALUES (?, 2, 'new')");
                $stmt->execute([$order_id]);
                
                $message = "Test order #{$order_id} created successfully!";
                break;
                
            case 'simulate_payment':
                $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
                if ($order_id > 0) {
                    // Update payment status
                    $stmt = $conn->prepare("UPDATE payment_details SET paid_at = CURRENT_TIMESTAMP, transaction_id = 'TEST_TXN_{$order_id}' WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    
                    $stmt = $conn->prepare("UPDATE orders SET payment_status = 'paid' WHERE id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Add tracking entry
                    $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description, updated_by) VALUES (?, 'payment_confirmed', 'Pembayaran berhasil dikonfirmasi', ?)");
                    $stmt->execute([$order_id, $_SESSION['user_id']]);
                    
                    $message = "Payment simulated for order #{$order_id}";
                }
                break;
                
            case 'simulate_shipping':
                $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
                if ($order_id > 0) {
                    $tracking_number = 'TEST' . str_pad($order_id, 8, '0', STR_PAD_LEFT);
                    
                    // Update shipping details
                    $stmt = $conn->prepare("UPDATE shipping_details SET tracking_number = ?, carrier_name = 'Test Courier' WHERE order_id = ?");
                    $stmt->execute([$tracking_number, $order_id]);
                    
                    // Update order status
                    $stmt = $conn->prepare("UPDATE orders SET status = 'shipped' WHERE id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Update seller order
                    $stmt = $conn->prepare("UPDATE seller_orders SET status = 'shipped', shipped_at = CURRENT_TIMESTAMP WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Add tracking entries
                    $tracking_steps = [
                        ['processing', 'Pesanan sedang diproses'],
                        ['shipped', 'Pesanan telah dikirim'],
                        ['in_transit', 'Pesanan dalam perjalanan'],
                        ['out_for_delivery', 'Pesanan akan segera tiba']
                    ];
                    
                    foreach ($tracking_steps as $step) {
                        $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description, location, updated_by) VALUES (?, ?, ?, 'Jakarta', ?)");
                        $stmt->execute([$order_id, $step[0], $step[1], $_SESSION['user_id']]);
                    }
                    
                    $message = "Shipping simulated for order #{$order_id} with tracking number {$tracking_number}";
                }
                break;
                
            case 'simulate_delivery':
                $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
                if ($order_id > 0) {
                    // Update order status
                    $stmt = $conn->prepare("UPDATE orders SET status = 'delivered' WHERE id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Update seller order
                    $stmt = $conn->prepare("UPDATE seller_orders SET status = 'delivered', delivered_at = CURRENT_TIMESTAMP WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Update shipping details
                    $stmt = $conn->prepare("UPDATE shipping_details SET actual_delivery_date = CURRENT_DATE WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    
                    // Add final tracking entry
                    $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description, location, updated_by) VALUES (?, 'delivered', 'Pesanan telah diterima oleh pelanggan', 'Jakarta', ?)");
                    $stmt->execute([$order_id, $_SESSION['user_id']]);
                    
                    $message = "Delivery simulated for order #{$order_id}";
                }
                break;
                
            case 'clear_test_data':
                // Clear all test data (be careful!)
                $stmt = $conn->prepare("DELETE FROM order_tracking WHERE order_id IN (SELECT id FROM orders WHERE user_id = 3)");
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM seller_orders WHERE order_id IN (SELECT id FROM orders WHERE user_id = 3)");
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM payment_details WHERE order_id IN (SELECT id FROM orders WHERE user_id = 3)");
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM shipping_details WHERE order_id IN (SELECT id FROM orders WHERE user_id = 3)");
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE user_id = 3)");
                $stmt->execute();
                
                $stmt = $conn->prepare("DELETE FROM orders WHERE user_id = 3");
                $stmt->execute();
                
                $message = "All test data cleared!";
                break;
        }
        
        $conn->commit();
        
    } catch (PDOException $e) {
        $conn->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Get existing test orders
$test_orders = [];
try {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT o.*, u.name as customer_name, sd.tracking_number 
                           FROM orders o 
                           JOIN users u ON o.user_id = u.id 
                           LEFT JOIN shipping_details sd ON o.id = sd.order_id 
                           WHERE o.user_id = 3 
                           ORDER BY o.created_at DESC");
    $stmt->execute();
    $test_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error loading test orders: " . $e->getMessage();
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Test Purchase Tracking Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-flask me-2"></i>Test Purchase Tracking System</h4>
                        <p class="mb-0 mt-2">Halaman testing untuk simulasi alur pembelian dan pelacakan pesanan</p>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($message): ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <!-- Test Actions -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="test_action" value="create_test_order">
                                    <button type="submit" class="btn btn-primary btn-lg w-100 h-100">
                                        <i class="fas fa-plus-circle me-2"></i>
                                        <div>Buat Test Order</div>
                                        <small>Buat pesanan test baru</small>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-3 mb-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="test_action" value="simulate_payment">
                                    <input type="hidden" name="order_id" value="<?php echo $test_orders[0]['id'] ?? ''; ?>">
                                    <button type="submit" class="btn btn-success btn-lg w-100 h-100" 
                                            <?php echo empty($test_orders) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-credit-card me-2"></i>
                                        <div>Simulasi Pembayaran</div>
                                        <small>Konfirmasi pembayaran</small>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-3 mb-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="test_action" value="simulate_shipping">
                                    <input type="hidden" name="order_id" value="<?php echo $test_orders[0]['id'] ?? ''; ?>">
                                    <button type="submit" class="btn btn-info btn-lg w-100 h-100" 
                                            <?php echo empty($test_orders) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-truck me-2"></i>
                                        <div>Simulasi Pengiriman</div>
                                        <small>Generate nomor resi</small>
                                    </button>
                                </form>
                            </div>
                            <div class="col-md-3 mb-3">
                                <form method="POST" class="h-100">
                                    <input type="hidden" name="test_action" value="simulate_delivery">
                                    <input type="hidden" name="order_id" value="<?php echo $test_orders[0]['id'] ?? ''; ?>">
                                    <button type="submit" class="btn btn-success btn-lg w-100 h-100" 
                                            <?php echo empty($test_orders) ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check-circle me-2"></i>
                                        <div>Simulasi Pengiriman Selesai</div>
                                        <small>Tandai sebagai terkirim</small>
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Test Orders List -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Test Orders</h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($test_orders)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <h5>Belum Ada Test Order</h5>
                                        <p class="text-muted">Klik "Buat Test Order" untuk memulai testing</p>
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>No. Pesanan</th>
                                                    <th>Tanggal</th>
                                                    <th>Total</th>
                                                    <th>Status</th>
                                                    <th>Status Pembayaran</th>
                                                    <th>No. Resi</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($test_orders as $order): ?>
                                                    <tr>
                                                        <td><strong>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                                                        <td><?php echo formatDateID($order['created_at']); ?></td>
                                                        <td><strong><?php echo formatPriceIDR($order['total_amount']); ?></strong></td>
                                                        <td>
                                                            <?php
                                                            $status_badges = [
                                                                'pending' => '<span class="badge bg-warning">Menunggu</span>',
                                                                'processing' => '<span class="badge bg-info">Diproses</span>',
                                                                'shipped' => '<span class="badge bg-primary">Dikirim</span>',
                                                                'delivered' => '<span class="badge bg-success">Terkirim</span>'
                                                            ];
                                                            echo $status_badges[$order['status']] ?? '<span class="badge bg-secondary">' . $order['status'] . '</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php
                                                            $payment_badges = [
                                                                'pending' => '<span class="badge bg-warning">Menunggu</span>',
                                                                'paid' => '<span class="badge bg-success">Dibayar</span>',
                                                                'failed' => '<span class="badge bg-danger">Gagal</span>'
                                                            ];
                                                            echo $payment_badges[$order['payment_status']] ?? '<span class="badge bg-secondary">' . $order['payment_status'] . '</span>';
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($order['tracking_number']): ?>
                                                                <code><?php echo htmlspecialchars($order['tracking_number']); ?></code>
                                                            <?php else: ?>
                                                                <span class="text-muted">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="order_confirmation_new.php?order_id=<?php echo $order['id']; ?>" 
                                                                   class="btn btn-sm btn-outline-primary" target="_blank">
                                                                    <i class="fas fa-eye"></i> View
                                                                </a>
                                                                <a href="user_orders.php" 
                                                                   class="btn btn-sm btn-outline-info" target="_blank">
                                                                    <i class="fas fa-list"></i> Track
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Clear Test Data -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle me-2"></i>Clear Test Data</h6>
                                    <p class="mb-2">Hapus semua data test yang telah dibuat. Ini akan menghapus semua pesanan test dan data terkait.</p>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="test_action" value="clear_test_data">
                                        <button type="submit" class="btn btn-danger btn-sm" 
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus semua data test?')">
                                            <i class="fas fa-trash me-2"></i>Clear All Test Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Testing Instructions -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Testing Instructions</h5>
                            </div>
                            <div class="card-body">
                                <h6>Langkah-langkah Testing:</h6>
                                <ol>
                                    <li><strong>Buat Test Order</strong> - Membuat pesanan test dengan data realistis</li>
                                    <li><strong>Simulasi Pembayaran</strong> - Mengkonfirmasi pembayaran pesanan</li>
                                    <li><strong>Simulasi Pengiriman</strong> - Generate nomor resi dan update status pengiriman</li>
                                    <li><strong>Simulasi Pengiriman Selesai</strong> - Menandai pesanan sebagai terkirim</li>
                                </ol>
                                
                                <h6 class="mt-3">Fitur yang Diuji:</h6>
                                <ul>
                                    <li>✅ Order creation and management</li>
                                    <li>✅ Payment processing and tracking</li>
                                    <li>✅ Shipping method selection and tracking</li>
                                    <li>✅ Order status updates and notifications</li>
                                    <li>✅ Seller order management</li>
                                    <li>✅ Real-time tracking system</li>
                                    <li>✅ Indonesian localization</li>
                                    <li>✅ Responsive design</li>
                                </ul>
                                
                                <h6 class="mt-3">Links untuk Testing:</h6>
                                <ul>
                                    <li><a href="user_orders.php" target="_blank">User Order Tracking</a></li>
                                    <li><a href="seller/orders.php" target="_blank">Seller Order Management</a></li>
                                    <li><a href="checkout.php" target="_blank">Checkout Process</a></li>
                                    <li><a href="shop.php" target="_blank">Product Catalog</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
