# Paṇi Marketplace

A modern Indonesian marketplace platform built with PHP, MySQL, and Tailwind CSS.

## Features

### 🛍️ Customer Features
- Browse products with advanced filtering
- Product details with image gallery
- Shopping cart and checkout
- Seller communication via chat
- Order tracking and history
- User reviews and ratings

### 🏪 Seller Features
- Product management (CRUD operations)
- Store dashboard with analytics
- Order management and fulfillment
- Customer communication
- Sales statistics and reporting
- Store customization

### 👑 Admin Features
- User management and roles
- Product approval and moderation
- Order management across all sellers
- System analytics and reporting
- Category management
- Revenue tracking

## Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 8.0+
- **Frontend**: Tailwind CSS + Vanilla JavaScript
- **Icons**: Font Awesome 6
- **Security**: Password hashing, prepared statements, XSS protection

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Web server (Apache/Nginx)
- Composer (for dependencies)

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/username/pani-marketplace.git
   cd pani-marketplace
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database setup**
   ```bash
   # Create database
   mysql -u root -p
   CREATE DATABASE pani_marketplace;
   
   # Import database structure
   mysql -u root -p pani_marketplace < database.sql
   ```

4. **Configure database**
   ```bash
   cp config/database.example.php config/database.php
   # Edit config/database.php with your credentials
   ```

5. **Set permissions**
   ```bash
   chmod -R 755 .
   chmod -R 777 uploads/
   ```

6. **Start development server**
   ```bash
   php -S localhost:8000
   ```

### Production Deployment

#### Using cPanel
1. Upload files to public_html directory
2. Create MySQL database via cPanel
3. Import database.sql
4. Configure config/database.php
5. Set file permissions

#### Using Docker
```bash
# Build image
docker build -t pani-marketplace .

# Run container
docker run -p 8080:80 pani-marketplace
```

## Configuration

### Environment Variables
Create `.env` file:
```env
DB_HOST=localhost
DB_NAME=pani_marketplace
DB_USER=your_username
DB_PASS=your_password
APP_URL=https://yourdomain.com
```

### Database Setup
Run the installation script:
```bash
php install/setup.php
```

## Project Structure

```
pani-marketplace/
├── api/                    # API endpoints
├── config/                  # Configuration files
├── includes/                 # Helper functions
├── uploads/                  # User uploads
├── templates/                # Email templates
├── assets/                   # Static assets
├── database.sql              # Database schema
├── install/                 # Installation scripts
├── index.php                # Homepage
├── login.php                # User login
├── register.php             # User registration
├── shop.php                 # Product listing
├── product_pani.php         # Product details
├── seller-dashboard.php      # Seller dashboard
├── admin-dashboard.php       # Admin dashboard
└── README.md                # This file
```

## API Endpoints

### Authentication
- `POST /api/login.php` - User login
- `POST /api/register.php` - User registration
- `POST /api/logout.php` - User logout

### Products
- `GET /api/products.php` - List products
- `POST /api/add-product.php` - Add product (seller)
- `PUT /api/update-product.php` - Update product (seller)
- `DELETE /api/delete-product.php` - Delete product (seller)

### Orders
- `GET /api/orders.php` - List orders
- `POST /api/create-order.php` - Create order
- `PUT /api/update-order.php` - Update order status

## Security Features

- **Authentication**: Session-based authentication
- **Authorization**: Role-based access control
- **Input Validation**: Server-side validation
- **SQL Injection Protection**: Prepared statements
- **XSS Protection**: Output escaping
- **CSRF Protection**: Token validation
- **Password Security**: Hashing and complexity requirements

## Performance Optimization

- **Database Indexing**: Optimized queries
- **Caching**: Session and query caching
- **Image Optimization**: Lazy loading and compression
- **Minification**: CSS and JS minification
- **CDN Ready**: Asset CDN integration

## Testing

### Run Tests
```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test
php vendor/bin/phpunit tests/AuthTest.php
```

### Test Coverage
- Unit tests for core functions
- Integration tests for API endpoints
- Security tests for authentication
- Performance tests for database queries

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -am 'Add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Create Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- 📧 **Development**: [GitHub Issues](https://github.com/username/pani-marketplace/issues)
- 📧 **Documentation**: [Wiki](https://github.com/username/pani-marketplace/wiki)
- 📧 **Discussions**: [GitHub Discussions](https://github.com/username/pani-marketplace/discussions)

## Roadmap

### Version 2.0
- [ ] Mobile app development
- [ ] Payment gateway integration
- [ ] Advanced analytics dashboard
- [ ] Multi-language support
- [ ] Vendor management system

### Version 1.5
- [ ] Email notifications
- [ ] Product recommendation system
- [ ] Advanced search filters
- [ ] Social login integration

## Changelog

### v1.0.0 (2024-02-24)
- ✅ Initial release
- ✅ Core marketplace functionality
- ✅ Role-based authentication
- ✅ Product management
- ✅ Order processing
- ✅ Admin dashboard

---

**Paṇi Marketplace** - *Platform Marketplace Modern untuk Indonesia* 🇮🇩
- **Cart Page**: View cart items, update quantity, remove items, display subtotal and total
- **Checkout Page**: Billing/shipping form with mock payment confirmation
- **User Authentication**: Login, Signup, and Forgot Password connected to MySQL database
- **User Dashboard**: Profile management, order history, and logout feature
- **Responsive Design**: Fully responsive, elegant, and smooth with animations and shadows

### Backend (Admin Panel)
- **Dashboard**: Total users, products, orders, and sales chart using Chart.js
- **Product Management**: Add, edit, delete products with image upload
- **Category Management**: Add, edit, delete categories
- **Order Management**: View orders, update order status (pending, shipped, delivered)
- **User Management**: View or delete users
- **Clean UI**: Sidebar navigation with MDBootstrap and responsive layout

### Seller Panel
- **Dashboard**: Product statistics, order overview, earnings
- **Product Management**: Add, edit, delete own products
- **Order Management**: View and manage orders for own products
- **Earnings**: Track revenue and sales analytics
- **Analytics**: Sales charts and performance metrics

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 5, MDBootstrap
- **Backend**: PHP 8+, MySQL/MariaDB
- **Fonts**: Poppins and Montserrat for modern appearance
- **Charts**: Chart.js for analytics
- **Icons**: Font Awesome 6

## Database Schema

The application uses the following tables:
- `users` (id, name, email, password, role, phone, address, created_at, updated_at)
- `categories` (id, name, description, image, created_at)
- `products` (id, name, description, category_id, price, stock, image, seller_id, status, created_at, updated_at)
- `orders` (id, user_id, total_amount, status, shipping_address, payment_method, payment_status, created_at, updated_at)
- `order_items` (id, order_id, product_id, quantity, price)
- `cart` (id, user_id, session_id, product_id, quantity, created_at, updated_at)
- `reviews` (id, product_id, user_id, rating, review_text, created_at)
- `wishlist` (id, user_id, product_id, created_at)

## Installation

### Prerequisites
- PHP 8.0 or higher
- MySQL/MariaDB
- Apache/Nginx web server
- Composer (optional)

### Setup Instructions

1. **Clone or download the project** to your web server directory (e.g., `htdocs` for XAMPP)

2. **Import the database**:
   ```sql
   -- Import the database.sql file into your MySQL database
   mysql -u root -p ecommerce_db < database.sql
   ```

3. **Configure database connection**:
   - Edit `config/database.php` if needed
   - Default settings: localhost, root user, no password, database name `ecommerce_db`

4. **Set file permissions**:
   ```bash
   chmod -R 755 /path/to/project
   chmod -R 777 uploads/
   ```

5. **Access the application**:
   - Main website: `http://localhost/pani-App/`
   - Choose account type: `http://localhost/pani-App/choose_account.php`
   - Admin Panel: `http://localhost/pani-App/admin_login.php`
   - Seller Panel: `http://localhost/pani-App/seller/` (after login as seller)

## Tentang Paṇi Marketplace

Paṇi Marketplace adalah platform ekosistem digital yang dirancang khusus untuk menjembatani pelaku UMKM dan konsumen melalui teknologi yang cerdas, transparan, dan terpercaya. Proyek ini dikembangkan di bawah inisiasi Dedi Kundana, S.Pd., M.T.I., seorang praktisi dan akademisi di bidang Teknologi Informasi dan Manajemen Teknologi.

### Visi & Misi

- **Memberdayakan UMKM**: Memberikan akses teknologi setara marketplace besar bagi pengusaha lokal untuk mengelola produk dan pesanan secara profesional.
- **Transparansi Transaksi**: Menyediakan sistem pelacakan pembelian (Purchase Tracking) yang akurat dan real-time bagi pembeli dan penjual.
- **Inovasi Berkelanjutan**: Mengintegrasikan riset teknologi terkini, seperti Aspect-Based Sentiment Analysis, untuk membantu penjual memahami kepuasan pelanggan secara mendalam.

### Fitur Utama Sistem

Sebagai bagian dari pengembangan sistem yang komprehensif, Paṇi Marketplace menawarkan keunggulan:

- **Sistem Pelacakan Mandiri**: Pembeli dapat memantau status pesanan mulai dari tahap pembayaran, pengemasan, hingga barang sampai di tujuan dengan riwayat lokasi yang detail.
- **Fleksibilitas Pembayaran**: Mendukung berbagai kanal pembayaran lokal Indonesia, termasuk Transfer Bank, E-Wallet (GoPay, OVO, DANA), hingga QRIS dan COD.
- **Dashboard Penjual yang Cerdas**: Fitur manajemen produk, stok, dan pendapatan yang dirancang intuitif untuk memudahkan operasional bisnis sehari-hari.
- **Keamanan Data**: Dibangun dengan standar keamanan modern (Role-based Access Control) untuk memastikan data pengguna dan transaksi tetap terlindungi.

### Landasan Teknologi & Riset

Paṇi Marketplace bukan sekadar aplikasi jual beli biasa. Platform ini menjadi laboratorium hidup bagi pengembangan riset Manajemen Teknologi dan Kecerdasan Buatan (AI). Fokus kami adalah menciptakan sistem yang tidak hanya berfungsi secara teknis, tetapi juga memberikan nilai strategis bagi pertumbuhan ekonomi digital di Indonesia.

## Registration Flow

The application now features a dual registration system:

### Account Selection
- Visit `choose_account.php` to select between Buyer or Seller registration
- Each registration type is tailored to the specific needs of buyers and sellers

### Buyer Registration (`register.php`)
- Standard user registration for shopping
- Focus on personal information and preferences
- Direct access to shopping features and order management

### Seller Registration (`register_seller.php`)
- Enhanced registration for sellers
- Shop information collection (name, description)
- Business-focused features and dashboard
- Access to product management, order processing, and shop customization

### Seller Dashboard Features
- **Shop Management**: Add/edit products, manage inventory
- **Order Processing**: Handle orders, update shipping status
- **Payment Management**: Track earnings and payment history
- **Shop Appearance**: Customize shop look and feel
- **Analytics**: Sales reports and performance metrics

## Default Login Credentials

### Admin Account
- **Email**: admin@ecommerce.com
- **Password**: password

### Seller Account
- **Email**: seller@ecommerce.com
- **Password**: password

### Regular User
- **Email**: john@example.com
- **Password**: password

## Project Structure

```
pani-App/
├── admin/                     # Admin panel files
│   ├── index.php             # Admin dashboard
│   ├── products.php          # Product management
│   ├── categories.php        # Category management
│   ├── orders.php           # Order management
│   ├── users.php            # User management
│   └── reports.php          # Reports and analytics
├── seller/                   # Seller panel files
│   ├── index.php            # Seller dashboard
│   ├── products.php         # Seller's products
│   ├── orders.php           # Seller's orders
│   ├── earnings.php         # Earnings overview
│   └── analytics.php        # Sales analytics
├── includes/                 # Common includes
│   ├── header.php           # Header template
│   ├── footer.php           # Footer template
│   ├── functions.php        # Helper functions
│   └── cart_functions.php   # Cart functionality
├── config/                   # Configuration files
│   └── database.php         # Database connection

├── assets/                   # Static assets
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   └── images/              # Image assets
├── uploads/                  # Upload directory
│   └── products/            # Product images
├── index.php                 # Homepage
├── shop.php                  # Shop page
├── product.php               # Product details
├── cart.php                  # Shopping cart
├── checkout.php              # Checkout process
├── login.php                 # User login
├── register.php              # User registration
├── dashboard.php             # User dashboard
├── profile.php               # User profile
├── admin_login.php           # Admin login
└── database.sql              # Database schema
```

## Features Highlights

### User Experience
- **Modern UI**: Clean, professional design with Material Design elements
- **Responsive**: Works perfectly on desktop, tablet, and mobile devices
- **Interactive**: Smooth animations, hover effects, and transitions
- **Search & Filter**: Advanced product search and category filtering
- **Reviews System**: Customer reviews and ratings for products
- **Wishlist**: Save favorite products for later

### Admin Features
- **Dashboard**: Real-time statistics and charts
- **Product Management**: Full CRUD operations with image upload
- **Order Processing**: Complete order management workflow
- **User Management**: View and manage customer accounts
- **Analytics**: Sales reports and performance metrics

### Seller Features
- **Product Management**: Add and manage own products
- **Order Fulfillment**: Process orders for own products
- **Revenue Tracking**: Monitor earnings and sales performance
- **Analytics**: Detailed sales insights and trends

## Security Features
- **Password Hashing**: Secure password storage using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Session Management**: Secure session handling
- **CSRF Protection**: Token-based CSRF protection

## Customization

### Adding New Features
- Follow the existing code structure and naming conventions
- Use the helper functions in `includes/functions.php`
- Follow the MVC-like pattern for better organization

### Styling
- Custom CSS is in `assets/css/style.css`
- Uses CSS variables for easy theme customization
- Bootstrap and MDBootstrap for responsive design

### Database Modifications
- Update `database.sql` for schema changes
- Modify connection settings in `config/database.php`

## Support

For issues, questions, or contributions:
1. Check the existing code structure
2. Follow PHP best practices
3. Ensure security measures are in place
4. Test thoroughly before deployment

## License & Contact

© 2026 Paṇi Marketplace. All rights reserved.

**Developer**: LPTIK  
**Email**: ict.jinarakkhita@gmail.com  
**Address**: Jl. Raya Suban, Pidada, Panjang District, Bandar Lampung City, Lampung 35241  
**Phone**: 081260952112  

This project is for educational purposes. Feel free to modify and use according to your needs.

---

**Note**: This is a complete eCommerce marketplace solution with all essential features including purchase tracking, seller management, and real-time order monitoring. The code is well-structured, secure, and ready for production deployment with proper server configuration.
#   p a n i - m a r k e t p l a c e  
 