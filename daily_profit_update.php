<?php
// Database connection
include 'includes/db_connection.php';

// Set timezone to Kigali
ini_set('date.timezone', 'Africa/Kigali');
$now = date('Y-m-d H:i:s');

// Create a lock file to prevent multiple script runs
$lockFile = '/tmp/earnings_script.lock';
if (file_exists($lockFile)) {
    exit('Script is already running.');
}
file_put_contents($lockFile, getmypid());
register_shutdown_function(function () use ($lockFile) {
    unlink($lockFile);
});

// Start transaction to ensure atomicity
mysqli_begin_transaction($conn);
try {
    // Query active purchases eligible for daily earnings
    $query = "
        SELECT 
            purchases.id AS purchase_id, 
            purchases.client_id, 
            products.daily_earning, 
            purchases.last_earned, 
            purchases.end_datetime
        FROM purchases
        JOIN products ON purchases.product_id = products.id
        WHERE purchases.status = 'active' 
        AND ? >= DATE_ADD(purchases.last_earned, INTERVAL 1 DAY)
        AND ? <= purchases.end_datetime
        FOR UPDATE";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $now, $now);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($purchase = $result->fetch_assoc()) {
        $purchaseId = $purchase['purchase_id'];
        $userId = $purchase['client_id'];
        $dailyEarning = $purchase['daily_earning'];
        $endDatetime = $purchase['end_datetime'];

        // Prevent duplicate entry by checking the transactions table
        $checkQuery = "SELECT COUNT(*) FROM transactions WHERE user_id = ? AND transaction_type = 'daily_earning' AND transaction_date = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('is', $userId, $now);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            continue; // Skip if already logged
        }

        // Process daily earnings if within the earning period
        if ($now < $endDatetime) {
            // Update user balance
            $balanceQuery = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $balanceStmt = $conn->prepare($balanceQuery);
            $balanceStmt->bind_param('di', $dailyEarning, $userId);
            $balanceStmt->execute();

            // Log transaction for daily earnings
            $transactionQuery = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, 'daily_earning', ?, ?)";
            $transactionStmt = $conn->prepare($transactionQuery);
            $transactionStmt->bind_param('ids', $userId, $dailyEarning, $now);
            $transactionStmt->execute();

            // Update last earning datetime in purchases
            $purchaseUpdateQuery = "UPDATE purchases SET last_earned = ? WHERE id = ?";
            $purchaseUpdateStmt = $conn->prepare($purchaseUpdateQuery);
            $purchaseUpdateStmt->bind_param('si', $now, $purchaseId);
            $purchaseUpdateStmt->execute();
        }
    }

    // Process expired purchases (mark as completed and refund capital)
    $completePurchasesQuery = "
        SELECT p.id AS purchase_id, 
               p.client_id,
               i.id AS investment_id,
               i.amount AS investment_amount
        FROM purchases p
        JOIN investments i ON p.client_id = i.user_id
        WHERE p.status = 'active' 
        AND p.end_datetime <= ?
        AND i.status = 'active'";
    
    $completePurchasesStmt = $conn->prepare($completePurchasesQuery);
    $completePurchasesStmt->bind_param('s', $now);
    $completePurchasesStmt->execute();
    $expiredResult = $completePurchasesStmt->get_result();

    while ($expired = $expiredResult->fetch_assoc()) {
        $purchaseId = $expired['purchase_id'];
        $userId = $expired['client_id'];
        $investmentId = $expired['investment_id'];
        $investmentAmount = $expired['investment_amount'];

        // Mark purchase as completed
        $completePurchaseQuery = "UPDATE purchases SET status = 'completed' WHERE id = ?";
        $completePurchaseStmt = $conn->prepare($completePurchaseQuery);
        $completePurchaseStmt->bind_param('i', $purchaseId);
        $completePurchaseStmt->execute();

        // Refund capital amount to user balance
        $refundQuery = "UPDATE users SET balance = balance + ? WHERE id = ?";
        $refundStmt = $conn->prepare($refundQuery);
        $refundStmt->bind_param('di', $investmentAmount, $userId);
        $refundStmt->execute();

        // Log capital return transaction
        $capitalTransactionQuery = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, 'capital_return', ?, ?)";
        $capitalTransactionStmt = $conn->prepare($capitalTransactionQuery);
        $capitalTransactionStmt->bind_param('ids', $userId, $investmentAmount, $now);
        $capitalTransactionStmt->execute();

        // Mark investment as completed
        $completeInvestmentQuery = "UPDATE investments SET status = 'completed' WHERE id = ?";
        $completeInvestmentStmt = $conn->prepare($completeInvestmentQuery);
        $completeInvestmentStmt->bind_param('i', $investmentId);
        $completeInvestmentStmt->execute();
    }

    // Commit transaction
    mysqli_commit($conn);
} catch (Exception $e) {
    // Rollback on error
    mysqli_rollback($conn);
    error_log("Error processing earnings and investments: " . $e->getMessage());
}

// Close connection
mysqli_close($conn);
?>
