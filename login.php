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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Paṇi Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .login-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .role-badge {
            transition: all 0.3s ease;
        }
        .role-badge:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body class="login-container flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md w-full">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">Paṇi</h1>
                <p class="text-white/80">Marketplace Terpercaya</p>
            </div>
            
            <!-- Login Card -->
            <div class="login-card rounded-2xl p-8 shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    <i class="fas fa-sign-in-alt mr-2 text-orange-500"></i>
                    Login
                </h2>
                
                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Login Form -->
                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-1"></i>
                            Email
                        </label>
                        <input type="email" name="email" required
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               placeholder="nama@email.com">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Password
                        </label>
                        <input type="password" name="password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               placeholder="•••••••••">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="rounded border-gray-300 text-orange-500 focus:ring-orange-500">
                            <span class="ml-2 text-sm text-gray-600">Remember me</span>
                        </label>
                        <a href="forgot-password.php" class="text-sm text-orange-500 hover:text-orange-600">
                            Forgot password?
                        </a>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-orange-500 text-white py-3 px-4 rounded-lg hover:bg-orange-600 focus:ring-4 focus:ring-orange-500 focus:ring-opacity-50 transition-all duration-200 font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Login
                    </button>
                </form>
                
                <!-- Register Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Belum punya akun? 
                        <a href="register.php" class="text-orange-500 hover:text-orange-600 font-medium">
                            Daftar sekarang
                        </a>
                    </p>
                </div>
                
                <!-- Role Information -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-4 text-center">Login sebagai:</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="role-badge text-center p-3 bg-green-50 rounded-lg border border-green-200">
                            <i class="fas fa-user text-green-600 text-xl mb-2"></i>
                            <p class="text-sm font-medium text-green-800">Customer</p>
                            <p class="text-xs text-green-600">Belanja produk</p>
                        </div>
                        <div class="role-badge text-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <i class="fas fa-store text-blue-600 text-xl mb-2"></i>
                            <p class="text-sm font-medium text-blue-800">Seller</p>
                            <p class="text-xs text-blue-600">Jual produk</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
