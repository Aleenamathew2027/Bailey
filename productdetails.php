<?php
session_start();

// Include database connection
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';

// Product Class
class Product {
    private $conn;
    private $table = "products";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get single product by ID
    public function getProductById($id) {
        $query = "SELECT p.*, 
                  GROUP_CONCAT(pi.image_path) as images
                  FROM " . $this->table . " p 
                  LEFT JOIN product_images pi ON pi.product_id = p.id
                  WHERE p.id = ?
                  GROUP BY p.id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}

$product = new Product($conn);

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($productId === 0) {
    die("Product not found");
}

// Get product details
$productData = $product->getProductById($productId);

if (!$productData) {
    die("Product not found");
}

// Get images
$images = $productData['images'] ? explode(',', $productData['images']) : [];
$primaryImage = !empty($images) ? $images[0] : 'https://via.placeholder.com/600x600/ffeef5/999999?text=No+Image';

// Check if fragrance or beauty
$isFragrance = $productData['category'] === 'fragrances';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productData['name']); ?> - BAILEY</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #fff5f9 0%, #ffe4f0 50%, #ffffff 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: #333;
        }

        /* Header */
        header {
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 32px;
            font-weight: 300;
            letter-spacing: 4px;
            color: #5a5a5a;
            text-decoration: none;
        }

        nav ul {
            display: flex;
            list-style: none;
            gap: 40px;
            align-items: center;
        }

        nav a {
            text-decoration: none;
            color: #5a5a5a;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #000;
        }

        .signup-btn {
            padding: 10px 25px;
            background: linear-gradient(135deg, #ff69b4 0%, #ffb3c1 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 105, 180, 0.4);
        }

        .header-icons {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 20px;
            color: #5a5a5a;
            transition: color 0.3s;
            position: relative;
            text-decoration: none;
        }

        .icon-btn:hover {
            color: #000;
        }

        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ffb6d9;
            color: #fff;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        /* User Profile Dropdown */
        .user-profile {
            position: relative;
            display: inline-block;
        }

        .user-initial {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff69b4 0%, #ffb3c1 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 105, 180, 0.2);
            user-select: none;
        }

        .user-initial:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(255, 105, 180, 0.3);
        }

        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 10px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            min-width: 220px;
            display: none;
            overflow: hidden;
            z-index: 2000;
        }

        .user-profile.active .dropdown-menu {
            display: block;
            animation: dropdownFadeIn 0.3s ease;
        }

        @keyframes dropdownFadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dropdown-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            background: linear-gradient(135deg, #fff0f5 0%, #fffaf7 100%);
        }

        .dropdown-header .user-name {
            font-weight: 600;
            color: #2a2a2a;
            font-size: 16px;
            margin-bottom: 4px;
        }

        .dropdown-item {
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #5a5a5a;
            font-size: 14px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .dropdown-item:hover {
            background: #f8f8f8;
            color: #000;
        }

        .dropdown-divider {
            height: 1px;
            background: #f0f0f0;
            margin: 4px 0;
        }

        /* Product Details */
        .product-details-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .product-main {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            margin-bottom: 100px;
        }

        .product-images {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .main-image {
            width: 100%;
            height: 600px;
            border-radius: 12px;
            overflow: hidden;
            background: #f8f8f8;
        }

        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
        }

        .thumbnail {
            width: 100%;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s;
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .thumbnail:hover {
            border-color: #ffb6d9;
        }

        .product-info {
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
        }

        .product-header {
            margin-bottom: 30px;
        }

        .product-category-tag {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #ff9ec8;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .product-name {
            font-size: 42px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 10px;
            line-height: 1.2;
        }

        .product-brand {
            font-size: 16px;
            color: #999;
            margin-bottom: 20px;
        }

        .rating {
            display: flex;
            gap: 5px;
            align-items: center;
            margin-bottom: 30px;
        }

        .stars {
            color: #ffb6d9;
            font-size: 16px;
        }

        .review-count {
            font-size: 14px;
            color: #666;
            margin-left: 10px;
        }

        .product-price {
            font-size: 36px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 30px;
        }

        .product-description {
            font-size: 15px;
            color: #666;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .quantity-input {
            display: flex;
            align-items: center;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            width: fit-content;
        }

        .qty-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: none;
            font-size: 18px;
            cursor: pointer;
            color: #666;
            transition: color 0.3s;
        }

        .qty-btn:hover {
            color: #000;
        }

        .qty-input-field {
            width: 60px;
            height: 40px;
            border: none;
            background: none;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
            outline: none;
        }

        .add-to-cart-btn {
            padding: 16px 40px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
            flex: 1;
            max-width: 300px;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 182, 217, 0.4);
        }

        /* Details Section */
        .details-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 100px;
            padding: 80px 0;
            border-top: 1px solid #e5e5e5;
            border-bottom: 1px solid #e5e5e5;
        }

        .details-left h2 {
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
            color: #2a2a2a;
            margin-bottom: 40px;
        }

        .details-item {
            margin-bottom: 50px;
        }

        .details-item h3 {
            font-size: 16px;
            font-weight: 600;
            color: #a8798a;
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .details-item p {
            font-size: 14px;
            color: #666;
            line-height: 1.8;
        }

        .details-right {
            position: relative;
        }

        .details-right img {
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .product-features {
            margin-top: 60px;
        }

        .feature-list {
            list-style: none;
        }

        .feature-list li {
            font-size: 14px;
            color: #666;
            padding: 10px 0 10px 25px;
            position: relative;
            line-height: 1.6;
        }

        .feature-list li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #ffb6d9;
            font-weight: bold;
        }

        /* Footer */
        footer {
            background: #fff;
            border-top: 1px solid #e5e5e5;
            padding: 60px 40px 30px;
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .footer-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-section h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 20px;
            color: #2a2a2a;
            font-weight: 600;
        }

        .footer-newsletter p {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .newsletter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .newsletter-form input {
            flex: 1;
            padding: 12px 15px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            font-size: 14px;
        }

        .newsletter-form button {
            padding: 12px 25px;
            background: #2a2a2a;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background 0.3s;
        }

        .newsletter-form button:hover {
            background: #000;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            text-decoration: none;
            color: #666;
            font-size: 14px;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: #000;
        }

        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icon {
            width: 36px;
            height: 36px;
            background: #f5f5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
            font-size: 16px;
        }

        .social-icon:hover {
            background: #2a2a2a;
            color: #fff;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #e5e5e5;
            color: #999;
            font-size: 12px;
        }

        @media (max-width: 1024px) {
            .product-main {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .details-section {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 15px 20px;
            }

            .product-details-container {
                padding: 30px 20px;
            }

            .product-name {
                font-size: 28px;
            }

            .product-main {
                gap: 30px;
            }

            .thumbnail-container {
                grid-template-columns: repeat(3, 1fr);
            }

            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="home.php" class="logo">bailey</a>
            <nav>
                <ul>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="#about">About</a></li>
                    <?php if (!$isLoggedIn): ?>
                        <li><a href="signup.php" class="signup-btn">Sign Up</a></li>
                    <?php endif; ?>
                    <li><a href="cart.php">Cart</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li><a href="addproducts.php">Add products</a></li>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                        <li class="user-profile" id="userProfile">
                            <div class="user-initial" id="userInitial"><?php echo $userInitial; ?></div>
                            <div class="dropdown-menu" id="dropdownMenu">
                                <div class="dropdown-header">
                                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                                </div>
                                <a href="account.php" class="dropdown-item">
                                    <i>👤</i>
                                    <span>My Account</span>
                                </a>
                                <a href="orders.php" class="dropdown-item">
                                    <i>📦</i>
                                    <span>Orders</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="logout.php" class="dropdown-item">
                                    <i>🚪</i>
                                    <span>Logout</span>
                                </a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
        </div>
    </header>

    <!-- Product Details -->
    <section class="product-details-container">
        <div class="product-main">
            <!-- Images -->
            <div class="product-images">
                <div class="main-image">
                    <img id="mainImage" src="<?php echo htmlspecialchars($primaryImage); ?>" alt="Product">
                </div>
                <?php if (count($images) > 1): ?>
                <div class="thumbnail-container">
                    <?php foreach ($images as $index => $image): ?>
                    <div class="thumbnail" onclick="changeMainImage('<?php echo htmlspecialchars($image); ?>')">
                        <img src="<?php echo htmlspecialchars($image); ?>" alt="Product thumbnail">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="product-info">
                <div class="product-header">
                    <div class="product-category-tag"><?php echo ucfirst($productData['category']); ?></div>
                    <h1 class="product-name"><?php echo htmlspecialchars($productData['name']); ?></h1>
                    <div class="product-brand"><?php echo htmlspecialchars($productData['brand']); ?></div>
                </div>

                <div class="rating">
                    <span class="stars">★★★☆☆</span>
                    <span class="review-count">(2) reviews</span>
                </div>

                <p class="product-description"><?php echo htmlspecialchars($productData['description']); ?></p>

                <div class="product-price">₹<?php echo number_format($productData['price'], 2); ?></div>

                <div class="quantity-selector">
                    <div class="quantity-input">
                        <button class="qty-btn" onclick="decrementQty()">−</button>
                        <input type="number" class="qty-input-field" id="quantity" value="1" min="1">
                        <button class="qty-btn" onclick="incrementQty()">+</button>
                    </div>
                    <button class="add-to-cart-btn" onclick="addToCart(<?php echo $productData['id']; ?>)">add to cart</button>
                </div>
            </div>
        </div>

        <!-- Details Section -->
        <div class="details-section">
            <div class="details-left">
                <h2>why we love it</h2>
                <?php if ($isFragrance): ?>
                    <div class="details-item">
                        <h3>Magnetic, skin-inspired scent</h3>
                        <p>A sweet, warm floral, elevated scent that smells out of this world.</p>
                    </div>
                    <div class="details-item">
                        <h3>Everyday fragrance</h3>
                        <p>Perfect for both day and night.</p>
                    </div>
                    <div class="details-item">
                        <h3>Sculpted bottle</h3>
                        <p>Designed to resemble a piece of art that fits perfectly in your hand.</p>
                    </div>
                <?php else: ?>
                    <div class="details-item">
                        <h3>Premium quality formula</h3>
                        <p>Enriched with nourishing ingredients for best results.</p>
                    </div>
                    <div class="details-item">
                        <h3>Long-lasting performance</h3>
                        <p>Provides all-day coverage and shine.</p>
                    </div>
                    <div class="details-item">
                        <h3>Professional results</h3>
                        <p>Achieve salon-quality beauty at home.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="details-right">
                <img src="https://via.placeholder.com/500x600/ffeef5/999999?text=Product+Gift+Set" alt="Product showcase">
            </div>
        </div>

        <!-- Product Features -->
        <div class="product-features">
            <h2 style="font-size: 28px; font-weight: 300; margin-bottom: 40px; letter-spacing: 1px;">key details</h2>
            
            <?php if ($isFragrance): ?>
                <div style="margin-bottom: 60px;">
                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2a2a2a;">Eau de Parfum</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;"><strong>Magnetic, skin-inspired scent</strong></p>
                    <p style="font-size: 14px; color: #666; line-height: 1.8; margin-bottom: 15px;">A sweet, warm floral, elevated scent that smells out of this world.</p>
                </div>

                <div style="margin-bottom: 60px;">
                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2a2a2a;">Body Lotion</h3>
                    <p style="font-size: 14px; color: #666; margin-bottom: 10px;"><strong>Lightly fragranced</strong></p>
                    <p style="font-size: 14px; color: #666; line-height: 1.8; margin-bottom: 15px;">With the same warm floral notes of the eau de parfum.</p>
                    <ul class="feature-list">
                        <li>Lightweight texture</li>
                        <li>Hydrates and nourishes the skin all day long</li>
                        <li>Gentle on skin</li>
                        <li>Leaves skin feeling smooth and velvety-soft</li>
                    </ul>
                </div>

                <div style="margin-bottom: 60px;">
                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2a2a2a;">Fragrance Profile</h3>
                    <ul class="feature-list">
                        <li><strong>Opening:</strong> Star jasmine and blood orange</li>
                        <li><strong>Heart:</strong> Golden amber accord and red peony accord</li>
                        <li><strong>Base:</strong> Vanilla musk accord and cedarwood</li>
                    </ul>
                </div>
            <?php else: ?>
                <div style="margin-bottom: 60px;">
                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2a2a2a;">Key Benefits</h3>
                    <ul class="feature-list">
                        <li>Professional formulation for superior coverage</li>
                        <li>Gentle on all skin types</li>
                        <li>Luxe finish for flawless appearance</li>
                        <li>Long-lasting staying power</li>
                        <li>Easy to blend and apply</li>
                    </ul>
                </div>

                <div style="margin-bottom: 60px;">
                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2a2a2a;">Usage Instructions</h3>
                    <ul class="feature-list">
                        <li>Apply to face with brush or fingertips</li>
                        <li>Use morning and evening</li>
                        <li>Works well alone or with primer</li>
                        <li>Can be layered for customized coverage</li>
                    </ul>
                </div>

                <div style="margin-bottom: 60px;">
                    <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 20px; color: #2a2a2a;">Ingredients</h3>
                    <p style="font-size: 14px; color: #666; line-height: 1.8;">Contains premium beauty ingredients carefully selected for their nourishing and protective properties. Perfect for all skin types and tones.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section footer-newsletter">
                    <h3>Join Our Community</h3>
                    <p>Subscribe to receive exclusive offers, beauty tips, and new product launches.</p>
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                        <input type="email" placeholder="Enter your email" required>
                        <button type="submit">Subscribe</button>
                    </form>
                    <div class="social-links">
                        <a href="#" class="social-icon">📷</a>
                        <a href="#" class="social-icon">𝕏</a>
                        <a href="#" class="social-icon">📘</a>
                        <a href="#" class="social-icon">📌</a>
                    </div>
                </div>
                <div class="footer-section">
                    <h3>Navigate</h3>
                    <ul class="footer-links">
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="#about">Our Story</a></li>
                        <li><a href="#collections">Collections</a></li>
                        <li><a href="#stores">Store Locator</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Support</h3>
                    <ul class="footer-links">
                        <li><a href="#contact">Contact Us</a></li>
                        <li><a href="#faq">FAQ</a></li>
                        <li><a href="#shipping">Shipping Info</a></li>
                        <li><a href="#returns">Returns</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul class="footer-links">
                        <li><a href="#privacy">Privacy Policy</a></li>
                        <li><a href="#terms">Terms of Service</a></li>
                        <li><a href="#accessibility">Accessibility</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul class="footer-links">
                        <li><a href="#about">About Bailey</a></li>
                        <li><a href="#careers">Careers</a></li>
                        <li><a href="#press">Press</a></li>
                        <li><a href="#sustainability">Sustainability</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 BAILEY. All rights reserved. | Crafted with excellence for beauty enthusiasts worldwide.</p>
            </div>
        </div>
    </footer>

    <script>
        // Dropdown functionality
        const userInitial = document.getElementById('userInitial');
        const userProfile = document.getElementById('userProfile');

        if (userInitial && userProfile) {
            userInitial.addEventListener('click', function(event) {
                event.stopPropagation();
                userProfile.classList.toggle('active');
            });

            document.addEventListener('click', function(event) {
                if (!userProfile.contains(event.target)) {
                    userProfile.classList.remove('active');
                }
            });

            const dropdownMenu = document.getElementById('dropdownMenu');
            if (dropdownMenu) {
                dropdownMenu.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            }
        }

        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }

        function incrementQty() {
            const qty = document.getElementById('quantity');
            qty.value = parseInt(qty.value) + 1;
        }

        function decrementQty() {
            const qty = document.getElementById('quantity');
            if (parseInt(qty.value) > 1) {
                qty.value = parseInt(qty.value) - 1;
            }
        }

        // Add to cart functionality with login check
        function addToCart(productId) {
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
            
            if (!isLoggedIn) {
                window.location.href = 'signup.php?redirect=productdetails&id=' + productId;
                return;
            }
            
            const quantity = document.getElementById('quantity').value;
            
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ ' + data.product_name + ' added to cart! Cart count: ' + data.cart_count);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding to cart');
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>