<?php
session_start();

// Database connection
require_once('../../includes/db_connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check database connection
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Get form input values
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_new_password = $_POST['confirm_password'] ?? '';

// Validate input fields
if (empty($current_password) || empty($new_password) || empty($confirm_new_password)) {
    header('Location: admin_dashboard.php?message=' . urlencode("All fields are required"));
    exit();
}

// Fetch the current password from the database
$query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // Verify current password
    if (password_verify($current_password, $user['password'])) {
        // Check if new password matches the confirmation
        if ($new_password === $confirm_new_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the password in the database
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $hashed_password, $user_id);

            if ($update_stmt->execute()) {
                header('Location: admin_dashboard.php?message=' . urlencode("Password updated successfully"));
                exit();
            } else {
                die("Error updating password: " . $update_stmt->error);
            }
        } else {
            header('Location: admin_dashboard.php?message=' . urlencode("New passwords do not match"));
            exit();
        }
    } else {
        header('Location: admin_dashboard.php?message=' . urlencode("Incorrect current password"));
        exit();
    }
} else {
    header('Location: admin_dashboard.php?message=' . urlencode("User not found"));
    exit();
}
?>