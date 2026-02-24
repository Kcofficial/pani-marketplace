<?php
$page_title = 'Seller Dashboard - Paṇi Seller';
require_once '../includes/functions.php';
require_once '../config/database.php';

requireSeller();

// Get seller statistics
$stats = [];
$recent_orders = [];
$my_products = [];

try {
    $conn = getDBConnection();
    $seller_id = $_SESSION['user_id'];
    
    // Get seller statistics
    $stmt = $conn->prepare("SELECT 
                           (SELECT COUNT(*) FROM products WHERE seller_id = ?) as total_products,
                           (SELECT COUNT(*) FROM orders o 
                            JOIN order_items oi ON o.id = oi.order_id 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE p.seller_id = ?) as total_orders,
                           (SELECT COALESCE(SUM(oi.quantity * oi.price), 0) 
                            FROM order_items oi 
                            JOIN products p ON oi.product_id = p.id 
                            WHERE p.seller_id = ?) as total_revenue");
    $stmt->execute([$seller_id, $seller_id, $seller_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent orders for seller's products
    $stmt = $conn->prepare("SELECT o.*, u.name as customer_name, oi.product_id, oi.quantity, oi.price, p.name as product_name
                           FROM orders o
                           JOIN order_items oi ON o.id = oi.order_id
                           JOIN products p ON oi.product_id = p.id
                           LEFT JOIN users u ON o.user_id = u.id
                           WHERE p.seller_id = ?
                           ORDER BY o.created_at DESC LIMIT 10");
    $stmt->execute([$seller_id]);
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get seller's products
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name,
                           (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.product_id = p.id) as total_sold
                           FROM products p
                           LEFT JOIN categories c ON p.category_id = c.id
                           WHERE p.seller_id = ?
                           ORDER BY p.created_at DESC");
    $stmt->execute([$seller_id]);
    $my_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $stats = ['total_products' => 0, 'total_orders' => 0, 'total_revenue' => 0];
    $recent_orders = [];
    $my_products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- MDB Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Seller Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-store me-2"></i>Paṇi Seller
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">My Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="earnings.php">Earnings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop_appearance.php">Shop Appearance</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $_SESSION['user_name']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../index.php"><i class="fas fa-store me-2"></i>View Store</a></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Seller Dashboard -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box me-2"></i>My Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="product_add.php">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="earnings.php">
                                <i class="fas fa-dollar-sign me-2"></i>Earnings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Seller Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="product_add.php" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i>Add Product
                            </a>
                        </div>
                        <button type="button" class="btn btn-outline-success" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_products']); ?></h4>
                                        <p class="mb-0">My Products</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-box fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo number_format($stats['total_orders']); ?></h4>
                                        <p class="mb-0">Total Orders</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo formatPrice($stats['total_revenue']); ?></h4>
                                        <p class="mb-0">Total Revenue</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h4 class="mb-0"><?php echo $stats['total_products'] > 0 ? number_format($stats['total_revenue'] / $stats['total_products'], 2) : '0.00'; ?></h4>
                                        <p class="mb-0">Avg Revenue/Product</p>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders and Products -->
                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Recent Orders</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Order #</th>
                                                <th>Customer</th>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_orders)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center">No orders found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach (array_slice($recent_orders, 0, 5) as $order): ?>
                                                <tr>
                                                    <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                                    <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                                                    <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                                    <td><?php echo $order['quantity']; ?></td>
                                                    <td><?php echo formatPrice($order['price'] * $order['quantity']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo getStatusColor($order['status']); ?>">
                                                            <?php echo ucfirst($order['status']); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="orders.php" class="btn btn-sm btn-success">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Products -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">My Top Products</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Price</th>
                                                <th>Sold</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($my_products)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">No products found</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php 
                                                $top_products = array_filter($my_products, function($p) { return $p['total_sold'] > 0; });
                                                usort($top_products, function($a, $b) { return $b['total_sold'] - $a['total_sold']; });
                                                $top_products = array_slice($top_products, 0, 5);
                                                ?>
                                                <?php foreach ($top_products as $product): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                    <td><?php echo formatPrice($product['price']); ?></td>
                                                    <td><?php echo $product['total_sold']; ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php if (empty($top_products)): ?>
                                                    <tr>
                                                        <td colspan="3" class="text-center">No sales yet</td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="products.php" class="btn btn-sm btn-success">View All Products</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="product_add.php" class="btn btn-outline-success">
                                                <i class="fas fa-plus me-2"></i>Add New Product
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="products.php" class="btn btn-outline-success">
                                                <i class="fas fa-box me-2"></i>Manage Products
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="orders.php" class="btn btn-outline-success">
                                                <i class="fas fa-shopping-cart me-2"></i>View Orders
                                            </a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid">
                                            <a href="earnings.php" class="btn btn-outline-success">
                                                <i class="fas fa-dollar-sign me-2"></i>View Earnings
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- MDB JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>
</body>
</html>

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
