<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Handle Approve/Reject actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['withdrawal_id'])) {
    $action = $_POST['action'];
    $withdrawalId = $_POST['withdrawal_id'];

    if ($action === 'approve') {
        $updateQuery = "UPDATE withdrawals SET status = 'approved' WHERE id = ?";
    } elseif ($action === 'reject') {
        $updateQuery = "UPDATE withdrawals SET status = 'rejected' WHERE id = ?";
    }

    if (isset($updateQuery)) {
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('i', $withdrawalId);
        $stmt->execute();
    }
}

// Fetch only pending withdrawal requests
$query = "SELECT w.id, w.client_id, w.amount, w.status, w.date, u.first_name 
          FROM withdrawals w 
          JOIN users u ON w.client_id = u.id
          WHERE w.status = 'pending'
          ORDER BY w.date DESC";
$result = $conn->query($query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pending Withdrawals</title>
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
            border: 1px solid #ccc;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: #6a11cb;
            color: #fff;
            font-weight: bold;
        }

        .card-actions button {
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <!-- Fixed Navigation Bar -->
    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">Pending Withdrawals</h2>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card">
                    <div class="card-header">
                        Withdrawal ID: <?= $row['id'] ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">User: <?= htmlspecialchars($row['first_name']) ?></h5>
                        <p class="card-text">
                            <strong>Amount:</strong> <?= number_format($row['amount'], 2) ?> <br>
                            <strong>Requested At:</strong> <?= $row['date'] ?>
                        </p>
                        <div class="card-actions">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="withdrawal_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                            </form>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="withdrawal_id" value="<?= $row['id'] ?>">
                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-warning text-center">
                No pending withdrawal requests found.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
