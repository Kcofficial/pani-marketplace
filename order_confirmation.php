<?php
$page_title = 'Konfirmasi Pesanan - Paṇi';
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
                'image' => $item['image']
            ];
        }
    }
    
} catch(PDOException $e) {
    $order = null;
}

// Clear session variables
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>

<?php require_once 'includes/header.php'; ?>

<!-- Order Confirmation Header -->
<section class="py-5 bg-success text-white">
    <div class="container text-center">
        <i class="fas fa-check-circle fa-4x mb-3"></i>
        <h1 class="display-4 fw-bold mb-3">Order Confirmed!</h1>
        <p class="lead">Thank you for your purchase. Your order has been received and is being processed.</p>
    </div>
</section>

<!-- Order Details -->
<section class="py-5">
    <div class="container">
        <?php if ($order): ?>
            <div class="row">
                <!-- Order Information -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Information</h5>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Order Number:</strong> #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                                    <p class="mb-2"><strong>Order Date:</strong> <?php echo date('F j, Y, g:i a', strtotime($order['created_at'])); ?></p>
                                    <p class="mb-2"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong>Order Status:</strong> 
                                        <span class="badge bg-warning"><?php echo ucfirst($order['status']); ?></span>
                                    </p>
                                    <p class="mb-2"><strong>Shipping Address:</strong><br><?php echo htmlspecialchars($order['shipping_address']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Items</h5>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($order['items'] as $item): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="uploads/products/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                         class="rounded-3 me-3" 
                                                         style="width: 50px; height: 50px; object-fit: cover;">
                                                    <div>
                                                        <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo formatPrice($item['price']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <h6>What's Next?</h6>
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-envelope text-primary me-2"></i>
                                            You'll receive an order confirmation email shortly
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-box text-primary me-2"></i>
                                            We'll process your order within 24 hours
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-shipping-fast text-primary me-2"></i>
                                            You'll receive tracking information once shipped
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-phone text-primary me-2"></i>
                                            Contact us if you have any questions
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6>Order Summary</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span><?php echo formatPrice($order['total_amount'] * 0.9); ?></span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Shipping:</span>
                                                <span>FREE</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Tax:</span>
                                                <span><?php echo formatPrice($order['total_amount'] * 0.1); ?></span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Total:</strong>
                                                <strong class="text-primary"><?php echo formatPrice($order['total_amount']); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body text-center">
                            <h5 class="card-title mb-3">Need Help?</h5>
                            <p class="text-muted mb-4">Our customer service team is here to help you with any questions about your order.</p>
                            
                            <div class="d-grid gap-2">
                                <a href="contact.php" class="btn btn-outline-primary">
                                    <i class="fas fa-envelope me-2"></i>Contact Support
                                </a>
                                <a href="tel:+1234567890" class="btn btn-primary">
                                    <i class="fas fa-phone me-2"></i>Call Us
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Track Your Order</h5>
                            <p class="text-muted mb-3">Use your order number to track your package status.</p>
                            <div class="input-group">
                                <input type="text" class="form-control" value="#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?>" readonly>
                                <button class="btn btn-outline-primary" type="button">Track</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Continue Shopping</h5>
                            <p class="text-muted mb-4">Check out our latest products and special offers.</p>
                            <a href="shop.php" class="btn btn-primary w-100">
                                <i class="fas fa-shopping-bag me-2"></i>Shop Now
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <h4>Order Not Found</h4>
                <p>We couldn't find your order information. Please contact customer support.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Newsletter Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="mb-3">Subscribe for Updates</h3>
                <p>Get notified about new products, special offers, and order updates.</p>
            </div>
            <div class="col-md-6">
                <form class="d-flex gap-2">
                    <input type="email" class="form-control" placeholder="Enter your email" required>
                    <button type="submit" class="btn btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
