<?php
// Include database connection
require_once 'db.php';

// Set page title
$pageTitle = 'Manage Categories';

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
    // Handle category actions (create, update, delete)
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        // Get form data
        $category_id = $_POST['category_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        // Validate form data
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Category name is required';
        }
        
        // Check if category name already exists
        $check_sql = "SELECT id FROM categories WHERE name = ? AND id != ?";
        $check_stmt = $db->prepare($check_sql);
        $check_stmt->bind_param("si", $name, $category_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $errors[] = 'A category with this name already exists';
        }
        
        // If no errors, proceed with database operation
        if (empty($errors)) {
            if ($action === 'create') {
                // Create new category
                $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ss", $name, $description);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Category created successfully';
                    header('Location: categories.php');
                    exit;
                } else {
                    $errors[] = 'Error creating category: ' . $stmt->error;
                }
            } else {
                // Update existing category
                $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("ssi", $name, $description, $category_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Category updated successfully';
                    header('Location: categories.php');
                    exit;
                } else {
                    $errors[] = 'Error updating category: ' . $stmt->error;
                }
            }
        }
        
        // If there were errors, store them in session
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }
    } elseif ($action === 'delete') {
        // Delete category
        $category_id = $_POST['category_id'] ?? '';
        
        if (!empty($category_id)) {
            // First check if there are products using this category
            $check_sql = "SELECT COUNT(*) as product_count FROM products WHERE category_id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("i", $category_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $row = $check_result->fetch_assoc();
            
            if ($row['product_count'] > 0) {
                $_SESSION['error_message'] = 'Cannot delete category: ' . $row['product_count'] . ' product(s) are using this category';
            } else {
                $sql = "DELETE FROM categories WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("i", $category_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Category deleted successfully';
                } else {
                    $_SESSION['error_message'] = 'Error deleting category: ' . $stmt->error;
                }
            }
        }
        
        header('Location: categories.php');
        exit;
    }
}

// Get category data for editing if ID is provided
$edit_mode = false;
$category = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $category_id = $_GET['edit'];
    
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $category = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = 'Category not found';
        header('Location: categories.php');
        exit;
    }
}

// Get all categories for display with product count
$categories = [];
$sql = "SELECT c.*, COUNT(p.id) as product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id 
        ORDER BY c.name";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="admin-header">
    <div class="admin-title"><?php echo $edit_mode ? 'Edit Category' : 'Manage Categories'; ?></div>
    <div class="admin-actions">
        <?php if ($edit_mode): ?>
            <a href="categories.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="toggleForm()">Add New Category</button>
        <?php endif; ?>
    </div>
</div>

<!-- Category Form (hidden by default unless in edit mode) -->
<div id="categoryForm" style="display: <?php echo $edit_mode ? 'block' : 'none'; ?>; margin-bottom: 30px;">
    <form method="post" action="categories.php">
        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="name">Category Name*</label>
            <input type="text" id="name" name="name" class="form-control" required 
                value="<?php echo $edit_mode ? htmlspecialchars($category['name']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_mode ? htmlspecialchars($category['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Category' : 'Create Category'; ?></button>
            <?php if (!$edit_mode): ?>
                <button type="button" class="btn btn-secondary" onclick="toggleForm()">Cancel</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Categories Table -->
<?php if (!$edit_mode): ?>
<div class="table-container">
    <?php if (empty($categories)): ?>
        <p>No categories found. Add your first category using the button above.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['description'] ?? 'No description'); ?></td>
                        <td><?php echo $item['product_count']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                        <td>
                            <a href="categories.php?edit=<?php echo $item['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">Edit</a>
                            <form method="post" action="categories.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?php echo $item['id']; ?>">
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
        var form = document.getElementById('categoryForm');
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