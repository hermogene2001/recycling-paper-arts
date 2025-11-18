<?php
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Include database connection
require_once('../../includes/db_connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data and sanitize it
    $phone_number = mysqli_real_escape_string($conn, $_POST['phone_number']);
    $name = mysqli_real_escape_string($conn, $_POST['fname']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $referral_code = mysqli_real_escape_string($conn, $_POST['referral_code']);
    
    // Validate phone number
    if (empty($phone_number) || empty($password) || empty($name)) {
        header('Location: manage_users.php?message=All fields are required&message_type=danger');
        exit();
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new agent into the database
    $insert_query = "INSERT INTO users (first_name,phone_number, password, referral_code, role) VALUES ('$name','$phone_number', '$hashed_password', '$referral_code', 'agent')";
    
    if (mysqli_query($conn, $insert_query)) {
        // Redirect with success message
        header('Location: manage_users.php?message=Agent created successfully&message_type=success');
    } else {
        // Redirect with error message
        header('Location: admin_dashboard.php?message=Error creating agent&message_type=danger');
    }
}
?>
