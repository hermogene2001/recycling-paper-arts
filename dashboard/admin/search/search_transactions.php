<?php
require_once('../../../includes/db_connection.php');

$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// SQL Query for search
$transactions_query = "
    SELECT t.*, u.phone_number 
    FROM transactions t 
    JOIN users u ON t.user_id = u.id 
    WHERE u.phone_number LIKE ? OR t.transaction_type LIKE ?
    ORDER BY t.transaction_type, t.transaction_date DESC
";

// Prepare statement
$stmt = $conn->prepare($transactions_query);
$search = '%' . $search_query . '%';
$stmt->bind_param('ss', $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$current_type = null;

// Generate table rows dynamically
if ($result->num_rows > 0) {
    while ($transaction = $result->fetch_assoc()) {
        if ($current_type !== $transaction['transaction_type']) {
            $current_type = $transaction['transaction_type'];
            echo "<tr class='table-primary'><td colspan='5'><strong>" . ucfirst(htmlspecialchars($current_type)) . " Transactions</strong></td></tr>";
        }
        echo "<tr>
                <td>" . htmlspecialchars($transaction['id']) . "</td>
                <td>" . htmlspecialchars($transaction['phone_number']) . "</td>
                <td>" . number_format($transaction['amount'], 2) . "</td>
                <td>" . ucfirst(htmlspecialchars($transaction['transaction_type'])) . "</td>
                <td>" . htmlspecialchars($transaction['transaction_date']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No transactions found.</td></tr>";
}

$stmt->close();
$conn->close();
?>
