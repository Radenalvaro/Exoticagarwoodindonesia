<?php
require_once 'database.php';
require_once 'components/productkayugaharu.php';
require_once 'components/productminyakgaharu.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($product_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get product details - HANYA KAYU GAHARU
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND category = 'kayu_gaharu' AND is_active = 1");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        header('Location: catalogkayugaharu.php');
        exit;
    }
} catch(PDOException $e) {
    header('Location: catalogkayugaharu.php');
    exit;
}

// Get related kayu gaharu products (same category, different product)
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'kayu_gaharu' AND id != ? AND is_active = 1 ORDER BY RAND() LIMIT 6");
    $stmt->execute([$product_id]);
    $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $related_products = [];
}

// Get minyak gaharu products for cross-selling
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = 'minyak_gaharu' AND is_active = 1 ORDER BY RAND() LIMIT 6");
    $stmt->execute();
    $other_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $other_products = [];
}

// Product images - GUNAKAN DARI DATABASE ATAU DEFAULT
$main_image = 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=600&fit=crop';

// FIXED PATH FOR ADMIN FOLDER
if (!empty($product['image_url'])) {
    // Jika path dimulai dengan 'uploads/', tambahkan prefix 'admin/'
    if (strpos($product['image_url'], 'uploads/') === 0) {
        $main_image = 'admin/' . $product['image_url'];
    }
    // Jika path sudah termasuk 'admin/uploads/', gunakan langsung
    elseif (strpos($product['image_url'], 'admin/uploads/') === 0) {
        $main_image = $product['image_url'];
    }
    // Jika path dimulai dengan 'http', gunakan langsung (URL eksternal)
    elseif (strpos($product['image_url'], 'http') === 0) {
        $main_image = $product['image_url'];
    }
    // Jika path lain, coba tambahkan prefix 'admin/'
    else {
        $main_image = 'admin/' . $product['image_url'];
    }
}

// Unit asli dari database untuk kayu gaharu
$display_unit = $product['unit'] ?? 'gram';

$whatsapp_number = '+6281554020227';
$whatsapp_message = urlencode("Hello, I'm interested in " . $product['name'] . " (Grade: " . $product['grade'] . ", Origin: " . ($product['origin'] ?? 'Indonesia') . "). Could you please provide more information?");
$whatsapp_url = "https://wa.me/" . str_replace(['+', '-', ' '], '', $whatsapp_number) . "?text=" . $whatsapp_message;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['name']); ?> - Agarwood Detail | ExoticAgarwood Indonesia</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Logo Text Styles */
        .logo a {
            font-size: 24px;
            font-weight: bold;
            color: #af7b00;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .logo a:hover {
            color: #d4941a;
        }
        
        /* Responsive logo */
        @media (max-width: 768px) {
            .logo a {
                font-size: 20px;
            }
        }
        
        /* Override any white backgrounds and ensure consistent dark theme */
        * {
            box-sizing: border-box;
        }
        
        body {
            background: #1c0c00 !important;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        
        .product-detail {
            background: #1c0c00;
            min-height: 100vh;
            padding: 50px 0 0;
            width: 100%;
        }
        
        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            background: #1c0c00;
        }
        
        .detail-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            margin-bottom: 80px;
            background: #1c0c00;
            padding-top: 40px;
        }
        
        .image-gallery {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5);
        }
        
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .main-image:hover img {
            transform: scale(1.05);
        }
        
        .product-info {
            color: white;
            background: #1c0c00;
        }
        
        .product-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: white;
            margin-bottom: 16px;
            line-height: 1.2;
        }
        
        /* Updated Product Meta Layout - 2x2 Grid with specific order */
        .product-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 24px;
            padding: 20px;
            background: rgba(175, 123, 0, 0.1);
            border-radius: 8px;
            border: 1px solid rgba(175, 123, 0, 0.3);
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
        }
        
        .meta-label {
            color: #af7b00;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .meta-value {
            color: white;
            font-size: 16px;
            font-weight: 500;
        }
        
        .product-price {
            font-size: 2rem;
            font-weight: bold;
            color: #af7b00;
            margin-bottom: 24px;
        }
        
        .product-description {
            color: white;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        
        .order-section {
            background: rgba(175, 123, 0, 0.1);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid rgba(175, 123, 0, 0.3);
            margin-bottom: 32px;
        }
        
        .order-title {
            color: white;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 16px;
        }
        
        .whatsapp-btn {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: #25D366;
            color: white;
            padding: 16px 32px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .whatsapp-btn:hover {
            background: #128C7E;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 211, 102, 0.3);
        }
        
        .whatsapp-btn i {
            font-size: 20px;
        }
        
        .related-section {
            margin-bottom: 80px;
            background: #1c0c00;
            padding: 40px 0;
        }
        
        .section-title {
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 32px;
            text-align: center;
        }
        
        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }
        
        .related-card {
            background: #2a1200;
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.3);
        }
        
        .related-card:hover {
            border-color: #af7b00;
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(175, 123, 0, 0.2);
        }
        
        .related-image {
            height: 200px;
            overflow: hidden;
        }
        
        .related-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .related-card:hover .related-image img {
            transform: scale(1.05);
        }
        
        .related-info {
            padding: 20px;
            background: #2a1200;
        }
        
        .related-name {
            color: white;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
        }
        
        .related-origin {
            color: #af7b00;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .related-grade {
            color: #af7b00;
            font-size: 14px;
            margin-bottom: 8px;
        }
        
        .related-price {
            color: #af7b00;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 12px;
        }
        
        .view-detail-btn {
            background: #af7b00;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: background 0.3s ease;
            display: inline-block;
        }
        
        .view-detail-btn:hover {
            background: #916700;
        }
        
        .footer {
            background: #1c0c00 !important;
            margin-top: 0 !important;
            padding-top: 64px !important;
        }
        
        @media (max-width: 768px) {
            .detail-content {
                grid-template-columns: 1fr;
                gap: 32px;
                padding-top: 20px;
            }
            
            .product-title {
                font-size: 2rem;
            }
            
            .product-meta {
                grid-template-columns: 1fr;
            }
            
            .related-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
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

    <!-- Product Detail Section -->
    <section class="product-detail">
        <div class="detail-container">
            <!-- Product Detail Content -->
            <div class="detail-content">
                <!-- Image Gallery -->
                <div class="image-gallery">
                    <div class="main-image">
                        <img src="<?php echo htmlspecialchars($main_image); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                             onerror="this.src='https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=600&fit=crop'">
                    </div>
                </div>

                <!-- Product Information -->
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    
                    <!-- Updated Product Meta with specific order: Category, Origin, Grade, Unit -->
                    <div class="product-meta">
                        <div class="meta-item meta-category">
                            <span class="meta-label">Category</span>
                            <span class="meta-value">Agarwood</span>
                        </div>
                        <div class="meta-item meta-origin">
                            <span class="meta-label">Origin</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['origin'] ?? 'Indonesia'); ?></span>
                        </div>
                        <div class="meta-item meta-grade">
                            <span class="meta-label">Grade</span>
                            <span class="meta-value"><?php echo htmlspecialchars($product['grade']); ?></span>
                        </div>
                        <div class="meta-item meta-unit">
                            <span class="meta-label">Unit</span>
                            <span class="meta-value"><?php echo htmlspecialchars($display_unit); ?></span>
                        </div>
                    </div>
                    
                    <div class="product-price">
                        $<?php echo number_format($product['price']); ?> / <?php echo htmlspecialchars($display_unit); ?>
                    </div>
                    
                    <div class="product-description">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>
                    
                    <!-- Order Section -->
                    <div class="order-section">
                        <h3 class="order-title">Ready to Order?</h3>
                        <p style="color: #ccc; margin-bottom: 16px;">Contact us directly via WhatsApp for immediate assistance and personalized service.</p>
                        <a href="<?php echo $whatsapp_url; ?>" target="_blank" class="whatsapp-btn">
                            <i class="fab fa-whatsapp"></i>
                            Order Now via WhatsApp
                        </a>
                    </div>
                </div>
            </div>

            <!-- Related Agarwood Products -->
            <?php if (!empty($related_products)): ?>
            <div class="related-section">
                <h2 class="section-title">More Agarwood Products</h2>
                <div class="related-grid">
                    <?php foreach ($related_products as $related): ?>
                    <?php
                    // FIXED PATH FOR ADMIN FOLDER
                    $related_image = 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=300&fit=crop';
                    if (!empty($related['image_url'])) {
                        // Jika path dimulai dengan 'uploads/', tambahkan prefix 'admin/'
                        if (strpos($related['image_url'], 'uploads/') === 0) {
                            $related_image = 'admin/' . $related['image_url'];
                        }
                        // Jika path sudah termasuk 'admin/uploads/', gunakan langsung
                        elseif (strpos($related['image_url'], 'admin/uploads/') === 0) {
                            $related_image = $related['image_url'];
                        }
                        // Jika path dimulai dengan 'http', gunakan langsung (URL eksternal)
                        elseif (strpos($related['image_url'], 'http') === 0) {
                            $related_image = $related['image_url'];
                        }
                        // Jika path lain, coba tambahkan prefix 'admin/'
                        else {
                            $related_image = 'admin/' . $related['image_url'];
                        }
                    }
                    ?>
                    <div class="related-card">
                        <div class="related-image">
                            <img src="<?php echo htmlspecialchars($related_image); ?>" 
                                 alt="<?php echo htmlspecialchars($related['name']); ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=300&h=300&fit=crop'">
                        </div>
                        <div class="related-info">
                            <h4 class="related-name"><?php echo htmlspecialchars($related['name']); ?></h4>
                            <p class="related-origin">Origin: <?php echo htmlspecialchars($related['origin'] ?? 'Indonesia'); ?></p>
                            <p class="related-grade">Grade: <?php echo htmlspecialchars($related['grade']); ?></p>
                            <p class="product-description"><?php echo htmlspecialchars($related['description']); ?></p>
                            <p class="related-price">$<?php echo number_format($related['price']); ?> / <?php echo htmlspecialchars($related['unit']); ?></p>
                            <a href="seedetail-kayugaharu.php?id=<?php echo $related['id']; ?>" class="view-detail-btn">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Agarwood Oil Products -->
            <?php if (!empty($other_products)): ?>
            <div class="related-section">
                <h2 class="section-title">Explore Our Agarwood Oil Collection</h2>
                <div class="related-grid">
                    <?php foreach ($other_products as $other): ?>
                    <?php
                    // FIXED PATH FOR ADMIN FOLDER
                    $other_image = 'https://images.unsplash.com/photo-1574263867128-a3d5c1b1deaa?w=300&h=300&fit=crop';
                    if (!empty($other['image_url'])) {
                        // Jika path dimulai dengan 'uploads/', tambahkan prefix 'admin/'
                        if (strpos($other['image_url'], 'uploads/') === 0) {
                            $other_image = 'admin/' . $other['image_url'];
                        }
                        // Jika path sudah termasuk 'admin/uploads/', gunakan langsung
                        elseif (strpos($other['image_url'], 'admin/uploads/') === 0) {
                            $other_image = $other['image_url'];
                        }
                        // Jika path dimulai dengan 'http', gunakan langsung (URL eksternal)
                        elseif (strpos($other['image_url'], 'http') === 0) {
                            $other_image = $other['image_url'];
                        }
                        // Jika path lain, coba tambahkan prefix 'admin/'
                        else {
                            $other_image = 'admin/' . $other['image_url'];
                        }
                    }
                    ?>
                    <div class="related-card">
                        <div class="related-image">
                            <img src="<?php echo htmlspecialchars($other_image); ?>" 
                                 alt="<?php echo htmlspecialchars($other['name']); ?>"
                                 onerror="this.src='https://images.unsplash.com/photo-1574263867128-a3d5c1b1deaa?w=300&h=300&fit=crop'">
                        </div>
                        <div class="related-info">
                            <h4 class="related-name"><?php echo htmlspecialchars($other['name']); ?></h4>
                            <p class="related-origin">Origin: <?php echo htmlspecialchars($other['origin'] ?? 'Indonesia'); ?></p>
                            <p class="related-grade">Grade: <?php echo htmlspecialchars($other['grade']); ?></p>
                            <p class="product-description"><?php echo htmlspecialchars($other['description']); ?></p>
                            <p class="related-price">$<?php echo number_format($other['price']); ?> / ml</p>
                            <a href="seedetail-minyakgaharu.php?id=<?php echo $other['id']; ?>" class="view-detail-btn">View Details</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
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
