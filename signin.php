<?php 
    session_start();
    if(isset($_POST['submit'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        include_once('db.php');
        // sql
        $QUERY = "SELECT * FROM users WHERE email = ?";
        $STMT = $db->prepare($QUERY);
        $STMT->bind_param('s', $email);
        $STMT->execute();
        $result = $STMT->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $isPass = password_verify($password, $row['password']);
            if($isPass) {
                // keep user logged in session
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['fullname'] = $row['full_name'];
                header('Location: index.php');
                // echo "Sign in successful";
            }else {
                echo "Password is incorrect";
            }
        }else {
            echo "Email does not exist";
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In Account</title>
</head>
<body>
    <h1>Sign In</h1>
    <form action="" method="post">
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <input type="submit" name="submit" value="Sign In">
        <p>
            I don't have an account 
            <a href="signup.php">Sign Up</a>
        </p>
    </form>
</body>
</html>