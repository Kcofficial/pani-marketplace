<?php
session_start();
$page_title = 'Register - Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'config/database.php';

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = cleanInput($_POST['role'] ?? 'customer');
    $phone = cleanInput($_POST['phone'] ?? '');
    $address = cleanInput($_POST['address'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Nama harus diisi';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Format email tidak valid';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password harus diisi';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password minimal 6 karakter';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Password tidak cocok';
    }
    
    if (!in_array($role, ['customer', 'seller'])) {
        $errors['role'] = 'Role tidak valid';
    }
    
    if (empty($errors)) {
        try {
            // Check if email already exists
            $stmt = getDBConnection()->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors['email'] = 'Email sudah terdaftar';
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = getDBConnection()->prepare("
                    INSERT INTO users (name, email, password, role, phone, address, status, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
                ");
                $stmt->execute([$name, $email, $hashed_password, $role, $phone, $address]);
                
                $success = 'Registrasi berhasil! Silakan login.';
                
                // Clear form
                $_POST = [];
            }
        } catch(PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            $errors['general'] = 'Terjadi kesalahan, silakan coba lagi';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Paṇi Marketplace</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .register-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        .register-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .role-option {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .role-option:hover {
            transform: scale(1.02);
        }
        .role-option.selected {
            border-color: #f97316;
            background-color: #fff7ed;
        }
    </style>
</head>
<body class="register-container flex items-center justify-center">
    <div class="container mx-auto px-4">
        <div class="max-w-md w-full">
            <!-- Logo and Title -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-white mb-2">Paṇi</h1>
                <p class="text-white/80">Marketplace Terpercaya</p>
            </div>
            
            <!-- Register Card -->
            <div class="register-card rounded-2xl p-8 shadow-2xl">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    <i class="fas fa-user-plus mr-2 text-orange-500"></i>
                    Register
                </h2>
                
                <!-- Error/Success Messages -->
                <?php if ($success): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
                        <i class="fas fa-check-circle mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Register Form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-1"></i>
                            Nama Lengkap
                        </label>
                        <input type="text" name="name" required
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               placeholder="John Doe">
                    </div>
                    
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
                               placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-1"></i>
                            Konfirmasi Password
                        </label>
                        <input type="password" name="confirm_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               placeholder="Ulangi password">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-phone mr-1"></i>
                            No. Telepon
                        </label>
                        <input type="tel" name="phone"
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                               placeholder="+62 812-3456-7890">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Alamat
                        </label>
                        <textarea name="address" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                  placeholder="Jl. Contoh No. 123, Jakarta"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <!-- Role Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tag mr-1"></i>
                            Daftar sebagai
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="role-option border-2 rounded-lg p-4 text-center <?php echo ($_POST['role'] ?? 'customer') === 'customer' ? 'selected' : ''; ?>"
                                 onclick="selectRole('customer')">
                                <i class="fas fa-user text-green-600 text-2xl mb-2"></i>
                                <p class="font-medium text-green-800">Customer</p>
                                <p class="text-sm text-green-600">Belanja produk</p>
                            </div>
                            <div class="role-option border-2 rounded-lg p-4 text-center <?php echo ($_POST['role'] ?? '') === 'seller' ? 'selected' : ''; ?>"
                                 onclick="selectRole('seller')">
                                <i class="fas fa-store text-blue-600 text-2xl mb-2"></i>
                                <p class="font-medium text-blue-800">Seller</p>
                                <p class="text-sm text-blue-600">Jual produk</p>
                            </div>
                        </div>
                        <input type="hidden" name="role" id="selectedRole" value="<?php echo htmlspecialchars($_POST['role'] ?? 'customer'); ?>">
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-orange-500 text-white py-3 px-4 rounded-lg hover:bg-orange-600 focus:ring-4 focus:ring-orange-500 focus:ring-opacity-50 transition-all duration-200 font-medium">
                        <i class="fas fa-user-plus mr-2"></i>
                        Register
                    </button>
                </form>
                
                <!-- Login Link -->
                <div class="mt-6 text-center">
                    <p class="text-gray-600">
                        Sudah punya akun? 
                        <a href="login.php" class="text-orange-500 hover:text-orange-600 font-medium">
                            Login sekarang
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectRole(role) {
            // Remove selected class from all options
            document.querySelectorAll('.role-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            event.currentTarget.classList.add('selected');
            
            // Update hidden input
            document.getElementById('selectedRole').value = role;
        }
    </script>
</body>
</html>
