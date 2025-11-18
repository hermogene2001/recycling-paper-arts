<?php
session_start();
require_once('../includes/db_connection.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must log in first.'); window.location.href = '../index.php';</script>";
    exit;
}

// Ensure product ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "<script>alert('Product ID is missing in the URL.'); window.location.href = 'client_dashboard.php';</script>";
    exit;
}

$clientId = intval($_SESSION['user_id']);
$productId = intval($_GET['id']);

// Fetch product details
$product_query = "SELECT id, price, daily_earning, profit_rate, cycle FROM products_compound WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $productId);
$stmt->execute();
$product_result = $stmt->get_result();
$product = $product_result->fetch_assoc();

if (!$product) {
    echo "<script>alert('No product found with the specified ID.'); window.location.href = 'view_products.php';</script>";
    exit;
}

// Fetch user balance
$user_query = "SELECT balance FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $clientId);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_balance = $user['balance'];

$price = $product['price'];
$cycle_days = $product['cycle'];
$dailyProfit = $product['daily_earning'];
$startDate = date('Y-m-d H:i:s');
$maturityDate = date('Y-m-d H:i:s', strtotime("+$cycle_days days", strtotime($startDate)));

// Check if user has sufficient balance
if ($user_balance >= $price) {
    // Deduct price from user balance
    $new_balance = $user_balance - $price;
    $update_balance_query = "UPDATE users SET balance = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_balance_query);
    $update_stmt->bind_param("di", $new_balance, $clientId);
    $update_stmt->execute();

$investment_query = "
    INSERT INTO compound_investments (
        client_id, product_id, investment_amount, daily_profit, start_date, maturity_date, 
        lock_in_period, profit_accumulated, withdrawable_amount, total_balance, status
    ) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
";

$investment_stmt = $conn->prepare($investment_query);

$lockInPeriod = $cycle_days ?? 30; // Default to 30 days if not set
$profitAccumulated = 0.00;
$withdrawableAmount = 0.00;
$totalBalance = 0.00;

$investment_stmt->bind_param(
    "iiddssdddd", 
    $clientId, $productId, $price, $dailyProfit, $startDate, 
    $maturityDate, $lockInPeriod, $profitAccumulated, $withdrawableAmount, $totalBalance
);

$investment_stmt->execute();

    // Log the transaction
    $transaction_query = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, 'compound', ?, NOW())";
    $transaction_stmt = $conn->prepare($transaction_query);
    $transaction_stmt->bind_param("id", $clientId, $price);
    $transaction_stmt->execute();

    // Redirect with success message
    echo "<script>
        alert('Purchase successful!\\nYour investment will mature on: $maturityDate');
        window.location.href = 'view_investments.php';
    </script>";
    exit;
} else {
    // If insufficient balance
    echo "<script>
        alert('Insufficient balance. Please top up your account.');
        window.location.href = 'view_products.php';
    </script>";
    exit;
}

// Update the status of completed investments
$today = date('Y-m-d');
$update_investments_query = "UPDATE compound_investments 
    SET status = 'completed', withdrawable_amount = total_balance 
    WHERE maturity_date < ? AND status = 'active'";
$update_stmt = $conn->prepare($update_investments_query);
$update_stmt->bind_param("s", $today);
$update_stmt->execute();
?>
