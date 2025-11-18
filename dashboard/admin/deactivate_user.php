<?php
session_start();
require_once('../../includes/db_connection.php');

// Ensure the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Check if user ID is provided
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Update user status to inactive
    $query = "UPDATE users SET status = 'inactive' WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    
    if ($stmt->execute()) {
        header("Location: view_user.php?id=$userId&message=User deactivated successfully");
    } else {
        echo "Error deactivating user.";
    }

    $stmt->close();
    $conn->close();
} else {
    echo "No user ID provided.";
}
?>
