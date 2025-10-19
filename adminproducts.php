<?php
// Include database connection
require_once 'db.php';

// Product Management Class
class ProductAdmin {
    private $conn;
    private $table = "products";
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Get all products
    public function getAllProducts() {
        $query = "SELECT p.*, 
                  (SELECT pi.image_path FROM product_images pi 
                   WHERE pi.product_id = p.id AND pi.is_primary = 1 
                   LIMIT 1) as primary_image
                  FROM " . $this->table . " p 
                  ORDER BY p.created_at DESC";
        
        $result = $this->conn->query($query);
        return $result;
    }
    
    // Delete product
    public function deleteProduct($id) {
        $query = "DELETE FROM " . $this->table . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    // Get single product
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
    
    // Update product
    public function updateProduct($id, $name, $description, $price, $brand, $category) {
        $query = "UPDATE " . $this->table . " 
                  SET name = ?, description = ?, price = ?, brand = ?, category = ? 
                  WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssdssi", $name, $description, $price, $brand, $category, $id);
        return $stmt->execute();
    }
}

$admin = new ProductAdmin($conn);

// Handle delete request
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($admin->deleteProduct($id)) {
        $delete_message = "Product deleted successfully!";
    } else {
        $delete_message = "Error deleting product.";
    }
}

// Handle update request
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['product_id']);
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);
    $price = floatval($_POST['price']);
    $brand = htmlspecialchars($_POST['brand']);
    $category = htmlspecialchars($_POST['category']);
    
    if ($admin->updateProduct($id, $name, $description, $price, $brand, $category)) {
        $update_message = "Product updated successfully!";
    } else {
        $update_message = "Error updating product.";
    }
}

// Get all products
$products = $admin->getAllProducts();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Products - BAILEY</title>
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
            padding: 20px 40px;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
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

        .admin-badge {
            background: #ffb6d9;
            color: #fff;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Main Content */
        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px;
        }

        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .admin-header h1 {
            font-size: 36px;
            font-weight: 300;
            color: #2a2a2a;
        }

        .add-product-btn {
            padding: 12px 30px;
            background: linear-gradient(135deg, #ffb6d9 0%, #ffd4e8 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .add-product-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(255, 182, 217, 0.4);
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Products Table */
        .products-table {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f8f8;
            border-bottom: 2px solid #e5e5e5;
        }

        th {
            padding: 20px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2a2a2a;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #666;
        }

        tbody tr:hover {
            background: #fafafa;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            background: #f8f8f8;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-name {
            font-weight: 600;
            color: #2a2a2a;
        }

        .product-category {
            display: inline-block;
            background: #ffb6d9;
            color: #fff;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .product-price {
            font-weight: 600;
            color: #2a2a2a;
            font-size: 16px;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .view-btn {
            background: #007bff;
            color: #fff;
        }

        .view-btn:hover {
            background: #0056b3;
        }

        .edit-btn {
            background: #28a745;
            color: #fff;
        }

        .edit-btn:hover {
            background: #1f7e34;
        }

        .delete-btn {
            background: #dc3545;
            color: #fff;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            animation: fadeIn 0.3s ease;
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: slideDown 0.3s ease;
        }

        .modal-header {
            font-size: 28px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #2a2a2a;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #2a2a2a;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #ffb6d9;
            box-shadow: 0 0 0 3px rgba(255, 182, 217, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.3s;
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
            background: #e5e5e5;
            color: #2a2a2a;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        /* View Modal */
        .view-details {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .view-image {
            width: 150px;
            height: 150px;
            border-radius: 8px;
            overflow: hidden;
            background: #f8f8f8;
            flex-shrink: 0;
        }

        .view-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .view-info h3 {
            font-size: 18px;
            font-weight: 600;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .view-info p {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .view-info strong {
            color: #2a2a2a;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
        }

        .empty-state-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h2 {
            font-size: 28px;
            font-weight: 300;
            color: #2a2a2a;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 16px;
            color: #666;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 20px;
                align-items: flex-start;
            }

            th, td {
                padding: 15px 10px;
                font-size: 12px;
            }

            .action-btn {
                padding: 6px 10px;
                font-size: 11px;
            }

            .modal-content {
                padding: 30px 20px;
            }

            .view-details {
                flex-direction: column;
                gap: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="home.php" class="logo">bailey</a>
            <span class="admin-badge">Admin Panel</span>
        </div>
    </header>

    <!-- Main Content -->
    <section class="admin-container">
        <div class="admin-header">
            <h1>Manage Products</h1>
            <a href="addproducts.php" class="add-product-btn">+ Add New Product</a>
        </div>

        <!-- Messages -->
        <?php if (isset($delete_message)): ?>
        <div class="message success">
            <?php echo $delete_message; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($update_message)): ?>
        <div class="message success">
            <?php echo $update_message; ?>
        </div>
        <?php endif; ?>

        <!-- Products Table -->
        <?php if ($products->num_rows > 0): ?>
        <div class="products-table">
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $products->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div class="product-image">
                                    <?php if ($row['primary_image']): ?>
                                        <img src="<?php echo htmlspecialchars($row['primary_image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    <?php else: ?>
                                        <img src="https://via.placeholder.com/60x60/ffeef5/999999?text=No+Image" alt="No Image">
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><span class="product-name"><?php echo htmlspecialchars($row['name']); ?></span></td>
                            <td><?php echo htmlspecialchars($row['brand']); ?></td>
                            <td>
                                <span class="product-category"><?php echo ucfirst($row['category']); ?></span>
                            </td>
                            <td><span class="product-price">₹<?php echo number_format($row['price'], 2); ?></span></td>
                            <td>
                                <div class="actions">
                                    <button class="action-btn view-btn" onclick="openViewModal(<?php echo $row['id']; ?>)">View</button>
                                    <button class="action-btn edit-btn" onclick="openEditModal(<?php echo $row['id']; ?>)">Edit</button>
                                    <a href="?delete=true&id=<?php echo $row['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <h2>No Products Found</h2>
            <p>Start by adding your first product</p>
        </div>
        <?php endif; ?>
    </section>

    <!-- View Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Product Details</span>
                <button class="close-btn" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div id="viewContent"></div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span>Edit Product</span>
                <button class="close-btn" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form id="editForm" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="product_id" id="editProductId">
                
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" id="editName" name="name" required>
                </div>

                <div class="form-group">
                    <label>Brand</label>
                    <input type="text" id="editBrand" name="brand" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select id="editCategory" name="category" required>
                        <option value="fragrances">Fragrances</option>
                        <option value="beauty">Beauty</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Price (₹)</label>
                    <input type="number" id="editPrice" name="price" step="0.01" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="editDescription" name="description" required></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openViewModal(productId) {
            const modal = document.getElementById('viewModal');
            const content = document.getElementById('viewContent');
            
            // Get product data from table
            const row = event.target.closest('tr');
            const image = row.querySelector('.product-image img').src;
            const name = row.querySelector('.product-name').textContent;
            const brand = row.cells[2].textContent;
            const category = row.querySelector('.product-category').textContent;
            const price = row.querySelector('.product-price').textContent;
            
            content.innerHTML = `
                <div class="view-details">
                    <div class="view-image">
                        <img src="${image}" alt="Product">
                    </div>
                    <div class="view-info">
                        <h3>${name}</h3>
                        <p><strong>Brand:</strong> ${brand}</p>
                        <p><strong>Category:</strong> ${category}</p>
                        <p><strong>Price:</strong> ${price}</p>
                    </div>
                </div>
            `;
            
            modal.classList.add('show');
        }

        function openEditModal(productId) {
            const modal = document.getElementById('editModal');
            const row = event.target.closest('tr');
            
            document.getElementById('editProductId').value = productId;
            document.getElementById('editName').value = row.querySelector('.product-name').textContent;
            document.getElementById('editBrand').value = row.cells[2].textContent;
            document.getElementById('editCategory').value = row.querySelector('.product-category').textContent.toLowerCase();
            document.getElementById('editPrice').value = parseFloat(row.querySelector('.product-price').textContent.replace('₹', ''));
            
            modal.classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            
            if (event.target === viewModal) {
                viewModal.classList.remove('show');
            }
            if (event.target === editModal) {
                editModal.classList.remove('show');
            }
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>