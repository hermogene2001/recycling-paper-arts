<?php
include 'includes/db_connection.php';

// Check for duplicate transactions
function isTransactionDuplicate($conn, $userId, $transactionType, $amount) {
    $sql = "SELECT id FROM transactions 
            WHERE user_id = ? 
            AND transaction_type = ? 
            AND amount = ? 
            AND DATE(transaction_date) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isd", $userId, $transactionType, $amount);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

// Record a transaction (only if it's not a duplicate)
function recordTransaction($conn, $userId, $transactionType, $amount, $status = 'approved') {
    if (!isTransactionDuplicate($conn, $userId, $transactionType, $amount)) {
        $sql = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date, status)
                VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isds", $userId, $transactionType, $amount, $status);
        $stmt->execute();
        $stmt->close();
    }
}

// Notify user
function notifyUser($conn, $userId, $message) {
    $sql = "INSERT INTO notifications (user_id, message, created_at, is_read) VALUES (?, ?, NOW(), 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $userId, $message);
    $stmt->execute();
    $stmt->close();
}

// Fetch all active investments
$sql = "SELECT id, client_id, investment_amount, lock_in_period, daily_profit, total_balance, 
               profit_accumulated, maturity_date, last_profit_update, status 
        FROM compound_investments 
        WHERE status = 'active'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $investmentId = $row['id'];
        $clientId = $row['client_id'];
        $investmentAmount = $row['investment_amount'];
        $lockPeriod = $row['lock_in_period'];
        $dailyProfit = $row['daily_profit'];
        $totalBalance = $row['total_balance'];
        $profitAccumulated = $row['profit_accumulated'];
        $maturityDate = $row['maturity_date'];
        $lastProfitUpdate = $row['last_profit_update'];

        $today = date('Y-m-d');

        // Check if investment has matured
        if ($today >= $maturityDate) {
            // Mark investment as completed and move balance to withdrawable
            $sql = "UPDATE compound_investments 
                    SET status = 'completed', 
                        withdrawable_amount = total_balance, 
                        total_balance = 0 
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $investmentId);
            $stmt->execute();
            $stmt->close();

            // Notify the user
            $notificationMessage = "Your investment has matured. A total of RWF " . number_format($totalBalance, 2) . " is now available for withdrawal.";
            notifyUser($conn, $clientId, $notificationMessage);

            // Record maturity transaction
            recordTransaction($conn, $clientId, 'capital_return', $investmentAmount, 'approved');

            // Update user balance
            $balanceQuery = "UPDATE users SET balance = balance + ? WHERE id = ?";
            $balanceStmt = $conn->prepare($balanceQuery);
            $balanceStmt->bind_param('di', $investmentAmount, $clientId);
            $balanceStmt->execute();
            $balanceStmt->close();

        } else {
            // Ensure daily profit is credited only once per day
            if (empty($lastProfitUpdate) || date('Y-m-d', strtotime($lastProfitUpdate)) < $today) {
                // Correct daily profit calculation
                $dailyProfitAmount = $lockPeriod * $dailyProfit;

                // Update investment balances
                $updatedProfitAccumulated = $profitAccumulated + $dailyProfitAmount;
                $updatedTotalBalance = $totalBalance + $dailyProfitAmount;

                $updateQuery = "UPDATE compound_investments 
                                SET profit_accumulated = ?, 
                                    total_balance = ?, 
                                    last_profit_update = NOW() 
                                WHERE id = ?";
                $stmt = $conn->prepare($updateQuery);
                $stmt->bind_param("ddi", $updatedProfitAccumulated, $updatedTotalBalance, $investmentId);
                $stmt->execute();
                $stmt->close();

                // Record daily profit transaction
                recordTransaction($conn, $clientId, 'daily_earning', $dailyProfitAmount, 'approved');

                // Notify user about daily profit
                $notificationMessage = "Your daily profit of RWF " . number_format($dailyProfitAmount, 2) . " has been credited to your account.";
                notifyUser($conn, $clientId, $notificationMessage);
            }
        }
    }
}

// Close connection
$conn->close();
echo "Cron job executed successfully.";
?>
