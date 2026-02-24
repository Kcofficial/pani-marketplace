<?php
$page_title = 'Keranjang - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

$cart_items = [];
$total_amount = 0;

try {
    $conn = getDBConnection();
    
    if (isLoggedIn()) {
        // Logged in user - get cart from database
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
                               FROM cart c 
                               LEFT JOIN products p ON c.product_id = p.id 
                               WHERE c.user_id = ? AND p.status = 'active'");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Guest user - get cart from session
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
                    'image' => $product['image'],
                    'stock' => $product['stock']
                ];
            }
        }
    }
    
    // Calculate total amount
    foreach ($cart_items as $item) {
        $total_amount += $item['price'] * $item['quantity'];
    }
    
} catch(PDOException $e) {
    $cart_items = [];
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Cart Header -->
<section class="py-4 bg-light">
    <div class="container">
        <h1 class="mb-0">Shopping Cart</h1>
        <p class="text-muted mb-0">
            <?php echo count($cart_items); ?> item(s) in your cart
        </p>
    </div>
</section>

<!-- Cart Content -->
<section class="py-5">
    <div class="container">
        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Looks like you haven't added anything to your cart yet.</p>
                <a href="shop.php" class="btn btn-primary btn-lg">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="uploads/products/<?php echo $item['image'] ?? 'placeholder.jpg'; ?>" 
                                                 alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                                 class="rounded-3 me-3" 
                                                 style="width: 80px; height: 80px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <small class="text-muted">Stock: <?php echo $item['stock']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td>
                                        <div class="input-group" style="width: 120px;">
                                            <button class="btn btn-outline-secondary btn-sm quantity-btn" 
                                                    data-action="decrease" 
                                                    data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control form-control-sm text-center quantity-input" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" 
                                                   max="<?php echo $item['stock']; ?>"
                                                   data-product-id="<?php echo $item['product_id']; ?>">
                                            <button class="btn btn-outline-secondary btn-sm quantity-btn" 
                                                    data-action="increase" 
                                                    data-product-id="<?php echo $item['product_id']; ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="fw-bold"><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger remove-from-cart" 
                                                data-product-id="<?php echo $item['product_id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Cart Actions -->
                    <div class="d-flex justify-content-between mt-4">
                        <a href="shop.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                        </a>
                        <button class="btn btn-outline-danger" id="clear-cart">
                            <i class="fas fa-trash me-2"></i>Clear Cart
                        </button>
                    </div>
                </div>
                
                <!-- Cart Summary -->
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Order Summary</h5>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal</span>
                                <span><?php echo formatPrice($total_amount); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Shipping</span>
                                <span id="shipping-cost"><?php echo $total_amount > 50 ? 'FREE' : formatPrice(10); ?></span>
                            </div>
                            
                            <div class="d-flex justify-content-between mb-3">
                                <span>Tax</span>
                                <span><?php echo formatPrice($total_amount * 0.1); ?></span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <h5>Total</h5>
                                <h5 id="total-amount"><?php echo formatPrice($total_amount + ($total_amount > 50 ? 0 : 10) + ($total_amount * 0.1)); ?></h5>
                            </div>
                            
                            <div class="alert alert-info small">
                                <i class="fas fa-info-circle me-2"></i>
                                Free shipping on orders over $50
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-lock me-2"></i>Proceed to Checkout
                            </a>
                            
                            <div class="mt-3 text-center">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Secure Checkout
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Coupon Code -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Coupon Code</h6>
                            <form class="d-flex gap-2">
                                <input type="text" class="form-control form-control-sm" placeholder="Enter code">
                                <button type="submit" class="btn btn-outline-primary btn-sm">Apply</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Quantity buttons
    $('.quantity-btn').on('click', function() {
        var productId = $(this).data('product-id');
        var action = $(this).data('action');
        var input = $('.quantity-input[data-product-id="' + productId + '"]');
        var currentQty = parseInt(input.val());
        var maxQty = parseInt(input.attr('max'));
        
        if (action === 'increase' && currentQty < maxQty) {
            input.val(currentQty + 1);
            updateCartQuantity(productId, currentQty + 1);
        } else if (action === 'decrease' && currentQty > 1) {
            input.val(currentQty - 1);
            updateCartQuantity(productId, currentQty - 1);
        }
    });
    
    // Quantity input change
    $('.quantity-input').on('change', function() {
        var productId = $(this).data('product-id');
        var quantity = parseInt($(this).val());
        var maxQty = parseInt($(this).attr('max'));
        
        if (quantity < 1) quantity = 1;
        if (quantity > maxQty) quantity = maxQty;
        
        $(this).val(quantity);
        updateCartQuantity(productId, quantity);
    });
    
    // Remove from cart
    $('.remove-from-cart').on('click', function() {
        if (confirm('Are you sure you want to remove this item?')) {
            var productId = $(this).data('product-id');
            removeFromCart(productId);
        }
    });
    
    // Clear cart
    $('#clear-cart').on('click', function() {
        if (confirm('Are you sure you want to clear your entire cart?')) {
            clearCart();
        }
    });
    
    function updateCartQuantity(productId, quantity) {
        $.ajax({
            url: 'includes/cart_functions.php',
            type: 'POST',
            data: {
                action: 'update',
                product_id: productId,
                quantity: quantity
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    }
    
    function removeFromCart(productId) {
        $.ajax({
            url: 'includes/cart_functions.php',
            type: 'POST',
            data: {
                action: 'remove',
                product_id: productId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            }
        });
    }
    
    function clearCart() {
        <?php if (isLoggedIn()): ?>
            $.ajax({
                url: 'includes/cart_functions.php',
                type: 'POST',
                data: { action: 'clear' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        <?php else: ?>
            // For guest users, clear session cart
            location.reload();
        <?php endif; ?>
    }
});
</script>
