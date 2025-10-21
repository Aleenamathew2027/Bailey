<?php
// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db.php';

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';

// Cart Class
class Cart {
    private $conn;
    private $table = "cart";
    private $session_id;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->session_id = session_id();
    }
    
    // Get cart items with product details
    public function getCartItems() {
        $query = "SELECT c.id as cart_id, c.quantity, p.id, p.name, p.price, p.brand, p.category,
                  (SELECT pi.image_path FROM product_images pi 
                   WHERE pi.product_id = p.id AND pi.is_primary = 1 
                   LIMIT 1) as image
                  FROM " . $this->table . " c
                  JOIN products p ON c.product_id = p.id
                  WHERE c.session_id = ?
                  ORDER BY c.added_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->session_id);
        $stmt->execute();
        return $stmt->get_result();
    }
    
    // Get cart count
    public function getCartCount() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table . " WHERE session_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->session_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result['count'];
    }
    
    // Update quantity
    public function updateQuantity($cart_id, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cart_id);
        }
        
        $query = "UPDATE " . $this->table . " SET quantity = ? WHERE id = ? AND session_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $quantity, $cart_id, $this->session_id);
        return $stmt->execute();
    }
    
    // Remove item
    public function removeItem($cart_id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ? AND session_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $cart_id, $this->session_id);
        return $stmt->execute();
    }
    
    // Clear cart
    public function clearCart() {
        $query = "DELETE FROM " . $this->table . " WHERE session_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->session_id);
        return $stmt->execute();
    }
}

$cart = new Cart($conn);
$cartItems = $cart->getCartItems();
$cartCount = $cart->getCartCount();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    if ($_POST['action'] === 'update_quantity') {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        $result = $cart->updateQuantity($cart_id, $quantity);
        
        echo json_encode(['success' => $result]);
        exit;
    } elseif ($_POST['action'] === 'remove_item') {
        $cart_id = intval($_POST['cart_id']);
        $result = $cart->removeItem($cart_id);
        
        echo json_encode(['success' => $result]);
        exit;
    } elseif ($_POST['action'] === 'clear_cart') {
        $result = $cart->clearCart();
        
        echo json_encode(['success' => $result]);
        exit;
    }
}

// Calculate total
$cartItems->data_seek(0);
$total = 0;
$itemCount = 0;
while($row = $cartItems->fetch_assoc()) {
    $total += $row['price'] * $row['quantity'];
    $itemCount += $row['quantity'];
}

// Reset result pointer
$cartItems->data_seek(0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - BAILEY</title>
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

        .dropdown-divider {
            height: 1px;
            background: #f0f0f0;
            margin: 4px 0;
        }

        /* Main Content */
        .cart-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .cart-header {
            margin-bottom: 50px;
        }

        .cart-header h1 {
            font-size: 42px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .cart-header p {
            font-size: 16px;
            color: #666;
        }

        .cart-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        /* Cart Items */
        .cart-items {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .cart-item {
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: start;
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 100px;
            height: 100px;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f8f8;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            display: flex;
            justify-content: space-between;
        }

        .item-info h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 5px;
        }

        .item-brand {
            font-size: 13px;
            color: #999;
            margin-bottom: 15px;
        }

        .item-price {
            font-size: 18px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 15px;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .qty-input {
            width: 60px;
            padding: 8px;
            border: 1px solid #d0d0d0;
            border-radius: 4px;
            text-align: center;
            font-size: 14px;
        }

        .qty-btn {
            width: 30px;
            height: 30px;
            border: 1px solid #d0d0d0;
            background: #fff;
            cursor: pointer;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .qty-btn:hover {
            background: #ffb6d9;
            border-color: #ffb6d9;
            color: #fff;
        }

        .remove-btn {
            background: #fff;
            border: 1px solid #d0d0d0;
            color: #666;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .remove-btn:hover {
            background: #ffb6d9;
            border-color: #ffb6d9;
            color: #fff;
        }

        .item-total {
            font-size: 18px;
            font-weight: 600;
            color: #2a2a2a;
        }

        /* Cart Summary */
        .cart-summary {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .summary-header {
            font-size: 20px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }

        .summary-row.total {
            font-size: 20px;
            font-weight: 600;
            color: #2a2a2a;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
            margin-bottom: 30px;
        }

        .checkout-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .checkout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 182, 217, 0.4);
        }

        .continue-shopping-btn {
            width: 100%;
            padding: 16px;
            background: #fff;
            color: #2a2a2a;
            border: 2px solid #e5e5e5;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .continue-shopping-btn:hover {
            border-color: #ffb6d9;
            color: #ffb6d9;
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 80px 40px;
        }

        .empty-cart-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-cart h2 {
            font-size: 32px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 15px;
        }

        .empty-cart p {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
        }

        .empty-cart a {
            display: inline-block;
            padding: 14px 40px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s;
        }

        .empty-cart a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 182, 217, 0.4);
        }

        /* Footer */
        footer {
            background: #fff;
            border-top: 1px solid #e5e5e5;
            padding: 60px 40px 30px;
            margin-top: 80px;
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

        @media (max-width: 768px) {
            .cart-content {
                grid-template-columns: 1fr;
            }

            .cart-summary {
                position: static;
            }

            .cart-item {
                grid-template-columns: 80px 1fr;
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

    <!-- Cart Content -->
    <section class="cart-container">
        <div class="cart-header">
            <h1>Shopping Cart</h1>
            <p><?php echo $itemCount; ?> item<?php echo $itemCount !== 1 ? 's' : ''; ?> in cart</p>
        </div>

        <?php if ($cartCount > 0): ?>
        <div class="cart-content">
            <!-- Cart Items -->
            <div class="cart-items">
                <?php 
                $cartItems->data_seek(0);
                while($item = $cartItems->fetch_assoc()): 
                ?>
                <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                    <div class="item-image">
                        <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/100x100/ffeef5/999999?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="item-details">
                        <div class="item-info">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <div class="item-brand"><?php echo htmlspecialchars($item['brand']); ?></div>
                            <div class="item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                            <div class="quantity-control">
                                <button class="qty-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>)">−</button>
                                <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" onchange="updateQuantity(<?php echo $item['cart_id']; ?>, this.value)" min="1">
                                <button class="qty-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>)">+</button>
                            </div>
                            <button class="remove-btn" onclick="removeItem(<?php echo $item['cart_id']; ?>)">Remove</button>
                        </div>
                        <div class="item-total">
                            ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <div class="summary-header">Order Summary</div>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>₹0.00</span>
                </div>
                
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>₹0.00</span>
                </div>
                
                <div class="summary-row total">
                    <span>Total:</span>
                    <span>₹<?php echo number_format($total, 2); ?></span>
                </div>

                <button class="checkout-btn" onclick="checkout()">Proceed to Checkout</button>
                <a href="shop.php" class="continue-shopping-btn">Continue Shopping</a>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h2>Your Cart is Empty</h2>
            <p>Looks like you haven't added anything to your cart yet. Start shopping now!</p>
            <a href="shop.php">Start Shopping</a>
        </div>
        <?php endif; ?>
    </section>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Company</h3>
                    <ul class="footer-links">
                        <li><a href="#about">About Bailey</a></li>
                        <li><a href="#careers">Careers</a></li>
                        <li><a href="#press">Press</a></li>
                        <li><a href="#sustainability">Sustainability</a></li>
                    </ul>
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

        function updateQuantity(cartId, newQuantity) {
            newQuantity = parseInt(newQuantity);
            if (newQuantity < 1) return;

            const formData = new FormData();
            formData.append('action', 'update_quantity');
            formData.append('cart_id', cartId);
            formData.append('quantity', newQuantity);

            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            });
        }

        function removeItem(cartId) {
            if (confirm('Are you sure you want to remove this item?')) {
                const formData = new FormData();
                formData.append('action', 'remove_item');
                formData.append('cart_id', cartId);

                fetch('cart.php',
                {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }

        
function checkout() {
    window.location.href = 'payment.php';
}
    </script>
</body>
</html>

<?php
$conn->close();
?>