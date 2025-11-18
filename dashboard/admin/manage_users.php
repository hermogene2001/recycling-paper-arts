<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Pagination logic
$records_per_page = 50;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

// Fetch users with pagination
$query = "SELECT id, first_name, last_name, phone_number, balance FROM users WHERE role != 'admin' LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('ii', $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Get total number of records for pagination
$total_query = "SELECT COUNT(*) AS total FROM users WHERE role != 'admin'";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_records = $total_row['total'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            font-family: Arial, sans-serif;
            /* padding-top: 70px; */
        }

        .container {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Manage Users</h2>

        
<div class="d-flex justify-content-between align-items-center mt-3">
    <nav>
        <ul class="pagination">
            <!-- Previous Button -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a>
            </li>

            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($current_page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>
    <span>Total Users: <?= $total_records ?></span>
</div>

<input type="text" id="searchPhone" class="form-control mb-3" placeholder="Search by phone number">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Balance</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                        <td><?= htmlspecialchars($row['phone_number']) ?></td>
                        <td><?= htmlspecialchars($row['balance']) ?></td>
                        <td>
                            <a href="view_user.php?id=<?= $row['id'] ?>" class="btn btn-primary">View</a>
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-warning">Edit</a>
                            <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="d-flex justify-content-between align-items-center mt-3">
    <nav>
        <ul class="pagination">
            <!-- Previous Button -->
            <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page - 1 ?>">Previous</a>
            </li>

            <!-- Page Numbers -->
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= ($current_page == $i) ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <!-- Next Button -->
            <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $current_page + 1 ?>">Next</a>
            </li>
        </ul>
    </nav>

    <span>Total Users: <?= $total_records ?></span>
</div>

    </div>

    <script>
        $(document).ready(function () {
            $('#searchPhone').on('keyup', function () {
                let searchValue = $(this).val();
                $.ajax({
                    url: 'search/search_user.php',
                    method: 'POST',
                    data: { phone: searchValue },
                    success: function (response) {
                        $('#userTable').html(response);
                    }
                });
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
