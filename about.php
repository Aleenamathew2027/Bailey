<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - BAILEY</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
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

        /* User Profile */
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

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(255,245,249,0.9) 0%, rgba(255,228,240,0.9) 50%, rgba(255,255,255,0.9) 100%), 
                        url('img9.jpg') center/cover;
            padding: 120px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: #666;
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Mission Section */
        .mission-section {
            padding: 100px 40px;
            background: #fff;
        }

        .mission-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .mission-content h2 {
            font-size: 2.5rem;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 30px;
        }

        .mission-text {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 30px;
        }

        .mission-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .mission-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .mission-image:hover img {
            transform: scale(1.05);
        }

        /* Values Section */
        .values-section {
            padding: 100px 40px;
            background: #f8f8f8;
        }

        .values-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 60px;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .value-card {
            background: #fff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-10px);
        }

        .value-image {
            height: 250px;
            overflow: hidden;
        }

        .value-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .value-card:hover .value-image img {
            transform: scale(1.1);
        }

        .value-content {
            padding: 30px;
            text-align: center;
        }

        .value-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: #ff69b4;
        }

        .value-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 15px;
        }

        .value-description {
            color: #666;
            line-height: 1.6;
        }

        /* Story Section */
        .story-section {
            padding: 100px 40px;
            background: #fff;
        }

        .story-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .story-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .story-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .story-content {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .story-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-top: 60px;
        }

        .stat-item {
            text-align: center;
            padding: 30px 20px;
            background: #f8f8f8;
            border-radius: 15px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 300;
            color: #ff69b4;
            margin-bottom: 10px;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Team Section */
        .team-section {
            padding: 100px 40px;
            background: #f8f8f8;
        }

        .team-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }

        .team-member {
            text-align: center;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-10px);
        }

        .member-image {
            height: 300px;
            overflow: hidden;
        }

        .member-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .team-member:hover .member-image img {
            transform: scale(1.1);
        }

        .member-info {
            padding: 30px 20px;
        }

        .member-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .member-role {
            color: #ff69b4;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .member-bio {
            color: #666;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        /* Commitment Section */
        .commitment-section {
            padding: 100px 40px;
            background: #fff;
        }

        .commitment-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 80px;
            align-items: center;
        }

        .commitment-image {
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        .commitment-image img {
            width: 100%;
            height: 500px;
            object-fit: cover;
        }

        .commitment-content {
            font-size: 1.1rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        .commitment-features {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-top: 40px;
        }

        .feature-item {
            display: grid;
            grid-template-columns: 80px 1fr;
            gap: 20px;
            align-items: start;
            padding: 25px;
            background: #f8f8f8;
            border-radius: 15px;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: #ff69b4;
        }

        .feature-content h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .feature-content p {
            color: #666;
            line-height: 1.6;
        }

        /* CTA Section */
        .cta-section {
            padding: 100px 40px;
            background: linear-gradient(135deg, rgba(255,105,180,0.9) 0%, rgba(255,179,193,0.9) 100%), 
                        url('im6.jpg') center/cover;
            text-align: center;
            color: white;
        }

        .cta-container {
            max-width: 600px;
            margin: 0 auto;
        }

        .cta-title {
            font-size: 2.5rem;
            font-weight: 300;
            margin-bottom: 20px;
        }

        .cta-text {
            font-size: 1.1rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .btn {
            padding: 15px 35px;
            border: 2px solid white;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: white;
            color: #ff69b4;
        }

        .btn-primary:hover {
            background: transparent;
            color: white;
        }

        .btn-secondary {
            background: transparent;
            color: white;
        }

        .btn-secondary:hover {
            background: white;
            color: #ff69b4;
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

        .footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid #e5e5e5;
            color: #999;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .mission-container,
            .values-grid,
            .story-container,
            .team-grid,
            .commitment-container {
                grid-template-columns: 1fr;
            }

            .story-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .footer-content {
                grid-template-columns: repeat(2, 1fr);
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 200px;
            }

            .feature-item {
                grid-template-columns: 1fr;
                text-align: center;
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
                    <li><a href="cart.php">Cart</a></li>
                    <?php if (!$isLoggedIn): ?>
                        <li><a href="signup.php" class="signup-btn">Sign Up</a></li>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                        <li class="user-profile" id="userProfile">
                            <div class="user-initial" id="userInitial"><?php echo $userInitial; ?></div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">About Bailey</h1>
            <p class="hero-subtitle">
                Where luxury meets authenticity. We believe in the power of clean, effective formulas 
                that enhance your natural beauty while celebrating your individuality.
            </p>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="mission-section">
        <div class="mission-container">
            <div class="mission-content">
                <h2>Our Mission</h2>
                <p class="mission-text">
                    At Bailey, we're redefining luxury beauty by creating products that are as good for your skin 
                    as they are for your soul. We believe that true beauty comes from within, and our mission is 
                    to provide the tools that help you express your unique self.
                </p>
                <p class="mission-text">
                    Every product is meticulously crafted with clean, ethically sourced ingredients, 
                    designed to deliver exceptional results without compromise. We're committed to 
                    transparency, sustainability, and the belief that everyone deserves to feel beautiful.
                </p>
            </div>
            <div class="mission-image">
                <img src="hail.webp" alt="Bailey Beauty Products" 
                     onerror="this.src='https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1180&q=80'">
            </div>
        </div>
    </section>

    <!-- Values Section -->
    <section class="values-section">
        <div class="values-container">
            <h2 class="section-title">Our Values</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-image">
                        <img src="img4.jpg" alt="Clean Ingredients"
                             onerror="this.src='https://images.unsplash.com/photo-1556228578-8c89e6adf883?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
                    </div>
                    <div class="value-content">
                        <div class="value-icon">🌿</div>
                        <h3 class="value-title">Clean Ingredients</h3>
                        <p class="value-description">
                            We use only the purest, most effective ingredients, free from harmful chemicals 
                            and always cruelty-free.
                        </p>
                    </div>
                </div>
                <div class="value-card">
                    <div class="value-image">
                        <img src="pin1.jpg" alt="Luxury Experience"
                             onerror="this.src='https://images.unsplash.com/photo-1596462502278-27bfdc403348?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
                    </div>
                    <div class="value-content">
                        <div class="value-icon">💎</div>
                        <h3 class="value-title">Luxury Experience</h3>
                        <p class="value-description">
                            From packaging to performance, every detail is crafted to deliver an exceptional 
                            luxury experience.
                        </p>
                    </div>
                </div>
                <div class="value-card">
                    <div class="value-image">
                        <img src="img5.jpg" alt="Sustainability"
                             onerror="this.src='https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
                    </div>
                    <div class="value-content">
                        <div class="value-icon">🌎</div>
                        <h3 class="value-title">Sustainability</h3>
                        <p class="value-description">
                            We're committed to reducing our environmental impact through responsible sourcing 
                            and eco-friendly packaging.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="story-section">
        <div class="story-container">
            <div class="story-image">
                <img src="img6.jpg" alt="Our Story"
                     onerror="this.src='https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
            </div>
            <div class="story-content">
                <h2 class="section-title">Our Story</h2>
                <p>
                    Founded in 2018, Bailey began as a passion project between two friends who believed 
                    that beauty should be both luxurious and responsible. Frustrated by the lack of 
                    transparency in the beauty industry, we set out to create a brand that puts people 
                    and planet first.
                </p>
                <p>
                    What started as a small collection of fragrances has grown into a comprehensive 
                    beauty line, but our core values remain unchanged. We're still independently owned, 
                    still obsessed with quality, and still committed to making beauty better.
                </p>
                
                <div class="story-stats">
                    <div class="stat-item">
                        <div class="stat-number">5+</div>
                        <div class="stat-label">Years of Excellence</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50K+</div>
                        <div class="stat-label">Happy Customers</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">100+</div>
                        <div class="stat-label">Products</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">1</div>
                        <div class="stat-label">Mission</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team-section">
        <div class="team-container">
            <h2 class="section-title">Meet Our Founders</h2>
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-image">
                        <img src="Sarah.jpg" alt="Sarah Chen"
                             onerror="this.src='https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Sarah Chen</h3>
                        <div class="member-role">Co-Founder & CEO</div>
                        <p class="member-bio">
                            Former cosmetic chemist with a passion for clean formulations and sustainable beauty practices.
                        </p>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/marcus-rodriguez.jpg" alt="Marcus Rodriguez"
                             onerror="this.src='https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Marcus Rodriguez</h3>
                        <div class="member-role">Co-Founder & Creative Director</div>
                        <p class="member-bio">
                            Perfumer and designer dedicated to creating sensory experiences that inspire and delight.
                        </p>
                    </div>
                </div>
                <div class="team-member">
                    <div class="member-image">
                        <img src="images/elena-petrova.jpg" alt="Dr. Elena Petrova"
                             onerror="this.src='https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
                    </div>
                    <div class="member-info">
                        <h3 class="member-name">Dr. Elena Petrova</h3>
                        <div class="member-role">Head of Research</div>
                        <p class="member-bio">
                            Dermatologist and researcher ensuring every product meets the highest standards of efficacy and safety.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Commitment Section -->
    <section class="commitment-section">
        <div class="commitment-container">
            <div class="commitment-content">
                <h2 class="section-title">Our Commitment</h2>
                <p class="commitment-content">
                    We're committed to more than just creating beautiful products. We're building a community 
                    that celebrates diversity, promotes self-love, and champions environmental responsibility.
                </p>
                
                <div class="commitment-features">
                    <div class="feature-item">
                        <div class="feature-icon">♻️</div>
                        <div class="feature-content">
                            <h3>Eco-Friendly Packaging</h3>
                            <p>All our packaging is recyclable, and we're constantly innovating to reduce waste.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🤝</div>
                        <div class="feature-content">
                            <h3>Community Support</h3>
                            <p>We donate 1% of all sales to organizations supporting women in STEM and environmental causes.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">🌱</div>
                        <div class="feature-content">
                            <h3>Sustainable Sourcing</h3>
                            <p>We partner with ethical suppliers who share our commitment to environmental stewardship.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="commitment-image">
                <img src="pin3.webp" alt="Our Commitment"
                     onerror="this.src='https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-4.0.3&auto=format&fit=crop&w=1180&q=80'">
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="cta-container">
            <h2 class="cta-title">Join the Bailey Family</h2>
            <p class="cta-text">
                Experience the difference of clean, luxurious beauty crafted with purpose and passion.
            </p>
            <div class="cta-buttons">
                <a href="shop.php" class="btn btn-primary">Shop Now</a>
                <a href="signup.php" class="btn btn-secondary">Create Account</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul class="footer-links">
                        <li><a href="about.php">About Bailey</a></li>
                        <li><a href="#careers">Careers</a></li>
                        <li><a href="#press">Press</a></li>
                        <li><a href="#sustainability">Sustainability</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Navigate</h3>
                    <ul class="footer-links">
                        <li><a href="shop.php">Shop</a></li>
                        <li><a href="about.php">Our Story</a></li>
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
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 BAILEY. All rights reserved. | Crafted with excellence for beauty enthusiasts worldwide.</p>
            </div>
        </div>
    </footer>

    <script>
        // Simple animation on scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all sections for animation
            document.querySelectorAll('.mission-content, .value-card, .team-member, .feature-item, .story-content, .commitment-content').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(30px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>