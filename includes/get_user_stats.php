<?php
require_once 'functions.php';
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['total_orders' => 0, 'total_spent' => '$0.00']);
    exit();
}

try {
    $conn = getDBConnection();
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("SELECT 
                           COUNT(*) as total_orders,
                           COALESCE(SUM(total_amount), 0) as total_spent
                           FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'total_orders' => $stats['total_orders'],
        'total_spent' => formatPrice($stats['total_spent'])
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['total_orders' => 0, 'total_spent' => '$0.00']);
}
?>
