<?php
require_once('../../../includes/db_connection.php');

$phone_number = isset($_GET['phone_number']) ? $_GET['phone_number'] : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

// Query to get filtered investments with pagination
$query = "
    SELECT 
        investments.id, investments.user_id, investments.amount, investments.daily_profit, 
        investments.invested_at, investments.start_date, investments.end_date, investments.status,
        users.phone_number
    FROM investments
    JOIN users ON investments.user_id = users.id
    WHERE users.phone_number LIKE ?
    ORDER BY investments.invested_at DESC
    LIMIT ?, ?";

$stmt = $conn->prepare($query);
$phone_number_search = "%$phone_number%";
$stmt->bind_param('sii', $phone_number_search, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

$data = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data .= "<tr>
                <td>{$row['id']}</td>
                <td>{$row['user_id']}</td>
                <td>{$row['phone_number']}</td>
                <td>" . number_format($row['amount'], 2) . "</td>
                <td>" . number_format($row['daily_profit'], 2) . "</td>
                <td>{$row['invested_at']}</td>
                <td>{$row['start_date']}</td>
                <td>{$row['end_date']}</td>
                <td>" . ucfirst($row['status']) . "</td>
              </tr>";
    }
} else {
    $data = "<tr><td colspan='9' class='text-center'>No investments found.</td></tr>";
}

// Get total records count
$count_query = "
    SELECT COUNT(*) AS total FROM investments 
    JOIN users ON investments.user_id = users.id
    WHERE users.phone_number LIKE ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param('s', $phone_number_search);
$stmt->execute();
$count_result = $stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);

// Generate pagination buttons
$pagination = "";
if ($total_pages > 1) {
    for ($i = 1; $i <= $total_pages; $i++) {
        $activeClass = ($i == $page) ? 'active' : '';
        $pagination .= "<li class='page-item $activeClass'><a class='page-link' href='#' onclick='fetchData($i)'>$i</a></li>";
    }
}

$response = [
    'data' => $data,
    'pagination' => $pagination
];

echo json_encode($response);
?>
