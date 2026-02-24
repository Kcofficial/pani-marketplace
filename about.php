<?php
$page_title = 'Tentang Paṇi Marketplace';
require_once 'includes/functions.php';
require_once 'includes/id_functions.php';
require_once 'config/database.php';
?>

<?php require_once 'includes/header.php'; ?>

<!-- About Hero Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Tentang Paṇi Marketplace</h1>
                <p class="lead mb-4">Platform ekosistem digital yang dirancang khusus untuk menjembatani pelaku UMKM dan konsumen melalui teknologi yang cerdas, transparan, dan terpercaya.</p>
                <div class="d-flex gap-3">
                    <a href="#features" class="btn btn-light btn-lg px-4">Pelajari Lebih Lanjut</a>
                    <a href="shop.php" class="btn btn-outline-light btn-lg px-4">Mulai Belanja</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-shopping-bag fa-5x mb-4"></i>
                    <h3 class="mb-3">Paṇi Marketplace</h3>
                    <p class="mb-0">Inovasi Teknologi untuk Pertumbuhan Ekonomi Digital Indonesia</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Content Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center mb-5">
                <h2 class="display-5 fw-bold mb-4">Platform Ekosistem Digital</h2>
                <p class="lead text-muted">Paṇi Marketplace dikembangkan di bawah inisiasi Dedi Kundana, S.Pd., M.T.I., seorang praktisi dan akademisi di bidang Teknologi Informasi dan Manajemen Teknologi.</p>
            </div>
        </div>
        
        <div class="row g-4">
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                        </div>
                        <h4 class="card-title text-center mb-3">Visi & Misi</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <strong>Memberdayakan UMKM</strong>
                                        <p class="text-muted small mb-0">Memberikan akses teknologi setara marketplace besar bagi pengusaha lokal untuk mengelola produk dan pesanan secara profesional.</p>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <strong>Transparansi Transaksi</strong>
                                        <p class="text-muted small mb-0">Menyediakan sistem pelacakan pembelian (Purchase Tracking) yang akurat dan real-time bagi pembeli dan penjual.</p>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <div>
                                        <strong>Inovasi Berkelanjutan</strong>
                                        <p class="text-muted small mb-0">Mengintegrasikan riset teknologi terkini, seperti Aspect-Based Sentiment Analysis, untuk membantu penjual memahami kepuasan pelanggan secara mendalam.</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-body p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-cogs fa-3x text-info mb-3"></i>
                        </div>
                        <h4 class="card-title text-center mb-3">Fitur Utama Sistem</h4>
                        <ul class="list-unstyled">
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-map-marked-alt text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Sistem Pelacakan Mandiri</strong>
                                        <p class="text-muted small mb-0">Pembeli dapat memantau status pesanan mulai dari tahap pembayaran, pengemasan, hingga barang sampai di tujuan dengan riwayat lokasi yang detail.</p>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-credit-card text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Fleksibilitas Pembayaran</strong>
                                        <p class="text-muted small mb-0">Mendukung berbagai kanal pembayaran lokal Indonesia, termasuk Transfer Bank, E-Wallet (GoPay, OVO, DANA), hingga QRIS dan COD.</p>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-chart-line text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Dashboard Penjual yang Cerdas</strong>
                                        <p class="text-muted small mb-0">Fitur manajemen produk, stok, dan pendapatan yang dirancang intuitif untuk memudahkan operasional bisnis sehari-hari.</p>
                                    </div>
                                </div>
                            </li>
                            <li class="mb-3">
                                <div class="d-flex">
                                    <i class="fas fa-shield-alt text-primary me-3 mt-1"></i>
                                    <div>
                                        <strong>Keamanan Data</strong>
                                        <p class="text-muted small mb-0">Dibangun dengan standar keamanan modern (Role-based Access Control) untuk memastikan data pengguna dan transaksi tetap terlindungi.</p>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology & Research Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <div class="pe-lg-5">
                    <h2 class="display-5 fw-bold mb-4">Landasan Teknologi & Riset</h2>
                    <p class="lead mb-4">Paṇi Marketplace bukan sekadar aplikasi jual beli biasa. Platform ini menjadi laboratorium hidup bagi pengembangan riset Manajemen Teknologi dan Kecerdasan Buatan (AI).</p>
                    <p class="mb-4">Fokus kami adalah menciptakan sistem yang tidak hanya berfungsi secara teknis, tetapi juga memberikan nilai strategis bagi pertumbuhan ekonomi digital di Indonesia.</p>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-brain fa-2x text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-0">AI Integration</h6>
                                    <small class="text-muted">Sentiment Analysis</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-database fa-2x text-success me-3"></i>
                                <div>
                                    <h6 class="mb-0">Big Data</h6>
                                    <small class="text-muted">Analytics Platform</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-mobile-alt fa-2x text-info me-3"></i>
                                <div>
                                    <h6 class="mb-0">Mobile First</h6>
                                    <small class="text-muted">Responsive Design</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-lock fa-2x text-warning me-3"></i>
                                <div>
                                    <h6 class="mb-0">Security</h6>
                                    <small class="text-muted">Enterprise Grade</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <img src="https://via.placeholder.com/600x400" class="img-fluid rounded-3 shadow-lg" alt="Technology Platform">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-primary mb-2">1000+</h3>
                    <p class="text-muted">UMKM Terdaftar</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-success mb-2">50K+</h3>
                    <p class="text-muted">Produk Aktif</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-info mb-2">200K+</h3>
                    <p class="text-muted">Transaksi Sukses</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stat-item">
                    <h3 class="display-4 fw-bold text-warning mb-2">98%</h3>
                    <p class="text-muted">Kepuasan Pelanggan</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-4">Tim Pengembang</h2>
            <p class="lead text-muted">Dipimpin oleh praktisi dan akademisi berpengalaman di bidang teknologi informasi</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-md-4 mb-4">
                <div class="card text-center shadow-sm">
                    <div class="card-body p-4">
                        <img src="https://via.placeholder.com/150x150" class="rounded-circle mb-3" alt="Team Member">
                        <h5 class="card-title">Dedi Kundana, S.Pd., M.T.I.</h5>
                        <p class="text-muted mb-2">Founder & Lead Developer</p>
                        <p class="small text-muted">Praktisi dan akademisi di bidang Teknologi Informasi dan Manajemen Teknologi dengan pengalaman lebih dari 10 tahun.</p>
                        <div class="d-flex justify-content-center gap-2">
                            <a href="#" class="text-primary"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-primary"><i class="fab fa-github"></i></a>
                            <a href="#" class="text-primary"><i class="fas fa-envelope"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-gradient-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="display-5 fw-bold mb-4">Bergabunglah dengan Paṇi Marketplace</h2>
                <p class="lead mb-4">Mari bersama membangun ekosistem digital yang mendukung pertumbuhan UMKM Indonesia. Baik Anda sebagai pembeli yang mencari produk berkualitas atau penjual yang ingin mengembangkan bisnis, Paṇi Marketplace siap membantu Anda.</p>
            </div>
            <div class="col-lg-4">
                <div class="d-grid gap-2">
                    <a href="register.php" class="btn btn-light btn-lg">Daftar Sekarang</a>
                    <a href="register_seller.php" class="btn btn-outline-light btn-lg">Jadi Penjual</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-item {
    padding: 2rem;
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.rounded-circle {
    width: 120px;
    height: 120px;
    object-fit: cover;
}

@media (max-width: 768px) {
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
