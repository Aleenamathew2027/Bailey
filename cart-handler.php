<?php
// cart-handler.php - Handle add to cart requests

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db.php';

class CartHandler {
    private $conn;
    private $table = "cart";
    private $session_id;
    
    public function __construct($db) {
        $this->conn = $db;
        $this->session_id = session_id();
    }
    
    // Add or update cart item
    public function addToCart($product_id, $quantity = 1) {
        $product_id = intval($product_id);
        $quantity = intval($quantity);
        
        if ($quantity < 1) {
            return ['success' => false, 'message' => 'Invalid quantity'];
        }
        
        // Check if product exists
        $check_query = "SELECT id, name, price FROM products WHERE id = ?";
        $check_stmt = $this->conn->prepare($check_query);
        $check_stmt->bind_param("i", $product_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        $product = $result->fetch_assoc();
        
        // Check if item already in cart
        $check_cart = "SELECT id, quantity FROM " . $this->table . " WHERE session_id = ? AND product_id = ?";
        $cart_stmt = $this->conn->prepare($check_cart);
        $cart_stmt->bind_param("si", $this->session_id, $product_id);
        $cart_stmt->execute();
        $cart_result = $cart_stmt->get_result();
        
        if ($cart_result->num_rows > 0) {
            // Update existing cart item
            $cart_item = $cart_result->fetch_assoc();
            $new_quantity = $cart_item['quantity'] + $quantity;
            
            $update_query = "UPDATE " . $this->table . " SET quantity = ? WHERE id = ?";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bind_param("ii", $new_quantity, $cart_item['id']);
            $success = $update_stmt->execute();
            
            return [
                'success' => $success, 
                'message' => $product['name'] . ' quantity updated',
                'product_name' => $product['name']
            ];
        } else {
            // Insert new cart item
            $insert_query = "INSERT INTO " . $this->table . " (session_id, product_id, quantity) VALUES (?, ?, ?)";
            $insert_stmt = $this->conn->prepare($insert_query);
            $insert_stmt->bind_param("sii", $this->session_id, $product_id, $quantity);
            $success = $insert_stmt->execute();
            
            return [
                'success' => $success, 
                'message' => $product['name'] . ' added to cart',
                'product_name' => $product['name']
            ];
        }
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
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    header('Content-Type: application/json');
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Please login to add items to cart',
            'redirect' => 'signup.php?redirect=cart'
        ]);
        exit;
    }
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    $handler = new CartHandler($conn);
    $result = $handler->addToCart($product_id, $quantity);
    $result['cart_count'] = $handler->getCartCount();
    
    echo json_encode($result);
    exit;
}

$conn->close();
?>