<?php
session_start();

// Check if the user is logged in and has the 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client') {
    header("Location: ../index.php"); // Redirect to login page if not logged in or not a client
    exit();
}

// Include your database connection
include('../includes/db_connection.php');

// Fetch user information from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newFirstName = trim($_POST['first_name']);
    $newLastName = trim($_POST['last_name']);
    $newPhoneNumber = trim($_POST['phone_number']);
    $newPassword = trim($_POST['password']);

    // Validate phone number format
    if (!preg_match('/^(078|073|072|2507|\+2507)\d{6,10}$/', $newPhoneNumber)) {
        $message = "Invalid phone number format. Please check the number.";
    } elseif (!empty($newPassword) && strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        // Prepare fields for update
        $updateFields = "first_name = ?, last_name = ?, phone_number = ?";
        $params = [$newFirstName, $newLastName, $newPhoneNumber];
        $types = "sss";

        // If the user provided a new password, hash it and add to the update
        if (!empty($newPassword)) {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateFields .= ", password = ?";
            $params[] = $newPasswordHash;
            $types .= "s";
        }

        // Add user ID for the WHERE clause
        $params[] = $user_id;
        $types .= "i";

        // Update the user's information in the database
        $updateSql = "UPDATE users SET $updateFields WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param($types, ...$params);

        if ($updateStmt->execute()) {
            $message = "Profile updated successfully!";
        } else {
            $message = "Failed to update profile. Please try again.";
        }

        $updateStmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile | DeltaOne Investment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            padding: 10px;
            margin: 0;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin-top: 80px;
            margin-bottom: 80px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #2575fc;
            color: #fff;
        }
        footer {
            background-color: #2575fc;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Navigation Bar -->
        <?php include('nav.php'); ?>

        <!-- Profile Form -->
        <div class="card">
            <div class="card-header">
                <h3>Update Your Profile</h3>
            </div>
            <div class="card-body">
                <?php if (isset($message)): ?>
                    <div class="alert alert-info">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="profile">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>">
                    </div>
                    <div class="mb-3">
                        <label for="phone_number" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2025 DeltaOneInvestment. All Rights Reserved.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>