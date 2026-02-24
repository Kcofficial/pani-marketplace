<?php
$page_title = 'Checkout - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=checkout.php');
}

// Check if cart is not empty
$cart_items = getCartItems();
if (empty($cart_items)) {
    redirect('cart.php');
}

// Initialize variables
$errors = [];
$success = '';
$shipping_methods = [];
$payment_methods = [];
$selected_shipping = null;
$selected_payment = null;
$total_amount = 0;
$shipping_cost = 0;
$payment_fee = 0;

// Get shipping methods and payment methods from database
try {
    $conn = getDBConnection();
    
    // Get shipping methods
    $stmt = $conn->prepare("SELECT * FROM shipping_methods WHERE is_active = 1 ORDER BY cost ASC");
    $stmt->execute();
    $shipping_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get payment methods
    $stmt = $conn->prepare("SELECT * FROM payment_methods WHERE is_active = 1 ORDER BY processing_fee ASC");
    $stmt->execute();
    $payment_methods = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errors[] = "Error loading checkout data: " . $e->getMessage();
}

// Calculate cart total
foreach ($cart_items as $item) {
    $total_amount += $item['price'] * $item['quantity'];
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $shipping_method_id = isset($_POST['shipping_method']) ? (int)$_POST['shipping_method'] : 0;
    $payment_method_id = isset($_POST['payment_method']) ? (int)$_POST['payment_method'] : 0;
    $recipient_name = isset($_POST['recipient_name']) ? cleanInput($_POST['recipient_name']) : '';
    $recipient_phone = isset($_POST['recipient_phone']) ? cleanInput($_POST['recipient_phone']) : '';
    $shipping_address = isset($_POST['shipping_address']) ? cleanInput($_POST['shipping_address']) : '';
    $notes = isset($_POST['notes']) ? cleanInput($_POST['notes']) : '';
    
    // Validate form data
    if (empty($shipping_method_id)) {
        $errors[] = 'Pilih metode pengiriman';
    }
    
    if (empty($payment_method_id)) {
        $errors[] = 'Pilih metode pembayaran';
    }
    
    if (empty($recipient_name)) {
        $errors[] = 'Nama penerima wajib diisi';
    }
    
    if (empty($recipient_phone)) {
        $errors[] = 'Nomor telepon wajib diisi';
    }
    
    if (empty($shipping_address)) {
        $errors[] = 'Alamat pengiriman wajib diisi';
    }
    
    // Get selected shipping and payment methods
    $selected_shipping = null;
    $selected_payment = null;
    
    foreach ($shipping_methods as $method) {
        if ($method['id'] === $shipping_method_id) {
            $selected_shipping = $method;
            break;
        }
    }
    
    foreach ($payment_methods as $method) {
        if ($method['id'] === $payment_method_id) {
            $selected_payment = $method;
            break;
        }
    }
    
    if (!$selected_shipping || !$selected_payment) {
        $errors[] = 'Metode pengiriman atau pembayaran tidak valid';
    }
    
    // If no errors, process order
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            $conn->beginTransaction();
            
            // Calculate final amounts
            $shipping_cost = $selected_shipping['cost'];
            $payment_fee = $selected_payment['processing_fee'];
            $final_total = $total_amount + $shipping_cost + $payment_fee;
            
            // Create order
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method, payment_status) VALUES (?, ?, 'pending', ?, ?, 'pending')");
            $stmt->execute([$_SESSION['user_id'], $final_total, $shipping_address, $selected_payment['name']]);
            $order_id = $conn->lastInsertId();
            
            // Create order items
            foreach ($cart_items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Create shipping details
            $estimated_delivery = date('Y-m-d', strtotime('+' . $selected_shipping['estimated_delivery_days'] . ' days'));
            $stmt = $conn->prepare("INSERT INTO shipping_details (order_id, shipping_method_id, shipping_address, recipient_name, recipient_phone, notes, estimated_delivery_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$order_id, $shipping_method_id, $shipping_address, $recipient_name, $recipient_phone, $notes, $estimated_delivery]);
            
            // Create payment details
            $stmt = $conn->prepare("INSERT INTO payment_details (order_id, payment_method_id, amount, currency) VALUES (?, ?, ?, 'IDR')");
            $stmt->execute([$order_id, $payment_method_id, $final_total]);
            
            // Create initial tracking entry
            $stmt = $conn->prepare("INSERT INTO order_tracking (order_id, status, description, updated_by) VALUES (?, 'order_placed', 'Pesanan berhasil dibuat', ?)");
            $stmt->execute([$order_id, $_SESSION['user_id']]);
            
            // Create seller orders for each seller
            $seller_orders = [];
            foreach ($cart_items as $item) {
                $seller_id = $item['seller_id'];
                if (!isset($seller_orders[$seller_id])) {
                    $seller_orders[$seller_id] = [];
                }
                $seller_orders[$seller_id][] = $item;
            }
            
            foreach ($seller_orders as $seller_id => $items) {
                $stmt = $conn->prepare("INSERT INTO seller_orders (order_id, seller_id, status) VALUES (?, ?, 'new')");
                $stmt->execute([$order_id, $seller_id]);
            }
            
            // Clear cart
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $conn->commit();
            
            // Redirect to order confirmation
            redirect('order_confirmation.php?order_id=' . $order_id);
            
        } catch (PDOException $e) {
            $conn->rollBack();
            $errors[] = "Error processing order: " . $e->getMessage();
        }
    }
}
$cart_items = [];
$subtotal = 0;
$shipping = 0;
$tax = 0;
$total = 0;

try {
    $conn = getDBConnection();
    
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image 
                               FROM cart c 
                               LEFT JOIN products p ON c.product_id = p.id 
                               WHERE c.user_id = ? AND p.status = 'active'");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            $product_ids = array_keys($_SESSION['cart']);
            $placeholders = str_repeat('?,', count($product_ids) - 1) . '?';
            
            $stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'");
            $stmt->execute($product_ids);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($products as $product) {
                $cart_items[] = [
                    'product_id' => $product['id'],
                    'quantity' => $_SESSION['cart'][$product['id']],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image']
                ];
            }
        }
    }
    
    // Calculate totals
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $shipping = $subtotal > 50 ? 0 : 10;
    $tax = $subtotal * 0.1;
    $total = $subtotal + $shipping + $tax;
    
} catch(PDOException $e) {
    $cart_items = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate form data
    $required_fields = ['first_name', 'last_name', 'email', 'phone', 'address', 'city', 'state', 'zip_code', 'payment_method'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = 'This field is required';
        }
    }
    
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            $conn->beginTransaction();
            
            // Create order
            $user_id = isLoggedIn() ? $_SESSION['user_id'] : null;
            $order_data = [
                'user_id' => $user_id,
                'total_amount' => $total,
                'status' => 'pending',
                'shipping_address' => $_POST['address'] . ', ' . $_POST['city'] . ', ' . $_POST['state'] . ' ' . $_POST['zip_code'],
                'payment_method' => $_POST['payment_method'],
                'payment_status' => 'pending'
            ];
            
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method, payment_status) 
                                   VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $order_data['user_id'],
                $order_data['total_amount'],
                $order_data['status'],
                $order_data['shipping_address'],
                $order_data['payment_method'],
                $order_data['payment_status']
            ]);
            
            $order_id = $conn->lastInsertId();
            
            // Create order items
            foreach ($cart_items as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // Update product stock
                $stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // Clear cart
            if (isLoggedIn()) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
            } else {
                unset($_SESSION['cart']);
            }
            
            $conn->commit();
            
            // Redirect to order confirmation
            $_SESSION['order_success'] = true;
            $_SESSION['order_id'] = $order_id;
            header('Location: order_confirmation.php');
            exit();
            
        } catch(PDOException $e) {
            $conn->rollBack();
            $errors['database'] = 'Order processing failed. Please try again.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Checkout Header -->
<section class="py-4 bg-light">
    <div class="container">
        <h1 class="mb-0">Checkout</h1>
        <p class="text-muted mb-0">Complete your order details</p>
    </div>
</section>

<!-- Checkout Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <form method="POST" id="checkout-form">
                    <!-- Billing Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-user me-2"></i>Billing Information
                            </h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
                                    <?php if (isset($errors['first_name'])): ?>
                                        <div class="text-danger small"><?php echo $errors['first_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
                                    <?php if (isset($errors['last_name'])): ?>
                                        <div class="text-danger small"><?php echo $errors['last_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? (isLoggedIn() ? $_SESSION['user_email'] : '')); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="text-danger small"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="text-danger small"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Shipping Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-shipping-fast me-2"></i>Shipping Information
                            </h5>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Street Address</label>
                                <input type="text" class="form-control" id="address" name="address" 
                                       value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                                <?php if (isset($errors['address'])): ?>
                                    <div class="text-danger small"><?php echo $errors['address']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                                    <?php if (isset($errors['city'])): ?>
                                        <div class="text-danger small"><?php echo $errors['city']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State</label>
                                    <input type="text" class="form-control" id="state" name="state" 
                                           value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>" required>
                                    <?php if (isset($errors['state'])): ?>
                                        <div class="text-danger small"><?php echo $errors['state']; ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="zip_code" class="form-label">ZIP Code</label>
                                    <input type="text" class="form-control" id="zip_code" name="zip_code" 
                                           value="<?php echo htmlspecialchars($_POST['zip_code'] ?? ''); ?>" required>
                                    <?php if (isset($errors['zip_code'])): ?>
                                        <div class="text-danger small"><?php echo $errors['zip_code']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-credit-card me-2"></i>Payment Method
                            </h5>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" required>
                                    <label class="form-check-label" for="cod">
                                        <strong>Cash on Delivery</strong>
                                        <p class="text-muted small mb-0">Pay when you receive your order</p>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" required>
                                    <label class="form-check-label" for="card">
                                        <strong>Credit/Debit Card</strong>
                                        <p class="text-muted small mb-0">Secure payment via card</p>
                                    </label>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal" required>
                                    <label class="form-check-label" for="paypal">
                                        <strong>PayPal</strong>
                                        <p class="text-muted small mb-0">Pay with your PayPal account</p>
                                    </label>
                                </div>
                            </div>
                            
                            <?php if (isset($errors['payment_method'])): ?>
                                <div class="text-danger small"><?php echo $errors['payment_method']; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (isset($errors['database'])): ?>
                        <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                    <?php endif; ?>
                    
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-lock me-2"></i>Place Order
                    </button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Order Summary</h5>
                        
                        <!-- Order Items -->
                        <div class="mb-4">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                                </div>
                                <span><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <hr>
                        
                        <!-- Price Breakdown -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span><?php echo formatPrice($subtotal); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span><?php echo $shipping == 0 ? 'FREE' : formatPrice($shipping); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax</span>
                                <span><?php echo formatPrice($tax); ?></span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total</h5>
                            <h5 class="text-primary"><?php echo formatPrice($total); ?></h5>
                        </div>
                        
                        <div class="alert alert-success small">
                            <i class="fas fa-shield-alt me-2"></i>
                            Your payment information is secure and encrypted
                        </div>
                        
                        <div class="text-center">
                            <small class="text-muted">
                                By placing this order, you agree to our 
                                <a href="terms.php" class="text-decoration-none">Terms of Service</a> and 
                                <a href="privacy.php" class="text-decoration-none">Privacy Policy</a>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Form validation
    $('#checkout-form').on('submit', function(e) {
        var isValid = true;
        
        // Check if payment method is selected
        if (!$('input[name="payment_method"]:checked').val()) {
            isValid = false;
            alert('Please select a payment method');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Show/hide card details based on payment method
    $('input[name="payment_method"]').on('change', function() {
        if ($(this).val() === 'card') {
            // You could show card details form here
        }
    });
});
</script>
