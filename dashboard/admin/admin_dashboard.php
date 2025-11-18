<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Fetch statistics
$activeUsersQuery = "SELECT COUNT(*) AS active_users FROM users WHERE status = 'active' and role != 'admin'";
$pendingRequestsQuery = "SELECT COUNT(*) AS pending_requests FROM transactions";
$rechargesQuery = "SELECT COUNT(*) AS total_recharges FROM recharges WHERE status = 'pending'";
$withdrawalsQuery = "SELECT COUNT(*) AS total_withdrawals FROM withdrawals WHERE status = 'pending'";


$activeUsersResult = $conn->query($activeUsersQuery);
$pendingRequestsResult = $conn->query($pendingRequestsQuery);
$rechargesResult = $conn->query($rechargesQuery);
$withdrawalsResult = $conn->query($withdrawalsQuery);


// Fetch data
$activeUsers = $activeUsersResult->fetch_assoc()['active_users'];
$pendingRequests = $pendingRequestsResult->fetch_assoc()['pending_requests'];
$totalRecharges = $rechargesResult->fetch_assoc()['total_recharges'];
$totalWithdrawals = $withdrawalsResult->fetch_assoc()['total_withdrawals'];

// Get count of products
$product_count_query = "SELECT COUNT(*) AS total_products FROM products";
$product_count_result = mysqli_query($conn, $product_count_query);
$product_count = mysqli_fetch_assoc($product_count_result)['total_products'];

// Handle agent password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $userId = $_POST['user_id'];
    $newPassword = $_POST['update_password'];
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateQuery = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('si', $hashedPassword, $userId);

    if ($stmt->execute()) {
        $successMessage = "Password updated successfully!";
    } else {
        $errorMessage = "Failed to update password.";
    }
}

// Handle agent creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_agent'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $createAgentQuery = "INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, 'agent', 'active')";
    $stmt = $conn->prepare($createAgentQuery);
    $stmt->bind_param('ssss', $name, $email, $phone, $password);

    if ($stmt->execute()) {
        $successMessage = "Agent created successfully!";
    } else {
        $errorMessage = "Failed to create agent.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/custom.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            padding-top: 70px;
        }

        .navbar {
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }

        .content {
            padding-top: 80px;
        }

        .current-time {
            font-size: 1.2rem;
            color: #fff;
            text-align: center;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 10px;
            position: fixed;
            top: 85px;
            right: 10px;
            z-index: 999;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background-color: #6a11cb;
            color: #fff;
        }
    </style>
    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').innerText = now.toLocaleString();
        }
        setInterval(updateTime, 1000);
    </script>
</head>
<body onload="updateTime()">

    <!-- Fixed Navigation Bar -->
    <?php include('../../includes/admin_nav.php'); ?>

    <!-- Current Time -->
    <div class="current-time">
        <strong>Current Time:</strong> <span id="currentTime"></span>
    </div>

    <!-- Main Content Container -->
    <div class="content">
        <div class="container">
            <h2 class="text-center mb-4 text-white">Admin Dashboard</h2>

            <!-- Dashboard Stats -->
            <!-- Dashboard Stats -->
<div class="row g-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-primary">
            <div class="card-body">
            <h5 class="card-title text-primary">Active Users</h5>
                <p class="card-text fs4"><?= $activeUsers ?></p>
                <button class="btn btn-outline-primary btn-sm" onclick="location.href='manage_users.php'">View Details</button>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-warning">
            <div class="card-body">
            <h5 class="card-title text-warning">Manage Transactions</h5>
                <p class="card-text fs4"><?= $pendingRequests ?></p>
                <button class="btn btn-outline-warning btn-sm" onclick="location.href='manage_transactions.php'">View Details</button>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-info">
            <div class="card-body">
            <h5 class="card-title text-info">Total Recharges</h5>
                <p class="card-text fs4"><?= $totalRecharges ?></p>
                <button class="btn btn-outline-info btn-sm" onclick="location.href='manage_recharges.php'">View Details</button>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card shadow-sm border-danger">
            <div class="card-body">
            <h5 class="card-title text-danger">Total Withdrawals</h5>
                <p class="card-text fs4"><?= $totalWithdrawals ?></p>
                <button class="btn btn-outline-danger btn-sm" onclick="location.href='manage_withdrawals.php'">View Details</button>
            </div>
        </div>
    </div>

    <!-- Total Products Card -->
    <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">Total Products</h5>
                    <p class="card-text fs-4"><?= $product_count; ?></p>
                    <a href="manage_products.php" class="btn btn-outline-success btn-sm">View All Products</a>
                </div>
            </div>
        </div>

    <!-- Free buttons under the card sections -->
    <!-- <div class="col-md-4 mt-3">
        <button class="btn btn-light btn-sm w-100" data-bs-toggle="modal" data-bs-target="#createAgentModal">Create Agent</button>
    </div>

    <div class="col-md-4 mt-3">
        <button class="btn btn-light btn-sm w-100" data-bs-toggle="modal" data-bs-target="#updatePasswordModal">Update Password</button>
    </div> -->
</div>
        </div>
    </div>
    <?php include('modals.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
