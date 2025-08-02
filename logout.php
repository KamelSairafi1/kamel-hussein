<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <link rel="stylesheet" href="logout_style.css">
</head>
<body>
    <div class="logout-container">
        <h2>Logged Out</h2>
        <p>You have been successfully logged out.</p>
        <a href="signin.php">Sign In Again</a>
        <a href="index.php" style="margin-left:12px;">Return to Home</a>
    </div>
</body>
</html>
