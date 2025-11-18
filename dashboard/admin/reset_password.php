<?php
session_start();
require_once('../../includes/db_connection.php');

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

if (isset($_GET['id']) && $_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_GET['id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if passwords match
    if ($newPassword !== $confirmPassword) {
        echo "<script>alert('Passwords do not match!'); history.back();</script>";
        exit();
    }

    // Ensure a strong password (at least 8 characters)
    if (strlen($newPassword) < 8) {
        echo "<script>alert('Password must be at least 8 characters long!'); history.back();</script>";
        exit();
    }

    // Hash the password before saving
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $query = "UPDATE users SET password = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $passwordHash, $userId);

    if ($stmt->execute()) {
        echo "<script>alert('Password updated successfully!'); window.location.href='view_user.php?id=$userId';</script>";
    } else {
        echo "<script>alert('Error updating password!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Reset Password</h2>
        <form method="POST" onsubmit="return validatePassword()">
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Reset Password</button>
            <a href="view_user.php?id=<?= $_GET['id'] ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        function validatePassword() {
            let password = document.getElementById("new_password").value;
            let confirmPassword = document.getElementById("confirm_password").value;

            if (password.length < 8) {
                alert("Password must be at least 8 characters long.");
                return false;
            }
            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
