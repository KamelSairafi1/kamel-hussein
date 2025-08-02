<?php
include 'db.php';
session_start();

// Fetch products with all available sizes
$result = mysqli_query($conn, "
    SELECT product.id, product.name, product.price, product.image_url, company.name AS company_name,
           GROUP_CONCAT(product_sizes.size ORDER BY product_sizes.size SEPARATOR ', ') AS sizes
    FROM product
    JOIN company ON product.company_id = company.id
    LEFT JOIN product_sizes ON product.id = product_sizes.product_id
    GROUP BY product.id
");
if (!$result) {
    die("Error: " . mysqli_error($conn));
}
$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of T-Shirts</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .logout-btn {
            background: linear-gradient(90deg, #dc3545 0%, #ff9800 100%);
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s;
            border: none;
            margin-left: 12px;
            cursor: pointer;
        }
        .admin-dashboard-btn {
            display: inline-block;
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s;
            margin-right: 12px;
        }
        .admin-dashboard-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #218838 100%);
            color: #fff;
        }
        .logout-btn:hover {
            background: linear-gradient(90deg, #b71c1c 0%, #ff9800 100%);
            color: #fff;
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(120deg, #e0eafc 0%, #cfdef3 100%);
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            margin-top: 30px;
            color: #2d3e50;
            font-size: 2em;
            letter-spacing: 1px;
        }
        .add-product-container {
            text-align: center;
            margin-bottom: 24px;
        }
        .add-product-button {
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            border: none;
            padding: 10px 24px;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s;
        }
        .add-product-button:hover {
            background: linear-gradient(90deg, #0056b3 0%, #218838 100%);
        }
        .product-list {
            display: flex;
            flex-wrap: wrap;
            gap: 32px;
            justify-content: center;
            margin: 32px auto;
            max-width: 1200px;
        }
        .product-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 24px 18px 18px 18px;
            width: 260px;
            text-align: center;
            transition: box-shadow 0.2s;
        }
        .product-card:hover {
            box-shadow: 0 6px 24px rgba(0,0,0,0.13);
        }
        .product-card img {
            max-width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 14px;
        }
        .product-card h2 {
            font-size: 1.2em;
            color: #333;
            margin-bottom: 8px;
        }
        .product-card p {
            font-size: 1em;
            color: #555;
            margin: 6px 0;
        }
        .product-card a {
            display: inline-block;
            margin-top: 10px;
            background: #007bff;
            color: #fff;
            padding: 7px 18px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 1em;
            transition: background 0.2s;
        }
        .product-card a:hover {
            background: #0056b3;
        }
        .user-bar {
            text-align: right;
            margin: 16px 32px 0 0;
        }
        .user-bar a, .user-bar span {
            font-size: 1.05em;
            font-weight: 500;
            color: #007bff;
            margin-right: 12px;
            text-decoration: none;
        }
        .user-bar a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    .view-cart-btn {
        display: inline-block;
        background: #fff;
        color: #007bff;
        border: 2px solid #007bff;
        padding: 14px 32px;
        border-radius: 8px;
        
        font-size: 1.2em;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        margin-bottom: 22px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        transition: background 0.2s, color 0.2s, box-shadow 0.2s;
        letter-spacing: 1px;
    }
    .admin-cart-btn {
        color: #fff !important;
        text-shadow: 0 1px 4px rgba(0,0,0,0.18);
    }
    .view-cart-btn:hover {
        background: #007bff;
        color: #fff;
        box-shadow: 0 8px 32px rgba(0,0,0,0.13);
        border-color: #0056b3;
    }
    .welcome-user {
            display: inline-block;
            background: linear-gradient(90deg, #e0eafc 0%, #cfdef3 100%);
            color: #2d3e50;
            font-size: 1.08em;
            font-weight: 600;
            padding: 7px 18px;
            border-radius: 6px;
            margin-right: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
        }
         .top-bar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.5em;
            margin: 16px 32px 0 0;
            min-height: 48px;
        }
        admin-action-btn {
            display: inline-block;
            padding: 7px 16px;
            border-radius: 5px;
            font-size: 1em;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            margin-top: 10px;
            margin-bottom: 2px;
            transition: background 0.2s, color 0.2s;
        }
        .admin-action-btn:hover {
            filter: brightness(0.95);
            opacity: 0.92;
        }
           .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #e0eafc;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            padding: 0 36px;
            min-height: 64px;
            margin-bottom: 12px;
            border-radius: 0 0 16px 16px;
        }
        .navbar-left, .navbar-right {
            display: flex;
            align-items: center;
            gap: 0.5em;
        }
        .signin-btn {
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s;
            margin-left: 12px;
        }
        .signin-btn:hover {
            background: linear-gradient(90deg, #0056b3 0%, #218838 100%);
            color: #fff;
        }
       
    </style>
</head>
<body>
    <h1>Available T-Shirts</h1>

    <nav class="navbar">
        <div class="navbar-left">
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin.php" class="admin-dashboard-btn">Admin Dashboard</a>
            <?php endif; ?>
        </div>
        <div class="navbar-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-user">Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</span>
                <a href="logout.php" class="logout-btn">Logout</a>
            <?php else: ?>
                <a href="signin.php" class="signin-btn">Sign In</a>
            <?php endif; ?>
        </div>
    </nav>
     
        
    <div class="user-bar" style="text-align:center; margin-top:24px;">
        <?php if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin'): ?>
            <a href="cart.php" class="view-cart-btn">🛒 View Cart</a>
        <?php endif; ?>
    </div>
    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <p>Price: $<?= htmlspecialchars($product['price']) ?></p>
                <p>Sizes: <?= htmlspecialchars($product['sizes']) ?></p>
                <p>Company: <?= htmlspecialchars($product['company_name']) ?></p>
                <a href="view_product.php?id=<?= $product['id'] ?>">View Details</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="add_product.php?edit=<?= $product['id'] ?>" class="admin-action-btn" style="background: linear-gradient(90deg, #ffc107 0%, #ff9800 100%); color: #222; margin-left: 6px;">Edit</a>
                    <a href="add_product.php?delete=<?= $product['id'] ?>" class="admin-action-btn" style="background: linear-gradient(90deg, #dc3545 0%, #ff9800 100%); color: #fff; margin-left: 6px;" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                <?php endif; ?>
        .
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>