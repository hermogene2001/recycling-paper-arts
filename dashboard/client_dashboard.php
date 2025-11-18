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

// Close the database connection

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            margin-top: 80px; /* Adding margin to avoid overlap with the fixed navbar */
            margin-bottom: 80px; /* Adding margin to avoid overlap with the footer */
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

    <!-- Include the navigation bar -->
    <?php include('nav.php'); ?>

    <div class="container">
        <!-- Dashboard Content -->
        <?php
$notificationQuery = "SELECT id, message, created_at FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($notificationQuery);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

while ($notification = $result->fetch_assoc()) {
    echo "<div class='notification'>";
    echo "<p>" . htmlspecialchars($notification['message']) . "</p>";
    echo "<small>Received on: " . $notification['created_at'] . "</small>";
    echo "</div>";
}

// Mark notifications as read (optional)
$markReadQuery = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
$markReadStmt = $conn->prepare($markReadQuery);
$markReadStmt->bind_param("i", $_SESSION['user_id']);
$markReadStmt->execute();
?>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3>Welcome, <?php echo $user['first_name']; ?>!</h3>
                    </div>
                    <div class="card-body">
                        <h5>Account Details</h5>
                        <p><strong>Phone Number:</strong> <?php echo $user['phone_number']; ?></p>
                        <p><strong>Balance:</strong> <?php echo number_format($user['balance'], 2)." "; ?><b>RWF</b></p>
                        <p><strong>Referral Code:</strong> <?php echo $user['referral_code']; ?></p>
                        <p><strong>Invitation Code:</strong> <?php echo $user['invitation_code']; ?></p>
                    </div>
                </div>

                <!-- Quick Access Links -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Quick Access</h4>
                    </div>
                    <div class="card-body">
                        <a href="view_investments.php" class="btn btn-primary">View My Investments</a>
                        <a href="profile.php" class="btn btn-secondary">Edit Profile</a>
                    </div>
                </div>
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
