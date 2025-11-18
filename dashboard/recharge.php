<?php
session_start();

// Ensure the user is logged in as a client
if ($_SESSION['role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

date_default_timezone_set("Africa/Kigali"); 

// Database connection
require_once('../includes/db_connection.php');
include '../includes/function.php';

// Minimum deposit amount
$min_deposit = 3000;
$max_deposit = 3000000;

// Initialize variables
$selected_agent = null;
$success_message = null;
$error_message = null;

// Handle the recharge form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = floatval($_POST['amount']);
    $client_id = $_SESSION['user_id'];
    $agent_id = intval($_POST['agent']);  // Selected agent

    // Validate the amount
    if ($amount < $min_deposit || $amount > $max_deposit) {
        $error_message = "The minimum deposit amount is 3,000 RWF and the maximum deposit amount is 3000,000 RWF. Please enter a valid amount.";
    } elseif ($amount > 0) {
        // Insert recharge record in the recharges table with status 'pending'
        $insert_recharge_query = "INSERT INTO recharges (client_id, agent_id, amount, status) VALUES (?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $insert_recharge_query);
        mysqli_stmt_bind_param($stmt, "iid", $client_id, $agent_id, $amount);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Simulate notification to agent (you could send an actual email or SMS here)
        $success_message = 'Recharge request successful! Copy that number and send money to that number.';

        // Fetch selected agent's information
        $agent_query = "SELECT phone_number, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $agent_query);
        mysqli_stmt_bind_param($stmt, "i", $agent_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $selected_agent_phone, $selected_agent_name);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $selected_agent = [
            'name' => $selected_agent_name,
            'phone' => $selected_agent_phone
        ];
    } else {
        $error_message = "Invalid amount. Please enter a positive number.";
    }
}

// Fetch the current balance
$current_balance_query = "SELECT balance FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $current_balance_query);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $current_balance);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Fetch a random agent (single random agent)
$random_agent_query = "SELECT id, phone_number, CONCAT(first_name, ' ', last_name) AS name FROM users WHERE role = 'agent' ORDER BY RAND() LIMIT 1";
$stmt = mysqli_prepare($conn, $random_agent_query);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $random_agent_id, $random_agent_phone, $random_agent_name);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

$random_agent = [
    'id' => $random_agent_id,
    'phone' => $random_agent_phone,
    'name' => $random_agent_name
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recharge - DeltaOne Investment</title>
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            padding: 10px;
            margin: 0;
        }
        .container { margin-top: 100px; }
        .alert { text-align: center; }
        .btn-primary { background: linear-gradient(135deg, #6a11cb, #2575fc); border: none; }
        .copy-btn { cursor: pointer; }
        .copy-btn:active { opacity: 0.7; }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
    <div class="container">
        
        <div class="card">
            <div class="card-header">
                <h4>Recharge Your Account</h4>
                <h5 class="text-primary">Balance: <b><?php echo number_format($current_balance, 2); ?> RWF</b></h5>
            </div>
            <div class="card-body">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo $success_message; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="alert alert-danger">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <h5>Why do you need to recharge?</h5>
                <p>Recharging your account is necessary to maintain your active investment status. The recharge amount will be used for your investment in our program, where you earn daily profits based on the amount you invest. Once the payment is processed, you can start earning immediately!</p>

                <h5>How does it work?</h5>
                <p>After you make a deposit, we process the transaction and update your account balance. A random agent is assigned to receive your payment, and they will confirm the transaction on our system. The recharge will be marked as "pending" until verified.</p>

                <?php if ($selected_agent): ?>
                    <div class="alert alert-info">
                        <h5>Send your recharge amount to the following agent:</h5>
                        <p><strong>Agent Name:</strong> <?php echo $selected_agent['name']; ?></p>
                        <p><strong>Phone Number:</strong> <span id="phoneNumber"><?php echo $selected_agent['phone']; ?></span></p>
                        <button class="btn btn-outline-info copy-btn" onclick="copyPhoneNumber()">Copy Phone Number</button>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group mb-3">
                        <label for="amount">Recharge Amount (RWF):</label>
                        <input type="number" class="form-control" id="amount" name="amount" min="<?php echo $min_deposit; ?>" max="<?php echo $max_deposit; ?>" required>
                    </div>
                    <div class="form-group mb-3">
                        <label for="agent">Select an Agent:</label>
                        <input type= 'text' class="form-control" id="agent" name="agent" required readonly value="<?php echo $random_agent['id']; ?>"><?php echo $random_agent['name']; ?> - <?php echo $random_agent['phone']; ?>
                        </input>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Proceed to Recharge</button>
                </form>

                <div class="mt-3 text-center">
                    <a href="client_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function copyPhoneNumber() {
            var phoneNumber = document.getElementById("phoneNumber").textContent;
            navigator.clipboard.writeText(phoneNumber).then(function() {
                alert("Phone number copied to clipboard!");
            }, function() {
                alert("Failed to copy phone number.");
            });
        }
    </script>
</body>
</html>
