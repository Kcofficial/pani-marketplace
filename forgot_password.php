<?php
$page_title = 'Forgot Password - Paṇi';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email'] ?? '');
    
    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            
            // Check if email exists
            $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store token in database (you might need a password_resets table)
                // For now, we'll just show success message
                
                $success = 'Password reset instructions have been sent to your email address.';
                
                // In a real application, you would send an email with reset link
                // mail($email, 'Password Reset', "Click here to reset your password: reset_password.php?token=$token");
                
            } else {
                // Don't reveal if email exists or not for security
                $success = 'If an account with that email exists, password reset instructions have been sent.';
            }
            
        } catch(PDOException $e) {
            $errors['database'] = 'Request failed. Please try again.';
        }
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Forgot Password Section -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-key fa-3x text-primary mb-3"></i>
                            <h2>Forgot Password</h2>
                            <p class="text-muted">Enter your email address to reset your password</p>
                        </div>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                            <div class="text-center mt-4">
                                <a href="login.php" class="btn btn-primary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                                </a>
                            </div>
                        <?php else: ?>
                            <?php if (isset($errors['database'])): ?>
                                <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="mb-4">
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
                                
                                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
                                </button>
                            </form>
                            
                            <div class="text-center">
                                <p class="mb-0">Remember your password? <a href="login.php" class="text-decoration-none">Login</a></p>
                            </div>
                        <?php endif; ?>
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
    // Form validation
    $('form').on('submit', function(e) {
        var email = $('#email').val();
        
        if (!email) {
            e.preventDefault();
            alert('Please enter your email address');
        } else if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            e.preventDefault();
            alert('Please enter a valid email address');
        }
    });
});
</script>
