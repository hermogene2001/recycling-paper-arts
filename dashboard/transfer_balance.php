<?php
session_start();
include '../includes/db_connection.php'; // Include database connection
include '../includes/function.php'; // Include helper functions

// Ensure the user is logged in and has a client role
if ($_SESSION['role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

// Fetch user ID
$userId = $_SESSION['user_id'];

// Retrieve the total withdrawable amount from all compound investments
$sql = "SELECT SUM(withdrawable_amount) AS total_withdrawable FROM compound_investments WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$totalWithdrawable = $row['total_withdrawable'] ?? 0; // Default to 0 if null

if ($totalWithdrawable > 0) {
    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Deduct the total withdrawable amount from compound investments
        $sql = "UPDATE compound_investments 
                SET withdrawable_amount = 0 
                WHERE client_id = ? AND withdrawable_amount > 0";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        if ($stmt->affected_rows < 1) {
            throw new Exception("No withdrawable balance found.");
        }

        // Add the total withdrawable amount to the user's main balance
        $sql = "UPDATE users 
                SET balance = balance + ? 
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("di", $totalWithdrawable, $userId);
        $stmt->execute();

        if ($stmt->affected_rows < 1) {
            throw new Exception("Failed to update main balance.");
        }

        // Commit the transaction
        $conn->commit();

        // Redirect with success message
        $_SESSION['success_message'] = "Successfully transferred RWF " . number_format($totalWithdrawable, 2) . " to your main balance.";
        header("Location: setting.php");
        exit;
    } catch (Exception $e) {
        // Rollback the transaction on error
        $conn->rollback();

        // Redirect with error message
        $_SESSION['error_message'] = "Transfer failed: " . $e->getMessage();
        header("Location: setting.php");
        exit;
    }
} else {
    $_SESSION['error_message'] = "No available balance to transfer.";
    header("Location: setting.php");
    exit;
}
