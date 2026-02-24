# 🚀 GitHub Deployment Guide

## 📋 Prerequisites

- **GitHub Account**: Buat akun di [github.com](https://github.com)
- **Git**: Install Git di komputer Anda
- **PHP 8.0+**: Pastikan PHP versi 8.0 atau lebih tinggi
- **MySQL 8.0+**: Database server
- **Composer**: Dependency manager untuk PHP

## 🔧 Step 1: Setup Repository

### 1.1 Buat Repository Baru
1. Login ke GitHub
2. Klik "New repository"
3. Repository name: `pani-marketplace`
4. Description: `Modern Indonesian Marketplace Platform`
5. Pilih "Public" atau "Private"
6. Klik "Create repository"

### 1.2 Clone Repository
```bash
# Clone ke komputer lokal
git clone https://github.com/username/pani-marketplace.git
cd pani-marketplace

# Atau jika sudah ada folder
git remote add origin https://github.com/username/pani-marketplace.git
```

## 📁 Step 2: Prepare Files

### 2.1 Copy Project Files
```bash
# Copy semua file dari XAMPP ke repository
cp -r /xampp/htdocs/paṇi-App/* /path/to/pani-marketplace/

# Atau gunakan rsync untuk lebih baik
rsync -av /xampp/htdocs/paṇi-App/ /path/to/pani-marketplace/
```

### 2.2 Setup Environment
```bash
# Buat file konfigurasi
cp config/database.example.php config/database.php

# Edit file config
nano config/database.php
```

### 2.3 Install Dependencies
```bash
# Install Composer dependencies
composer install

# Install npm dependencies (jika ada)
npm install
```

## 🚀 Step 3: First Commit

### 3.1 Initialize Git (jika belum)
```bash
# Inisialisasi repository
git init
git add .
git commit -m "Initial commit: Paṇi Marketplace v1.0.0"
```

### 3.2 Add Remote dan Push
```bash
# Tambahkan remote origin
git remote add origin https://github.com/username/pani-marketplace.git

# Push ke GitHub
git push -u origin main
```

## 🌐 Step 4: Deployment Options

### Option A: GitHub Pages (Static)
```bash
# Build untuk production
npm run build

# Deploy ke GitHub Pages
git subtree push --prefix dist origin gh-pages
```

### Option B: VPS/Server Hosting
```bash
# Clone di server
git clone https://github.com/username/pani-marketplace.git /var/www/html/

# Setup database
mysql -u root -p < database.sql

# Configure permissions
chown -R www-data:www-data /var/www/html/
chmod -R 755 /var/www/html/
```

### Option C: Docker Deployment
```bash
# Build dan jalankan dengan Docker Compose
docker-compose up -d

# Akses di browser
open http://localhost:8080
```

### Option D: Cloud Platform (Heroku, DigitalOcean, dll)

#### Heroku Setup
```bash
# Install Heroku CLI
npm install -g heroku

# Login ke Heroku
heroku login

# Buat app
heroku create pani-marketplace

# Add database add-on
heroku addons:create heroku-postgresql:hobby-dev

# Set environment variables
heroku config:set APP_ENV=production
heroku config:set DB_HOST=$(heroku config:get DATABASE_URL)

# Deploy
git push heroku main
```

#### DigitalOcean App Platform
```bash
# Install doctl
curl -sL https://github.com/digitalocean/doctl/releases/download/v1.88.0 | tar xz && sudo mv doctl /usr/local/bin

# Login
doctl auth login

# Buat app
doctl apps create --region sgp1 pani-marketplace

# Deploy
doctl apps create-deployment pani-marketplace --image registry.digitalocean.com/username/pani-marketplace:latest
```

## 🔧 Step 5: Domain Configuration

### 5.1 Custom Domain
```bash
# Tambahkan custom domain di DNS
# A record: @ -> IP server
# CNAME record: www -> @ (jika ada)

# Update konfigurasi aplikasi
# Di config/database.php atau environment variables
define('APP_URL', 'https://yourdomain.com');
```

### 5.2 SSL Certificate
```bash
# Generate SSL dengan Let's Encrypt
sudo certbot --apache -d yourdomain.com

# Atau gunakan CloudFlare SSL gratis
# Setup di CloudFlare dashboard
```

## 📊 Step 6: Monitoring & Maintenance

### 6.1 Error Logging
```bash
# Monitor error logs
tail -f /var/log/apache2/error.log

# Monitor application logs
tail -f logs/app.log
```

### 6.2 Performance Monitoring
```bash
# Setup monitoring tools
# - Google Analytics
# - Uptime monitoring
# - Server resource monitoring
```

## 🔒 Step 7: Security

### 7.1 Environment Security
```bash
# Set file permissions yang aman
chmod 600 config/database.php
chmod 755 uploads/

# Hide sensitive files
echo "config/database.php" >> .htaccess
```

### 7.2 Firewall Configuration
```bash
# Buka port yang diperlukan saja
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

## 🔄 Step 8: CI/CD Pipeline

### 8.1 GitHub Actions
```yaml
# .github/workflows/deploy.yml
name: Deploy to Production
on:
  push:
    branches: [main]
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install --no-dev
      - name: Deploy to server
        run: |
          # Add deployment script here
```

### 8.2 Automated Testing
```bash
# Run tests sebelum deploy
php vendor/bin/phpunit

# Code quality check
php vendor/bin/phpstan analyse

# Code style check
php vendor/bin/php-cs-fixer fix --dry-run
```

## 📱 Step 9: Mobile App Deployment

### 9.1 Progressive Web App
```bash
# Build PWA
npm run build:pwa

# Service worker registration
# Tambahkan manifest.json dan service worker
```

### 9.2 App Store Submission
- **Google Play Store**: Prepare APK dan upload
- **Apple App Store**: Prepare IPA dan upload melalui Xcode

## 🎯 Quick Start Commands

### One-Command Setup
```bash
# Clone, install, dan deploy dalam satu command
curl -sL https://raw.githubusercontent.com/username/pani-marketplace/main/quick-deploy.sh | bash
```

### Development Server
```bash
# Start development server
php -S localhost:8000

# Dengan auto-reload
npm run dev
```

## 📈 Step 10: Scaling & Optimization

### 10.1 Database Optimization
```sql
-- Add indexes untuk performance
CREATE INDEX idx_products_seller ON products(seller_id);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_status ON products(status);
```

### 10.2 Caching Strategy
```php
// Setup Redis cache
$redis = new Redis();
$redis->set('products_' . $id, json_encode($product));

// Atau gunakan file cache
file_put_contents('cache/products_' . $id . '.json', json_encode($product));
```

### 10.3 CDN Setup
```html
<!-- Gunakan CDN untuk assets -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

## 🆘 Troubleshooting

### Common Issues & Solutions

#### Database Connection Error
```bash
# Check database credentials
php -r "echo 'DB_HOST: ' . DB_HOST . PHP_EOL;"

# Test connection
mysql -h localhost -u username -p database_name
```

#### Permission Denied Error
```bash
# Check file permissions
ls -la uploads/

# Fix permissions
sudo chown -R www-data:www-data uploads/
sudo chmod -R 755 uploads/
```

#### 404 Error
```bash
# Check .htaccess configuration
cat .htaccess

# Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

## 📞 Support & Resources

### Documentation
- 📖 [Full Documentation](https://github.com/username/pani-marketplace/wiki)
- 📧 [API Documentation](https://github.com/username/pani-marketplace/blob/main/docs/api.md)
- 🎥 [Video Tutorial](https://youtube.com/playlist?list=...)

### Community
- 💬 [Discussions](https://github.com/username/pani-marketplace/discussions)
- 🐛 [Issues](https://github.com/username/pani-marketplace/issues)
- 📧 [Pull Requests](https://github.com/username/pani-marketplace/pulls)

---

**🎉 Selamat! Paṇi Marketplace siap di-deploy ke GitHub!**

**Ikuti langkah-langkah di atas untuk deployment yang berhasil.** 🚀
