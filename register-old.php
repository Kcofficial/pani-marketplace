<?php
$page_title = 'Register - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
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
    
    if (!$terms) {
        $errors['terms'] = 'You must agree to the terms and conditions';
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
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) 
                                       VALUES (?, ?, ?, ?, ?, 'user')");
                $stmt->execute([$name, $email, $hashed_password, $phone, $address]);
                
                $success = 'Registration successful! You can now login.';
                
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

<!-- Register Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                            <h2>Create Buyer Account</h2>
                            <p class="text-muted">Join us and start shopping</p>
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
                            <div class="row">
                                <div class="col-12 mb-3">
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
                            </div>
                            
                            <div class="row">
                                <div class="col-12 mb-3">
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
                            </div>
                            
                            <div class="row">
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
                            
                            <div class="row">
                                <div class="col-12 mb-3">
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
                            </div>
                            
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </span>
                                        <textarea class="form-control" id="address" name="address" rows="2" 
                                                  required><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="terms.php" target="_blank">Terms and Conditions</a> and 
                                        <a href="privacy.php" target="_blank">Privacy Policy</a>
                                    </label>
                                </div>
                                <?php if (isset($errors['terms'])): ?>
                                    <div class="text-danger small mt-1"><?php echo $errors['terms']; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Buyer Account
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none">Login</a></p>
                            <p class="mb-0">Want to sell products? <a href="register_seller.php" class="text-decoration-none">Register as Seller</a></p>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Social Registration -->
                        <div class="text-center mb-3">
                            <p class="text-muted">Or register with</p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button class="btn btn-outline-primary">
                                <i class="fab fa-google me-2"></i>Google
                            </button>
                            <button class="btn btn-outline-primary">
                                <i class="fab fa-facebook-f me-2"></i>Facebook
                            </button>
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
        
        var strengthText = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        var strengthColor = ['danger', 'warning', 'info', 'success', 'success'];
        
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
            alert('You must agree to the terms and conditions');
        }
    });
});
</script>
