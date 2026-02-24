<?php
/**
 * Discount and Promotion Functions
 * Handles all discount, coupon, and flash sale operations
 */

/**
 * Get active discounts for a product
 */
function getProductDiscounts($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT pd.*, d.name as discount_name, d.discount_type, d.discount_value, d.end_date
            FROM product_discounts pd
            JOIN discounts d ON pd.discount_id = d.id
            WHERE pd.product_id = ? AND pd.is_active = TRUE AND d.is_active = TRUE
            AND (pd.start_date IS NULL OR pd.start_date <= CURRENT_TIMESTAMP)
            AND (pd.end_date IS NULL OR pd.end_date >= CURRENT_TIMESTAMP)
            ORDER BY d.discount_value DESC
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting product discounts: " . $e->getMessage());
        return [];
    }
}

/**
 * Get active flash sales
 */
function getActiveFlashSales() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT fs.*, fsp.product_id, fsp.flash_price, fsp.flash_stock, fsp.sold_count,
                   p.name as product_name, p.image as product_image, p.original_price
            FROM flash_sales fs
            JOIN flash_sale_products fsp ON fs.id = fsp.flash_sale_id
            JOIN products p ON fsp.product_id = p.id
            WHERE fs.is_active = TRUE 
            AND fs.start_time <= CURRENT_TIMESTAMP 
            AND fs.end_time >= CURRENT_TIMESTAMP
            AND fsp.flash_stock > fsp.sold_count
            ORDER BY fs.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting flash sales: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if product is in flash sale
 */
function isProductInFlashSale($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT fsp.flash_price, fsp.flash_stock, fsp.sold_count, fs.end_time
            FROM flash_sale_products fsp
            JOIN flash_sales fs ON fsp.flash_sale_id = fs.id
            WHERE fsp.product_id = ? AND fs.is_active = TRUE 
            AND fs.start_time <= CURRENT_TIMESTAMP 
            AND fs.end_time >= CURRENT_TIMESTAMP
            AND fsp.flash_stock > fsp.sold_count
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error checking flash sale: " . $e->getMessage());
        return null;
    }
}

/**
 * Validate and apply coupon
 */
function validateCoupon($code, $user_id, $total_amount) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT c.*, 
                   COUNT(cu.id) as user_usage_count
            FROM coupons c
            LEFT JOIN coupon_usage cu ON c.id = cu.coupon_id AND cu.user_id = ?
            WHERE c.code = ? AND c.is_active = TRUE
            AND (c.start_date IS NULL OR c.start_date <= CURRENT_TIMESTAMP)
            AND (c.end_date IS NULL OR c.end_date >= CURRENT_TIMESTAMP)
            AND (c.usage_limit IS NULL OR c.usage_count < c.usage_limit)
            AND (c.user_usage_limit IS NULL OR user_usage_count < c.user_usage_limit)
            AND (c.min_purchase_amount IS NULL OR ? >= c.min_purchase_amount)
            GROUP BY c.id
        ");
        $stmt->execute([$user_id, strtoupper($code), $total_amount]);
        $coupon = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($coupon) {
            // Check if coupon applies to specific products or categories
            if (!empty($coupon['applicable_products']) || !empty($coupon['applicable_categories'])) {
                // This would need cart items to validate
                // For now, assume it's valid
            }
        }
        
        return $coupon;
    } catch (PDOException $e) {
        error_log("Error validating coupon: " . $e->getMessage());
        return null;
    }
}

/**
 * Calculate discount amount
 */
function calculateDiscount($original_price, $discount_type, $discount_value) {
    if ($discount_type === 'percentage') {
        return $original_price * ($discount_value / 100);
    } else {
        return $discount_value;
    }
}

/**
 * Get final price for product (with discounts)
 */
function getProductFinalPrice($product) {
    $final_price = $product['price'];
    
    // Check flash sale first
    $flash_sale = isProductInFlashSale($product['id']);
    if ($flash_sale) {
        $final_price = min($final_price, $flash_sale['flash_price']);
    }
    
    // Check product discounts
    $discounts = getProductDiscounts($product['id']);
    if (!empty($discounts)) {
        foreach ($discounts as $discount) {
            $discount_amount = calculateDiscount($final_price, $discount['discount_type'], $discount['discount_value']);
            $discounted_price = $final_price - $discount_amount;
            if ($discounted_price < $final_price) {
                $final_price = $discounted_price;
            }
        }
    }
    
    return [
        'final_price' => $final_price,
        'original_price' => $product['original_price'] ?? $product['price'],
        'has_discount' => $final_price < ($product['original_price'] ?? $product['price']),
        'discount_percentage' => round((1 - $final_price / ($product['original_price'] ?? $product['price'])) * 100, 0),
        'flash_sale' => $flash_sale,
        'discounts' => $discounts
    ];
}

/**
 * Record coupon usage
 */
function recordCouponUsage($coupon_id, $user_id, $order_id, $discount_amount) {
    try {
        $conn = getDBConnection();
        $conn->beginTransaction();
        
        // Update coupon usage count
        $stmt = $conn->prepare("UPDATE coupons SET usage_count = usage_count + 1 WHERE id = ?");
        $stmt->execute([$coupon_id]);
        
        // Record usage
        $stmt = $conn->prepare("
            INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$coupon_id, $user_id, $order_id, $discount_amount]);
        
        $conn->commit();
        return true;
    } catch (PDOException $e) {
        $conn->rollBack();
        error_log("Error recording coupon usage: " . $e->getMessage());
        return false;
    }
}

/**
 * Get store promotions for a seller
 */
function getStorePromotions($seller_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT sp.*, spp.product_id
            FROM store_promotions sp
            LEFT JOIN store_promotion_products spp ON sp.id = spp.promotion_id
            WHERE sp.seller_id = ? AND sp.is_active = TRUE
            AND (sp.start_date IS NULL OR sp.start_date <= CURRENT_TIMESTAMP)
            AND (sp.end_date IS NULL OR sp.end_date >= CURRENT_TIMESTAMP)
            ORDER BY sp.created_at DESC
        ");
        $stmt->execute([$seller_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting store promotions: " . $e->getMessage());
        return [];
    }
}

/**
 * Format discount display
 */
function formatDiscountDisplay($discount_type, $discount_value) {
    if ($discount_type === 'percentage') {
        return $discount_value . '%';
    } else {
        return formatPriceIDR($discount_value);
    }
}

/**
 * Get active global discounts
 */
function getActiveDiscounts() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM discounts 
            WHERE is_active = TRUE 
            AND (start_date IS NULL OR start_date <= CURRENT_TIMESTAMP)
            AND (end_date IS NULL OR end_date >= CURRENT_TIMESTAMP)
            ORDER BY created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting active discounts: " . $e->getMessage());
        return [];
    }
}

/**
 * Update flash sale sold count
 */
function updateFlashSaleSoldCount($flash_sale_id, $product_id, $quantity) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            UPDATE flash_sale_products 
            SET sold_count = sold_count + ? 
            WHERE flash_sale_id = ? AND product_id = ?
        ");
        return $stmt->execute([$quantity, $flash_sale_id, $product_id]);
    } catch (PDOException $e) {
        error_log("Error updating flash sale count: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if user can use coupon
 */
function canUserUseCoupon($coupon_id, $user_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT COUNT(*) as usage_count
            FROM coupon_usage 
            WHERE coupon_id = ? AND user_id = ?
        ");
        $stmt->execute([$coupon_id, $user_id]);
        $usage_count = $stmt->fetchColumn();
        
        // Get coupon limits
        $stmt = $conn->prepare("SELECT user_usage_limit FROM coupons WHERE id = ?");
        $stmt->execute([$coupon_id]);
        $user_limit = $stmt->fetchColumn();
        
        return $user_limit === null || $usage_count < $user_limit;
    } catch (PDOException $e) {
        error_log("Error checking coupon usage: " . $e->getMessage());
        return false;
    }
}
?>
