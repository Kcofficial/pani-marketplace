<?php
session_start();
$page_title = 'Login - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: admin-dashboard.php');
            break;
        case 'seller':
            header('Location: seller-dashboard.php');
            break;
        case 'customer':
            header('Location: index.php');
            break;
        default:
            header('Location: index.php');
    }
    exit();
}

$error = '';
$success = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email dan password harus diisi';
    } else {
        try {
            // Get user from database
            $stmt = getDBConnection()->prepare("
                SELECT id, name, email, password, role, status 
                FROM users 
                WHERE email = ? AND status = 'active'
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin-dashboard.php');
                        break;
                    case 'seller':
                        header('Location: seller-dashboard.php');
                        break;
                    case 'customer':
                        header('Location: index.php');
                        break;
                    default:
                        header('Location: index.php');
                }
                exit();
            } else {
                $error = 'Email atau password salah';
            }
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'Terjadi kesalahan, silakan coba lagi';
        }
    }
}
    
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
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set remember me cookie if checked
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
                    // Store token in database (implementation needed)
                }
                
                // Redirect to intended page or dashboard
                $redirect = $_GET['redirect'] ?? '';
                if (empty($redirect)) {
                    if ($user['role'] === 'seller' || $user['role'] === 'admin') {
                        $redirect = 'seller/index.php';
                    } else {
                        $redirect = 'dashboard.php';
                    }
                }
                header('Location: ' . $redirect);
                exit();
                
            } else {
                $errors['login'] = 'Invalid email or password';
            }
            
        } catch(PDOException $e) {
            $errors['database'] = 'Login failed. Please try again.';
        }
    }
}

// Check for remember me cookie
if (isset($_COOKIE['remember_token']) && !isLoggedIn()) {
    // Implement remember me functionality
    // This would validate the token and auto-login the user
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Login Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-shopping-bag fa-3x text-primary mb-3"></i>
                            <h2>Welcome Back</h2>
                            <p class="text-muted">Login to your account</p>
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
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </form>
                        
                        <div class="text-center">
                            <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Sign up</a></p>
                        </div>
                        
                        <hr class="my-4">
                        
                        <!-- Social Login -->
                        <div class="text-center mb-3">
                            <p class="text-muted">Or login with</p>
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
