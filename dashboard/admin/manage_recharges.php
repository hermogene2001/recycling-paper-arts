<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Fetch only pending recharge requests
$query = "SELECT r.id, r.client_id, r.agent_id, r.amount, r.status, r.recharge_time, 
                 u.first_name AS client_name, a.first_name AS agent_name
          FROM recharges r
          JOIN users u ON r.client_id = u.id
          JOIN users a ON r.agent_id = a.id
          WHERE r.status = 'pending'
          ORDER BY r.recharge_time DESC";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pending Recharges</title>
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
    </style>
</head>
<body>

    <!-- Fixed Navigation Bar -->
    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">Pending Recharges</h2>
        
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Recharge ID: <?= $row['id'] ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Client: <?= htmlspecialchars($row['client_name']) ?></h5>
                        <p class="card-text">Agent: <?= htmlspecialchars($row['agent_name']) ?></p>
                        <p class="card-text">Amount: <?= number_format($row['amount'], 2) ?> </p>
                        <p class="card-text">Recharge Time: <?= $row['recharge_time'] ?></p>
                        <p class="card-text">Status: <?= ucfirst($row['status']) ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-center">No pending recharges found.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
