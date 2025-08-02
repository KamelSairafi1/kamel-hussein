<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php');
    exit;
}
// Check if the cart exists, if not, create it
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle add to cart action and insert into database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $product_price = floatval($_POST['product_price']);
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $size = isset($_POST['size']) ? mysqli_real_escape_string($conn, $_POST['size']) : '';
    $cart_key = $product_id . '|' . $size;

    // If product+size already in cart, update quantity
    if (isset($_SESSION['cart'][$cart_key])) {
        $_SESSION['cart'][$cart_key] += $quantity;
    } else {
        $_SESSION['cart'][$cart_key] = $quantity;
    }

    // Insert or update in cart table in database
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $check = mysqli_query($conn, "SELECT * FROM cart WHERE user_id=$user_id AND product_id=$product_id AND size='$size'");
    if (mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE cart SET quantity = quantity + $quantity WHERE user_id=$user_id AND product_id=$product_id AND size='$size'");
    } else {
        $sql = "INSERT INTO cart (user_id, product_id, product_name, product_price, quantity, size) VALUES ($user_id, $product_id, '$product_name', $product_price, $quantity, '$size')";
        mysqli_query($conn, $sql);
    }

    header('Location: cart.php');
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <link rel="stylesheet" href="cart.css">
</head>
<body>
    <div class="cart-container">
        <h2>Your Cart</h2>
        <form action="index.php" method="get" style="text-align:right; margin-bottom:18px;">
            <button type="submit" style="background:#007bff; color:#fff; border:none; padding:8px 18px; border-radius:4px; font-size:1em; cursor:pointer;">Return to Home</button>
        </form>
        <?php
        // Handle edit quantity/size
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_cart'])) {
            $edit_id = intval($_POST['edit_id']);
            $edit_size = isset($_POST['edit_size']) ? $_POST['edit_size'] : '';
            $new_qty = isset($_POST['new_quantity']) ? (int)$_POST['new_quantity'] : 1;
            $new_size = isset($_POST['new_size']) ? $_POST['new_size'] : '';
            if ($new_size === 'Other' && !empty($_POST['custom_size'])) {
                $new_size = mysqli_real_escape_string($conn, $_POST['custom_size']);
            }
            $old_key = $edit_id . '|' . $edit_size;
            $new_key = $edit_id . '|' . $new_size;
            // Remove old entry if size changed
            if ($old_key !== $new_key) {
                unset($_SESSION['cart'][$old_key]);
            }
            $_SESSION['cart'][$new_key] = $new_qty;
            // Update in database
            include 'db.php';
            $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
            if ($edit_size !== $new_size) {
                mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id AND product_id=$edit_id AND size='" . mysqli_real_escape_string($conn, $edit_size) . "'");
                $sql = "INSERT INTO cart (user_id, product_id, product_name, product_price, quantity, size) VALUES ($user_id, $edit_id, '', 0, $new_qty, '" . mysqli_real_escape_string($conn, $new_size) . "') ON DUPLICATE KEY UPDATE quantity=$new_qty, size='" . mysqli_real_escape_string($conn, $new_size) . "'";
                mysqli_query($conn, $sql);
            } else {
                $sql = "UPDATE cart SET quantity=$new_qty, size='" . mysqli_real_escape_string($conn, $new_size) . "' WHERE user_id=$user_id AND product_id=$edit_id AND size='" . mysqli_real_escape_string($conn, $edit_size) . "'";
                mysqli_query($conn, $sql);
            }
        }

        // Handle delete from cart
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_cart'])) {
            $delete_id = intval($_POST['delete_id']);
            $delete_size = isset($_POST['delete_size']) ? $_POST['delete_size'] : '';
            $cart_key = $delete_id . '|' . $delete_size;
            // Remove from session cart
            unset($_SESSION['cart'][$cart_key]);
            // Remove from database
            include 'db.php';
            $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
            $sql = "DELETE FROM cart WHERE user_id=$user_id AND product_id=$delete_id AND size='" . mysqli_real_escape_string($conn, $delete_size) . "'";
            mysqli_query($conn, $sql);
            
        }

        if (isset($_POST['buy_cart'])) {
            // Here you can add order creation logic if needed

            // Clear cart from session and database
            $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
            $_SESSION['cart'] = [];
            mysqli_query($conn, "DELETE FROM cart WHERE user_id=$user_id");
            echo '<script>alert("Thank you for your purchase!");</script>';
            // Optionally, you can redirect or show a message
        }

        if (empty($_SESSION['cart'])) {
            echo '<p class="empty-cart">Your cart is empty.</p>';
        } else {
            $total = 0;
            echo '<form method="POST" onsubmit="return confirm(\'Are you sure you want to buy these products?\');">';
            echo '<table>';
            echo '<tr><th>Product ID</th><th>Quantity</th><th>Size</th><th>Price</th><th>Subtotal</th><th>Action</th></tr>';
            foreach ($_SESSION['cart'] as $key => $qty) {
                if (strpos($key, '|') !== false) {
                    list($id, $size) = explode('|', $key, 2);
                } else {
                    $id = $key;
                    $size = '';
                }
                // Fetch price from products table
                $price_res = mysqli_query($conn, "SELECT price FROM product WHERE id=$id LIMIT 1");
                $price_row = mysqli_fetch_assoc($price_res);
                $price = $price_row ? floatval($price_row['price']) : 0;
                $subtotal = $price * $qty;
                $total += $subtotal;

                // Fetch available sizes for this product
                $sizes_res = mysqli_query($conn, "SELECT size FROM product_sizes WHERE product_id=$id");
                $sizeOptions = [];
                while ($row = mysqli_fetch_assoc($sizes_res)) {
                    $sizeOptions[] = $row['size'];
                }
                echo '<tr>';
                echo '<td>' . $id . '</td>';
                echo '<td>' . $qty . '</td>';
                echo '<td>';
                echo '<form class="edit-form" method="POST" style="display:inline; margin-right:4px; vertical-align:middle;">';
                echo '<input type="hidden" name="edit_cart" value="1">';
                echo '<input type="hidden" name="edit_id" value="' . $id . '">';
                echo '<input type="hidden" name="edit_size" value="' . htmlspecialchars($size) . '">';
                echo '<input type="number" name="new_quantity" min="1" value="' . $qty . '" required style="margin-right:8px;">';
                echo '<select name="new_size" id="size-select-' . $id . '" required style="margin-right:8px;">';
                foreach ($sizeOptions as $opt) {
                    $selected = ($size === $opt) ? "selected" : "";
                    echo '<option value="' . htmlspecialchars($opt) . '" ' . $selected . '>' . htmlspecialchars($opt) . '</option>';
                }
                echo '</td>';
                echo '<td>$' . number_format($price, 2) . '</td>';
                echo '<td>$' . number_format($subtotal, 2) . '</td>';
                echo '<td>';
                // Delete button
                echo '<form method="POST" style="display:inline;">';
                echo '<input type="hidden" name="delete_cart" value="1">';
                echo '<input type="hidden" name="delete_id" value="' . $id . '">';
                echo '<input type="hidden" name="delete_size" value="' . htmlspecialchars($size) . '">';
                echo '<button type="submit" class="edit-btn" style="background:#dc3545;">Delete</button>';
                echo '</form>';
                echo '</td>';
                echo '</tr>';
            }
            echo '<tr><td colspan="4" style="text-align:right;font-weight:bold;">Total:</td><td colspan="2" style="font-weight:bold;" id="cart-total">$' . number_format($total, 2) . '</td></tr>';
            echo '</table>';
            echo '<button type="submit" name="buy_cart" class="edit-btn" style="background:#28a745; margin-top:16px;">Buy</button>';
            echo '</form>';
        }
        ?>
    </div>
</body>
</html>