<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Check if the user ID is provided in the URL
if (isset($_GET['id'])) {
    $userId = $_GET['id'];

    // Fetch user details based on the user ID to confirm the user exists
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user is found
    if ($result->num_rows === 0) {
        echo "User not found.";
        exit();
    }

    // Delete user from the database
    $deleteQuery = "DELETE FROM users WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param('i', $userId);

    if ($deleteStmt->execute()) {
        // Redirect to manage users page after deletion
        header("Location: manage_users.php");
        exit();
    } else {
        echo "Failed to delete user.";
    }
} else {
    echo "No user ID provided.";
    exit();
}
?>
