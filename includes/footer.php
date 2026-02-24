<!-- Footer -->
    <footer class="bg-dark text-white mt-5">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-4">
                    <h5 class="mb-3" style="font-family: 'Poppins', sans-serif;">Tentang Paṇi</h5>
                    <p>Marketplace terpercaya Anda untuk produk berkualitas. Kami menawarkan berbagai macam produk dengan pengiriman cepat dan layanan pelanggan yang sangat baik.</p>
                    <div class="mt-3">
                        <a href="#" class="text-white me-3"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-white"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <h5 class="mb-3" style="font-family: 'Poppins', sans-serif;">Link Cepat</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Beranda</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-white text-decoration-none">Toko</a></li>
                        <li class="mb-2"><a href="about.php" class="text-white text-decoration-none">Tentang Kami</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none">Kontak</a></li>
                        <li class="mb-2"><a href="faq.php" class="text-white text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4">
                    <h5 class="mb-3" style="font-family: 'Poppins', sans-serif;">Info Kontak</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i>Jl. Raya Suban, Pidada, Panjang District, Bandar Lampung City, Lampung 35241</li>
                        <li class="mb-2"><i class="fas fa-phone me-2"></i>081260952112</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2"></i>ict.jinarakkhita@gmail.com</li>
                        <li class="mb-2"><i class="fas fa-clock me-2"></i>Senin - Sabtu: 09:00 - 18:00</li>
                    </ul>
                </div>
            </div>
            
            <hr class="border-secondary">
            
            <div class="row">
                <div class="col-md-6">
                    <p>&copy; 2026 Paṇi Marketplace. Dikembangkan oleh LPTIK. Semua hak dilindungi.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <a href="privacy.php" class="text-white text-decoration-none me-3">Kebijakan Privasi</a>
                    <a href="terms.php" class="text-white text-decoration-none">Syarat & Ketentuan</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- MDB JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    
    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="assets/js/script.js"></script>
    
    <?php if (isset($_SESSION['alert'])): ?>
        <script>
            $(document).ready(function() {
                displayAlert('<?php echo $_SESSION['alert']['type']; ?>', '<?php echo $_SESSION['alert']['message']; ?>');
            });
        </script>
        <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>
</body>
</html>
