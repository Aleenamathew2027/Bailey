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
    
    // Get all products with filters
    public function getProducts($category = 'all', $minPrice = 0, $maxPrice = 10000) {
        if ($category === 'all') {
            $query = "SELECT p.*, 
                      (SELECT pi.image_path FROM product_images pi 
                       WHERE pi.product_id = p.id AND pi.is_primary = 1 
                       LIMIT 1) as primary_image
                      FROM " . $this->table . " p 
                      WHERE p.price BETWEEN ? AND ?
                      ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("dd", $minPrice, $maxPrice);
        } else {
            $query = "SELECT p.*, 
                      (SELECT pi.image_path FROM product_images pi 
                       WHERE pi.product_id = p.id AND pi.is_primary = 1 
                       LIMIT 1) as primary_image
                      FROM " . $this->table . " p 
                      WHERE p.category = ? AND p.price BETWEEN ? AND ?
                      ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sdd", $category, $minPrice, $maxPrice);
        }
        
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get price range
    public function getPriceRange() {
        $query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM " . $this->table;
        $result = $this->conn->query($query);
        return $result->fetch_assoc();
    }
}

$product = new Product($conn);

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 10000;

// Get price range for slider
$priceRange = $product->getPriceRange();
$dbMinPrice = $priceRange['min_price'] ?? 0;
$dbMaxPrice = $priceRange['max_price'] ?? 1000;

// Get products
$products = $product->getProducts($category, $minPrice, $maxPrice);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop - BAILEY</title>
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

        /* Header Styles */
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #ffeef5 0%, #fff5f9 100%);
            padding: 80px 40px;
            text-align: center;
            margin-bottom: 60px;
        }

        .hero h1 {
            font-size: 48px;
            font-weight: 300;
            letter-spacing: 2px;
            color: #2a2a2a;
            margin-bottom: 15px;
        }

        .hero p {
            font-size: 16px;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Filter Section */
        .filter-section {
            max-width: 1400px;
            margin: 0 auto 40px;
            padding: 0 40px;
        }

        .filter-container {
            background: #fff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 40px;
            align-items: center;
        }

        .category-tabs {
            display: flex;
            gap: 10px;
        }

        .category-tab {
            padding: 12px 30px;
            background: transparent;
            border: 2px solid #e5e5e5;
            border-radius: 25px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #666;
        }

        .category-tab:hover {
            border-color: #ffb6d9;
            color: #2a2a2a;
        }

        .category-tab.active {
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            border-color: #ffb6d9;
            color: #fff;
        }

        .price-filter {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .price-filter label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            font-weight: 600;
        }

        .price-range {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .price-slider {
            flex: 1;
            height: 6px;
            border-radius: 3px;
            background: #e5e5e5;
            outline: none;
            -webkit-appearance: none;
        }

        .price-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            cursor: pointer;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .price-slider::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            cursor: pointer;
            border: 3px solid #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        .price-values {
            display: flex;
            gap: 10px;
            align-items: center;
            font-size: 14px;
            color: #2a2a2a;
            font-weight: 600;
        }

        .apply-filter-btn {
            padding: 12px 25px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            border: none;
            border-radius: 25px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .apply-filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 182, 217, 0.4);
        }

        /* Products Grid */
        .products-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px 80px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 32px;
            font-weight: 300;
            letter-spacing: 2px;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .product-count {
            font-size: 14px;
            color: #999;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
        }

        .product-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(255, 182, 217, 0.3);
        }

        .product-image {
            width: 100%;
            height: 320px;
            overflow: hidden;
            background: #f8f8f8;
            position: relative;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }

        .product-card:hover .product-image img {
            transform: scale(1.08);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .product-info {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-category {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #ff9ec8;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .product-name {
            font-size: 18px;
            font-weight: 500;
            color: #2a2a2a;
            margin-bottom: 8px;
            line-height: 1.4;
        }

        .product-brand {
            font-size: 13px;
            color: #999;
            margin-bottom: 12px;
        }

        .product-description {
            font-size: 13px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            flex: 1;
        }

        .product-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }

        .product-price {
            font-size: 22px;
            font-weight: 600;
            color: #2a2a2a;
        }

        .add-to-cart-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            border: none;
            border-radius: 20px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 600;
        }

        .add-to-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 182, 217, 0.4);
        }

        .no-products {
            text-align: center;
            padding: 80px 20px;
            color: #999;
        }

        .no-products-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .no-products h3 {
            font-size: 24px;
            font-weight: 300;
            margin-bottom: 10px;
            color: #666;
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

        @media (max-width: 768px) {
            .header-container {
                padding: 15px 20px;
            }

            .hero {
                padding: 50px 20px;
            }

            .hero h1 {
                font-size: 32px;
            }

            .filter-container {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .category-tabs {
                flex-wrap: wrap;
            }

            .price-filter {
                flex-direction: column;
                align-items: stretch;
            }

            .products-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
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
                    <li><a href="about.php">About</a></li>
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

    <!-- Hero Section -->
    <section class="hero">
        <h1>Discover Your Beauty</h1>
        <p>Explore our curated collection of luxury fragrances and premium beauty products</p>
    </section>

    <!-- Filter Section -->
    <section class="filter-section">
        <div class="filter-container">
            <div class="category-tabs">
                <a href="shop.php?category=all" class="category-tab <?php echo $category === 'all' ? 'active' : ''; ?>">All</a>
                <a href="shop.php?category=beauty" class="category-tab <?php echo $category === 'beauty' ? 'active' : ''; ?>">Beauty</a>
                <a href="shop.php?category=fragrances" class="category-tab <?php echo $category === 'fragrances' ? 'active' : ''; ?>">Fragrances</a>
            </div>

            <form id="priceFilterForm" class="price-filter" method="GET">
                <input type="hidden" name="category" value="<?php echo htmlspecialchars($category); ?>">
                <label>Price Range:</label>
                <div class="price-range">
                    <input type="range" id="maxPriceSlider" class="price-slider" 
                           min="<?php echo $dbMinPrice; ?>" 
                           max="<?php echo $dbMaxPrice; ?>" 
                           value="<?php echo $maxPrice; ?>" 
                           step="1">
                </div>
                <div class="price-values">
                    <span>₹<span id="minPriceDisplay"><?php echo number_format($minPrice, 0); ?></span></span>
                    <span>-</span>
                    <span>₹<span id="maxPriceDisplay"><?php echo number_format($maxPrice, 0); ?></span></span>
                </div>
                <input type="hidden" name="min_price" id="minPriceInput" value="<?php echo $minPrice; ?>">
                <input type="hidden" name="max_price" id="maxPriceInput" value="<?php echo $maxPrice; ?>">
                <button type="submit" class="apply-filter-btn">Apply</button>
            </form>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-container">
        <div class="section-header">
            <h2>
                <?php 
                    if ($category === 'beauty') echo 'Beauty Products';
                    elseif ($category === 'fragrances') echo 'Fragrances';
                    else echo 'All Products';
                ?>
            </h2>
            <p class="product-count"><?php echo $products->num_rows; ?> products found</p>
        </div>

        <?php if ($products->num_rows > 0): ?>
        <div class="products-grid">
            <?php while($row = $products->fetch_assoc()): ?>
            <div class="product-card">
                <a href="productdetails.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">
                    <div class="product-image">
                        <?php if ($row['primary_image']): ?>
                            <img src="<?php echo htmlspecialchars($row['primary_image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x400/ffeef5/999999?text=No+Image" alt="No Image">
                        <?php endif; ?>
                        <span class="product-badge"><?php echo ucfirst($row['category']); ?></span>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo ucfirst($row['category']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="product-brand"><?php echo htmlspecialchars($row['brand']); ?></div>
                        <p class="product-description"><?php echo htmlspecialchars($row['description']); ?></p>
                    </div>
                </a>
                <div class="product-footer" style="padding: 25px 25px 0;">
                    <span class="product-price">₹<?php echo number_format($row['price'], 2); ?></span>
                    <button type="button" class="add-to-cart-btn" onclick="addToCart(<?php echo $row['id']; ?>)">Add to Cart</button>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="no-products">
            <div class="no-products-icon">🛒</div>
            <h3>No products found</h3>
            <p>Try adjusting your filters or browse all products</p>
        </div>
        <?php endif; ?>
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
                        <li><a href="aboutphp">Our Story</a></li>
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

        // Price range slider functionality
        const minSlider = document.getElementById('minPriceSlider');
        const maxSlider = document.getElementById('maxPriceSlider');
        const minDisplay = document.getElementById('minPriceDisplay');
        const maxDisplay = document.getElementById('maxPriceDisplay');
        const minInput = document.getElementById('minPriceInput');
        const maxInput = document.getElementById('maxPriceInput');

        function updateMaxPrice() {
            let maxVal = parseInt(maxSlider.value);
            maxDisplay.textContent = maxVal.toLocaleString();
            maxInput.value = maxVal;
        }

        maxSlider.addEventListener('input', updateMaxPrice);

        // Add to cart functionality with login check
        function addToCart(productId) {
            const isLoggedIn = <?php echo json_encode($isLoggedIn); ?>;
            
            if (!isLoggedIn) {
                window.location.href = 'signup.php?redirect=shop';
                return;
            }
            
            const quantity = 1;
            
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
                    alert('✓ ' + data.product_name + ' added to cart!');
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