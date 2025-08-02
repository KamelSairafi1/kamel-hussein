<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}
if (!isset($_GET['id'])) {
    die("Product ID is required");
}
$id = intval($_GET['id']);
try {
    // Fetch product details
// Fetch product details with all available sizes
$result = mysqli_query($conn, "
    SELECT product.*, company.name AS company_name,
           GROUP_CONCAT(product_sizes.size ORDER BY product_sizes.size SEPARATOR ', ') AS sizes
    FROM product
    JOIN company ON product.company_id = company.id
    LEFT JOIN product_sizes ON product.id = product_sizes.product_id
    WHERE product.id = $id
    GROUP BY product.id
");
    if (!$result) {
        die("Error: " . mysqli_error($conn));
    }
$product = mysqli_fetch_assoc($result);
if (!$product) {
    die("Product not found");
}
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - Details</title>
    <link rel="stylesheet" href="style.css">
    <style>
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
        .product-detail {
            background: #fff;
            max-width: 500px;
            margin: 30px auto;
            padding: 32px 36px;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            text-align: center;
        }
        .product-detail img {
            max-width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 18px;
        }
        .product-detail p {
            font-size: 1.15em;
            margin: 12px 0;
            color: #444;
        }
        a {
            display: inline-block;
            margin: 22px auto 0 auto;
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
            font-size: 1.05em;
            border-radius: 6px;
            padding: 8px 18px;
            background: #f7faff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s;
        }
        a:hover {
            background: #007bff;
            color: #fff;
        }
        form {
            margin-top: 24px;
        }
        button[type="submit"] {
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s;
        }
        button[type="submit"]:hover {
            background: linear-gradient(90deg, #0056b3 0%, #218838 100%);
        }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <div class="product-detail">
        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        <p><strong>Price:</strong> $<?= htmlspecialchars($product['price']) ?></p>
        <p><strong>Sizes:</strong> <?= htmlspecialchars($product['sizes']) ?></p>
        <p><strong>Company:</strong> <?= htmlspecialchars($product['company_name']) ?></p>
    </div>
    <form action="index.php" method="get" style="text-align:center; margin-top:24px;">
        <button type="submit" style="background: linear-gradient(90deg, #007bff 0%, #28a745 100%); color: #fff; border: none; padding: 12px 28px; border-radius: 6px; font-size: 1.1em; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.07); transition: background 0.2s;">Back to Products</button>
    </form>
    <!-- Add to Cart Button -->
    <form action="cart.php" method="POST" style="text-align:center; margin-top:20px;">
        <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
        <input type="hidden" name="product_name" value="<?= htmlspecialchars($product['name']) ?>">
        <input type="hidden" name="product_price" value="<?= htmlspecialchars($product['price']) ?>">
        <!-- Size will be selected in cart page -->
        <button type="submit" style="background:#28a745; color:#fff; border:none; padding:10px 20px; border-radius:4px; font-size:1em; cursor:pointer;">
            Add to Cart
        </button>
    </form>
</body>
</html>