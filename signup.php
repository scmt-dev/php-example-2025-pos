<?php 
    
    $message = '';
    if(isset($_POST['submit'])) {
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        include_once('db.php');
        // check email exists
        $QUERY = "SELECT * FROM users WHERE email = ?";
        $STMT = $db->prepare($QUERY);
        $STMT->bind_param('s', $email);
        $STMT->execute();
        $result = $STMT->get_result();
        if($result->num_rows > 0) {
            $message = "Email already exists";
        }else {

            $QUERY = "INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)";
            $STMT = $db->prepare($QUERY);

            $passwordHas = password_hash($password, PASSWORD_DEFAULT); // hash
            $STMT->bind_param('sss', $fullname, $email, $passwordHas);
            $STMT->execute();
            $message = "Sign up successful";
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

        <label for="fullname">Name:</label>
        <input type="text" name="fullname" id="fullname" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <div>
            <?php echo $message; ?>
        </div>
        <input type="submit" name="submit" value="Sign In">
    </form>
</body>
</html>