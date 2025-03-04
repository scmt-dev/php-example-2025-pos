<?php
// Include database connection
require_once 'db.php';

// Set page title
$pageTitle = 'Manage Customers';

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
    // Handle customer actions (create, update, delete)
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create' || $action === 'update') {
        // Get form data
        $customer_id = $_POST['customer_id'] ?? '';
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        
        // Validate form data
        $errors = [];
        
        if (empty($first_name)) {
            $errors[] = 'First name is required';
        }
        
        if (empty($last_name)) {
            $errors[] = 'Last name is required';
        }
        
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        // Check if email already exists (only if provided)
        if (!empty($email)) {
            $check_sql = "SELECT id FROM customers WHERE email = ? AND id != ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("si", $email, $customer_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                $errors[] = 'A customer with this email already exists';
            }
        }
        
        // If no errors, proceed with database operation
        if (empty($errors)) {
            if ($action === 'create') {
                // Create new customer
                $sql = "INSERT INTO customers (first_name, last_name, email, phone, address) VALUES (?, ?, ?, ?, ?)";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $address);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Customer created successfully';
                    header('Location: customers.php');
                    exit;
                } else {
                    $errors[] = 'Error creating customer: ' . $stmt->error;
                }
            } else {
                // Update existing customer
                $sql = "UPDATE customers SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
                $stmt = $db->prepare($sql);
                $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $customer_id);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Customer updated successfully';
                    header('Location: customers.php');
                    exit;
                } else {
                    $errors[] = 'Error updating customer: ' . $stmt->error;
                }
            }
        }
        
        // If there were errors, store them in session
        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }
    } elseif ($action === 'delete') {
        // Delete customer
        $customer_id = $_POST['customer_id'] ?? '';
        
        if (!empty($customer_id)) {
            // First check if there are orders for this customer
            $check_sql = "SELECT COUNT(*) as order_count FROM orders WHERE customer_id = ?";
            $check_stmt = $db->prepare($check_sql);
            $check_stmt->bind_param("i", $customer_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $row = $check_result->fetch_assoc();
            
            if ($row['order_count'] > 0) {
                // We'll set the customer_id to NULL in orders rather than preventing deletion
                $update_sql = "UPDATE orders SET customer_id = NULL WHERE customer_id = ?";
                $update_stmt = $db->prepare($update_sql);
                $update_stmt->bind_param("i", $customer_id);
                $update_stmt->execute();
            }
            
            // Now delete the customer
            $sql = "DELETE FROM customers WHERE id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param("i", $customer_id);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Customer deleted successfully';
            } else {
                $_SESSION['error_message'] = 'Error deleting customer: ' . $stmt->error;
            }
        }
        
        header('Location: customers.php');
        exit;
    }
}

// Get customer data for editing if ID is provided
$edit_mode = false;
$customer = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $customer_id = $_GET['edit'];
    
    $sql = "SELECT * FROM customers WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
    } else {
        $_SESSION['error_message'] = 'Customer not found';
        header('Location: customers.php');
        exit;
    }
}

// Get all customers for display with order count
$customers = [];
$sql = "SELECT c.*, COUNT(o.id) as order_count, SUM(CASE WHEN o.status = 'completed' THEN o.total_amount ELSE 0 END) as total_spent 
        FROM customers c 
        LEFT JOIN orders o ON c.id = o.customer_id 
        GROUP BY c.id 
        ORDER BY c.last_name, c.first_name";
$result = $db->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $customers[] = $row;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="admin-header">
    <div class="admin-title"><?php echo $edit_mode ? 'Edit Customer' : 'Manage Customers'; ?></div>
    <div class="admin-actions">
        <?php if ($edit_mode): ?>
            <a href="customers.php" class="btn btn-secondary">Cancel</a>
        <?php else: ?>
            <button type="button" class="btn btn-primary" onclick="toggleForm()">Add New Customer</button>
        <?php endif; ?>
    </div>
</div>

<!-- Customer Form (hidden by default unless in edit mode) -->
<div id="customerForm" style="display: <?php echo $edit_mode ? 'block' : 'none'; ?>; margin-bottom: 30px;">
    <form method="post" action="customers.php">
        <input type="hidden" name="action" value="<?php echo $edit_mode ? 'update' : 'create'; ?>">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="customer_id" value="<?php echo $customer['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="first_name">First Name*</label>
            <input type="text" id="first_name" name="first_name" class="form-control" required 
                value="<?php echo $edit_mode ? htmlspecialchars($customer['first_name']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="last_name">Last Name*</label>
            <input type="text" id="last_name" name="last_name" class="form-control" required 
                value="<?php echo $edit_mode ? htmlspecialchars($customer['last_name']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control" 
                value="<?php echo $edit_mode ? htmlspecialchars($customer['email']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" 
                value="<?php echo $edit_mode ? htmlspecialchars($customer['phone']) : ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" class="form-control" rows="3"><?php echo $edit_mode ? htmlspecialchars($customer['address']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update Customer' : 'Create Customer'; ?></button>
            <?php if (!$edit_mode): ?>
                <button type="button" class="btn btn-secondary" onclick="toggleForm()">Cancel</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- Customers Table -->
<?php if (!$edit_mode): ?>
<div class="table-container">
    <?php if (empty($customers)): ?>
        <p>No customers found. Add your first customer using the button above.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Orders</th>
                    <th>Total Spent</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($customers as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo $item['order_count']; ?></td>
                        <td>$<?php echo number_format($item['total_spent'] ?? 0, 2); ?></td>
                        <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                        <td>
                            <a href="customers.php?edit=<?php echo $item['id']; ?>" class="btn btn-secondary" style="padding: 4px 8px; font-size: 12px;">Edit</a>
                            <form method="post" action="customers.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="customer_id" value="<?php echo $item['id']; ?>">
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
        var form = document.getElementById('customerForm');
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