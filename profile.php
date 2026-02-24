<?php
$page_title = 'Profile - Paṇi';
require_once 'includes/functions.php';
require_once 'config/database.php';

requireLogin();

$user_data = [];
$errors = [];
$success = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate basic fields
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }
    
    // Validate password change if provided
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {
        if (empty($current_password)) {
            $errors['current_password'] = 'Current password is required';
        }
        if (empty($new_password)) {
            $errors['new_password'] = 'New password is required';
        } elseif (strlen($new_password) < 6) {
            $errors['new_password'] = 'Password must be at least 6 characters';
        }
        if (empty($confirm_password)) {
            $errors['confirm_password'] = 'Please confirm your new password';
        } elseif ($new_password !== $confirm_password) {
            $errors['confirm_password'] = 'Passwords do not match';
        }
    }
    
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            $user_id = $_SESSION['user_id'];
            
            // Verify current password if changing password
            if (!empty($current_password)) {
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!password_verify($current_password, $user['password'])) {
                    $errors['current_password'] = 'Current password is incorrect';
                }
            }
            
            if (empty($errors)) {
                // Check if email is taken by another user
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                if ($stmt->fetch()) {
                    $errors['email'] = 'Email is already taken';
                }
            }
            
            if (empty($errors)) {
                // Update profile
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $address, $hashed_password, $user_id]);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
                    $stmt->execute([$name, $email, $phone, $address, $user_id]);
                }
                
                // Update session data
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                
                $success = 'Profile updated successfully!';
                
                // Reload user data
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
        } catch(PDOException $e) {
            $errors['database'] = 'Update failed. Please try again.';
        }
    }
}

// Get current user data
if (empty($user_data)) {
    try {
        $conn = getDBConnection();
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $user_data = [];
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Profile Header -->
<section class="py-4 bg-light">
    <div class="container">
        <h1 class="mb-0">My Profile</h1>
        <p class="text-muted mb-0">Manage your personal information</p>
    </div>
</section>

<!-- Profile Content -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Profile Form -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-4">Personal Information</h5>
                        
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
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="role" class="form-label">Account Type</label>
                                    <input type="text" class="form-control" id="role" 
                                           value="<?php echo ucfirst($user_data['role'] ?? 'user'); ?>" readonly>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">Change Password</h6>
                            <p class="text-muted small mb-3">Leave blank if you don't want to change your password</p>
                            
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                    <?php if (isset($errors['current_password'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['current_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                    <?php if (isset($errors['new_password'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['new_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    <?php if (isset($errors['confirm_password'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['confirm_password']; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="dashboard.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Profile Summary -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <img src="https://via.placeholder.com/100x100/2E86AB/FFFFFF?text=<?php echo strtoupper(substr($user_data['name'], 0, 2)); ?>" 
                                 class="rounded-circle" alt="Profile Avatar" style="width: 100px; height: 100px;">
                        </div>
                        <h5><?php echo htmlspecialchars($user_data['name']); ?></h5>
                        <p class="text-muted"><?php echo htmlspecialchars($user_data['email']); ?></p>
                        <span class="badge bg-primary"><?php echo ucfirst($user_data['role']); ?></span>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Account Stats</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Member Since</span>
                            <strong><?php echo date('M Y', strtotime($user_data['created_at'])); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Total Orders</span>
                            <strong id="total-orders">-</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Total Spent</span>
                            <strong id="total-spent">-</strong>
                        </div>
                    </div>
                </div>
                
                <!-- Account Actions -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title mb-3">Account Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="orders.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-list me-2"></i>View Orders
                            </a>
                            <a href="wishlist.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-heart me-2"></i>My Wishlist
                            </a>
                            <a href="addresses.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-map-marker-alt me-2"></i>Manage Addresses
                            </a>
                            <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="fas fa-trash me-2"></i>Delete Account
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone and will permanently delete:</p>
                <ul>
                    <li>Your profile information</li>
                    <li>Order history</li>
                    <li>Wishlist items</li>
                    <li>Cart contents</li>
                </ul>
                <p class="text-danger">This is a permanent action and cannot be reversed.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete Account</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
$(document).ready(function() {
    // Load account stats
    $.ajax({
        url: 'includes/get_user_stats.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $('#total-orders').text(response.total_orders);
            $('#total-spent').text(response.total_spent);
        }
    });
    
    // Password confirmation validation
    $('#confirm_password').on('input', function() {
        var newPassword = $('#new_password').val();
        var confirmPassword = $(this).val();
        
        if (confirmPassword && newPassword !== confirmPassword) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
    
    // Delete account confirmation
    $('#confirmDelete').on('click', function() {
        if (confirm('Are you absolutely sure? This cannot be undone!')) {
            // In a real application, you would send an AJAX request to delete the account
            alert('Account deletion would be processed here');
            $('#deleteAccountModal').modal('hide');
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        var newPassword = $('#new_password').val();
        var confirmPassword = $('#confirm_password').val();
        var currentPassword = $('#current_password').val();
        
        // If any password field is filled, all must be filled
        if (newPassword || confirmPassword || currentPassword) {
            if (!currentPassword || !newPassword || !confirmPassword) {
                e.preventDefault();
                alert('Please fill in all password fields to change password');
            } else if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match');
            }
        }
    });
});
</script>
