<?php
// Include database connection
require_once 'db.php';

// Set page title
$pageTitle = 'Manage Products';

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to access this page';
    header('Location: signin.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle product actions (create, update, delete)
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        // Get form data
        $product_id = $_POST['product_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $barcode = trim($_POST['barcode'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $cost_price = floatval($_POST['cost_price'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 10);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validate form data
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Product name is required';
        }
        
        if (empty($price) || $price <= 0) {
            $errors[] = 'Valid price is required';
        }
        
        if (empty($category_id)) {
            $errors[] = 'Category selection is required';
        }
        
        // If no errors, proceed with database operation
        if (empty($errors)) {
            if ($action === 'create') {
                // Create new product
                $sql = "INSERT INTO products (name, sku, barcode, description, price, cost_price, 
                        category_id, stock_quantity, low_stock_threshold, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ssssddiiis", $name, $sku, $barcode, $description, $price, 
                                $cost_price, $category_id, $stock_quantity, $low_stock_threshold, $is_active);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Product created successfully';
                    header('Location: products.php');
                    exit;
                } else {
                    $errors[] = 'Error creating product: ' . $stmt->error;
                }
            } else {
                // Update existing product
                $sql = "UPDATE products SET name = ?, sku = ?, barcode = ?, description = ?, 
                        price = ?, cost_price = ?, category_id = ?, stock_quantity = ?, 
                        low_stock_threshold = ?, is_active = ? WHERE id = ?";
                        
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ssssddiisii", $name, $sku, $barcode, $description, $price, 
                                $cost_price, $category_id, $stock_quantity, $low_stock_threshold, $is_active, $product_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Product updated successfully';
                    header('Location: products.php');
                    exit;
                } else {
                    $errors[] = 'Error updating product: ' . $stmt->error;
                }
            }
        }
        
        // If there were errors, store them in session
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }
    } elseif ($action === 'delete') {
        // Delete product
        $product_id = $_POST['product_id'] ?? '';
        
        if (!empty($product_id)) {
            $sql = "DELETE FROM products WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $product_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Product deleted successfully';
            } else {
                $_SESSION['error_message'] = 'Error deleting product: ' . $stmt->error;
            }
        }
        
        header('Location: products.php');
        exit;
    }
}

// Get product data for editing if ID is provided
$edit_mode = false;
$product = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $product_id = $_GET['edit'];
    
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = 'Product not found';
        header('Location: products.php');
        exit;
    }
}

// Get categories for dropdown
$categories = [];
$sql = "SELECT id, name FROM categories ORDER BY name";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Get all products for display
$products = [];
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.name";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="admin-header">
    <div class="admin-title"><?php echo $edit_mode ? 'Edit Product' : 'Manage Products'; ?></div>
    <div class="admin-actions">
        <?php if ($edit_mode): ?>
            <a href="products.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="toggleForm()">Add New Product</button>
        <?php endif; ?>
    </div>
</div>

<!-- Product Form (hidden by default unless in edit mode) -->
<div id="productForm" style="display: <?php echo $edit_mode ? 'block' : 'none'; ?>; margin-bottom: 30px;">
    <form method="post" action="products.php">
        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="name">Product Name*</label>
            <input type="text" id="name" name="name" class="form-control" required 
                value="<?php echo $edit_mode ? htmlspecialchars($product['name']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="category_id">Category*</label>
            <select id="category_id" name="category_id" class="form-control" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?php echo $category['id']; ?>" 
                        <?php echo $edit_mode && $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($category['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="sku">SKU</label>
            <input type="text" id="sku" name="sku" class="form-control" 
                value="<?php echo $edit_mode ? htmlspecialchars($product['sku']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="barcode">Barcode</label>
            <input type="text" id="barcode" name="barcode" class="form-control" 
                value="<?php echo $edit_mode ? htmlspecialchars($product['barcode']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_mode ? htmlspecialchars($product['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="price">Price*</label>
            <input type="number" step="0.01" id="price" name="price" class="form-control" required 
                value="<?php echo $edit_mode ? htmlspecialchars($product['price']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="cost_price">Cost Price</label>
            <input type="number" step="0.01" id="cost_price" name="cost_price" class="form-control" 
                value="<?php echo $edit_mode ? htmlspecialchars($product['cost_price']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="stock_quantity">Stock Quantity*</label>
            <input type="number" id="stock_quantity" name="stock_quantity" class="form-control" required 
                value="<?php echo $edit_mode ? htmlspecialchars($product['stock_quantity']) : '0'; ?>">
        </div>
        
        <div class="form-group">
            <label for="low_stock_threshold">Low Stock Threshold</label>
            <input type="number" id="low_stock_threshold" name="low_stock_threshold" class="form-control" 
                value="<?php echo $edit_mode ? htmlspecialchars($product['low_stock_threshold']) : '10'; ?>">
        </div>
        
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" 
                    <?php echo (!$edit_mode || ($edit_mode && $product['is_active'])) ? 'checked' : ''; ?>>
                Active
            </label>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Product' : 'Create Product'; ?></button>
            <?php if (!$edit_mode): ?>
                <button type="button" class="btn btn-secondary" onclick="toggleForm()">Cancel</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Products Table -->
<?php if (!$edit_mode): ?>
<div class="table-container">
    <?php if (empty($products)): ?>
        <p>No products found. Add your first product using the button above.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['category_name'] ?? 'Uncategorized'); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <?php echo $item['stock_quantity']; ?>
                            <?php if ($item['stock_quantity'] <= $item['low_stock_threshold']): ?>
                                <span style="color: red; font-weight: bold;"> (Low)</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?></td>
                        <td>
                            <a href="products.php?edit=<?php echo $item['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">Edit</a>
                            <form method="post" action="products.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="product_id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn btn-danger" style="padding: 4px 8px; font-size: 12px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php endif; ?>

<script>
    function toggleForm() {
        var form = document.getElementById('productForm');
        if (form.style.display === 'none') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
</script>

<?php
// Include footer
include 'includes/footer.php';
?>