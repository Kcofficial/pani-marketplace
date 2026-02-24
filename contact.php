<?php
$page_title = 'Kontak Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = cleanInput($_POST['name'] ?? '');
    $email = cleanInput($_POST['email'] ?? '');
    $subject = cleanInput($_POST['subject'] ?? '');
    $message = cleanInput($_POST['message'] ?? '');
    
    $errors = [];
    
    if (empty($name)) {
        $errors[] = 'Nama harus diisi';
    }
    
    if (empty($email)) {
        $errors[] = 'Email harus diisi';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    }
    
    if (empty($subject)) {
        $errors[] = 'Subjek harus diisi';
    }
    
    if (empty($message)) {
        $errors[] = 'Pesan harus diisi';
    }
    
    if (empty($errors)) {
        // Here you would normally send email or save to database
        // For demo purposes, we'll just show success message
        $success = 'Pesan Anda telah terkirim! Kami akan menghubungi Anda segera.';
    }
}
?>

<?php require_once 'includes/header.php'; ?>

<!-- Contact Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Hubungi Kami</h1>
                <p class="lead mb-4">Kami siap membantu Anda dengan pertanyaan, saran, atau masalah yang Anda hadapi. Tim Paṇi Marketplace selalu siap memberikan solusi terbaik untuk Anda.</p>
                <div class="d-flex gap-3">
                    <a href="#contact-form" class="btn btn-light btn-lg px-4">Kirim Pesan</a>
                    <a href="tel:081260952112" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-phone me-2"></i>Hubungi Langsung
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-headset fa-5x mb-4"></i>
                    <h3 class="mb-3">Layanan 24/7</h3>
                    <p class="mb-0">Tim customer service kami siap melayani Anda kapan saja, di mana saja</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Information Section -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Main Office -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="text-primary mb-3">
                            <i class="fas fa-building fa-3x"></i>
                        </div>
                        <h5 class="card-title mb-3">Kantor Pusat</h5>
                        <p class="text-muted mb-2">
                            <strong>Alamat:</strong><br>
                            Jl. Raya Suban, Pidada<br>
                            Panjang District, Bandar Lampung City<br>
                            Lampung 35241
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Telepon:</strong><br>
                            081260952112
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Email:</strong><br>
                            ict.jinarakkhita@gmail.com
                        </p>
                        <p class="text-muted mb-0">
                            <strong>Jam Operasional:</strong><br>
                            Senin - Sabtu: 09:00 - 18:00
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Customer Service -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="text-success mb-3">
                            <i class="fas fa-headset fa-3x"></i>
                        </div>
                        <h5 class="card-title mb-3">Customer Service</h5>
                        <p class="text-muted mb-2">
                            <strong>Hotline:</strong><br>
                            081260952112
                        </p>
                        <p class="text-muted mb-2">
                            <strong>WhatsApp:</strong><br>
                            081260952112
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Email Support:</strong><br>
                            support@pani-marketplace.com
                        </p>
                        <p class="text-muted mb-0">
                            <strong>Response Time:</strong><br>
                            1-2 jam kerja
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Business Inquiries -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <div class="text-info mb-3">
                            <i class="fas fa-briefcase fa-3x"></i>
                        </div>
                        <h5 class="card-title mb-3">Kerjasama Bisnis</h5>
                        <p class="text-muted mb-2">
                            <strong>Email Bisnis:</strong><br>
                            business@pani-marketplace.com
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Telepon Bisnis:</strong><br>
                            081260952112
                        </p>
                        <p class="text-muted mb-2">
                            <strong>Developer:</strong><br>
                            LPTIK
                        </p>
                        <p class="text-muted mb-0">
                            <strong>Website:</strong><br>
                            www.pani-marketplace.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form Section -->
<section class="py-5 bg-light" id="contact-form">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body p-5">
                        <h2 class="card-title text-center mb-4">Kirim Pesan</h2>
                        <p class="text-center text-muted mb-4">Ada pertanyaan atau saran? Kirimkan pesan Anda dan kami akan segera merespons.</p>
                        
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                           placeholder="Masukkan nama lengkap Anda" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                           placeholder="email@example.com" required>
                                </div>
                                <div class="col-12">
                                    <label for="subject" class="form-label">Subjek *</label>
                                    <input type="text" class="form-control" id="subject" name="subject" 
                                           value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>"
                                           placeholder="Subjek pesan Anda" required>
                                </div>
                                <div class="col-12">
                                    <label for="message" class="form-label">Pesan *</label>
                                    <textarea class="form-control" id="message" name="message" rows="5" 
                                              placeholder="Tulis pesan Anda di sini..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12 text-center">
                                    <button type="submit" name="submit_contact" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane me-2"></i>Kirim Pesan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="display-5 fw-bold text-center mb-5">Pertanyaan yang Sering Diajukan</h2>
                
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Bagaimana cara mendaftar sebagai penjual di Paṇi Marketplace?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Anda dapat mendaftar sebagai penjual dengan mengunjungi halaman registrasi penjual. Isi formulir informasi toko Anda, upload dokumen yang diperlukan, dan tim kami akan memproses pendaftaran Anda dalam 1-2 hari kerja.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Apa saja metode pembayaran yang tersedia?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Kami mendukung berbagai metode pembayaran: Transfer Bank (BCA, Mandiri, BNI, BRI), E-Wallet (GoPay, OVO, DANA, ShopeePay), QRIS, dan Cash on Delivery (COD) untuk kenyamanan bertransaksi Anda.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Bagaimana sistem pelacakan pesanan (tracking) bekerja?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Setiap pesanan memiliki sistem pelacakan real-time yang dapat Anda pantau melalui dashboard. Status pesanan akan diperbarui dari tahap pembayaran, pengemasan, pengiriman, hingga barang sampai di tujuan dengan detail lokasi yang akurat.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Apakah ada biaya untuk mendaftar sebagai penjual?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Pendaftaran sebagai penjual di Paṇi Marketplace gratis tanpa biaya pendaftaran. Kami hanya mengambil komisi kecil dari setiap transaksi yang berhasil. Komisi bervariasi tergantung pada kategori produk dan volume penjualan.
                            </div>
                        </div>
                    </div>
                    
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Bagaimana cara mengajukan keluhan atau pengembalian produk?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Untuk keluhan atau pengembalian, Anda dapat menghubungi customer service kami melalui email atau WhatsApp. Sertakan detail pesanan dan foto produk yang bermasalah. Tim kami akan memproses pengajuan Anda sesuai dengan kebijakan pengembalian yang berlaku.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Social Media Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Ikuti Kami di Media Sosial</h2>
            <p class="lead text-muted">Dapatkan update terbaru tentang produk, promo, dan tips berbelanja</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="d-flex justify-content-center gap-4">
                    <a href="#" class="btn btn-primary btn-lg">
                        <i class="fab fa-facebook-f me-2"></i>Facebook
                    </a>
                    <a href="#" class="btn btn-info btn-lg">
                        <i class="fab fa-twitter me-2"></i>Twitter
                    </a>
                    <a href="#" class="btn btn-danger btn-lg">
                        <i class="fab fa-instagram me-2"></i>Instagram
                    </a>
                    <a href="#" class="btn btn-success btn-lg">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <a href="#" class="btn btn-dark btn-lg">
                        <i class="fab fa-youtube me-2"></i>YouTube
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Map Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Temui Kami</h2>
            <p class="lead text-muted">Kunjungi kantor kami untuk konsultasi langsung</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-body p-0">
                        <!-- Google Maps Embed (placeholder) -->
                        <div class="map-container" style="height: 400px; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                            <div class="text-center">
                                <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
                                <h4>Peta Lokasi</h4>
                                <p class="text-muted">Jl. Raya Suban, Pidada, Panjang District<br>Bandar Lampung City, Lampung 35241</p>
                                <p class="small text-muted">Map akan ditampilkan setelah konfigurasi Google Maps API</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.accordion-button:not(.collapsed) {
    background-color: #667eea;
    color: white;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #667eea;
}

.map-container {
    border-radius: 0.375rem;
    overflow: hidden;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
    
    .btn-lg {
        padding: 0.5rem 1rem;
        font-size: 1rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
