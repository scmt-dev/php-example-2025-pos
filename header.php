<?php 
session_start();
$userId = $_SESSION['user_id'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to POS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="nav">
        <h1>POS App</h1>
        <ul class="flex">
            <?php if($userId > 0): ?>
            <li><?php echo ($_SESSION['fullname'] ?? 'User'); ?></li>
            <li>
                <a class="logout" href="logout.php">Logout</a>
            </li>
            <?php else: ?>
            <li>
                 <a href="signin.php">Login</a>
            </li>
            <li>
                <a href="signup.php">Sign Up</a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>