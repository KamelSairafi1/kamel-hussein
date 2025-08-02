<?php
include 'db.php';
session_start();

// Only allow access if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php');
    exit;
}

// Handle admin cart edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_edit_cart'])) {
    $edit_user_id = intval($_POST['edit_user_id']);
    $edit_product_id = intval($_POST['edit_product_id']);
    $new_qty = isset($_POST['new_quantity']) ? (int)$_POST['new_quantity'] : 1;
    $new_size = isset($_POST['new_size']) ? $_POST['new_size'] : '';
    if ($new_size === 'Other' && !empty($_POST['custom_size'])) {
        $new_size = mysqli_real_escape_string($conn, $_POST['custom_size']);
    }
    $sql = "UPDATE cart SET quantity=$new_qty, size='" . mysqli_real_escape_string($conn, $new_size) . "' WHERE user_id=$edit_user_id AND product_id=$edit_product_id";
    mysqli_query($conn, $sql);
}

// Handle admin cart delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_delete_cart'])) {
    $delete_user_id = intval($_POST['delete_user_id']);
    $delete_product_id = intval($_POST['delete_product_id']);
    $sql = "DELETE FROM cart WHERE user_id=$delete_user_id AND product_id=$delete_product_id";
    mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .logout-btn {
            position: absolute;
            top: 24px;
            right: 36px;
            background: linear-gradient(90deg, #dc3545 0%, #ff9800 100%);
            color: #fff;
            padding: 10px 22px;
            border-radius: 6px;
            font-size: 1.08em;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s;
            z-index: 100;
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
        .admin-container {
            max-width: 600px;
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
        .admin-links {
            text-align: center;
            margin-top: 32px;
        }
        .admin-links a {
            display: inline-block;
            margin: 0 18px;
            color: #007bff;
            text-decoration: none;
            font-size: 1.15em;
            font-weight: 500;
            padding: 8px 18px;
            border-radius: 6px;
            background: #f7faff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, color 0.2s;
        }
        .admin-links a:hover {
            background: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div class="admin-container">
        <h2>Welcome, Admin!</h2>
        <div class="admin-links">
            <a href="add_product.php">Add Product</a>
            <a href="add_company.php">Add Company</a>
            <a href="index.php">View Products</a>
        </div>

        <h3 style="margin-top:40px; text-align:center;">All User Carts</h3>
        <table style="width:100%; border-collapse:collapse; margin-top:18px;">
            <thead>
                <tr style="background:#f7faff;">
                    <th style="padding:8px; border:1px solid #bcd0ee;">User</th>
                    <th style="padding:8px; border:1px solid #bcd0ee;">Email</th>
                    <th style="padding:8px; border:1px solid #bcd0ee;">Product</th>
                    <th style="padding:8px; border:1px solid #bcd0ee;">Size</th>
                    <th style="padding:8px; border:1px solid #bcd0ee;">Quantity</th>
                    <th style="padding:8px; border:1px solid #bcd0ee;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $cartResult = $conn->query("SELECT cart.*, users.username, users.email, product.name AS product_name FROM cart JOIN users ON cart.user_id = users.id JOIN product ON cart.product_id = product.id");
                if ($cartResult && $cartResult->num_rows > 0) {
                    while ($row = $cartResult->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td style="padding:8px; border:1px solid #bcd0ee;">' . htmlspecialchars($row['username']) . '</td>';
                        echo '<td style="padding:8px; border:1px solid #bcd0ee;">' . htmlspecialchars($row['email']) . '</td>';
                        echo '<td style="padding:8px; border:1px solid #bcd0ee;">' . htmlspecialchars($row['product_name']) . '</td>';
                        // Edit form for size and quantity
                        echo '<td style="padding:8px; border:1px solid #bcd0ee;">';
                        // Fetch available sizes for this product
                        $sizes_res = $conn->query("SELECT size FROM product_sizes WHERE product_id=" . intval($row['product_id']));
                        $sizeOptions = [];
                        if ($sizes_res) {
                            while ($srow = $sizes_res->fetch_assoc()) {
                                $sizeOptions[] = $srow['size'];
                            }
                        }
                        echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to update this cart item?\');">';
                        echo '<input type="hidden" name="admin_edit_cart" value="1">';
                        echo '<input type="hidden" name="edit_user_id" value="' . intval($row['user_id']) . '">';
                        echo '<input type="hidden" name="edit_product_id" value="' . intval($row['product_id']) . '">';
                        echo '<select name="new_size" required style="margin-right:8px;">';
                        foreach ($sizeOptions as $opt) {
                            $selected = ($row['size'] === $opt) ? "selected" : "";
                            echo '<option value="' . htmlspecialchars($opt) . '" ' . $selected . '>' . htmlspecialchars($opt) . '</option>';
                        }
                        echo '</select>';
                        echo '</td>';
                        echo '<td style="padding:8px; border:1px solid #bcd0ee;">';
                        echo '<input type="number" name="new_quantity" min="1" value="' . intval($row['quantity']) . '" required style="width:60px; margin-right:8px;">';
                        echo '<button type="submit" class="edit-btn" style="padding:6px 14px;">Update</button>';
                        echo '</form>';
                        echo '</td>';
                        // Delete form
                        echo '<td style="padding:8px; border:1px solid #bcd0ee;">';
                        echo '<form method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure you want to delete this cart item?\');">';
                        echo '<input type="hidden" name="admin_delete_cart" value="1">';
                        echo '<input type="hidden" name="delete_user_id" value="' . intval($row['user_id']) . '">';
                        echo '<input type="hidden" name="delete_product_id" value="' . intval($row['product_id']) . '">';
                        echo '<button type="submit" class="edit-btn" style="background:#dc3545; padding:6px 14px;">Delete</button>';
                        echo '</form>';
                        echo '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="6" style="padding:12px; text-align:center; color:#888;">No cart items found.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
