<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Transactions</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            padding-top: 70px; /* Space for the fixed navbar */
        }

        .navbar {
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        table {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Fixed Navigation Bar -->
    <?php include('../../includes/admin_nav.php'); ?>

<?php

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL Query to fetch transactions, ordered by transaction type and date
$transactions_query = "
    SELECT t.*, u.phone_number 
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    WHERE u.phone_number LIKE ? OR t.transaction_type LIKE ?
    ORDER BY t.transaction_type, t.transaction_date DESC
";

// Prepare statement
$stmt = $conn->prepare($transactions_query);

// Bind search parameter
$search = '%' . $search_query . '%';
$stmt->bind_param('ss', $search, $search);
$stmt->execute();
$transactions_result = $stmt->get_result();

$current_type = null; // To track grouped transactions
?>

<div class="container mt-5">
    <h2>All Transactions</h2>

    <!-- Search Form -->
    <form id="searchForm" method="GET" class="mb-3">
        <input type="text" name="search" id="searchInput" class="form-control" 
               value="<?= htmlspecialchars($search_query); ?>" 
               placeholder="Search by phone number or transaction type">
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>User Phone</th>
                <th>Amount</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody id="transactionsTable">
            <?php while ($transaction = $transactions_result->fetch_assoc()) { 
                if ($current_type !== $transaction['transaction_type']) {
                    // Display a new section header when transaction type changes
                    $current_type = $transaction['transaction_type'];
            ?>
                <tr class="table-primary">
                    <td colspan="5"><strong><?= ucfirst(htmlspecialchars($current_type)); ?> Transactions</strong></td>
                </tr>
            <?php } ?>
                <tr>
                    <td><?= htmlspecialchars($transaction['id']); ?></td>
                    <td><?= htmlspecialchars($transaction['phone_number']); ?></td>
                    <td><?= number_format($transaction['amount'], 2); ?></td>
                    <td><?= ucfirst(htmlspecialchars($transaction['transaction_type'])); ?></td>
                    <td><?= htmlspecialchars($transaction['transaction_date']); ?></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<!-- jQuery for AJAX -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Live search functionality
    $('#searchInput').on('input', function() {
        var query = $(this).val();

        $.ajax({
            url: 'search/search_transactions.php', // Handles live search
            method: 'GET',
            data: { search: query },
            success: function(response) {
                $('#transactionsTable').html(response);
            }
        });
    });
});
</script>

<?php
$stmt->close();
$conn->close();
?>

</body>
</html>
