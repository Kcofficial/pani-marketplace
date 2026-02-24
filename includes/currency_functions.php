<?php
/**
 * Currency and Product Variant Functions
 * Handles currency formatting, product variants, flavors, weights, and pre-orders
 */

/**
 * Get active currency settings
 */
function getCurrencySettings($currency_code = 'IDR') {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM currency_settings 
            WHERE currency_code = ? AND is_active = TRUE
        ");
        $stmt->execute([$currency_code]);
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            // Default to IDR if not found
            return [
                'currency_code' => 'IDR',
                'currency_symbol' => 'Rp',
                'currency_name' => 'Indonesian Rupiah',
                'decimal_places' => 0,
                'thousands_separator' => '.',
                'decimal_separator' => ',',
                'symbol_position' => 'before'
            ];
        }
        
        return $settings;
    } catch (PDOException $e) {
        error_log("Error getting currency settings: " . $e->getMessage());
        return [
            'currency_code' => 'IDR',
            'currency_symbol' => 'Rp',
            'currency_name' => 'Indonesian Rupiah',
            'decimal_places' => 0,
            'thousands_separator' => '.',
            'decimal_separator' => ',',
            'symbol_position' => 'before'
        ];
    }
}

/**
 * Format currency with proper formatting
 */
function formatCurrency($amount, $currency_code = 'IDR') {
    $settings = getCurrencySettings($currency_code);
    
    // Format the number
    $formatted_number = number_format(
        $amount, 
        $settings['decimal_places'], 
        $settings['decimal_separator'], 
        $settings['thousands_separator']
    );
    
    // Add currency symbol
    if ($settings['symbol_position'] === 'before') {
        return $settings['currency_symbol'] . $formatted_number;
    } else {
        return $formatted_number . ' ' . $settings['currency_symbol'];
    }
}

/**
 * Format Indonesian Rupiah (alias for backward compatibility)
 * This function is already defined in id_functions.php, so we'll use that one
 */
// function formatPriceIDR($amount) {
//     return formatCurrency($amount, 'IDR');
// }

/**
 * Get product variants
 */
function getProductVariants($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM product_variants 
            WHERE product_id = ? AND is_active = TRUE
            ORDER BY variant_name, variant_value
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting product variants: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product variants grouped by type
 */
function getProductVariantsGrouped($product_id) {
    $variants = getProductVariants($product_id);
    $grouped = [];
    
    foreach ($variants as $variant) {
        $grouped[$variant['variant_name']][] = $variant;
    }
    
    return $grouped;
}

/**
 * Get product flavors
 */
function getProductFlavors($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM product_flavors 
            WHERE product_id = ? AND is_active = TRUE
            ORDER BY flavor_name
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting product flavors: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product weights/sizes
 */
function getProductWeights($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM product_weights 
            WHERE product_id = ? AND is_active = TRUE
            ORDER BY weight_value
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting product weights: " . $e->getMessage());
        return [];
    }
}

/**
 * Get variant combination price and stock
 */
function getVariantCombination($product_id, $combination) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM product_variant_combinations 
            WHERE product_id = ? AND variant_combination = ? AND is_active = TRUE
        ");
        $stmt->execute([$product_id, $combination]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting variant combination: " . $e->getMessage());
        return null;
    }
}

/**
 * Calculate final price with variants, flavors, and weights
 */
function calculateFinalPrice($base_price, $variant_adjustments = [], $flavor_adjustment = 0, $weight_adjustment = 0) {
    $final_price = $base_price;
    
    // Add variant adjustments
    foreach ($variant_adjustments as $adjustment) {
        $final_price += $adjustment;
    }
    
    // Add flavor adjustment
    $final_price += $flavor_adjustment;
    
    // Add weight adjustment
    $final_price += $weight_adjustment;
    
    return max(0, $final_price); // Ensure price doesn't go negative
}

/**
 * Get preorder settings for product
 */
function getPreorderSettings($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM preorder_settings 
            WHERE product_id = ? AND is_active = TRUE
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting preorder settings: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if product is available for preorder
 */
function isProductPreorder($product_id) {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT is_preorder, preorder_days, preorder_max_quantity 
            FROM products 
            WHERE id = ? AND is_preorder = TRUE
        ");
        $stmt->execute([$product_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error checking preorder status: " . $e->getMessage());
        return null;
    }
}

/**
 * Get category types
 */
function getCategoryTypes() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM category_types 
            WHERE is_active = TRUE 
            ORDER BY name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting category types: " . $e->getMessage());
        return [];
    }
}

/**
 * Get categories with type information
 */
function getCategoriesWithTypes() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT c.*, ct.name as type_name, ct.icon as type_icon 
            FROM categories c
            LEFT JOIN category_types ct ON c.category_type_id = ct.id
            ORDER BY ct.name, c.name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting categories with types: " . $e->getMessage());
        return [];
    }
}

/**
 * Generate variant combinations for product
 */
function generateVariantCombinations($product_id, $selected_variants = []) {
    $variants = getProductVariantsGrouped($product_id);
    $combinations = [];
    
    if (empty($variants)) {
        return [];
    }
    
    // Generate all possible combinations
    $variant_names = array_keys($variants);
    $combination_keys = [];
    
    foreach ($variant_names as $name) {
        $values = array_column($variants[$name], 'variant_value');
        $combination_keys[$name] = $values;
    }
    
    // Generate cartesian product
    $combinations = cartesianProduct($combination_keys);
    
    $result = [];
    foreach ($combinations as $combination) {
        $key = implode('-', $combination);
        $price = 0;
        $stock = 0;
        
        // Calculate price and stock for this combination
        foreach ($combination as $variant_name => $variant_value) {
            foreach ($variants[$variant_name] as $variant) {
                if ($variant['variant_value'] === $variant_value) {
                    $price += $variant['price_adjustment'];
                    $stock += $variant['stock_adjustment'];
                    break;
                }
            }
        }
        
        $result[$key] = [
            'combination' => $combination,
            'price_adjustment' => $price,
            'stock_adjustment' => $stock,
            'key' => $key
        ];
    }
    
    return $result;
}

/**
 * Generate cartesian product for variant combinations
 */
function cartesianProduct($arrays) {
    $result = [[]];
    
    foreach ($arrays as $key => $values) {
        $temp = [];
        foreach ($result as $item) {
            foreach ($values as $value) {
                $temp[] = array_merge($item, [$key => $value]);
            }
        }
        $result = $temp;
    }
    
    return $result;
}

/**
 * Format weight display
 */
function formatWeight($value, $unit) {
    $units = [
        'g' => 'gram',
        'kg' => 'kilogram',
        'ml' => 'mililiter',
        'l' => 'liter',
        'pcs' => 'pieces'
    ];
    
    $unit_name = $units[$unit] ?? $unit;
    
    return $value . ' ' . $unit_name;
}

/**
 * Get product with all variants and options
 */
function getProductWithOptions($product_id) {
    try {
        $conn = getDBConnection();
        
        // Get basic product info
        $stmt = $conn->prepare("
            SELECT p.*, c.name as category_name, ct.name as category_type, ct.icon as category_icon
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN category_types ct ON c.category_type_id = ct.id
            WHERE p.id = ?
        ");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            return null;
        }
        
        // Get variants
        $product['variants'] = getProductVariantsGrouped($product_id);
        
        // Get flavors
        $product['flavors'] = getProductFlavors($product_id);
        
        // Get weights
        $product['weights'] = getProductWeights($product_id);
        
        // Get preorder settings
        $product['preorder_settings'] = getPreorderSettings($product_id);
        
        // Check if preorder
        $product['is_preorder_product'] = isProductPreorder($product_id);
        
        return $product;
    } catch (PDOException $e) {
        error_log("Error getting product with options: " . $e->getMessage());
        return null;
    }
}

/**
 * Validate variant selection
 */
function validateVariantSelection($product_id, $selected_variants) {
    $variants = getProductVariantsGrouped($product_id);
    $errors = [];
    
    foreach ($variants as $variant_name => $variant_options) {
        if (!isset($selected_variants[$variant_name])) {
            $errors[] = "Silakan pilih $variant_name";
            continue;
        }
        
        $selected_value = $selected_variants[$variant_name];
        $valid_values = array_column($variant_options, 'variant_value');
        
        if (!in_array($selected_value, $valid_values)) {
            $errors[] = "Pilihan $variant_name tidak valid";
        }
    }
    
    return $errors;
}

/**
 * Calculate estimated delivery date for preorder
 */
function calculatePreorderDeliveryDate($preorder_days) {
    $delivery_date = new DateTime();
    $delivery_date->add(new DateInterval("P{$preorder_days}D"));
    
    return $delivery_date->format('d M Y');
}

/**
 * Format preorder message
 */
function formatPreorderMessage($preorder_settings) {
    if (!$preorder_settings) {
        return '';
    }
    
    $days = $preorder_settings['preorder_days'];
    $delivery_date = calculatePreorderDeliveryDate($days);
    
    return "Pre-order - Estimasi pengiriman: $delivery_date";
}

/**
 * Get all available currencies
 */
function getAvailableCurrencies() {
    try {
        $conn = getDBConnection();
        $stmt = $conn->prepare("
            SELECT * FROM currency_settings 
            WHERE is_active = TRUE 
            ORDER BY currency_code
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting available currencies: " . $e->getMessage());
        return [];
    }
}

/**
 * Convert currency (basic implementation)
 */
function convertCurrency($amount, $from_currency, $to_currency) {
    // This is a basic implementation
    // In production, you would use real exchange rates from an API
    
    $exchange_rates = [
        'IDR' => 1,
        'USD' => 0.000064,
        'EUR' => 0.000059
    ];
    
    if (!isset($exchange_rates[$from_currency]) || !isset($exchange_rates[$to_currency])) {
        return $amount;
    }
    
    $amount_in_idr = $amount / $exchange_rates[$from_currency];
    $converted_amount = $amount_in_idr * $exchange_rates[$to_currency];
    
    return $converted_amount;
}
?>
