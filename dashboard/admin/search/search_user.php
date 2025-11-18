<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once('../../../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';

    $query = "SELECT id, first_name, last_name, phone_number, balance FROM users WHERE phone_number LIKE ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
        $searchValue = "%{$phone}%";
        $stmt->bind_param('s', $searchValue);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                <td>" . htmlspecialchars($row['phone_number']) . "</td>
                <td>" . htmlspecialchars($row['balance']) . "</td>
                <td>
                    <a href='view_user.php?id=" . $row['id'] . "' class='btn btn-primary'>View</a>
                    <a href='edit_user.php?id=" . $row['id'] . "' class='btn btn-warning'>Edit</a>
                    <a href='delete_user.php?id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure?\")'>Delete</a>
                </td>
            </tr>";
        }
    } else {
        die("Query error: " . $conn->error);
    }
}
?>
