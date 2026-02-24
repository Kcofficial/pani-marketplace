<?php
$page_title = 'Choose Account Type - Paṇi';
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isSeller() || isAdmin()) {
        header('Location: seller/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Account Type Selection Section -->
<section class="py-5 bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="text-center text-white mb-5">
                    <h1 class="display-4 fw-bold mb-3">Join Paṇi Today</h1>
                    <p class="lead">Choose how you want to participate in our marketplace</p>
                </div>
                
                <div class="row g-4">
                    <!-- Buyer Option -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-lg border-0 hover-lift">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-shopping-cart fa-4x text-primary"></i>
                                </div>
                                <h3 class="card-title mb-3">I Want to Buy</h3>
                                <p class="card-text text-muted mb-4">
                                    Shop from thousands of products from trusted sellers. Enjoy secure payments, 
                                    fast delivery, and excellent customer service.
                                </p>
                                
                                <ul class="list-unstyled text-start mb-4">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Access to millions of products
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Secure payment options
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Order tracking and updates
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Customer reviews and ratings
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        24/7 customer support
                                    </li>
                                </ul>
                                
                                <a href="register.php" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-user-plus me-2"></i>Register as Buyer
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Seller Option -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-lg border-0 hover-lift">
                            <div class="card-body text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-store fa-4x text-success"></i>
                                </div>
                                <h3 class="card-title mb-3">I Want to Sell</h3>
                                <p class="card-text text-muted mb-4">
                                    Start your own online business and reach thousands of customers. 
                                    Manage your products, orders, and grow your revenue.
                                </p>
                                
                                <ul class="list-unstyled text-start mb-4">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Easy product management
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Order management system
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Payment processing
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Sales analytics dashboard
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Marketing tools
                                    </li>
                                </ul>
                                
                                <a href="register_seller.php" class="btn btn-success btn-lg w-100">
                                    <i class="fas fa-store me-2"></i>Register as Seller
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Already Have Account -->
                <div class="text-center mt-5">
                    <div class="bg-white bg-opacity-10 rounded-3 p-4 d-inline-block">
                        <h5 class="text-white mb-3">Already have an account?</h5>
                        <a href="login.php" class="btn btn-light btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login Now
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose Paṇi?</h2>
            <p class="lead text-muted">The best marketplace for buyers and sellers</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h5>Secure & Trusted</h5>
                    <p class="text-muted">Your data and payments are protected with industry-leading security</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-users fa-3x text-success"></i>
                    </div>
                    <h5>Large Community</h5>
                    <p class="text-muted">Join thousands of satisfied buyers and successful sellers</p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="text-center">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-headset fa-3x text-info"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Our team is always here to help you succeed</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Success Stories</h2>
            <p class="lead text-muted">See what our community members are saying</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"Paṇi helped me grow my small business into a thriving online store. The seller dashboard is amazing!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40x40" class="rounded-circle me-2" alt="Seller">
                            <div>
                                <h6 class="mb-0">Sarah Johnson</h6>
                                <small class="text-muted">Seller since 2023</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"I love shopping on Paṇi! Great products, amazing prices, and fast delivery every time."</p>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40x40" class="rounded-circle me-2" alt="Buyer">
                            <div>
                                <h6 class="mb-0">Mike Chen</h6>
                                <small class="text-muted">Verified Buyer</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                            <i class="fas fa-star text-warning"></i>
                        </div>
                        <p class="card-text">"The best platform I've used. Easy to use, great support, and my sales have increased 300%!"</p>
                        <div class="d-flex align-items-center">
                            <img src="https://via.placeholder.com/40x40" class="rounded-circle me-2" alt="Seller">
                            <div>
                                <h6 class="mb-0">Emily Davis</h6>
                                <small class="text-muted">Top Seller</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    min-height: 100vh;
}

.feature-icon {
    width: 100px;
    height: 100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: rgba(46, 134, 171, 0.1);
}

.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-lift:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}
</style>
