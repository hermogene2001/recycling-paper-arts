<?php
session_start();

// Ensure the user is logged in as a client
if ($_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

date_default_timezone_set("Africa/Kigali");

// Database connection
require_once('../includes/db_connection.php');
include '../includes/function.php';

// Fetch current balance
$current_balance_query = "SELECT balance FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $current_balance_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $current_balance);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Fetch transaction history (including status and balance_after)
$transaction_history_query = "SELECT transaction_type, amount, status, transaction_date FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC";
$stmt = mysqli_prepare($conn, $transaction_history_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $type, $amount, $status, $created_at);

// Store transaction history in an array
$transactions = [];
while (mysqli_stmt_fetch($stmt)) {
    $transactions[] = [
        'transaction_type' => $type,
        'amount' => $amount,
        'status' => $status,
        'date' => $created_at
    ];
}
mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet - DeltaOne Investment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            font-family: Arial, sans-serif;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 70%;
            max-width: 800px;
            margin-top: 80px;
        }
        .card {
            margin: 30px auto;
            max-width: 800px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .table thead {
            background: #2575fc;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
    <div class="container">
        <div class="card">
            <div class="card-header text-center">
                <h4>My Wallet</h4>
            </div>
            <div class="card-body">
                <h5 class="text-center">Current Balance: <span class="text-success"><b><?php echo number_format($current_balance, 2); ?> RWF</b></span></h5>
                <div class="text-center mt-3">
                    <a href="recharge.php" class="btn btn-primary">Recharge Account</a>
                    <a href="withdrawal.php" class="btn btn-secondary">Withdraw Funds</a>
                </div>
                <hr>
                <h5>Transaction History</h5>
<?php if (!empty($transactions)): ?>
    <div class="transaction-list">
        <?php foreach ($transactions as $transaction): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <h6 class="card-title">
                        <span class="badge bg-primary"><?php echo ucfirst($transaction['transaction_type']); ?></span>
                    </h6>
                    <p class="card-text mb-1"><strong>Amount:</strong> <?php echo number_format($transaction['amount'], 2); ?> RWF</p>
                    <p class="card-text mb-1"><strong>Date:</strong> <?php echo date("d M Y, H:i", strtotime($transaction['date'])); ?></p>
                    <!-- Uncomment if status is available -->
                     <!-- <p class="card-text"><strong>Status:</strong> <?php echo ucfirst($transaction['status']); ?></p> -->
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="text-center text-muted">No transactions found.</p>
<?php endif; ?>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
