<?php
include 'db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php');
    exit;
}
// Fetch companies for dropdown
$companies = [];
$result = $conn->query("SELECT id, name FROM company");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
} else {
    die("Error: " . $conn->error);
}
// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM product_sizes WHERE product_id=$delete_id");
    $conn->query("DELETE FROM product WHERE id=$delete_id");
    header('Location: index.php');
    exit;
}

// Handle edit
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_product = null;
$edit_sizes = [];
if ($edit_id) {
    $res = $conn->query("SELECT * FROM product WHERE id=$edit_id");
    if ($res && $res->num_rows > 0) {
        $edit_product = $res->fetch_assoc();
        $res2 = $conn->query("SELECT size FROM product_sizes WHERE product_id=$edit_id");
        while ($row = $res2->fetch_assoc()) {
            $edit_sizes[] = $row['size'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $image = '';
    // Handle file upload if present
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0777, true); }
        $fileName = basename($_FILES['image_file']['name']);
        $targetFile = $uploadDir . uniqid() . '_' . $fileName;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
            $image = $conn->real_escape_string($targetFile);
        }
    } else if (isset($_POST['image']) && !empty($_POST['image'])) {
        $image = $conn->real_escape_string($_POST['image']);
    } else if ($edit_product) {
        $image = $edit_product['image_url'];
    }
    $company = isset($_POST['company']) ? intval($_POST['company']) : 0;
    $sizes = isset($_POST['sizes']) ? $_POST['sizes'] : [];

    if ($name && $price && $company) {
        if ($edit_product) {
            // Update product
            $sql = "UPDATE product SET name='$name', price=$price, image_url='$image', company_id=$company WHERE id=$edit_id";
            if ($conn->query($sql)) {
                $conn->query("DELETE FROM product_sizes WHERE product_id=$edit_id");
                foreach ($sizes as $size) {
                    $size = $conn->real_escape_string($size);
                    $conn->query("INSERT INTO product_sizes (product_id, size) VALUES ($edit_id, '$size')");
                }
                $success = "Product updated successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        } else {
            // Add new product
            $sql = "INSERT INTO product (name, price, image_url, company_id) VALUES ('$name', $price, '$image', $company)";
            if ($conn->query($sql)) {
                $product_id = $conn->insert_id;
                foreach ($sizes as $size) {
                    $size = $conn->real_escape_string($size);
                    $conn->query("INSERT INTO product_sizes (product_id, size) VALUES ($product_id, '$size')");
                }
                $success = "Product added successfully!";
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add T-Shirt</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Add a New T-Shirt</h1>
    <?php if (isset($success)): ?>
        <p class="success"><?= htmlspecialchars($success) ?></p>
    <?php elseif (isset($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form action="add_product.php<?= $edit_id ? '?edit=' . $edit_id : '' ?>" method="POST" enctype="multipart/form-data" class="add-product-form">
        <label for="name">T-Shirt Name:</label>
        <input type="text" name="name" id="name" required value="<?= $edit_product ? htmlspecialchars($edit_product['name']) : '' ?>">
        
        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" required value="<?= $edit_product ? htmlspecialchars($edit_product['price']) : '' ?>">
        
        <label for="description">Description:</label>
        <textarea name="description" id="description" rows="3" placeholder="Enter product description" style="resize:vertical; padding:10px; border:1px solid #bcd0ee; border-radius:6px; font-size:1em; background:#f7faff; margin-bottom:12px;"><?= $edit_product ? htmlspecialchars($edit_product['description'] ?? '') : '' ?></textarea>
        <label for="sizes">Available Sizes:</label>
        <div class="size-options">
            <?php $sizeOptions = ["XS","S","M","L","XL","XXL","XXXL"]; ?>
            <?php foreach ($sizeOptions as $opt): ?>
                <label class="size-label">
                    <input type="checkbox" name="sizes[]" value="<?= $opt ?>" <?= ($edit_product && in_array($opt, $edit_sizes)) ? 'checked' : '' ?>> <?= $opt ?>
                </label>
            <?php endforeach; ?>
        </div>
        
        <label for="image">Image URL:</label>
        <input type="text" name="image" id="image" placeholder="Paste image URL or upload below" value="<?= $edit_product ? htmlspecialchars($edit_product['image_url']) : '' ?>">
        <label for="image_file">Or Upload Image:</label>
        <input type="file" name="image_file" id="image_file" accept="image/*">
        
        <label for="company">Company:</label>
        <select name="company" id="company" required>
            <?php foreach ($companies as $company): ?>
                <option value="<?= $company['id'] ?>" <?= ($edit_product && $edit_product['company_id'] == $company['id']) ? 'selected' : '' ?>><?= htmlspecialchars($company['name']) ?></option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit"><?= $edit_product ? 'Update Product' : 'Add Product' ?></button>
        <a href="index.php" class="home-link">Return to Home</a>
        <a href="logout.php">Logout</a>
    <style>
        .size-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            margin-bottom: 12px;
        }
        .size-label {
            background: #f7faff;
            border: 1px solid #bcd0ee;
            border-radius: 5px;
            padding: 6px 14px 6px 10px;
            font-size: 1em;
            color: #333;
            cursor: pointer;
            transition: background 0.2s, border 0.2s;
        }
        .size-label input[type="checkbox"] {
            margin-right: 6px;
        }
        .size-label:hover {
            background: #e0eafc;
            border-color: #007bff;
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
        .add-product-form {
            background: #fff;
            max-width: 420px;
            margin: 30px auto;
            padding: 36px 32px 28px 32px;
            border-radius: 14px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .add-product-form label {
            font-weight: 500;
            margin-bottom: 4px;
            color: #444;
        }
        .add-product-form input[type="text"],
        .add-product-form input[type="number"],
        .add-product-form input[type="file"],
        .add-product-form select {
            padding: 10px;
            border: 1px solid #bcd0ee;
            border-radius: 6px;
            font-size: 1em;
            margin-bottom: 12px;
            background: #f7faff;
            transition: border 0.2s;
        }
        .add-product-form input:focus, .add-product-form select:focus {
            border-color: #007bff;
            outline: none;
        }
        .add-product-form button[type="submit"] {
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 1.1em;
            cursor: pointer;
            margin-top: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s;
        }
        .add-product-form button[type="submit"]:hover {
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
        .add-product-form a {
            display: block;
            text-align: center;
            margin: 18px auto 0 auto;
            color: #007bff;
            text-decoration: none;
            font-size: 1.05em;
            font-weight: 500;
            transition: color 0.2s;
        }
        .add-product-form a.home-link {
            margin-top: 8px;
            margin-bottom: 0;
            color: #28a745;
            font-weight: 600;
        }
        .add-product-form a:hover {
            text-decoration: underline;
            color: #0056b3;
        }
        .add-product-form div[style*="margin-bottom:12px"] {
            margin-bottom: 12px;
        }
    </style>
</body>
</html>