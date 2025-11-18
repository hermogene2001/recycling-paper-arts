<?php
session_start();

// Check if the user is logged in and has the 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client') {
    header("Location: ../index.php"); // Redirect to login page if not logged in or not a client
    exit();
}

// Include your database connection
include('../includes/db_connection.php');

$user_id = $_SESSION['user_id'];

// Fetch transaction history for the logged-in user
$sql = "SELECT transaction_type, amount, transaction_date
        FROM transactions 
        WHERE user_id = ? 
        ORDER BY transaction_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - DeltaOneInvestment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            flex: 1;
            margin-top: 80px;
            margin-bottom: 80px;
        }
        .table th {
            background-color: #2575fc;
            color: #fff;
        }
        footer {
            background-color: #2575fc;
            color: white;
            text-align: center;
            padding: 10px 0;
        }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
<div class="container">
    <h3 class="text-center mb-4">Transaction History</h3>

    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Transaction Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo ucfirst($row['transaction_type']); ?></td>
                        <td><?php echo number_format($row['amount'], 2); ?> RWF</td>
                        <td><?php echo date("Y-m-d H:i:s", strtotime($row['transaction_date'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No transactions found.</p>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2025 DeltaOneInvestment. All Rights Reserved.</p>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
