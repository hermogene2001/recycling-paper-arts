<?php
session_start();
require_once('../includes/db_connection.php');

if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You must log in first.'); window.location.href = '../index.php';</script>";
    exit;
}

if (!isset($_GET['id'])) {
    echo "<script>alert('Product ID is missing in the URL.'); window.location.href = 'client_dashboard.php';</script>";
    exit;
}

$clientId = $_SESSION['user_id'];
$productId = $_GET['id']; // Corrected to match the URL parameter

// Fetch product details
$product_query = "SELECT price, daily_earning, cycle FROM products WHERE id = '$productId'";
$product_result = mysqli_query($conn, $product_query);

if (!$product_result) {
    die("Query failed: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($product_result);

if (!$product) {
    echo "<script>alert('No product found with ID: $productId'); window.location.href = '../views/client_dashboard.php';</script>";
    exit;
}
else {
    $price = $product['price'];
    $cycle_days = $product['cycle'];
    $dailyEarning = $product['daily_earning'];

    // Fetch user balance
    $user_query = "SELECT balance FROM users WHERE id = '$clientId'";
    $user_result = mysqli_query($conn, $user_query);
    $user = mysqli_fetch_assoc($user_result);
    $user_balance = $user['balance'];

    // Check if the user has enough balance to make the purchase
    if ($user_balance >= $price) {
        // Deduct the product price from the user's balance
        $new_balance = $user_balance - $price;
        $update_balance_query = "UPDATE users SET balance = '$new_balance' WHERE id = '$clientId'";
        mysqli_query($conn, $update_balance_query);

        // Record the transaction for the purchase
        $transaction_query = "INSERT INTO transactions (user_id, transaction_type, amount, transaction_date) VALUES (?, 'purchase', ?, NOW())";
        $stmt = $conn->prepare($transaction_query);
        $stmt->bind_param("id", $clientId, $price);
        $stmt->execute();

        // Calculate purchase start and end dates based on the investment cycle
        $purchase_datetime = date('Y-m-d H:i:s');
        $end_datetime = date('Y-m-d H:i:s', strtotime("+$cycle_days days", strtotime($purchase_datetime)));

        // Record the purchase details
        $insert_purchase_query = "INSERT INTO purchases (client_id, product_id, purchase_date, end_datetime, last_earned) 
                                  VALUES ('$clientId', '$productId', '$purchase_datetime', '$end_datetime', '$purchase_datetime')";
        mysqli_query($conn, $insert_purchase_query);

        // Add the investment details
        $investment_query = "INSERT INTO investments (user_id, amount, invested_at, start_date, end_date, status, daily_profit, last_profit_update) 
                             VALUES ('$clientId', '$price', NOW(), '$purchase_datetime', '$end_datetime', 'active', '0.00', '$purchase_datetime')";
        mysqli_query($conn, $investment_query);

        // Display a success message and redirect
        echo "<script>
            alert('Purchase and investment successful!\\nYour investment end date is: $end_datetime');
            window.location.href = 'view_investments.php';
        </script>";
        exit;
    } else {
        // If the user does not have enough balance
        echo "<script>
            alert('Insufficient balance.');
            window.location.href = 'view_products.php';
        </script>";
        exit;
    }
} 
// else {
//     // If the product does not exist
//     echo "<script>
//         alert('Product not found.');
//         window.location.href = 'view_products.php';
//     </script>";
//     exit;
// }

// Update the status of completed investments
$today = date('Y-m-d');
$update_investments_query = "UPDATE investments SET status = 'completed' WHERE end_date < ? AND status = 'active'";
$stmt = $conn->prepare($update_investments_query);
$stmt->bind_param("s", $today);
$stmt->execute();

// Investment status update success
echo "<script>
    alert('Investment statuses updated successfully.');
</script>";
?>
