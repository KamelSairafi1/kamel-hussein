<?php
include 'db.php';
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: signin.php');
    exit;
}
$success = $error = '';
// Handle delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM company WHERE id=$delete_id");
    header('Location: add_company.php');
    exit;
}
// Handle edit
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_name = '';
if ($edit_id) {
    $res = $conn->query("SELECT name FROM company WHERE id=$edit_id");
    if ($row = $res->fetch_assoc()) {
        $edit_name = $row['name'];
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    if ($edit_id) {
        if ($name) {
            $conn->query("UPDATE company SET name='$name' WHERE id=$edit_id");
            $success = 'Company updated successfully!';
            header('Location: add_company.php');
            exit;
        } else {
            $error = 'Company name is required.';
        }
    } else {
        if ($name) {
            $sql = "INSERT INTO company (name) VALUES ('$name')";
            if ($conn->query($sql)) {
                $success = 'Company added successfully!';
            } else {
                $error = 'Error: ' . $conn->error;
            }
        } else {
            $error = 'Company name is required.';
        }
    }
}
// Fetch all companies
$companies = [];
$res = $conn->query("SELECT * FROM company ORDER BY id DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $companies[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add/Edit/Delete Company</title>
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
        .add-company-container {
            max-width: 400px;
            margin: 40px auto 20px auto;
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
        button, .company-action {
            background: linear-gradient(90deg, #007bff 0%, #28a745 100%);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            font-size: 1em;
            cursor: pointer;
            margin-top: 8px;
            margin-right: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .company-action:hover {
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
        .company-list {
            max-width: 400px;
            margin: 0 auto 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
            padding: 24px 32px 18px 32px;
        }
        .company-list h3 {
            text-align: center;
            color: #2d3e50;
            margin-bottom: 18px;
            font-size: 1.2em;
        }
        .company-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0eafc;
        }
        .company-row:last-child {
            border-bottom: none;
        }
        .company-name {
            font-size: 1.08em;
            color: #333;
        }
    </style>
</head>
<body>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div class="add-company-container">
        <h2><?= $edit_id ? 'Edit Company' : 'Add Company' ?></h2>
        <?php if ($success): ?>
            <p class="success"><?= htmlspecialchars($success) ?></p>
        <?php elseif ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="POST" action="add_company.php<?= $edit_id ? '?edit=' . $edit_id : '' ?>">
            <label for="name">Company Name:</label>
            <input type="text" name="name" id="name" required value="<?= htmlspecialchars($edit_name) ?>">
            <button type="submit"><?= $edit_id ? 'Update Company' : 'Add Company' ?></button>
        </form>
        <a href="admin.php">Back to Admin</a>
    </div>
    <div class="company-list">
        <h3>All Companies</h3>
        <?php if (count($companies) === 0): ?>
            <p style="text-align:center; color:#888;">No companies found.</p>
        <?php else: ?>
            <?php foreach ($companies as $company): ?>
                <div class="company-row">
                    <span class="company-name"><?= htmlspecialchars($company['name']) ?></span>
                    <span>
                        <a href="add_company.php?edit=<?= $company['id'] ?>" class="company-action" style="background: #ffc107; color: #222;">Edit</a>
                        <a href="add_company.php?delete=<?= $company['id'] ?>" class="company-action" style="background: #dc3545; color: #fff;" onclick="return confirm('Are you sure you want to delete this company?');">Delete</a>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>
