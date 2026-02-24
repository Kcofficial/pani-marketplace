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
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    $product_id = (int)($input['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID produk tidak valid']);
        exit();
    }
    
    // Check if product belongs to seller
    $stmt = getDBConnection()->prepare("
        SELECT seller_id FROM products WHERE id = ? AND seller_id = ?
    ");
    $stmt->execute([$product_id, $seller_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        echo json_encode(['success' => false, 'message' => 'Produk tidak ditemukan atau bukan milik Anda']);
        exit();
    }
    
    // Delete product
    $stmt = getDBConnection()->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
    $stmt->execute([$product_id, $seller_id]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Produk berhasil dihapus'
    ]);
    
} catch(PDOException $e) {
    error_log("Error deleting product: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menghapus produk']);
}
?>
