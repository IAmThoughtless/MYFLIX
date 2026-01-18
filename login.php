<?php
require_once 'db_connect.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // 1. Fetch user data securely by email
    $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // 2. Verify hashed password
        if (password_verify($password, $user['password'])) {
            // Success: Set session variables
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Redirect to protected homepage
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
    
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | MYFLIX</title>
    <style>
        body { font-family: sans-serif; background-color: #141414; color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .login-box { background-color: rgba(0, 0, 0, 0.75); padding: 60px; border-radius: 5px; width: 300px; margin-bottom: 20px; }
        input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: none; background-color: #333; color: white; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #46d369; color: white; padding: 15px; border: none; border-radius: 4px; width: 100%; font-size: 16px; cursor: pointer; margin-top: 20px; font-weight: bold; }
        button:hover { background-color: #2e7d32; }
        .error { color: #e50914; margin-bottom: 10px; }
        a { color: #b3b3b3; text-decoration: none; font-size: 0.9em; }
        a:hover { text-decoration: underline; }
        .back-link { margin-top: 10px; color: #888; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2 style="margin-bottom: 20px;">Sign In</h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

        <form method="POST" action="login.php">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign In</button>
        </form>
        
        <p style="margin-top: 20px; color: #737373;">
            New to MYFLIX? <a href="register.php" style="color: white;">Sign up now.</a>
        </p>
    </div>
    
    <!-- NEW: Back to Landing Page Link -->
    <a href="landing.php" class="back-link">← Back to Home</a>
</body>
</html>