<?php
/**
 * Enhanced Review Functions
 * Handles product reviews, ratings, and review management
 */

/**
 * Get product reviews with pagination
 */
function getProductReviews($product_id, $page = 1, $per_page = 10) {
    try {
        $conn = getDBConnection();
        $offset = ($page - 1) * $per_page;
        
        $stmt = $conn->prepare("
            SELECT r.*, u.name as reviewer_name, u.created_at as user_since,
                   (SELECT COUNT(*) FROM review_helpful_votes rhv WHERE rhv.review_id = r.id AND rhv.is_helpful = TRUE) as helpful_count,
                   (SELECT COUNT(*) FROM review_helpful_votes rhv WHERE rhv.review_id = r.id AND rhv.is_helpful = FALSE) as not_helpful_count
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            WHERE r.product_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$product_id, $per_page, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting product reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Get product rating summary
 */
function getProductRatingSummary($product_id) {
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM reviews 
            WHERE product_id = ? AND status = 'approved'
        ");
        $stmt->execute([$product_id]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($summary['total_reviews'] > 0) {
            $summary['average_rating'] = round($summary['average_rating'], 1);
        } else {
            $summary['average_rating'] = 0;
        }
        
        return $summary;
    } catch (PDOException $e) {
        error_log("Error getting rating summary: " . $e->getMessage());
        return [
            'total_reviews' => 0,
            'average_rating' => 0,
            'five_star' => 0,
            'four_star' => 0,
            'three_star' => 0,
            'two_star' => 0,
            'one_star' => 0
        ];
    }
}

/**
 * Add product review
 */
function addProductReview($product_id, $user_id, $order_id, $rating, $review_title, $review_text, $pros = null, $cons = null, $would_recommend = null) {
    try {
        $conn = getDBConnection();
        
        // Check if user already reviewed this product
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM reviews 
            WHERE product_id = ? AND user_id = ? AND status = 'approved'
        ");
        $stmt->execute([$product_id, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Anda sudah memberikan ulasan untuk produk ini'];
        }
        
        $stmt = $conn->prepare("
            INSERT INTO reviews (
                product_id, user_id, order_id, rating, review_title, review_text, 
                pros, cons, would_recommend, is_verified_purchase, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, 'pending')
        ");
        $stmt->execute([
            $product_id, $user_id, $order_id, $rating, $review_title, 
            $review_text, $pros, $cons, $would_recommend
        ]);
        
        return ['success' => true, 'message' => 'Ulasan Anda telah dikirim dan menunggu persetujuan'];
    } catch (PDOException $e) {
        error_log("Error adding review: " . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat mengirim ulasan'];
    }
}

/**
 * Vote on review helpfulness
 */
function voteReviewHelpful($review_id, $user_id, $is_helpful) {
    try {
        $conn = getDBConnection();
        
        // Check if user already voted
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM review_helpful_votes 
            WHERE review_id = ? AND user_id = ?
        ");
        $stmt->execute([$review_id, $user_id]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Anda sudah memberikan suara untuk ulasan ini'];
        }
        
        $stmt = $conn->prepare("
            INSERT INTO review_helpful_votes (review_id, user_id, is_helpful) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$review_id, $user_id, $is_helpful]);
        
        // Update helpful count
        $stmt = $conn->prepare("
            UPDATE reviews 
            SET helpful_count = helpful_count + ? 
            WHERE id = ?
        ");
        $increment = $is_helpful ? 1 : -1;
        $stmt->execute([$increment, $review_id]);
        
        return ['success' => true, 'message' => 'Suara Anda telah dicatat'];
    } catch (PDOException $e) {
        error_log("Error voting review: " . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat memberikan suara'];
    }
}

/**
 * Get user reviews
 */
function getUserReviews($user_id, $page = 1, $per_page = 10) {
    try {
        $conn = getDBConnection();
        $offset = ($page - 1) * $per_page;
        
        $stmt = $conn->prepare("
            SELECT r.*, p.name as product_name, p.image as product_image
            FROM reviews r
            JOIN products p ON r.product_id = p.id
            WHERE r.user_id = ? AND r.status = 'approved'
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$user_id, $per_page, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting user reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Get pending reviews for admin
 */
function getPendingReviews($page = 1, $per_page = 20) {
    try {
        $conn = getDBConnection();
        $offset = ($page - 1) * $per_page;
        
        $stmt = $conn->prepare("
            SELECT r.*, u.name as reviewer_name, u.email as reviewer_email,
                   p.name as product_name, p.image as product_image
            FROM reviews r
            JOIN users u ON r.user_id = u.id
            JOIN products p ON r.product_id = p.id
            WHERE r.status = 'pending'
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$per_page, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting pending reviews: " . $e->getMessage());
        return [];
    }
}

/**
 * Approve/reject review
 */
function moderateReview($review_id, $action, $admin_id) {
    try {
        $conn = getDBConnection();
        
        if (!in_array($action, ['approve', 'reject'])) {
            return ['success' => false, 'message' => 'Aksi tidak valid'];
        }
        
        $status = $action === 'approve' ? 'approved' : 'rejected';
        
        $stmt = $conn->prepare("
            UPDATE reviews 
            SET status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$status, $review_id]);
        
        return ['success' => true, 'message' => "Ulasan telah di$status"];
    } catch (PDOException $e) {
        error_log("Error moderating review: " . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat memoderasi ulasan'];
    }
}

/**
 * Generate star rating HTML
 */
function generateStarRating($rating, $show_value = true) {
    $full_stars = floor($rating);
    $has_half_star = ($rating - $full_stars) >= 0.5;
    $empty_stars = 5 - $full_stars - ($has_half_star ? 1 : 0);
    
    $html = '<div class="star-rating">';
    
    // Full stars
    for ($i = 0; $i < $full_stars; $i++) {
        $html .= '<i class="fas fa-star text-warning"></i>';
    }
    
    // Half star
    if ($has_half_star) {
        $html .= '<i class="fas fa-star-half-alt text-warning"></i>';
    }
    
    // Empty stars
    for ($i = 0; $i < $empty_stars; $i++) {
        $html .= '<i class="far fa-star text-warning"></i>';
    }
    
    if ($show_value) {
        $html .= '<span class="rating-value ms-2">' . number_format($rating, 1) . '</span>';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Generate rating distribution HTML
 */
function generateRatingDistribution($summary) {
    $total = $summary['total_reviews'];
    if ($total == 0) return '';
    
    $html = '<div class="rating-distribution">';
    $stars = [5, 4, 3, 2, 1];
    
    foreach ($stars as $star) {
        $count = $summary[strtolower($star) . '_star'] ?? 0;
        $percentage = ($count / $total) * 100;
        
        $html .= '
            <div class="rating-bar mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span>' . $star . ' <i class="fas fa-star text-warning"></i></span>
                    <span class="text-muted">' . $count . '</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-warning" style="width: ' . $percentage . '%"></div>
                </div>
            </div>
        ';
    }
    
    $html .= '</div>';
    return $html;
}

/**
 * Check if user can review product
 */
function canUserReviewProduct($product_id, $user_id) {
    try {
        $conn = getDBConnection();
        
        // Check if user has purchased the product
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE oi.product_id = ? AND o.user_id = ? AND o.status = 'delivered'
        ");
        $stmt->execute([$product_id, $user_id]);
        $has_purchased = $stmt->fetchColumn() > 0;
        
        if (!$has_purchased) {
            return ['can_review' => false, 'reason' => 'Anda harus membeli produk ini sebelum dapat memberikan ulasan'];
        }
        
        // Check if already reviewed
        $stmt = $conn->prepare("
            SELECT COUNT(*) FROM reviews 
            WHERE product_id = ? AND user_id = ? AND status = 'approved'
        ");
        $stmt->execute([$product_id, $user_id]);
        $already_reviewed = $stmt->fetchColumn() > 0;
        
        if ($already_reviewed) {
            return ['can_review' => false, 'reason' => 'Anda sudah memberikan ulasan untuk produk ini'];
        }
        
        return ['can_review' => true, 'reason' => ''];
    } catch (PDOException $e) {
        error_log("Error checking review eligibility: " . $e->getMessage());
        return ['can_review' => false, 'reason' => 'Terjadi kesalahan sistem'];
    }
}

/**
 * Get review statistics for admin
 */
function getReviewStatistics() {
    try {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as total_reviews,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_reviews,
                COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_reviews,
                COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_reviews,
                AVG(CASE WHEN status = 'approved' THEN rating END) as average_rating
            FROM reviews
        ");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error getting review statistics: " . $e->getMessage());
        return [];
    }
}
?>
