<?php
// payment.php - Handle payment page

// Start session only if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signup.php?redirect=cart');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get cart items and calculate total
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
        $query = "SELECT SUM(quantity) as count FROM " . $this->table . " WHERE session_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $this->session_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return intval($result['count'] ?? 0);
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
$cart_count = $cart->getCartCount();

// Calculate totals
$cart_total = 0;
$cart_items_data = [];

while($item = $cartItems->fetch_assoc()) {
    $cart_total += $item['price'] * $item['quantity'];
    $cart_items_data[] = $item;
}

// If cart is empty, redirect back to cart
if (empty($cart_items_data)) {
    header('Location: cart.php');
    exit;
}

$shipping_cost = 0.00; // Free shipping
$tax_rate = 0.00; // No tax for simplicity
$tax_amount = $cart_total * $tax_rate;
$grand_total = $cart_total + $tax_amount + $shipping_cost;

// Razorpay credentials (test mode)
$razorpay_key = "rzp_test_iZLI83hLdG7JqU";
// Note: In production, you should store the secret key securely and not expose it

// Generate order ID for tracking
$order_id = "ORDER_" . $user_id . "_" . time();

// Razorpay expects amount in paise (1 rupee = 100 paise)
$razorpay_amount = intval($grand_total * 100);

$isLoggedIn = isset($_SESSION['user_id']);
$userInitial = $isLoggedIn ? strtoupper(substr($user_name, 0, 1)) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - BAILEY</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
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

        /* Payment Container */
        .payment-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 40px;
        }

        .payment-header {
            margin-bottom: 50px;
            text-align: center;
        }

        .payment-header h1 {
            font-size: 42px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .payment-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        .order-summary {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            height: fit-content;
        }

        .payment-details {
            background: #fff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        .summary-header {
            font-size: 20px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-items {
            margin: 20px 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-info h4 {
            font-size: 16px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 5px;
        }

        .item-meta {
            font-size: 13px;
            color: #999;
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

        .payment-method {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }

        .payment-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #ff69b4;
        }

        .payment-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #ff69b4 0%, #ffb3c1 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .payment-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 105, 180, 0.4);
        }

        .back-btn {
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

        .back-btn:hover {
            border-color: #ff69b4;
            color: #ff69b4;
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
            .payment-content {
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
                    <li><a href="#about">About</a></li>
                    <li><a href="cart.php">Cart</a></li>
                    <?php if ($isLoggedIn): ?>
                        <li class="user-profile" id="userProfile">
                            <div class="user-initial" id="userInitial"><?php echo $userInitial; ?></div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Payment Content -->
    <section class="payment-container">
        <div class="payment-header">
            <h1>Complete Your Order</h1>
            <p>Secure payment powered by Razorpay</p>
        </div>

        <div class="payment-content">
            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-header">Order Summary</div>
                
                <div class="order-items">
                    <?php foreach($cart_items_data as $item): ?>
                    <div class="order-item">
                        <div class="item-info">
                            <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                            <div class="item-meta">
                                Qty: <?php echo $item['quantity']; ?> × ₹<?php echo number_format($item['price'], 2); ?>
                            </div>
                        </div>
                        <div class="item-total">
                            ₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>₹<?php echo number_format($cart_total, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Shipping:</span>
                    <span>₹<?php echo number_format($shipping_cost, 2); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Tax:</span>
                    <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                </div>
                
                <div class="summary-row total">
                    <span>Total Amount:</span>
                    <span>₹<?php echo number_format($grand_total, 2); ?></span>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="payment-details">
                <div class="summary-header">Payment Details</div>
                
                <div class="payment-method">
                    <div class="payment-icon">💳</div>
                    <h3>Razorpay Payment</h3>
                    <p>Secure payment gateway</p>
                </div>

                <button class="payment-btn" id="rzp-button">
                    Pay Now - ₹<?php echo number_format($grand_total, 2); ?>
                </button>
                
                <a href="cart.php" class="back-btn">
                    ← Back to Cart
                </a>
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
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                "key": "<?php echo $razorpay_key; ?>",
                "amount": "<?php echo $razorpay_amount; ?>",
                "currency": "INR",
                "name": "BAILEY Cosmetics",
                "description": "Beauty Products Order",
                "image": "",
                "handler": function (response) {
                    // Redirect to success page with payment details
                    window.location.href = 'order-success.php?payment_id=' + response.razorpay_payment_id + 
                                          '&order_id=' + response.razorpay_order_id + 
                                          '&amount=<?php echo $grand_total; ?>';
                },
                "prefill": {
                    "name": "<?php echo htmlspecialchars($user_name); ?>",
                    "email": "<?php echo isset($_SESSION['user_email']) ? htmlspecialchars($_SESSION['user_email']) : ''; ?>"
                },
                "theme": {
                    "color": "#ff69b4"
                }
            };
            
            var rzp = new Razorpay(options);
            
            document.getElementById('rzp-button').onclick = function(e) {
                rzp.open();
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>