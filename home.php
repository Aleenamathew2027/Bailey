<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BAILEY - Luxury Beauty & Perfume</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #333;
            background: linear-gradient(135deg, #fff0f5 0%, #fffaf7 50%, #ffffff 100%);
            background-attachment: fixed;
            line-height: 1.6;
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
        }

        .icon-btn:hover {
            color: #000;
        }

        /* User Profile Dropdown - FIXED */
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

        .dropdown-header .user-email {
            font-size: 13px;
            color: #999;
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

        .dropdown-item i {
            font-size: 16px;
            width: 20px;
        }

        .dropdown-divider {
            height: 1px;
            background: #f0f0f0;
            margin: 4px 0;
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

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #f5f5f5 0%, #e8e8e8 100%);
            background-image: url('img3.webp'); 
            background-size: cover;
            background-position: center;
            padding: 100px 40px;
            text-align: center;
        }

        .hero h1 {
            font-size: 56px;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 20px;
            color: #2a2a2a;
        }

        .hero p {
            font-size: 18px;
            color: #666;
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-button {
            display: inline-block;
            padding: 15px 40px;
            background: #2a2a2a;
            color: #fff;
            text-decoration: none;
            border-radius: 30px;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .cta-button:hover {
            background: #000;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        .cta-button-outline {
            display: inline-block;
            padding: 15px 40px;
            background: transparent;
            color: #2a2a2a;
            text-decoration: none;
            border: 2px solid #2a2a2a;
            border-radius: 30px;
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .cta-button-outline:hover {
            background: #2a2a2a;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }

        /* Eye Prep Section */
        .eye-prep-section {
            background: #f8f8f8;
            padding: 0;
            margin: 80px 0;
            overflow: hidden;
        }

        .eye-prep-container {
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            align-items: stretch;
            min-height: 700px;
            max-width: 100%;
            margin: 0 auto;
            gap: 0;
        }

        .eye-prep-image {
            position: relative;
            height: 100%;
            min-height: 700px;
            overflow: hidden;
            background: #e8e8e8;
        }

        .eye-prep-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #f0f0f0 0%, #e0e0e0 100%);
            z-index: 1;
        }

        .eye-prep-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
            z-index: 2;
            transition: transform 0.5s ease;
        }

        .eye-prep-image.left::after {
            content: '💤';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 100px;
            z-index: 3;
            opacity: 0.3;
        }

        .eye-prep-image.right::after {
            content: '💥';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 100px;
            z-index: 3;
            opacity: 0.3;
        }

        .eye-prep-image:hover img {
            transform: scale(1.05);
        }

        .eye-prep-content {
            padding: 80px 60px;
            text-align: center;
            background: #fff;
            background-image: url('img1.webp');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        .eye-prep-content h2 {
            font-size: 52px;
            font-weight: 300;
            letter-spacing: 1px;
            margin-bottom: 30px;
            color: #5a5a5a;
        }

        .eye-prep-content p {
            font-size: 17px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 40px;
            max-width: 550px;
        }

        @media (max-width: 1024px) {
            .eye-prep-container {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .eye-prep-image {
                height: 400px;
                min-height: 400px;
            }

            .eye-prep-image.left {
                order: 1;
            }

            .eye-prep-content {
                order: 2;
                padding: 60px 40px;
            }

            .eye-prep-image.right {
                order: 3;
            }
        }

        /* Product Collections */
        .collections {
            max-width: 1400px;
            margin: 80px auto;
            padding: 0 40px;
        }

        .section-title {
            text-align: center;
            font-size: 36px;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 60px;
            color: #2a2a2a;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 40px;
            margin-bottom: 80px;
        }

        .product-card {
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .product-image {
    width: 100%;
    height: 400px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 80px;
    color: #d0d0d0;
    overflow: hidden;
    position: relative;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}
        .product-info {
            padding: 25px;
            text-align: center;
        }

        .product-category {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #999;
            margin-bottom: 10px;
        }

        .product-name {
            font-size: 20px;
            font-weight: 400;
            margin-bottom: 10px;
            color: #2a2a2a;
        }

        .product-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .product-price {
            font-size: 18px;
            font-weight: 500;
            color: #2a2a2a;
        }

        /* Featured Banner */
        .featured-banner {
            background: linear-gradient(135deg, #2a2a2a 0%, #4a4a4a 100%);
            background-image: url('pin3.webp'); 
            background-size: cover;
            background-position: center;
            color: #fff;
            padding: 100px 40px;
            text-align: center;
            margin: 80px 0;
            position: relative;
        }

        .featured-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(42, 42, 42, 0.6);
            z-index: 1;
        }

        .featured-banner h2,
        .featured-banner p,
        .featured-banner .cta-button {
            position: relative;
            z-index: 2;
        }

        .featured-banner h2 {
            font-size: 42px;
            font-weight: 300;
            letter-spacing: 2px;
            margin-bottom: 20px;
        }

        .featured-banner p {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.9;
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

        /* Mobile Menu */
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #5a5a5a;
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 15px 20px;
            }

            nav {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }

            .hero h1 {
                font-size: 36px;
            }

            .product-grid {
                grid-template-columns: 1fr;
            }

            .footer-content {
                grid-template-columns: 1fr;
            }

            .dropdown-menu {
                right: 0;
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

    <!-- Hero Section -->
    <section class="hero">
        <h1>Elevate Your Beauty</h1>
        <p>Discover luxury perfumes and premium beauty products crafted for the modern connoisseur</p>
        <a href="shop.php" class="cta-button">Shop Collection</a>
    </section>

    <!-- Product Collections -->
<section class="collections">
    <h2 class="section-title">Signature Collections</h2>
    <div class="product-grid">
        <?php
        // Fetch featured products from database
        require_once 'db.php';
        
        $featuredQuery = "SELECT p.*, 
                         (SELECT pi.image_path FROM product_images pi 
                          WHERE pi.product_id = p.id AND pi.is_primary = 1 
                          LIMIT 1) as primary_image
                         FROM products p 
                         ORDER BY p.created_at DESC 
                         LIMIT 3";
        
        $featuredResult = $conn->query($featuredQuery);
        
        if ($featuredResult && $featuredResult->num_rows > 0) {
            while($product = $featuredResult->fetch_assoc()):
        ?>
        <div class="product-card" onclick="window.location.href='productdetails.php?id=<?php echo $product['id']; ?>'">
            <div class="product-image">
                <?php if ($product['primary_image']): ?>
                    <img src="<?php echo htmlspecialchars($product['primary_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100%; height: 100%; object-fit: cover;">
                <?php else: ?>
                    <!-- Fallback image if no product image -->
                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; font-size: 80px; color: #d0d0d0; background: #f5f5f5;">
                        <?php 
                        // Display emoji based on category as fallback
                        if ($product['category'] === 'fragrances') echo '🌸';
                        elseif ($product['category'] === 'skincare') echo '✨';
                        else echo '💎';
                        ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="product-info">
                <div class="product-category"><?php echo ucfirst($product['category']); ?></div>
                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                <p class="product-description"><?php echo htmlspecialchars($product['description']); ?></p>
                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
            </div>
        </div>
        <?php 
            endwhile;
        } else {
            // Fallback products if no products in database
            $fallbackProducts = [
                [
                    'id' => 1,
                    'category' => 'fragrances', 
                    'name' => 'Essence de Luxe', 
                    'description' => 'A sophisticated blend of floral and woody notes', 
                    'price' => '125.00',
                    'image' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'
                ],
                [
                    'id' => 2,
                    'category' => 'skincare', 
                    'name' => 'Radiance Serum', 
                    'description' => 'Luminous hydration for glowing skin', 
                    'price' => '89.00',
                    'image' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'
                ],
                [
                    'id' => 3,
                    'category' => 'fragrances', 
                    'name' => 'Midnight Mystique', 
                    'description' => 'An intoxicating evening fragrance', 
                    'price' => '145.00',
                    'image' => 'https://images.unsplash.com/photo-1592945403244-b3fbafd7f539?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'
                ]
            ];
            
            foreach($fallbackProducts as $product):
        ?>
        <div class="product-card" onclick="window.location.href='productdetails.php?id=<?php echo $product['id']; ?>'">
            <div class="product-image">
                <img src="<?php echo $product['image']; ?>" 
                     alt="<?php echo $product['name']; ?>"
                     style="width: 100%; height: 100%; object-fit: cover;"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div style="display: none; align-items: center; justify-content: center; height: 100%; font-size: 80px; color: #d0d0d0; background: #f5f5f5;">
                    <?php 
                    if ($product['category'] === 'fragrances') echo '🌸';
                    elseif ($product['category'] === 'skincare') echo '✨';
                    else echo '💎';
                    ?>
                </div>
            </div>
            <div class="product-info">
                <div class="product-category"><?php echo ucfirst($product['category']); ?></div>
                <h3 class="product-name"><?php echo $product['name']; ?></h3>
                <p class="product-description"><?php echo $product['description']; ?></p>
                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
            </div>
        </div>
        <?php endforeach; } ?>
    </div>
</section>

    <!-- Featured Eye Prep Section -->
    <section class="eye-prep-section">
        <div class="eye-prep-container">
            <div class="eye-prep-image left">
                <img src="img4.jpg" alt="Model with Eye Patches"> 
            </div>
            <div class="eye-prep-content">
                <h2>prep, set, go</h2>
                <p>Meet Peptide Eye Prep, ready-to-wear skincare for your everyday routine. Our new cooling hydrogel eye patches depuff and brighten whenever your under-eyes need a little wake-up.</p>
                <a href="#eye-prep" class="cta-button-outline">Shop Eye Prep</a>
            </div>
            <div class="eye-prep-image right">
                <img src="img2.webp" alt="Models with Eye Patches"> 
            </div>
        </div>
    </section>

    <!-- Featured Banner -->
    <section class="featured-banner">
        <h2>New Arrivals</h2>
        <p>Explore our latest collection of exquisite fragrances and beauty essentials</p>
        <a href="shop.php" class="cta-button">Discover Now</a>
    </section>

    <!-- Best Sellers -->
    <section class="collections">
        <h2 class="section-title">Best Sellers</h2>
        <div class="product-grid">
            <div class="product-card">
                <div class="product-image">🌹</div>
                <div class="product-info">
                    <div class="product-category">Fragrance</div>
                    <h3 class="product-name">Rose Éternelle</h3>
                    <p class="product-description">Classic elegance in every drop</p>
                    <div class="product-price">$135.00</div>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image">🧴</div>
                <div class="product-info">
                    <div class="product-category">Skincare</div>
                    <h3 class="product-name">Silk Moisturizer</h3>
                    <p class="product-description">Velvety smooth hydration</p>
                    <div class="product-price">$78.00</div>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image">🌙</div>
                <div class="product-info">
                    <div class="product-category">Perfume</div>
                    <h3 class="product-name">Luna Noir</h3>
                    <p class="product-description">Mysterious and captivating</p>
                    <div class="product-price">$158.00</div>
                </div>
            </div>
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
                        <a href="#" class="social-icon">🦆</a>
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
        // Dropdown functionality - FIXED
        const userInitial = document.getElementById('userInitial');
        const userProfile = document.getElementById('userProfile');

        if (userInitial && userProfile) {
            // Toggle dropdown on click
            userInitial.addEventListener('click', function(event) {
                event.stopPropagation();
                userProfile.classList.toggle('active');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!userProfile.contains(event.target)) {
                    userProfile.classList.remove('active');
                }
            });

            // Prevent dropdown from closing when clicking inside it
            const dropdownMenu = document.getElementById('dropdownMenu');
            if (dropdownMenu) {
                dropdownMenu.addEventListener('click', function(event) {
                    event.stopPropagation();
                });
            }
        }

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to header
        let lastScroll = 0;
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > lastScroll && currentScroll > 100) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            lastScroll = currentScroll;
        });

        // Product card animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.product-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>