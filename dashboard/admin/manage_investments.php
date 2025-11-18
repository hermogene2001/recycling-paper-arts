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
    <title>Investments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .pagination {
            justify-content: center;
        }
    </style>
</head>
<body>

    <!-- Fixed Navigation Bar -->
    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">Investment Records</h2>

        <!-- Search Form -->
        <form id="searchForm" method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" id="phone_number" class="form-control" placeholder="Search by Phone Number" onkeyup="fetchData(1)" required>
            </div>
        </form>

        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>User ID</th>
                    <th>Phone Number</th>
                    <th>Amount</th>
                    <th>Daily Profit</th>
                    <th>Invested At</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="investmentTableBody">
                <!-- Table rows will be populated dynamically -->
            </tbody>
        </table>

        <!-- Pagination -->
        <nav>
            <ul class="pagination" id="pagination">
                <!-- Pagination buttons will be dynamically populated -->
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function fetchData(page) {
            const searchQuery = document.getElementById("phone_number").value;
            
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "search/search_investments.php?phone_number=" + searchQuery + "&page=" + page, true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    const response = JSON.parse(xhr.responseText);
                    document.getElementById("investmentTableBody").innerHTML = response.data;
                    document.getElementById("pagination").innerHTML = response.pagination;
                }
            };
            xhr.send();
        }

        // Load first page on page load
        window.onload = function() {
            fetchData(1);
        };
    </script>
</body>
</html>
