<?php
session_start();
if ($_SESSION['role'] !== 'agent') {
    header("Location: ../../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 50px;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .search-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include('nav.php'); ?>
    <div class="container">
        <h2 class="text-center mb-4">Agent Dashboard</h2>
        
        <!-- 🔍 Search Box -->
        <div class="search-container">
            <input type="text" id="searchInput" class="form-control" placeholder="Search by phone number...">
        </div>

        <div class="row">
            <!-- Pending Recharges -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning text-dark">Pending Recharges</div>
                    <div class="card-body">
                        <ul class="list-group" id="rechargesList"></ul>
                    </div>
                </div>
            </div>
            
            <!-- Pending Withdrawals -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-danger text-white">Pending Withdrawals</div>
                    <div class="card-body">
                        <ul class="list-group" id="withdrawalsList"></ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🔥 jQuery Live Search Script -->
    <script>
        $(document).ready(function () {
            function loadRecords(searchValue = '') {
                $.ajax({
                    url: "search.php",
                    type: "POST",
                    data: { search: searchValue },
                    dataType: "json",
                    success: function (response) {
                        $("#rechargesList").empty();
                        $("#withdrawalsList").empty();

                        response.forEach(function (item) {
                            let listItem = `
                                <li class='list-group-item d-flex justify-content-between align-items-center'>
                                    User: ${item.first_name} (Phone: ${item.phone_number}) - Amount: ${item.amount} RWF
                                    <div>
                                        <form method='POST' action='process_recharge_approve.php' onsubmit='return confirmAction("approve");'>
                                            <input type='hidden' name='${item.type === "recharge" ? "recharge_id" : "withdrawal_id"}' value='${item.id}'>
                                            <input type='hidden' name='action' value='approve'>
                                            <button type='submit' class='btn btn-success btn-sm'>Approve</button>
                                        </form>
                                        <form method='POST' action='process_recharge_approve.php' onsubmit='return confirmAction("reject");'>
                                            <input type='hidden' name='${item.type === "recharge" ? "recharge_id" : "withdrawal_id"}' value='${item.id}'>
                                            <input type='hidden' name='action' value='reject'>
                                            <button type='submit' class='btn btn-danger btn-sm'>Reject</button>
                                        </form>
                                    </div>
                                </li>`;

                            if (item.type === "recharge") {
                                $("#rechargesList").append(listItem);
                            } else {
                                $("#withdrawalsList").append(listItem);
                            }
                        });
                    }
                });
            }

            // Load all records on page load
            loadRecords();

            // Filter results when typing in the search box
            $("#searchInput").on("input", function () {
                let searchValue = $(this).val();
                loadRecords(searchValue);
            });
        });

        function confirmAction(action) {
            return confirm("Are you sure you want to " + action + " this request?");
        }
    </script>
</body>
</html>
