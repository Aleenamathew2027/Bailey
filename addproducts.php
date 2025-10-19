<?php
// Include database connection
require_once 'db.php';

// Product Class
class Product {
    private $conn;
    private $table = "products";
    
    public $id;
    public $name;
    public $description;
    public $price;
    public $brand;
    public $category;
    public $created_at;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET name=?, description=?, price=?, brand=?, category=?, created_at=NOW()";
        $stmt = $this->conn->prepare($query);
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->price = htmlspecialchars(strip_tags($this->price));
        $this->brand = htmlspecialchars(strip_tags($this->brand));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $stmt->bind_param("ssdss", $this->name, $this->description, $this->price, $this->brand, $this->category);
        if ($stmt->execute()) {
            $this->id = $this->conn->insert_id;
            return true;
        }
        return false;
    }
}

// ProductImage Class
class ProductImage {
    private $conn;
    private $table = "product_images";
    private $uploadDir = "uploads/products/";
    
    public $id;
    public $product_id;
    public $image_path;
    public $is_primary;
    
    public function __construct($db) {
        $this->conn = $db;
        if (!file_exists($this->uploadDir)) mkdir($this->uploadDir, 0777, true);
    }
    
    public function uploadImages($files, $product_id) {
        $uploadedImages = [];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; 
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileType = $files['type'][$i];
                $fileSize = $files['size'][$i];
                if (!in_array($fileType, $allowedTypes)) continue;
                if ($fileSize > $maxSize) continue;
                $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '_' . $i . '.' . $extension;
                $filepath = $this->uploadDir . $filename;
                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $is_primary = ($i === 0) ? 1 : 0;
                    $this->saveImage($product_id, $filepath, $is_primary);
                    $uploadedImages[] = $filepath;
                }
            }
        }
        return $uploadedImages;
    }
    
    private function saveImage($product_id, $image_path, $is_primary) {
        $query = "INSERT INTO " . $this->table . " 
                  SET product_id=?, image_path=?, is_primary=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $product_id, $image_path, $is_primary);
        return $stmt->execute();
    }
}

// Initialize
$product = new Product($conn);
$productImage = new ProductImage($conn);

$message = "";
$messageType = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product->name = $_POST['product_name'];
    $product->description = $_POST['description'];
    $product->price = $_POST['price'];
    $product->brand = $_POST['brand'];
    $product->category = $_POST['category'];
    
    if ($product->create()) {
        if (!empty($_FILES['product_images']['name'][0])) {
            $uploadedImages = $productImage->uploadImages($_FILES['product_images'], $product->id);
            if (count($uploadedImages) > 0) {
                $message = "Product added successfully with " . count($uploadedImages) . " image(s)!";
                $messageType = "success";
            } else {
                $message = "Product added but images upload failed!";
                $messageType = "warning";
            }
        } else {
            $message = "Product added successfully without images!";
            $messageType = "success";
        }
    } else {
        $message = "Failed to add product!";
        $messageType = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product - BAILEY Admin</title>
<style>
* {margin:0; padding:0; box-sizing:border-box;}
body {font-family: 'Poppins',sans-serif; background: linear-gradient(135deg,#fff5f9 0%,#ffe4f0 50%,#ffffff 100%); min-height:100vh; padding:40px 20px;}
.container {max-width:900px; margin:0 auto; background:#fff; border-radius:16px; box-shadow:0 10px 40px rgba(0,0,0,0.1); overflow:hidden;}
.header {background: linear-gradient(135deg, #2a2a2a 0%, #4a4a4a 100%); color:#fff; padding:40px; text-align:center;}
.header h1 {font-size:32px; font-weight:300; letter-spacing:3px; margin-bottom:10px;}
.header p {font-size:14px; opacity:0.9; letter-spacing:1px;}
.form-container {padding:50px 40px;}
.alert {padding:15px 20px; border-radius:8px; margin-bottom:30px; font-size:14px; display:none;}
.alert.success {background:#d4edda; color:#155724; border:1px solid #c3e6cb;}
.alert.error {background:#f8d7da; color:#721c24; border:1px solid #f5c6cb;}
.alert.warning {background:#fff3cd; color:#856404; border:1px solid #ffeaa7;}
.alert.show {display:block;}
.form-group {margin-bottom:30px;}
.form-group label {display:block; font-size:13px; font-weight:600; color:#2a2a2a; text-transform:uppercase; letter-spacing:1px; margin-bottom:10px;}
.form-group input, .form-group textarea, .form-group select {width:100%; padding:15px; border:2px solid #e5e5e5; border-radius:8px; font-size:15px; transition:all 0.3s; font-family:inherit;}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus {outline:none; border-color:#2a2a2a;}
.form-group textarea {resize:vertical; min-height:120px;}
.form-row {display:grid; grid-template-columns:1fr 1fr; gap:20px;}
.image-upload-area {border:3px dashed #d0d0d0; border-radius:12px; padding:40px; text-align:center; cursor:pointer; background:#fafafa; transition:all 0.3s;}
.image-upload-area:hover {border-color:#2a2a2a; background:#f5f5f5;}
.image-upload-area.dragover {border-color:#2a2a2a; background:#f0f0f0;}
#product_images {display:none;}
.image-preview {display:grid; grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); gap:15px; margin-top:20px;}
.preview-item {position:relative; border-radius:8px; overflow:hidden; border:2px solid #e5e5e5;}
.preview-item img {width:100%; height:150px; object-fit:cover;}
.preview-item .remove-btn {position:absolute; top:8px; right:8px; background:rgba(255,0,0,0.8); color:#fff; border:none; border-radius:50%; width:28px; height:28px; cursor:pointer; font-size:18px; display:flex; align-items:center; justify-content:center; transition:all 0.3s;}
.preview-item .remove-btn:hover {background:rgba(255,0,0,1);}
.primary-badge {position:absolute; bottom:8px; left:8px; background:#2a2a2a; color:#fff; padding:4px 10px; border-radius:4px; font-size:11px; font-weight:600; letter-spacing:0.5px;}
.submit-btn {width:100%; padding:18px; background:#2a2a2a; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:600; text-transform:uppercase; letter-spacing:2px; cursor:pointer; transition:all 0.3s;}
.submit-btn:hover {background:#000; transform:translateY(-2px); box-shadow:0 10px 30px rgba(0,0,0,0.2);}
.back-link {display:inline-block; margin-bottom:20px; color:#2a2a2a; text-decoration:none; font-size:14px; transition:all 0.3s;}
.back-link:hover {color:#000;}
.back-link.all-products {background:#d63384; color:#fff; padding:10px 20px; border-radius:8px; margin-right:10px;}
.back-link.all-products:hover {background:#b12a6c;}
@media (max-width:768px){.form-container{padding:30px 20px;}.form-row{grid-template-columns:1fr;}.header h1{font-size:24px;}}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>BAILEY</h1>
        <p>Add New Product</p>
    </div>

    <div class="form-container">
        <!-- All Products Button -->
        <a href="adminproducts.php" class="back-link all-products">All Products</a>
        <!-- Back to Home -->
        <a href="home.php" class="back-link">← Back to Home</a>

        <?php if ($message): ?>
        <div class="alert <?php echo $messageType; ?> show">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <form id="productForm" method="POST" enctype="multipart/form-data">
            <!-- Product Images -->
            <div class="form-group">
                <label>Product Images (2-4 images) *</label>
                <div class="image-upload-area" id="uploadArea">
                    <div class="upload-icon">📸</div>
                    <div class="upload-text">Click to upload or drag and drop</div>
                    <div class="upload-hint">PNG, JPG, WEBP (Max 5MB each)</div>
                </div>
                <input type="file" id="product_images" name="product_images[]" multiple accept="image/*" required>
                <div class="image-preview" id="imagePreview"></div>
            </div>

            <!-- Product Name -->
            <div class="form-group">
                <label for="product_name">Product Name *</label>
                <input type="text" id="product_name" name="product_name" placeholder="e.g., Rose Éternelle" required>
            </div>

            <!-- Description -->
            <div class="form-group">
                <label for="description">Product Description *</label>
                <textarea id="description" name="description" placeholder="Describe your product in detail..." required></textarea>
            </div>

            <!-- Price and Brand -->
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Price ($) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" placeholder="0.00" required>
                </div>

                <div class="form-group">
                    <label for="brand">Brand *</label>
                    <input type="text" id="brand" name="brand" placeholder="e.g., BAILEY" required>
                </div>
            </div>

            <!-- Category -->
            <div class="form-group">
                <label for="category">Category *</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="fragrances">Fragrances</option>
                    <option value="beauty">Beauty</option>
                </select>
            </div>

            <button type="submit" class="submit-btn">Add Product</button>
        </form>
    </div>
</div>

<script>
const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('product_images');
const imagePreview = document.getElementById('imagePreview');
let selectedFiles = [];

uploadArea.addEventListener('click', ()=> fileInput.click());
uploadArea.addEventListener('dragover', e=>{e.preventDefault(); uploadArea.classList.add('dragover');});
uploadArea.addEventListener('dragleave', ()=> uploadArea.classList.remove('dragover'));
uploadArea.addEventListener('drop', e=>{e.preventDefault(); uploadArea.classList.remove('dragover'); handleFiles(e.dataTransfer.files);});
fileInput.addEventListener('change', e=> handleFiles(e.target.files));

function handleFiles(files){
    const filesArray = Array.from(files);
    if(selectedFiles.length + filesArray.length > 4){alert('Maximum 4 images allowed!'); return;}
    filesArray.forEach(file=>{if(file.type.startsWith('image/')) selectedFiles.push(file);});
    updateFileInput(); displayPreviews();
}

function updateFileInput(){
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file=> dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

function displayPreviews(){
    imagePreview.innerHTML='';
    selectedFiles.forEach((file,index)=>{
        const reader = new FileReader();
        reader.onload=(e)=>{
            const div=document.createElement('div');
            div.className='preview-item';
            div.innerHTML=`<img src="${e.target.result}" alt="Preview ${index+1}">
                <button type="button" class="remove-btn" onclick="removeImage(${index})">×</button>
                ${index===0?'<span class="primary-badge">Primary</span>':''}`;
            imagePreview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(index){
    selectedFiles.splice(index,1);
    updateFileInput();
    displayPreviews();
}

document.getElementById('productForm').addEventListener('submit',function(e){
    if(selectedFiles.length<2 || selectedFiles.length>4){e.preventDefault(); alert('Please upload 2 to 4 product images!'); return false;}
});
</script>
</body>
</html>

<?php $conn->close(); ?>
