<?php
$page_title = 'Seller Registration - Paṇi';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isSeller() || isAdmin()) {
        header('Location: seller/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$errors = [];
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    $shop_name = cleanInput($_POST['shop_name'] ?? '');
    $shop_description = cleanInput($_POST['shop_description'] ?? '');
    $terms = isset($_POST['terms']);
    
    // Validate form data
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 3) {
        $errors['name'] = 'Name must be at least 3 characters';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter, one lowercase letter, and one number';
    }
    
    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required';
    } elseif (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone)) {
        $errors['phone'] = 'Invalid phone number format';
    }
    
    if (empty($shop_name)) {
        $errors['shop_name'] = 'Shop name is required';
    } elseif (strlen($shop_name) < 3) {
        $errors['shop_name'] = 'Shop name must be at least 3 characters';
    }
    
    if (!$terms) {
        $errors['terms'] = 'You must agree to terms and conditions';
    }
    
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            
            // Check if email already exists
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Email already exists';
            } else {
                // Create new seller account
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) 
                                       VALUES (?, ?, ?, ?, ?, 'seller')");
                $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
                
                $user_id = $conn->lastInsertId();
                
                // Create seller profile (you might want to create a separate sellers table)
                // For now, we'll store shop info in the users table or create a new table
                
                $success = 'Seller registration successful! You can now login and access your seller dashboard.';
                
                // Redirect to login after successful registration
                header('refresh:2;url=login.php');
            }
            
        } catch(PDOException $e) {
            $errors['database'] = 'Registration failed. Please try again.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Seller Registration Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-store fa-3x text-success mb-3"></i>
                            <h2>Become a Seller</h2>
                            <p class="text-muted">Start selling your products on Paṇi</p>
                        </div>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors['database'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <!-- Personal Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-user me-2"></i>Personal Information</h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-envelope"></i>
                                        </span>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <input type="text" class="form-control" id="address" name="address" 
                                               value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shop Information -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-store me-2"></i>Shop Information</h5>
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="shop_name" class="form-label">Shop Name</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-store"></i>
                                        </span>
                                        <input type="text" class="form-control" id="shop_name" name="shop_name" 
                                               value="<?php echo htmlspecialchars($_POST['shop_name'] ?? ''); ?>" required>
                                    </div>
                                    <?php if (isset($errors['shop_name'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['shop_name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="shop_description" class="form-label">Shop Description</label>
                                    <textarea class="form-control" id="shop_description" name="shop_description" rows="3" 
                                              placeholder="Tell customers about your shop..."><?php echo htmlspecialchars($_POST['shop_description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            
                            <!-- Security -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="mb-3"><i class="fas fa-lock me-2"></i>Security</h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <button class="btn btn-outline-secondary" type="button" id="toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($errors['password'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['password']; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Min 6 characters, include uppercase, lowercase, and number</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['confirm_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Terms -->
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a>, 
                                        <a href="privacy.php" target="_blank">Privacy Policy</a>, and 
                                        <a href="seller_terms.php" target="_blank">Seller Agreement</a>
                                    </label>
                                </div>
                                <?php if (isset($errors['terms'])): ?>
                                    <div class="text-danger small mt-1"><?php echo $errors['terms']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-success btn-lg w-100 mb-3">
                                <i class="fas fa-store me-2"></i>Create Seller Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login</a></p>
                            <p class="mb-0">Want to buy instead? <a href="register.php" class="text-decoration-none">Register as Buyer</a></p>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Seller Benefits -->
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                                <h6>Grow Your Business</h6>
                                <p class="small text-muted">Reach thousands of customers</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                                <h6>Secure Payments</h6>
                                <p class="small text-muted">Safe and reliable transactions</p>
                            </div>
                            <div class="col-md-4 mb-3">
                                <i class="fas fa-headset fa-2x text-success mb-2"></i>
                                <h6>24/7 Support</h6>
                                <p class="small text-muted">Dedicated seller support</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#toggle-password').on('click', function() {
        var passwordField = $('#password');
        var icon = $(this).find('i');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Password strength indicator
    $('#password').on('input', function() {
        var password = $(this).val();
        var strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        // You can display password strength indicator here if needed
    });
    
    // Confirm password validation
    $('#confirm_password').on('input', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();
        
        if (confirmPassword && password !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var password = $('#password').val();
        var confirmPassword = $('#confirm_password').val();
        var terms = $('#terms').is(':checked');
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match');
        } else if (!terms) {
            e.preventDefault();
            alert('You must agree to terms and conditions');
        }
    });
});
</script>
