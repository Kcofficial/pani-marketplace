<?php
$page_title = 'Admin Login - Paṇi';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn() && isAdmin()) {
    header('Location: admin/index.php');
    exit();
}

$errors = [];

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validate form data
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role IN ('admin', 'seller')");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to admin dashboard
                header('Location: admin/index.php');
                exit();
                
            } else {
                $errors['login'] = 'Invalid email or password';
            }
            
        } catch(PDOException $e) {
            $errors['database'] = 'Login failed. Please try again.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Admin Login Section -->
<section class="py-5 bg-gradient-primary">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-cog fa-3x text-primary mb-3"></i>
                            <h2>Admin Login</h2>
                            <p class="text-muted">Access admin control panel</p>
                        </div>
                        
                        <?php if (isset($errors['database'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($errors['login'])): ?>
                            <div class="alert alert-danger"><?php echo $errors['login']; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
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
                            
                            <div class="mb-3">
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
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <a href="forgot_password.php" class="text-decoration-none">Forgot Password?</a>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>Login to Admin
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Regular user? <a href="login.php" class="text-decoration-none">Login here</a></p>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Demo Credentials -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-2"></i>Demo Credentials</h6>
                            <p class="mb-1"><strong>Admin:</strong> admin@ecommerce.com / password</p>
                            <p class="mb-0"><strong>Seller:</strong> seller@ecommerce.com / password</p>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="text-center mt-4">
                    <a href="index.php" class="text-decoration-none text-white">
                        <i class="fas fa-arrow-left me-2"></i>Back to Store
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    min-height: 100vh;
    display: flex;
    align-items: center;
}

.bg-gradient-primary .container {
    flex: 1;
}
</style>

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
    
    // Form validation
    $('form').on('submit', function(e) {
        var email = $('#email').val();
        var password = $('#password').val();
        
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in all required fields');
        }
    });
});
</script>
