<?php
$page_title = 'Dashboard - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();

$user_data = [];
$recent_orders = [];
$order_stats = [];

try {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders
    $stmt = $conn->prepare("SELECT o.*, COUNT(oi.id) as item_count 
                           FROM orders o 
                           LEFT JOIN order_items oi ON o.id = oi.order_id 
                           WHERE o.user_id = ? 
                           GROUP BY o.id 
                           ORDER BY o.created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get order statistics
    $stmt = $conn->prepare("SELECT 
                           COUNT(*) as total_orders,
                           SUM(total_amount) as total_spent,
                           AVG(total_amount) as avg_order_value
                           FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $order_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $user_data = [];
    $recent_orders = [];
    $order_stats = ['total_orders' => 0, 'total_spent' => 0, 'avg_order_value' => 0];
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Dashboard Header -->
<section class="py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-0">Welcome, <?php echo htmlspecialchars($user_data['name']); ?>!</h1>
                <p class="mb-0">Manage your account and view your orders</p>
            </div>
            <div class="col-md-4 text-md-end">
                <div class="d-inline-block text-start">
                    <small>Member since</small><br>
                    <strong><?php echo date('M Y', strtotime($user_data['created_at'])); ?></strong>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Dashboard Content -->
<section class="py-5">
    <div class="container">
        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-gradient-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Orders</h6>
                                <h3 class="mb-0"><?php echo $order_stats['total_orders']; ?></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-shopping-bag fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-gradient-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Total Spent</h6>
                                <h3 class="mb-0"><?php echo formatPrice($order_stats['total_spent'] ?? 0); ?></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-dollar-sign fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 bg-gradient-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-white-50">Avg Order Value</h6>
                                <h3 class="mb-0"><?php echo formatPrice($order_stats['avg_order_value'] ?? 0); ?></h3>
                            </div>
                            <div class="text-white-50">
                                <i class="fas fa-chart-line fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-lg-8 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Orders</h5>
                            <a href="orders.php" class="btn btn-outline-primary btn-sm">View All</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_orders)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h6>No orders yet</h6>
                                <p class="text-muted">Start shopping to see your orders here</p>
                                <a href="shop.php" class="btn btn-primary">Shop Now</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td><?php echo $order['item_count']; ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="col-lg-4 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-2"></i>Edit Profile
                            </a>
                            <a href="orders.php" class="btn btn-outline-primary">
                                <i class="fas fa-list me-2"></i>View All Orders
                            </a>
                            <a href="wishlist.php" class="btn btn-outline-primary">
                                <i class="fas fa-heart me-2"></i>My Wishlist
                            </a>
                            <a href="addresses.php" class="btn btn-outline-primary">
                                <i class="fas fa-map-marker-alt me-2"></i>Manage Addresses
                            </a>
                            <a href="settings.php" class="btn btn-outline-primary">
                                <i class="fas fa-cog me-2"></i>Account Settings
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Account Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small">Email</label>
                            <p class="mb-0"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Phone</label>
                            <p class="mb-0"><?php echo htmlspecialchars($user_data['phone'] ?? 'Not provided'); ?></p>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small">Address</label>
                            <p class="mb-0"><?php echo htmlspecialchars($user_data['address'] ?? 'Not provided'); ?></p>
                        </div>
                        <div>
                            <label class="text-muted small">Account Type</label>
                            <p class="mb-0">
                                <span class="badge bg-primary"><?php echo ucfirst($user_data['role']); ?></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php if (empty($recent_orders)): ?>
                                <div class="text-center py-4">
                                    <p class="text-muted">No recent activity</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recent_orders as $order): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h6>
                                        <p class="text-muted small mb-0">
                                            Placed on <?php echo date('M j, Y', strtotime($order['created_at'])); ?> • 
                                            <?php echo formatPrice($order['total_amount']); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}
?>

<?php require_once 'includes/footer.php'; ?>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
}

.bg-gradient-success {
    background: linear-gradient(135deg, var(--success-color), #45a049);
}

.bg-gradient-info {
    background: linear-gradient(135deg, var(--info-color), #1976d2);
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e0e0e0;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--primary-color);
    border: 2px solid white;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
}
</style>
