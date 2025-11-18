<?php
include '../../includes/db_connection.php';
session_start();

$agent_id = $_SESSION['user_id'];
$searchTerm = isset($_POST['search']) ? $_POST['search'] : '';

// 🔍 Search for pending recharges and withdrawals, ordered by newest first
$sql = "
    (SELECT 'recharge' AS type, r.id, r.amount, u.first_name, u.phone_number, '' AS bank_name, '' AS account_number 
    FROM recharges r 
    JOIN users u ON r.client_id = u.id 
    WHERE r.status = 'pending' AND r.agent_id = ? AND u.phone_number LIKE ?
    ORDER BY r.id DESC)
    UNION
    (SELECT 'withdrawal' AS type, w.id, w.amount, u.first_name, u.phone_number, b.bank_name, b.account_number 
    FROM withdrawals w 
    JOIN users u ON w.client_id = u.id 
    JOIN user_banks b ON u.id = b.user_id 
    WHERE w.status = 'pending' AND u.phone_number LIKE ?
    ORDER BY w.id DESC)";

$stmt = $conn->prepare($sql);
$searchTermWildcard = "%" . $searchTerm . "%";
$stmt->bind_param("iss", $agent_id, $searchTermWildcard, $searchTermWildcard);
$stmt->execute();
$result = $stmt->get_result();

// Convert results to JSON and return
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>
