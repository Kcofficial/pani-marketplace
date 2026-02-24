<?php
// Indonesian language helper functions

// Indonesian translations
function t($key) {
    $translations = [
        // General
        'home' => 'Beranda',
        'shop' => 'Toko',
        'categories' => 'Kategori',
        'about' => 'Tentang',
        'contact' => 'Kontak',
        'login' => 'Masuk',
        'logout' => 'Keluar',
        'register' => 'Daftar',
        'dashboard' => 'Dashboard',
        'profile' => 'Profil',
        'settings' => 'Pengaturan',
        'search' => 'Cari',
        'cart' => 'Keranjang',
        'wishlist' => 'Daftar Keinginan',
        'orders' => 'Pesanan',
        'products' => 'Produk',
        'price' => 'Harga',
        'quantity' => 'Jumlah',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'shipping' => 'Pengiriman',
        'payment' => 'Pembayaran',
        'cancel' => 'Batal',
        'save' => 'Simpan',
        'edit' => 'Edit',
        'delete' => 'Hapus',
        'add' => 'Tambah',
        'view' => 'Lihat',
        'details' => 'Detail',
        'submit' => 'Kirim',
        'back' => 'Kembali',
        'next' => 'Lanjut',
        'previous' => 'Sebelumnya',
        'close' => 'Tutup',
        'yes' => 'Ya',
        'no' => 'Tidak',
        'ok' => 'OK',
        'error' => 'Error',
        'success' => 'Berhasil',
        'warning' => 'Peringatan',
        'info' => 'Informasi',
        
        // Navigation
        'welcome_to_eshop' => 'Selamat Datang di Paṇi',
        'discover_amazing_products' => 'Temukan produk luar biasa dengan harga tak tertandingi. Belanja dengan percaya diri dan nikmati pengiriman cepat!',
        'shop_now' => 'Belanja Sekarang',
        'view_products' => 'Lihat Produk',
        
        // User Authentication
        'email_address' => 'Alamat Email',
        'password' => 'Kata Sandi',
        'confirm_password' => 'Konfirmasi Kata Sandi',
        'full_name' => 'Nama Lengkap',
        'phone_number' => 'Nomor Telepon',
        'address' => 'Alamat',
        'remember_me' => 'Ingat Saya',
        'forgot_password' => 'Lupa Kata Sandi?',
        'create_account' => 'Buat Akun',
        'already_have_account' => 'Sudah punya akun?',
        'dont_have_account' => 'Belum punya akun?',
        
        // Product
        'add_to_cart' => 'Tambah ke Keranjang',
        'out_of_stock' => 'Habis',
        'in_stock' => 'Tersedia',
        'product_details' => 'Detail Produk',
        'description' => 'Deskripsi',
        'category' => 'Kategori',
        'rating' => 'Penilaian',
        'reviews' => 'Ulasan',
        'write_review' => 'Tulis Ulasan',
        'related_products' => 'Produk Terkait',
        
        // Cart
        'shopping_cart' => 'Keranjang Belanja',
        'your_cart_is_empty' => 'Keranjang Anda kosong',
        'continue_shopping' => 'Lanjutkan Belanja',
        'proceed_to_checkout' => 'Lanjut ke Pembayaran',
        'update_cart' => 'Perbarui Keranjang',
        'remove_item' => 'Hapus Item',
        
        // Checkout
        'checkout' => 'Checkout',
        'billing_information' => 'Informasi Penagihan',
        'shipping_information' => 'Informasi Pengiriman',
        'payment_method' => 'Metode Pembayaran',
        'place_order' => 'Buat Pesanan',
        'order_confirmation' => 'Konfirmasi Pesanan',
        'order_placed_successfully' => 'Pesanan berhasil dibuat',
        
        // Admin
        'admin_panel' => 'Panel Admin',
        'manage_products' => 'Kelola Produk',
        'manage_categories' => 'Kelola Kategori',
        'manage_orders' => 'Kelola Pesanan',
        'manage_users' => 'Kelola Pengguna',
        'reports' => 'Laporan',
        'total_users' => 'Total Pengguna',
        'total_products' => 'Total Produk',
        'total_orders' => 'Total Pesanan',
        'total_revenue' => 'Total Pendapatan',
        
        // Seller
        'seller_panel' => 'Panel Penjual',
        'my_products' => 'Produk Saya',
        'my_orders' => 'Pesanan Saya',
        'earnings' => 'Pendapatan',
        'analytics' => 'Analitik',
        'shop_appearance' => 'Tampilan Toko',
        'become_a_seller' => 'Menjadi Penjual',
        'start_selling' => 'Mulai Berjualan',
        'shop_name' => 'Nama Toko',
        'shop_description' => 'Deskripsi Toko',
        
        // Order Status
        'pending' => 'Menunggu',
        'processing' => 'Diproses',
        'shipped' => 'Dikirim',
        'delivered' => 'Terkirim',
        'cancelled' => 'Dibatalkan',
        
        // Messages
        'product_added_to_cart' => 'Produk berhasil ditambahkan ke keranjang',
        'product_removed_from_cart' => 'Produk berhasil dihapus dari keranjang',
        'cart_updated_successfully' => 'Keranjang berhasil diperbarui',
        'order_placed_successfully' => 'Pesanan berhasil dibuat',
        'profile_updated_successfully' => 'Profil berhasil diperbarui',
        'registration_successful' => 'Pendaftaran berhasil',
        'login_successful' => 'Login berhasil',
        'logout_successful' => 'Logout berhasil',
        
        // Validation
        'required_field' => 'Field ini wajib diisi',
        'invalid_email' => 'Format email tidak valid',
        'password_mismatch' => 'Kata sandi tidak cocok',
        'min_password_length' => 'Kata sandi minimal 6 karakter',
        'invalid_phone' => 'Format nomor telepon tidak valid',
        
        // Footer
        'about_us' => 'Tentang Kami',
        'privacy_policy' => 'Kebijakan Privasi',
        'terms_of_service' => 'Syarat & Ketentuan',
        'faq' => 'FAQ',
        'customer_support' => 'Layanan Pelanggan',
        'follow_us' => 'Ikuti Kami',
        'newsletter' => 'Newsletter',
        'subscribe' => 'Berlangganan',
        'all_rights_reserved' => 'Hak Cipta Dilindungi',
        
        // Currency
        'currency' => 'Rp',
    ];
    
    return $translations[$key] ?? $key;
}

// Format price in Indonesian Rupiah
function formatPriceIDR($price) {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

// Indonesian date format
function formatDateID($date) {
    $months = [
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    ];
    
    $date = date('d F Y', strtotime($date));
    foreach ($months as $en => $id) {
        $date = str_replace($en, $id, $date);
    }
    return $date;
}
?>
