<?php 
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

// Database connection
require_once('../includes/db_connection.php');

// Fetch the logged-in user's ID
$userId = $_SESSION['user_id'];


// Fetch all users who were referred by this user (i.e., where invitation_code = userId)
$sql = "SELECT id, first_name, phone_number, created_at FROM users WHERE invitation_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Store the team members in an array
$teamMembers = [];
while ($row = $result->fetch_assoc()) {
    $teamMembers[] = $row;
}

// Close statement and database connection
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Team | DeltaOneInvestment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            margin-top: 80px;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1200px;
        }
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            text-align: center;
        }
        .card-title {
            font-size: 1.5rem;
        }
        .card-footer {
            background-color: #2575fc;
            color: white;
        }
        footer {
            background-color: #2575fc;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: relative;
            margin-top: auto;
            width: 100%;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<?php include('nav.php'); ?>

<div class="container">
    <h2 class="text-center mb-4">My Team</h2>

    <?php if (!empty($teamMembers)): ?>
        <div class="row">
            <?php foreach ($teamMembers as $member): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($member['first_name']); ?></h5>
                            <p class="card-text">Phone: <?= htmlspecialchars($member['phone_number']); ?></p>
                            <p class="card-text">Joined On: <?= date('F j, Y', strtotime($member['created_at'])); ?></p>
                        </div>
                        <div class="card-footer">
                            <small>Referral Code: <?= htmlspecialchars($member['referral_code']); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-center">You currently have no team members.</p>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer>
    <p>&copy; <?= date('Y'); ?> DeltaOneInvestment. All Rights Reserved.</p>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
