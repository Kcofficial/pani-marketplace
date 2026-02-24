<?php
// Include Indonesian functions
require_once 'id_functions.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Paṇi Marketplace'; ?></title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- MDB Bootstrap -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php" style="font-family: 'Poppins', sans-serif; color: #2E86AB;">
                <i class="fas fa-shopping-bag me-2"></i>Paṇi
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><?php echo t('home'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="shop.php"><?php echo t('shop'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="categories.php"><?php echo t('categories'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php"><?php echo t('about'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php"><?php echo t('contact'); ?></a>
                    </li>
                </ul>
                
                <div class="d-flex align-items-center">
                    <!-- Search Bar -->
                    <form class="d-flex me-3" action="shop.php" method="GET">
                        <input class="form-control form-control-sm" type="search" name="search" placeholder="Cari produk..." style="min-width: 200px;">
                        <button class="btn btn-sm btn-outline-primary ms-2" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                    
                    <!-- Cart -->
                    <a href="cart.php" class="btn btn-outline-primary btn-sm me-2 position-relative">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?php echo getCartCount(); ?>
                        </span>
                    </a>
                    
                    <!-- User Menu -->
                    <?php if (isLoggedIn()): ?>
                        <div class="dropdown">
                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user me-1"></i>
                                <?php echo $_SESSION['user_name']; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit me-2"></i>Profil</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-box me-2"></i>Pesanan</a></li>
                                <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                                <?php if (isAdmin()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-cog me-2"></i>Panel Admin</a></li>
                                <?php endif; ?>
                                <?php if (isSeller()): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="seller/index.php"><i class="fas fa-store me-2"></i>Panel Penjual</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Keluar</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary btn-sm me-2"><?php echo t('login'); ?></a>
                        <a href="choose_account.php" class="btn btn-primary btn-sm"><?php echo t('register'); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
