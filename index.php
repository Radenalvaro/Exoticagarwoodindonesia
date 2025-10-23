<?php
require_once 'database.php';
require_once 'components/productkayugaharu.php';
require_once 'components/productminyakgaharu.php';

// Get kayu gaharu products for homepage - LANGSUNG DARI DATABASE
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'kayu_gaharu' AND is_active = 1 ORDER BY grade, name LIMIT 6");
    $stmt->execute();
    $kayu_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $kayu_products = [];
}

// Get minyak gaharu products for homepage - LANGSUNG DARI DATABASE
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'minyak_gaharu' AND is_active = 1 ORDER BY grade, name LIMIT 6");
    $stmt->execute();
    $minyak_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $minyak_products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exotic Agarwood Indonesia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
/* SCROLL CONTAINER STYLES - PERBAIKAN */
.scroll-container {
    position: relative;
    width: 100%;
    margin: 10px 0;
}

/* PRODUCT CATEGORY SPACING */
.product-category {
    margin-bottom: 30px;
}

.product-category:last-child {
    margin-bottom: 10px;
}

.products-scroll {
    display: flex;
    gap: 24px;
    overflow-x: auto;
    overflow-y: hidden;
    scroll-behavior: smooth;
    padding: 20px 0;
    scrollbar-width: none;
    -ms-overflow-style: none;
    cursor: grab;
}

.products-scroll::-webkit-scrollbar {
    display: none;
}

.products-scroll.dragging {
    cursor: grabbing;
    user-select: none;
}

/* SCROLL BUTTONS */
.scroll-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(175, 123, 0, 0.9);
    border: 2px solid transparent;
    border-radius: 50%;
    width: 48px;
    height: 48px;
    color: white;
    font-size: 18px;
    cursor: pointer;
    z-index: 100;
    transition: border-color 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}

.scroll-button:hover {
    border-color: #af7b00;
}

.scroll-button:active {
    border-color: #916700;
}

.scroll-button-left {
    left: -24px;
}

.scroll-button-right {
    right: -24px;
}

.scroll-button:disabled {
    opacity: 0.3;
    cursor: not-allowed;
    pointer-events: none;
    border-color: transparent;
}

.scroll-container {
    position: relative;
    width: 100%;
    margin: 10px 0;
    padding-top: 40px;
}

/* CATEGORY HEADER */
.category-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
    position: relative;
}

.category-header h3 {
    color: white;
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0;
}

/* PRODUCT CARD STYLES */
.product-card {
    flex: 0 0 300px;
    min-width: 300px;
    background: #2a1200;
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
}

.product-card:hover {
    border-color: #af7b00;
    transform: translateY(-4px);
    box-shadow: 0 8px 32px rgba(175, 123, 0, 0.2);
}

.product-image {
    height: 200px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.product-info {
    padding: 20px;
    background: #2a1200;
}

.product-name {
    color: white;
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 8px;
    line-height: 1.3;
}

.product-origin,
.product-grade {
    color: #af7b00;
    font-size: 14px;
    margin-bottom: 8px;
}

.product-description {
    color: #ccc;
    font-size: 14px;
    margin-bottom: 12px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.product-price {
    color: #af7b00;
    font-weight: bold;
    font-size: 18px;
    margin-bottom: 16px;
}

.btn-full {
    width: 100%;
    text-align: center;
    padding: 12px;
    background: #af7b00;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background 0.3s ease;
    display: block;
}

.btn-full:hover {
    background: #916700;
}

/* HERO BADGE STYLES */
.hero-badge {
    position: absolute;
    background: rgba(175, 123, 0, 0.95);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    padding: 12px 16px;
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    transition: transform 0.3s ease, background 0.3s ease;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
}

.hero-badge:hover {
    transform: scale(1.05);
    background: rgba(175, 123, 0, 1);
}

.badge-1 {
    top: 20px;
    left: 20px;
}

.badge-2 {
    bottom: 20px;
    right: 20px;
}

.badge-3 {
    top: 50%;
    left: -10px;
    transform: translateY(-50%);
}

.badge-4 {
    top: 20px;
    right: 20px;
}

.badge-title {
    color: white;
    font-weight: 700;
    font-size: 14px;
    margin-bottom: 2px;
}

.badge-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 11px;
    font-weight: 500;
}

.section-description {
    margin-bottom: 40px;
}

/* MOBILE MENU STYLES - COMPLETE FIX */
.mobile-menu-btn {
    display: none !important;
    background: none !important;
    border: none !important;
    color: white !important;
    font-size: 24px !important;
    cursor: pointer !important;
    padding: 8px !important;
    z-index: 1001 !important;
    position: relative !important;
}

.nav-mobile {
    display: none !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(28, 12, 0, 0.98) !important;
    backdrop-filter: blur(10px) !important;
    z-index: 9999 !important;
    flex-direction: column !important;
    justify-content: center !important;
    align-items: center !important;
    gap: 30px !important;
    opacity: 0 !important;
    visibility: hidden !important;
    transition: all 0.3s ease !important;
}

.nav-mobile.active {
    display: flex !important;
    opacity: 1 !important;
    visibility: visible !important;
}

.nav-mobile .nav-link {
    color: white !important;
    text-decoration: none !important;
    font-size: 24px !important;
    font-weight: 600 !important;
    padding: 15px 30px !important;
    border-radius: 8px !important;
    transition: all 0.3s ease !important;
    text-align: center !important;
}

.nav-mobile .nav-link:hover {
    background: rgba(175, 123, 0, 0.2) !important;
    color: #af7b00 !important;
}

.nav-mobile .btn {
    margin-top: 20px !important;
    padding: 15px 30px !important;
    font-size: 18px !important;
}

/* HEADER RESPONSIVE */
.header {
    position: relative;
    z-index: 1000;
}

/* HEADER FIXED STYLES */
.header {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    width: 100% !important;
    background: rgba(28, 12, 0, 0.95) !important;
    backdrop-filter: blur(10px) !important;
    border-bottom: 1px solid rgba(175, 123, 0, 0.2) !important;
    z-index: 1000 !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.3) !important;
}

/* Add padding to body to account for fixed header */
body {
    padding-top: 80px !important;
}

/* Header on scroll effect */
.header.scrolled {
    background: rgb(28, 12, 0) !important;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5) !important;
}

.nav-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* RESPONSIVE BREAKPOINTS */
@media (max-width: 768px) {
    .mobile-menu-btn {
        display: block !important;
    }
    
    .nav-desktop {
        display: none !important;
    }
    
    .nav-cta {
        display: none !important;
    }
    
    .header {
        padding: 15px 0;
    }
    
    .scroll-container {
        margin: 8px 0;
    }
    
    .product-category {
        margin-bottom: 20px;
    }
    
    .product-category:last-child {
        margin-bottom: 8px;
    }
    
    .scroll-button {
        width: 40px;
        height: 40px;
        font-size: 16px;
        z-index: 2000;
    }
    
    .scroll-button-left {
        left: -20px;
    }
    
    .scroll-button-right {
        right: -20px;
    }
    
    .product-card {
        flex: 0 0 280px;
        min-width: 280px;
    }
    
    .logo a {
        font-size: 18px !important;
    }
    
    .mobile-menu-btn {
        font-size: 20px !important;
    }
    
    .nav-mobile .nav-link {
        font-size: 20px !important;
        padding: 12px 25px !important;
    }
    
    .scroll-container {
        margin: 5px 0;
    }
    
    .product-category {
        margin-bottom: 15px;
    }
    
    .product-category:last-child {
        margin-bottom: 5px;
    }
    
    .scroll-button {
        z-index: 2000;
    }
    
    .product-card {
        flex: 0 0 250px;
        min-width: 250px;
    }
    
    .scroll-button-left {
        left: -15px;
    }
    
    .scroll-button-right {
        right: -15px;
    }
}

/* HERO IMAGE STYLES */
.hero-image {
    height: 600px;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
    transition: transform 0.3s ease;
}

.hero-image:hover {
    transform: scale(1.03);
}

.hero-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

/* RESPONSIVE HERO BADGES */
@media (max-width: 768px) {
    .hero-badge {
        padding: 8px 12px;
        border-width: 1px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .badge-title {
        font-size: 12px;
    }

    .badge-subtitle {
        font-size: 10px;
    }

    .badge-1 {
        top: 10px;
        left: 10px;
    }

    .badge-2 {
        bottom: 10px;
        right: 10px;
    }

    .badge-3 {
        top: 50%;
        left: -5px;
        transform: translateY(-50%);
    }

    .badge-4 {
        top: 10px;
        right: 10px;
    }
}

@media (max-width: 480px) {
    .hero-badge {
        padding: 6px 10px;
    }

    .badge-title {
        font-size: 10px;
    }

    .badge-subtitle {
        font-size: 9px;
    }
}
</style>
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
                    <a href="#about" class="nav-link">ABOUT</a>
                    <a href="#products" class="nav-link">PRODUCTS</a>
                    <a href="catalogkayugaharu.php" class="nav-link">AGARWOOD</a>
                    <a href="catalogminyakgaharu.php" class="nav-link">AGARWOOD OIL</a>
                    <a href="#contact" class="nav-link">CONTACT</a>
                </nav>
                <div class="nav-cta">
                    <a href="#contact" class="btn btn-primary">Get Quote</a>
                </div>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Mobile Navigation -->
            <nav class="nav-mobile" id="mobileNav">
                <a href="index.php" class="nav-link">HOME</a>
                <a href="#about" class="nav-link">ABOUT</a>
                <a href="#products" class="nav-link">PRODUCTS</a>
                <a href="catalogkayugaharu.php" class="nav-link">AGARWOOD</a>
                <a href="catalogminyakgaharu.php" class="nav-link">AGARWOOD OIL</a>
                <a href="#contact" class="nav-link">CONTACT</a>
                <a href="#contact" class="btn btn-primary">Get Quote</a>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-pattern">
            <div class="pattern-circle circle-1"></div>
            <div class="pattern-circle circle-2"></div>
            <div class="pattern-circle circle-3"></div>
        </div>
        
        <div class="container">
            <div class="hero-content">
                <div class="hero-left">
                    <div class="hero-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span>Trusted by 1000+ customers</span>
                    </div>

                    <h1 class="hero-title">
                        Exotic
                        <span class="highlight">Agarwood</span>
                        Indonesia
                    </h1>

                    <p class="hero-description">
                        Discover the finest collection of authentic Indonesian agarwood and premium oud oil. 
                        Experience the luxury of nature's most precious fragrance.
                    </p>

                    <div class="hero-buttons">
                        <a href="#products" class="btn btn-primary btn-large">
                            Explore Products
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#contact" class="btn btn-outline btn-large">Get Quote</a>
                    </div>

                    <div class="hero-stats">
                        <div class="stat">
                            <div class="stat-number">15+</div>
                            <div class="stat-label">Years Experience</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">1000+</div>
                            <div class="stat-label">Happy Customers</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number">50+</div>
                            <div class="stat-label">Premium Products</div>
                        </div>
                    </div>
                </div>

                <div class="hero-right">
                    <div class="hero-image">
                        <img src="https://images.tokopedia.net/img/cache/700/VqbcmM/2021/7/16/6a557e13-f792-4701-aa75-0bb2a1b1424e.jpg" alt="Premium Agarwood Collection"> <!--pichero-->
                        <div class="hero-badge badge-1">
                            <div class="badge-title">Premium Grade A+</div>
                            <div class="badge-subtitle">Royal Collection</div>
                        </div>
                        <div class="hero-badge badge-2">
                            <div class="badge-title">100% Authentic</div>
                            <div class="badge-subtitle">Indonesian Origin</div>
                        </div>
                        <div class="hero-badge badge-3">
                            <div class="badge-title">Natural Resin</div>
                            <div class="badge-subtitle">Wild Harvested</div>
                        </div>
                        <div class="hero-badge badge-4">
                            <div class="badge-title">Export Quality</div>
                            <div class="badge-subtitle">International Standard</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
<section id="about" class="about">
    <div class="container">
        <div class="about-content">
            <div class="about-left">
                <p class="section-subtitle">ABOUT US</p>
                <h2 class="section-title">Indonesia's Premier Agarwood Specialist</h2>
                <p class="about-description">
                    With over 15 years of expertise in the agarwood industry, we are dedicated to bringing you the finest 
                    quality Indonesian agarwood and premium oud oil. Our commitment to authenticity and excellence has made us a 
                    trusted name among connoisseurs worldwide.
                </p>

                <div class="features-grid">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="feature-content">
                            <h4>100% Natural</h4>
                            <p>Pure, authentic agarwood without any artificial additives</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Quality Assured</h4>
                            <p>Rigorous quality control and certification process</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-award"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Expert Sourcing</h4>
                            <p>Direct partnerships with trusted Indonesian suppliers</p>
                        </div>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="feature-content">
                            <h4>Global Reach</h4>
                            <p>Serving customers across 25+ countries worldwide</p>
                        </div>
                    </div>
                </div>

                <!-- Our Mission Section -->
                <div class="mission-section">
                    <h3 class="mission-title">Our Mission</h3>
                    <p class="mission-description">
                        To preserve and share the ancient tradition of Indonesian agarwood while maintaining the highest standards of quality and sustainability for future generations.
                    </p>
                </div>
            </div>

            <div class="about-right">
                <div class="about-images">
                    <div class="image-column">
                        <div class="about-image">
                            <img src="assets\237-536x354.jpg" alt="Agarwood harvesting">
                        </div>
                        <div class="about-image">
                            <img src="assets\866-536x354.jpg" alt="Quality inspection">
                        </div>
                    </div>
                    <div class="image-column offset">
                        <div class="about-image">
                            <img src="assets\870-536x354-blur_2-grayscale.jpg" alt="Oud oil distillation">
                        </div>
                        <div class="about-image">
                            <img src="assets\894-536x354.jpg" alt="Premium packaging">
                        </div>
                    </div>
                </div>
                <div class="experience-badge">15+ Years Experience</div>
            </div>
        </div>
    </div>
</section>

    <!-- Products Section -->
    <section id="products" class="products">
        <div class="container">
            <div class="section-header">
                <p class="section-subtitle">PRODUCTS</p>
                <h2 class="section-title">Our Premium Products</h2>
            </div>

            <!-- Agarwood Section - DATA DARI DATABASE -->
            <div class="product-category">
                <div class="category-header">
                    <h3>Agarwood</h3>
                    <a href="catalogkayugaharu.php" class="btn btn-outline">
                        See More
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="scroll-container">
                    <button class="scroll-button scroll-button-left" id="kayuScrollLeft">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="scroll-button scroll-button-right" id="kayuScrollRight">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="products-scroll" id="kayuProductsScroll">
                        <?php if (!empty($kayu_products)): ?>
                            <?php foreach ($kayu_products as $product): ?>
                                <?php echo renderKayuGaharuCard($product); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: white;">No agarwood products available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Agarwood Oil Section - DATA DARI DATABASE -->
            <div class="product-category">
                <div class="category-header">
                    <h3>Agarwood Oil</h3>
                    <a href="catalogminyakgaharu.php" class="btn btn-outline">
                        See More
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="scroll-container">
                    <button class="scroll-button scroll-button-left" id="minyakScrollLeft">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="scroll-button scroll-button-right" id="minyakScrollRight">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                    <div class="products-scroll" id="minyakProductsScroll">
                        <?php if (!empty($minyak_products)): ?>
                            <?php foreach ($minyak_products as $product): ?>
                                <?php echo renderMinyakGaharuCard($product); ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="color: white;">No agarwood oil products available.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <p class="section-subtitle">CONTACT US</p>
                <h2 class="section-title">Get In Touch</h2>
                <p class="section-description">
                    Ready to experience the finest agarwood? Contact us for quotes, inquiries, or to learn more about our premium collection.
                </p>
            </div>

            <div class="contact-content">
                <div class="contact-info">
                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p>+62 815-5402-0227</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p>info@exoticagarwood.com</p>
                        </div>
                    </div>

                    <div class="contact-card">
                        <div class="contact-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="contact-details">
                            <h4>Address</h4>
                            <p>Jl. Agarwood Premium No. 123</p>
                            <p>Jakarta, Indonesia 12345</p>
                        </div>
                    </div>
                </div>

                <div class="contact-form-wrapper">
                    <div class="contact-form-card">
                        <h3>Send us a message</h3>
                        
                        <!-- POPUP CONTAINER - LOKASI YANG TEPAT -->
                        <div id="messageContainer"></div>
                        
                        <form class="contact-form" id="contactForm" action="prosesscontact.php" method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" id="name" name="name" required placeholder="Your full name">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" required placeholder="your.email@example.com">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" placeholder="+62 815-5402-0227">
                            </div>
                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" required rows="6" placeholder="Tell us about your requirements..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-full">Send Message</button>
                        </form>
                    </div>
                </div>
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
