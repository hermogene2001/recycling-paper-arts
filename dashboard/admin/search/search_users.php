<?php
require_once('../../../includes/db_connection.php');

$queryString = isset($_POST['query']) ? trim($_POST['query']) : '';
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$sql = "SELECT id, first_name, last_name, phone_number FROM users WHERE role != 'admin'";

// Apply search filter if a query is provided
$params = [];
$types = "";

if (!empty($queryString)) {
    $sql .= " AND (phone_number LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
    $searchParam = "%$queryString%";
    array_push($params, $searchParam, $searchParam, $searchParam);
    $types .= "sss";
}

$sql .= " LIMIT ?, ?";
array_push($params, $offset, $records_per_page);
$types .= "ii";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$users_data = "";
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users_data .= "<tr>
            <td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>
            <td>" . htmlspecialchars($row['phone_number']) . "</td>
            <td>
                <a href='view_user.php?id=" . $row['id'] . "' class='btn btn-primary'>View</a>
                <a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-warning'>Edit</a>
                <a href='delete_user.php?id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>
            </td>
        </tr>";
    }
} else {
    $users_data = "<tr><td colspan='3' class='text-center'>No users found.</td></tr>";
}

// Get total records count
$total_query = "SELECT COUNT(*) AS total FROM users WHERE role != 'admin'";
if (!empty($queryString)) {
    $total_query .= " AND (phone_number LIKE ? OR first_name LIKE ? OR last_name LIKE ?)";
}

$total_stmt = $conn->prepare($total_query);
if (!empty($queryString)) {
    $total_stmt->bind_param("sss", $searchParam, $searchParam, $searchParam);
}
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);

// Generate pagination links
$pagination = "";
if ($total_pages > 1) {
    $pagination .= "<li class='page-item " . ($page <= 1 ? "disabled" : "") . "'>
                        <a class='page-link' href='#' data-page='1'>First</a>
                    </li>";

    $pagination .= "<li class='page-item " . ($page <= 1 ? "disabled" : "") . "'>
                        <a class='page-link' href='#' data-page='" . ($page - 1) . "'>Previous</a>
                    </li>";

    for ($i = 1; $i <= $total_pages; $i++) {
        $pagination .= "<li class='page-item " . ($page == $i ? "active" : "") . "'>
                            <a class='page-link' href='#' data-page='$i'>$i</a>
                        </li>";
    }

    $pagination .= "<li class='page-item " . ($page >= $total_pages ? "disabled" : "") . "'>
                        <a class='page-link' href='#' data-page='" . ($page + 1) . "'>Next</a>
                    </li>";

    $pagination .= "<li class='page-item " . ($page >= $total_pages ? "disabled" : "") . "'>
                        <a class='page-link' href='#' data-page='$total_pages'>Last</a>
                    </li>";
}

// Return JSON response
echo json_encode([
    "users" => $users_data,
    "pagination" => $pagination
]);

$stmt->close();
$total_stmt->close();
$conn->close();
?>
