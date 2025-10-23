<?php
require_once 'database.php';
require_once 'components/productkayugaharu.php';


try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'kayu_gaharu' AND is_active = 1 ORDER BY grade, name");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalog Agarwood Oil - Exotic Agarwood Indonesia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="nav-wrapper">
                <div class="logo">
                    <a href="index.php">Exotic Agarwood Indonesia</a>
                </div>
                <nav class="nav-desktop">
                    <a href="index.php" class="nav-link">HOME</a>
                    <a href="index.php#about" class="nav-link">ABOUT</a>
                    <a href="index.php#products" class="nav-link">PRODUCTS</a>
                    <a href="catalogkayugaharu.php" class="nav-link">AGARWOOD</a>
                    <a href="catalogminyakgaharu.php" class="nav-link">AGARWOOD OIL</a>
                    <a href="index.php#contact" class="nav-link">CONTACT</a>
                </nav>
                <div class="nav-cta">
                    <a href="index.php#contact" class="btn btn-primary">Get Quote</a>
                </div>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <nav class="nav-mobile" id="mobileNav">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="index.php#about" class="nav-link">ABOUT</a>
                <a href="index.php#products" class="nav-link">PRODUCTS</a>
                <a href="catalogkayugaharu.php" class="nav-link">AGARWOOD</a>
                <a href="catalogminyakgaharu.php" class="nav-link">AGARWOOD OIL</a>
                <a href="index.php#contact" class="nav-link">CONTACT</a>
                <a href="index.php#contact" class="btn btn-primary">Get Quote</a>
            </nav>
        </div>
    </header>

    <!-- Catalog Section -->
    <section class="catalog">
        <div class="container">
            <div class="catalog-header">
                <h1>Catalog Agarwood</h1>
                <p>Explore our exquisite collection of premium ouds</p>
            </div>

            <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <?php echo renderKayuGaharuCard($product); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">
                        <p>No agarwood oil products available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">Exotic Agarwood Indonesia</div>
                    <p class="footer-description">
                        Indonesia's premier agarwood specialist, bringing you the finest quality authentic agarwood and oud oil for over 15 years.
                    </p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>

                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="index.php#products">Products</a></li>
                        <li><a href="catalogkayugaharu.php">Agarwood</a></li>
                        <li><a href="catalogminyakgaharu.php">Agarwood Oil</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Our Products</h4>
                    <ul>
                        <li><a href="#">Premium Agarwood</a></li>
                        <li><a href="#">Royal Collection</a></li>
                        <li><a href="#">Oud Oil</a></li>
                        <li><a href="#">Traditional Agarwood</a></li>
                        <li><a href="#">Custom Orders</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <div class="footer-contact">
                        <div class="footer-contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Jl. Agarwood Premium No. 123<br>Jakarta, Indonesia 12345</span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-phone"></i>
                            <span>+62 815-5402-0227</span>
                        </div>
                        <div class="footer-contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>info@exoticagarwood.com</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; 2024 Exotic Agarwood Indonesia. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Shipping Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
