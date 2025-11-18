<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Check if the user ID is provided in the URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user details based on the user ID
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user is found
    if ($result->num_rows === 0) {
        echo "User not found.";
        exit();
    }

    // Fetch the user data
    $user = $result->fetch_assoc();

    // Handle form submission for updating user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate input data
        $firstName = trim($_POST['first_name']);
        $lastName = trim($_POST['last_name']);
        $role = $_POST['role'];
        $balance = floatval($_POST['balance']);

        if (empty($firstName) || empty($lastName) || !in_array($role, ['client', 'admin', 'agent'])) {
            echo "Please fill in all fields and select a valid role.";
        } else {
            // Update the user data
            $updateQuery = "UPDATE users SET first_name = ?, last_name = ?, role = ?, balance = ? WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param('sssdi', $firstName, $lastName, $role, $balance, $userId);
            if ($updateStmt->execute()) {
                header("Location: view_user.php?id=$userId");
                exit();
            } else {
                echo "Failed to update user details.";
            }
        }
    }
} else {
    echo "No user ID provided.";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            padding-top: 70px; /* Space for the fixed navbar */
        }

        .navbar {
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .card {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Edit User Details</h2>

        <form action="" method="POST">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="role">Role</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="client" <?= $user['role'] === 'client' ? 'selected' : '' ?>>Client</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                    <option value="agent" <?= $user['role'] === 'agent' ? 'selected' : '' ?>>Agent</option>
                </select>
            </div>
            <div class="form-group">
                <label for="balance">Balance</label>
                <input type="number" step="0.01" class="form-control" id="balance" name="balance" value="<?= htmlspecialchars($user['balance']) ?>" required>
            </div>
            <div class="form-group text-center">
                <button type="submit" class="btn btn-success">Update User</button>
                <a href="manage_users.php" class="btn btn-secondary btn-back">Back to Manage Users</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
