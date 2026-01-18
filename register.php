<?php
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and retrieve input data
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // --- NEW: Check if email already exists ---
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $error = "This email is already registered. Please sign in.";
            $check_stmt->close();
        } else {
            $check_stmt->close();
            
            // 2. Hash the password for secure storage (CRITICAL)
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // 3. Prepare SQL statement (Security: Prevents SQL Injection)
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            // 4. Execute the statement
            if ($stmt->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                // Fallback for other DB errors (like username duplicate if unique)
                if ($conn->errno == 1062) {
                    $error = "This username is already taken.";
                } else {
                    $error = "Error registering user: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Netflix Clone</title>
    <style>
        body { font-family: sans-serif; background-color: #141414; color: white; display: flex; flex-direction: column; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .register-box { background-color: rgba(0, 0, 0, 0.75); padding: 60px; border-radius: 5px; width: 300px; margin-bottom: 20px; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: none; background-color: #333; color: white; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #46d369; color: white; padding: 15px; border: none; border-radius: 4px; width: 100%; font-size: 16px; cursor: pointer; margin-top: 20px; font-weight: bold; }
        button:hover { background-color: #2e7d32; }
        .error { color: #e50914; margin-bottom: 10px; }
        .success { color: #4CAF50; margin-bottom: 10px; }
        a { color: #b3b3b3; text-decoration: none; font-size: 0.9em; }
        a:hover { text-decoration: underline; }
        .back-link { margin-top: 10px; color: #888; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="register-box">
        <h2 style="margin-bottom: 20px;">Sign Up</h2>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <?php if ($success): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>

        <form method="POST" action="register.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password (min 6 characters)" required>
            <button type="submit">Register</button>
        </form>
        
        <p style="margin-top: 20px; color: #737373;">
            Already a member? <a href="login.php" style="color: white;">Sign In.</a>
        </p>
    </div>

    <!-- NEW: Back to Landing Page Link -->
    <a href="landing.php" class="back-link">← Back to Home</a>
</body>
</html>