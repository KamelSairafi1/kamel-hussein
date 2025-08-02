<?php
include 'db.php';

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';

    // Check if username or email exists
    $check = $conn->query("SELECT id FROM users WHERE username='$username' OR email='$email'");
    if ($check && $check->num_rows > 0) {
        $error = 'Username or email already exists.';
    } else {
        $sql = "INSERT INTO users (firstname, lastname, username, email, password, role) VALUES ('$firstname', '$lastname', '$username', '$email', '$password', '$role')";
        if ($conn->query($sql)) {
            $success = 'Signup successful! You can now sign in.';
        } else {
            $error = 'Error: ' . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            margin: 0;
            padding: 0;
        }
        .signup-container {
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
        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #bcd0ee;
            border-radius: 6px;
            font-size: 1em;
            background: #f7faff;
            transition: border 0.2s;
        }
        input:focus, select:focus {
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
        .success {
            color: #28a745;
            text-align: center;
            margin-bottom: 14px;
            font-weight: 500;
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
    <div class="signup-container">
        <h2>Sign Up</h2>
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
            <form action="signin.php" method="get" style="text-align:center;">
                <button type="submit" style="background:#007bff; color:#fff; border:none; padding:10px; border-radius:4px; font-size:1em; cursor:pointer; margin-top:10px;">Sign In</button>
            </form>
        <?php else: ?>
            <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form method="POST" action="signup.php">
                <label for="firstname">First Name:</label>
                <input type="text" name="firstname" id="firstname" required>
                <label for="lastname">Last Name:</label>
                <input type="text" name="lastname" id="lastname" required>
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                <label for="role">Role:</label>
                <select name="role" id="role">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
                <button type="submit">Sign Up</button>
            </form>
            <a href="signin.php">Already have an account? Sign In</a>
        <?php endif; ?>
    </div>
</body>
</html>
