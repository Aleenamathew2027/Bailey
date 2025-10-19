<?php
// order-success.php - Display order success

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: signup.php?redirect=cart');
    exit;
}

$order_number = isset($_GET['order_id']) ? $_GET['order_id'] : '';

if ($order_number) {
    $query = "SELECT * FROM orders WHERE order_number = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $user_id = $_SESSION['user_id'];
    $stmt->bind_param("si", $order_number, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
} else {
    $order = null;
}

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] : '';
$userInitial = $isLoggedIn ? strtoupper(substr($userName, 0, 1)) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - BAILEY</title>
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

        .success-container {
            max-width: 600px;
            margin: 60px auto;
            padding: 60px 40px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            text-align: center;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: bounce 1s;
        }

        @keyframes bounce {
            0%, 100% {
                transform: scale(0.5);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        h1 {
            font-size: 36px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 15px;
        }

        .order-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .order-details {
            background: #f8f8f8;
            padding: 25px;
            border-radius: 8px;
            margin: 30px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e5e5e5;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .detail-label {
            color: #666;
            font-size: 14px;
        }

        .detail-value {
            font-weight: 600;
            color: #2a2a2a;
            font-size: 14px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 40px;
            justify-content: center;
        }

        .btn {
            padding: 14px 35px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 182, 217, 0.4);
        }

        .btn-secondary {
            background: #fff;
            color: #2a2a2a;
            border: 2px solid #e5e5e5;
        }

        .btn-secondary:hover {
            border-color: #ffb6d9;
            color: #ffb6d9;
        }

        .info-box {
            background: #f0f8ff;
            border-left: 4px solid #ffb6d9;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            font-size: 14px;
            color: #555;
            text-align: left;
        }

        footer {
            background: #fff;
            border-top: 1px solid #e5e5e5;
            padding: 40px;
            text-align: center;
            color: #999;
            font-size: 12px;
            margin-top: 60px;
        }

        @media (max-width: 768px) {
            .success-container {
                margin: 20px;
                padding: 40px 20px;
            }

            .button-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="home.php" class="logo">bailey</a>
            <nav>
                <ul>
                    <li><a href="shop.php">Shop</a></li>
                    <li><a href="cart.php">Cart</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="success-container">
        <div class="success-icon">✓</div>
        
        <?php if ($order): ?>
            <h1>Order Confirmed!</h1>
            <p class="order-message">Thank you for your purchase. Your order has been successfully placed and is being processed.</p>
            
            <div class="order-details">
                <div class="detail-row">
                    <span class="detail-label">Order Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($order['order_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Order Date:</span>
                    <span class="detail-value"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value">₹<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value" style="color: #22c55e; text-transform: capitalize;">
                        <?php echo htmlspecialchars($order['status']); ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment ID:</span>
                    <span class="detail-value" style="font-size: 12px;">
                        <?php echo htmlspecialchars(substr($order['payment_id'], 0, 20) . '...'); ?>
                    </span>
                </div>
            </div>

            <div class="info-box">
                <strong>📧 Confirmation Email:</strong><br>
                A confirmation email has been sent to your registered email address with order details and tracking information.
            </div>

            <div class="button-group">
                <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                <a href="orders.php" class="btn btn-secondary">View All Orders</a>
            </div>
        <?php else: ?>
            <h1>Order Error</h1>
            <p class="order-message">We couldn't find your order. Please check your order number or contact support.</p>
            
            <div class="button-group">
                <a href="cart.php" class="btn btn-primary">Go to Cart</a>
                <a href="shop.php" class="btn btn-secondary">Continue Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 BAILEY. All rights reserved. | Thank you for shopping with us!</p>
    </footer>
</body>
</html>

<?php
$conn->close();
?>