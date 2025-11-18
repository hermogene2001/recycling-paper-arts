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
    <title>View User</title>
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

        .user-info {
            margin-bottom: 20px;
        }

        .user-info .label {
            font-weight: bold;
        }

        .user-info .value {
            margin-left: 10px;
        }
    </style>
</head>
<body>

    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">User Details</h2>

        <div class="user-info">
            <p><span class="label">Full Name:</span><span class="value"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span></p>
            <p><span class="label">Phone Number:</span><span class="value"><?= htmlspecialchars($user['phone_number']) ?></span></p>
            <p><span class="label">Referral Code:</span><span class="value"><?= htmlspecialchars($user['referral_code']) ?></span></p>
            <p><span class="label">Balance:</span><span class="value"><?= number_format($user['balance'], 2) ?></span></p>
            <p><span class="label">Status:</span><span class="value"><?= ucfirst($user['status']) ?></span></p>
            <p><span class="label">Role:</span><span class="value"><?= ucfirst($user['role']) ?></span></p>
            <p><span class="label">Created At:</span><span class="value"><?= $user['created_at'] ?></span></p>

            <!-- Password Field (Only if stored in plain text, NOT recommended) -->
            <p><span class="label">Password:</span>
                <span class="value">
                    <input type="password" id="userPassword" value="<?= htmlspecialchars($user['password']) ?>" readonly>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="togglePassword()">Show</button>
                </span>
            </p>
        </div>

        <div class="text-center">
            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning">Edit User</a>
            <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete User</a>

            <!-- Deactivate Button -->
            <?php if ($user['status'] === 'active') { ?>
                <a href="deactivate_user.php?id=<?= $user['id'] ?>" class="btn btn-secondary" onclick="return confirm('Are you sure you want to deactivate this user?')">Deactivate User</a>
            <?php } else { ?>
                <a href="activate_user.php?id=<?= $user['id'] ?>" class="btn btn-success" onclick="return confirm('Activate this user?')">Activate User</a>
            <?php } ?>

            <!-- Password Reset -->
            <a href="reset_password.php?id=<?= $user['id'] ?>" class="btn btn-danger">Reset Password</a>

            <a href="manage_users.php" class="btn btn-primary">Back to Manage Users</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePassword() {
            var passwordField = document.getElementById("userPassword");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
    </script>

</body>
</html>
