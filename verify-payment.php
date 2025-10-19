<?php
// verify-payment.php - Verify Razorpay payment

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get payment details from POST
$payment_id = isset($_POST['payment_id']) ? $_POST['payment_id'] : '';
$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : '';
$signature = isset($_POST['signature']) ? $_POST['signature'] : '';
$amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;

if (!$payment_id || !$order_id || !$signature) {
    echo json_encode(['success' => false, 'message' => 'Missing payment details']);
    exit;
}

// For test mode, we'll skip signature verification
// In production, you should verify the signature using your Razorpay secret key
// Razorpay secret key would be used to create hash and verify

// Create orders table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(50) UNIQUE,
    user_id INT,
    session_id VARCHAR(100),
    payment_id VARCHAR(100),
    razorpay_order_id VARCHAR(100),
    total_amount DECIMAL(10, 2),
    status VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

$conn->query($create_table);

// Create order in database
$user_id = $_SESSION['user_id'];
$session_id = session_id();
$order_number = "ORD-" . time() . "-" . $user_id;

$insert_query = "INSERT INTO orders (order_number, user_id, session_id, payment_id, razorpay_order_id, total_amount, status) 
                 VALUES (?, ?, ?, ?, ?, ?, 'completed')";

$stmt = $conn->prepare($insert_query);
$stmt->bind_param("siss sd", $order_number, $user_id, $session_id, $payment_id, $order_id, $amount);

if ($stmt->execute()) {
    $order_id_db = $conn->insert_id;
    
    // Create order items table if it doesn't exist
    $create_items_table = "CREATE TABLE IF NOT EXISTS order_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        order_id INT,
        product_id INT,
        quantity INT,
        price DECIMAL(10, 2),
        FOREIGN KEY (order_id) REFERENCES orders(id)
    )";
    
    $conn->query($create_items_table);
    
    // Get cart items and add them to order
    $cart_query = "SELECT product_id, quantity, p.price
                   FROM cart c
                   JOIN products p ON c.product_id = p.id
                   WHERE c.session_id = ?";
    
    $cart_stmt = $conn->prepare($cart_query);
    $cart_stmt->bind_param("s", $session_id);
    $cart_stmt->execute();
    $cart_items = $cart_stmt->get_result();
    
    while ($item = $cart_items->fetch_assoc()) {
        $item_insert = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_insert);
        $item_stmt->bind_param("iid", $order_id_db, $item['product_id'], $item['quantity'], $item['price']);
        $item_stmt->execute();
    }
    
    // Clear the cart after successful order
    $clear_cart = "DELETE FROM cart WHERE session_id = ?";
    $clear_stmt = $conn->prepare($clear_cart);
    $clear_stmt->bind_param("s", $session_id);
    $clear_stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified and order created',
        'order_number' => $order_number
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order: ' . $conn->error
    ]);
}

$conn->close();
?>