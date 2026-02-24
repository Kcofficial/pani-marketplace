<?php
session_start();
$page_title = 'Admin Dashboard - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

try {
    // Get system statistics
    $stmt = getDBConnection()->prepare("
        SELECT 
            COUNT(DISTINCT u.id) as total_users,
            COUNT(DISTINCT CASE WHEN u.role = 'customer' THEN u.id END) as total_customers,
            COUNT(DISTINCT CASE WHEN u.role = 'seller' THEN u.id END) as total_sellers,
            COUNT(DISTINCT p.id) as total_products,
            COUNT(DISTINCT CASE WHEN p.status = 'active' THEN p.id END) as active_products,
            COUNT(DISTINCT o.id) as total_orders,
            COUNT(DISTINCT CASE WHEN o.status = 'completed' THEN o.id END) as completed_orders,
            SUM(CASE WHEN o.status = 'completed' THEN o.total_amount ELSE 0 END) as total_revenue,
            AVG(CASE WHEN o.status = 'completed' THEN o.total_amount ELSE NULL END) as avg_order_value
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id
        LEFT JOIN orders o ON 1=1
    ");
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get recent users
    $stmt = getDBConnection()->prepare("
        SELECT id, name, email, role, created_at
        FROM users
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent products
    $stmt = getDBConnection()->prepare("
        SELECT p.*, u.name as seller_name, c.name as category_name,
               (SELECT SUM(oi.quantity) FROM order_items oi 
                JOIN orders o ON oi.order_id = o.id 
                WHERE oi.product_id = p.id AND o.status != 'cancelled') as total_sold
        FROM products p
        LEFT JOIN users u ON p.seller_id = u.id
        LEFT JOIN categories c ON p.category_id = c.id
        ORDER BY p.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent orders
    $stmt = getDBConnection()->prepare("
        SELECT o.*, u.name as customer_name,
               COUNT(oi.id) as item_count,
               SUM(oi.quantity) as total_quantity
        FROM orders o
        LEFT JOIN users u ON o.customer_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get top sellers
    $stmt = getDBConnection()->prepare("
        SELECT u.id, u.name, u.email,
               COUNT(p.id) as total_products,
               SUM(oi.quantity) as total_sold,
               SUM(oi.price * oi.quantity) as total_revenue
        FROM users u
        LEFT JOIN products p ON u.id = p.seller_id
        LEFT JOIN order_items oi ON p.id = oi.product_id
        LEFT JOIN orders o ON oi.order_id = o.id AND o.status != 'cancelled'
        WHERE u.role = 'seller'
        GROUP BY u.id
        ORDER BY total_revenue DESC
        LIMIT 5
    ");
    $stmt->execute();
    $top_sellers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error loading admin dashboard: " . $e->getMessage());
    $stats = [];
    $recent_users = [];
    $recent_products = [];
    $recent_orders = [];
    $top_sellers = [];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Paṇi Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.15);
        }
        .user-card {
            transition: all 0.3s ease;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .product-card {
            transition: all 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .order-card {
            transition: all 0.3s ease;
        }
        .order-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <nav class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-bold text-orange-500">Paṇi Admin</h1>
                    <div class="hidden md:flex space-x-6">
                        <a href="admin-dashboard.php" class="text-orange-500 font-semibold">Dashboard</a>
                        <a href="admin-users.php" class="text-gray-600 hover:text-orange-500">Users</a>
                        <a href="admin-products.php" class="text-gray-600 hover:text-orange-500">Products</a>
                        <a href="admin-orders.php" class="text-gray-600 hover:text-orange-500">Orders</a>
                        <a href="admin-categories.php" class="text-gray-600 hover:text-orange-500">Categories</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Admin: <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                    <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Users</p>
                        <p class="text-3xl font-bold"><?php echo $stats['total_users'] ?? 0; ?></p>
                        <p class="text-white/60 text-xs">
                            <?php echo $stats['total_customers'] ?? 0; ?> customers, 
                            <?php echo $stats['total_sellers'] ?? 0; ?> sellers
                        </p>
                    </div>
                    <i class="fas fa-users text-3xl text-white/50"></i>
                </div>
            </div>
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Products</p>
                        <p class="text-3xl font-bold"><?php echo $stats['total_products'] ?? 0; ?></p>
                        <p class="text-white/60 text-xs">
                            <?php echo $stats['active_products'] ?? 0; ?> active
                        </p>
                    </div>
                    <i class="fas fa-box text-3xl text-white/50"></i>
                </div>
            </div>
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Orders</p>
                        <p class="text-3xl font-bold"><?php echo $stats['total_orders'] ?? 0; ?></p>
                        <p class="text-white/60 text-xs">
                            <?php echo $stats['completed_orders'] ?? 0; ?> completed
                        </p>
                    </div>
                    <i class="fas fa-shopping-cart text-3xl text-white/50"></i>
                </div>
            </div>
            <div class="stat-card text-white p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 text-sm">Total Revenue</p>
                        <p class="text-3xl font-bold">Rp <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?></p>
                        <p class="text-white/60 text-xs">
                            Avg: Rp <?php echo number_format($stats['avg_order_value'] ?? 0, 0, ',', '.'); ?>
                        </p>
                    </div>
                    <i class="fas fa-money-bill-wave text-3xl text-white/50"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-users mr-2 text-orange-500"></i>
                        Recent Users
                    </h3>
                    <a href="admin-users.php" class="text-orange-500 hover:text-orange-600">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($recent_users as $user): ?>
                        <div class="user-card border rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($user['name']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['email']); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                           <?php echo $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                   ($user['role'] === 'seller' ? 'bg-blue-100 text-blue-800' : 
                                                   'bg-green-100 text-green-800'); ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-box mr-2 text-orange-500"></i>
                        Recent Products
                    </h3>
                    <a href="admin-products.php" class="text-orange-500 hover:text-orange-600">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($recent_products as $product): ?>
                        <div class="product-card border rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <img src="https://picsum.photos/seed/<?php echo $product['id']; ?>/60/60.jpg" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         class="w-12 h-12 rounded-lg object-cover">
                                    <div>
                                        <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($product['name']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['seller_name']); ?></p>
                                        <p class="text-orange-500 font-bold">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-medium
                                           <?php echo $product['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Top Sellers -->
        <div class="mt-6">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-trophy mr-2 text-orange-500"></i>
                        Top Sellers
                    </h3>
                    <a href="admin-sellers.php" class="text-orange-500 hover:text-orange-600">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($top_sellers as $seller): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                                    <i class="fas fa-store text-gray-600"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($seller['name']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo $seller['total_products']; ?> products</p>
                                    <p class="text-sm text-orange-500">Rp <?php echo number_format($seller['total_revenue'], 0, ',', '.'); ?> revenue</p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="mt-6">
            <div class="bg-white rounded-lg p-6 shadow-sm">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-shopping-cart mr-2 text-orange-500"></i>
                        Recent Orders
                    </h3>
                    <a href="admin-orders.php" class="text-orange-500 hover:text-orange-600">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                <div class="space-y-3">
                    <?php foreach ($recent_orders as $order): ?>
                        <div class="order-card border rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">Order #<?php echo $order['id']; ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($order['customer_name']); ?> • 
                                        <?php echo date('d M Y H:i', strtotime($order['created_at'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <?php echo $order['item_count']; ?> items • 
                                        Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-medium
                                           <?php echo $order['status'] === 'completed' ? 'bg-green-100 text-green-800' : 
                                                   ($order['status'] === 'processing' ? 'bg-yellow-100 text-yellow-800' : 
                                                   'bg-gray-100 text-gray-800'); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
