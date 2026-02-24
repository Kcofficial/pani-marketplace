<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if user is logged in and is a seller
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'seller') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$seller_id = $_SESSION['user_id'];

try {
    // Validate input
    $name = cleanInput($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $description = cleanInput($_POST['description'] ?? '');
    
    if (empty($name) || $category_id <= 0 || $price <= 0 || $stock < 0) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi dengan benar']);
        exit();
    }
    
    // Generate SKU
    $sku = 'PRD-' . strtoupper(uniqid());
    
    // Insert product
    $stmt = getDBConnection()->prepare("
        INSERT INTO products (name, description, category_id, price, stock, sku, seller_id, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    $stmt->execute([$name, $description, $category_id, $price, $stock, $sku, $seller_id]);
    
    $product_id = getDBConnection()->lastInsertId();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Produk berhasil ditambahkan',
        'product_id' => $product_id
    ]);
    
} catch(PDOException $e) {
    error_log("Error adding product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menambahkan produk']);
}
?>
