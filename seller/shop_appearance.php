<?php
$page_title = 'Shop Appearance - Paṇi Seller';
require_once '../includes/functions.php';
require_once '../config/database.php';

requireSeller();

$seller_info = [];
$errors = [];
$success = '';

try {
    $conn = getDBConnection();
    $seller_id = $_SESSION['user_id'];
    
    // Get seller information
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$seller_id]);
    $seller_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    $seller_info = [];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop_name = cleanInput($_POST['shop_name'] ?? '');
    $shop_description = cleanInput($_POST['shop_description'] ?? '');
    $shop_banner_color = cleanInput($_POST['shop_banner_color'] ?? '#2E86AB');
    $shop_theme = cleanInput($_POST['shop_theme'] ?? 'default');
    
    // Validate form data
    if (empty($shop_name)) {
        $errors['shop_name'] = 'Shop name is required';
    } elseif (strlen($shop_name) < 3) {
        $errors['shop_name'] = 'Shop name must be at least 3 characters';
    }
    
    if (empty($errors)) {
        try {
            $conn = getDBConnection();
            $seller_id = $_SESSION['user_id'];
            
            // Update seller information
            $stmt = $conn->prepare("UPDATE users SET name = ?, address = ? WHERE id = ?");
            $stmt->execute([$shop_name, $shop_description, $seller_id]);
            
            // In a real application, you would store theme settings in a separate table
            // For now, we'll use session or create a seller_settings table
            
            $success = 'Shop appearance updated successfully!';
            
            // Reload seller info
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$seller_id]);
            $seller_info = $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch(PDOException $e) {
            $errors['database'] = 'Update failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- MDB Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Seller Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-store me-2"></i>Paṇi Seller
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">My Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="product_add.php">
                            <i class="fas fa-plus me-1"></i>Add Product
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="earnings.php">Earnings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analytics.php">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="shop_appearance.php">Shop Appearance</a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i>
                            <?php echo $_SESSION['user_name']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="../profile.php"><i class="fas fa-user-edit me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../index.php"><i class="fas fa-store me-2"></i>View Store</a></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">
                                <i class="fas fa-box me-2"></i>My Products
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="product_add.php">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="orders.php">
                                <i class="fas fa-shopping-cart me-2"></i>Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="earnings.php">
                                <i class="fas fa-dollar-sign me-2"></i>Earnings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="analytics.php">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="shop_appearance.php">
                                <i class="fas fa-palette me-2"></i>Shop Appearance
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Shop Appearance</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-outline-success" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i>Refresh
                        </button>
                    </div>
                </div>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($errors['database'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['database']; ?></div>
                <?php endif; ?>

                <!-- Shop Preview -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Shop Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="border rounded p-4" style="background-color: #f8f9fa;">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" 
                                             style="width: 60px; height: 60px; font-size: 24px; font-weight: bold;">
                                            <?php echo strtoupper(substr($seller_info['name'], 0, 2)); ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-0"><?php echo htmlspecialchars($seller_info['name']); ?></h5>
                                            <p class="text-muted small mb-0">Professional Seller</p>
                                        </div>
                                    </div>
                                    <p class="mb-3"><?php echo htmlspecialchars($seller_info['address'] ?? 'Your shop description will appear here'); ?></p>
                                    <div class="d-flex gap-2">
                                        <span class="badge bg-success">Verified Seller</span>
                                        <span class="badge bg-info">Fast Shipping</span>
                                        <span class="badge bg-warning">Top Rated</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6>Shop Stats</h6>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border rounded p-2 mb-2">
                                                <div class="h5 mb-0">-</div>
                                                <small class="text-muted">Products</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border rounded p-2 mb-2">
                                                <div class="h5 mb-0">-</div>
                                                <small class="text-muted">Sales</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Customize Your Shop</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="shop_name" class="form-label">Shop Name</label>
                                    <input type="text" class="form-control" id="shop_name" name="shop_name" 
                                           value="<?php echo htmlspecialchars($seller_info['name'] ?? ''); ?>" required>
                                    <?php if (isset($errors['shop_name'])): ?>
                                        <div class="text-danger small mt-1"><?php echo $errors['shop_name']; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">This will be displayed as your shop name</div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="shop_banner_color" class="form-label">Banner Color</label>
                                    <div class="input-group">
                                        <input type="color" class="form-control form-control-color" id="shop_banner_color" 
                                               name="shop_banner_color" value="#2E86AB">
                                        <input type="text" class="form-control" value="#2E86AB" readonly>
                                    </div>
                                    <div class="form-text">Choose your shop's banner color</div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="shop_description" class="form-label">Shop Description</label>
                                <textarea class="form-control" id="shop_description" name="shop_description" rows="4" 
                                          placeholder="Tell customers about your shop, your products, and what makes you unique..."><?php echo htmlspecialchars($seller_info['address'] ?? ''); ?></textarea>
                                <div class="form-text">Describe your shop and what makes it special (max 500 characters)</div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Shop Theme</label>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shop_theme" id="theme_default" value="default" checked>
                                            <label class="form-check-label" for="theme_default">
                                                <div class="border rounded p-3 text-center">
                                                    <i class="fas fa-palette fa-2x mb-2"></i>
                                                    <div>Default</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shop_theme" id="theme_modern" value="modern">
                                            <label class="form-check-label" for="theme_modern">
                                                <div class="border rounded p-3 text-center">
                                                    <i class="fas fa-brush fa-2x mb-2"></i>
                                                    <div>Modern</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shop_theme" id="theme_classic" value="classic">
                                            <label class="form-check-label" for="theme_classic">
                                                <div class="border rounded p-3 text-center">
                                                    <i class="fas fa-crown fa-2x mb-2"></i>
                                                    <div>Classic</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="shop_theme" id="theme_minimal" value="minimal">
                                            <label class="form-check-label" for="theme_minimal">
                                                <div class="border rounded p-3 text-center">
                                                    <i class="fas fa-circle fa-2x mb-2"></i>
                                                    <div>Minimal</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shop Logo Upload -->
                            <div class="mb-4">
                                <label for="shop_logo" class="form-label">Shop Logo</label>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="border rounded d-flex align-items-center justify-content-center" 
                                             style="height: 120px; background-color: #f8f9fa;">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="file" class="form-control" id="shop_logo" accept="image/*">
                                        <div class="form-text">Upload your shop logo (PNG, JPG, max 2MB)</div>
                                        <button type="button" class="btn btn-outline-success btn-sm mt-2">Upload Logo</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shop Banner Upload -->
                            <div class="mb-4">
                                <label for="shop_banner" class="form-label">Shop Banner</label>
                                <div class="border rounded p-4 text-center" style="background-color: #f8f9fa;">
                                    <i class="fas fa-image fa-3x text-muted mb-3"></i>
                                    <p class="mb-3">Upload a banner for your shop</p>
                                    <input type="file" class="form-control" id="shop_banner" accept="image/*">
                                    <div class="form-text">Recommended size: 1200x300px (PNG, JPG, max 5MB)</div>
                                    <button type="button" class="btn btn-outline-success btn-sm mt-2">Upload Banner</button>
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="location.reload()">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- MDB JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../assets/js/script.js"></script>

    <script>
        $(document).ready(function() {
            // Color picker synchronization
            $('#shop_banner_color').on('input', function() {
                $(this).next('input').val($(this).val());
            });
            
            // Theme selection
            $('input[name="shop_theme"]').on('change', function() {
                $('.form-check-label .border').removeClass('border-success');
                $(this).next('label').find('.border').addClass('border-success');
            });
            
            // File upload preview (placeholder functionality)
            $('#shop_logo').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // In a real application, you would preview the image
                    console.log('Logo selected:', file.name);
                }
            });
            
            $('#shop_banner').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // In a real application, you would preview the image
                    console.log('Banner selected:', file.name);
                }
            });
        });
    </script>
</body>
</html>
