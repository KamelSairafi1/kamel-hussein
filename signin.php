<?php
include 'db.php';
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);// Prevent SQL injection
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid password.';
        }
    } else {
        $error = 'User not found.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            margin: 0;
            padding: 0;
        }
        .signin-container {
            max-width: 400px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 36px 32px 28px 32px;
        }
        h2 {
            text-align: center;
            color: #2d3e50;
            margin-bottom: 28px;
            font-size: 2em;
            letter-spacing: 1px;
        }
        label {
            font-weight: 500;
            color: #444;
            margin-bottom: 4px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #bcd0ee;
            border-radius: 6px;
            font-size: 1em;
            background: #f7faff;
            transition: border 0.2s;
        }
        input:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            width: 100%;
            margin-top: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s;
        }
        button:hover {
            background: linear-gradient(90deg, #0056b3 0%, #218838 100%);
        }
        .error {
            color: #dc3545;
            text-align: center;
            margin-bottom: 14px;
            font-weight: 500;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 22px;
            color: #007bff;
            text-decoration: none;
            font-size: 1.05em;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="signin-container">
        <h2>Sign In</h2>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST" action="signin.php">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>
            <button type="submit">Sign In</button>
        </form>
        <a href="signup.php">Don't have an account? Sign Up</a>
    </div>
</body>
</html>
