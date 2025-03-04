<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] == 'admin';

// Page title (can be set before including this file)
if (!isset($pageTitle)) {
    $pageTitle = 'POS System';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - POS System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Admin panel specific styles */
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .admin-title {
            font-size: 24px;
            font-weight: bold;
        }
        .admin-actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #4CAF50;
        }
        .btn-secondary {
            background-color: #2196F3;
        }
        .btn-danger {
            background-color: #f44336;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .alert-danger {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .admin-sidebar {
            width: 200px;
            float: left;
            margin-right: 20px;
        }
        .admin-content {
            margin-left: 220px;
        }
        .admin-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .admin-menu li {
            margin-bottom: 5px;
        }
        .admin-menu a {
            display: block;
            padding: 10px;
            background-color: #f5f5f5;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        .admin-menu a:hover, .admin-menu a.active {
            background-color: #ddd;
        }
        @media (max-width: 768px) {
            .admin-sidebar {
                float: none;
                width: 100%;
                margin-right: 0;
                margin-bottom: 20px;
            }
            .admin-content {
                margin-left: 0;
            }
            .admin-menu li {
                display: inline-block;
                margin-right: 5px;
            }
        }
    </style>
</head>
<body>
    <nav class="nav">
        <h1>POS System</h1>
        <ul class="flex">
            <?php if ($isLoggedIn): ?>
                <li><?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></li>
                <li><a class="logout" href="logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="signin.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="admin-container">
        <?php if ($isLoggedIn): ?>
        <!-- Admin navigation -->
        <div class="admin-sidebar">
            <ul class="admin-menu">
                <li><a href="index-widget.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="categories.php">Categories</a></li>
                <li><a href="customers.php">Customers</a></li>
                <li><a href="orders.php">Orders</a></li>
                <?php if ($isAdmin): ?>
                <li><a href="users.php">Users</a></li>
                <li><a href="reports.php">Reports</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Display flash messages if any -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Main content div -->
        <div class="admin-content">