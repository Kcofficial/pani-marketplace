<?php
require_once 'functions.php';
require_once 'config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addToCart();
        break;
    case 'update':
        updateCart();
        break;
    case 'remove':
        removeFromCart();
        break;
    case 'count':
        getCartCount();
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function addToCart() {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    try {
        $conn = getDBConnection();
        
        // Check if product exists and has sufficient stock
        $stmt = $conn->prepare("SELECT name, stock, price FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        if ($product['stock'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            return;
        }
        
        if (isLoggedIn()) {
            // Logged in user - save to database
            $user_id = $_SESSION['user_id'];
            
            // Check if item already exists in cart
            $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_item) {
                $new_quantity = $existing_item['quantity'] + $quantity;
                if ($new_quantity > $product['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                    return;
                }
                
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$new_quantity, $existing_item['id']]);
            } else {
                $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $product_id, $quantity]);
            }
        } else {
            // Guest user - save to session
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (isset($_SESSION['cart'][$product_id])) {
                $new_quantity = $_SESSION['cart'][$product_id] + $quantity;
                if ($new_quantity > $product['stock']) {
                    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
                    return;
                }
                $_SESSION['cart'][$product_id] = $new_quantity;
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Product added to cart successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function updateCart() {
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($product_id <= 0 || $quantity < 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
        return;
    }
    
    try {
        $conn = getDBConnection();
        
        // Check if product exists and has sufficient stock
        $stmt = $conn->prepare("SELECT name, stock FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            return;
        }
        
        if ($quantity > $product['stock']) {
            echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
            return;
        }
        
        if (isLoggedIn()) {
            // Logged in user - update database
            $user_id = $_SESSION['user_id'];
            
            if ($quantity == 0) {
                $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            } else {
                $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$quantity, $user_id, $product_id]);
            }
        } else {
            // Guest user - update session
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if ($quantity == 0) {
                unset($_SESSION['cart'][$product_id]);
            } else {
                $_SESSION['cart'][$product_id] = $quantity;
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Cart updated successfully']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function removeFromCart() {
    $product_id = (int)$_POST['product_id'];
    
    if ($product_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        return;
    }
    
    try {
        if (isLoggedIn()) {
            // Logged in user - remove from database
            $user_id = $_SESSION['user_id'];
            $conn = getDBConnection();
            $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            // Guest user - remove from session
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Item removed from cart']);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

function getCartCount() {
    $count = 0;
    
    try {
        if (isLoggedIn()) {
            // Logged in user - count from database
            $user_id = $_SESSION['user_id'];
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = $result['count'] ?? 0;
        } else {
            // Guest user - count from session
            if (isset($_SESSION['cart'])) {
                $count = array_sum($_SESSION['cart']);
            }
        }
        
        echo json_encode(['success' => true, 'count' => $count]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'count' => 0]);
    }
}
?>
