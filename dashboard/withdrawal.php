<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

include '../includes/db_connection.php';

$userId = $_SESSION['user_id'];
$successMessage = $errorMessage = "";

// Get the user's current balance
$sql = "SELECT balance FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($current_balance);
$stmt->fetch();
$stmt->close();

// Check if the user has bank details
$sql = "SELECT id FROM user_banks WHERE user_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    header("Location: binding_bank.php");
    exit;
}
$stmt->close();

// Check the last withdrawal date
$sql = "SELECT MAX(date) FROM withdrawals WHERE client_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($lastWithdrawal);
$stmt->fetch();
$stmt->close();

if ($lastWithdrawal) {
    $lastDate = date('Y-m-d', strtotime($lastWithdrawal));
    if ($lastDate === date('Y-m-d')) {
        $errorMessage = "You can only withdraw once per day.";
    }
}

// Handle withdrawal form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errorMessage)) {
    $amount = trim($_POST['amount']);
    $source = $_POST['source']; // 'compound' or 'main'

    // Validate withdrawal amount
    if (!is_numeric($amount) || $amount < 3000 || $amount > 3000000) {
        $errorMessage = "The withdrawal amount must be between 3,000 and 3,000,000.";
    } else {
        $conn->begin_transaction();
        try {
            if ($source === 'compound') {
                // Check if user has sufficient compound balance
                $sql = "SELECT id, withdrawable_amount FROM compound_investments 
                        WHERE client_id = ? AND status = 'completed' ORDER BY withdrawable_amount DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $userId);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows === 0) {
                    throw new Exception("No completed compound investments found.");
                }

                $stmt->bind_result($compoundInvestmentId, $compoundBalance);
                $stmt->fetch();

                if ($compoundBalance < $amount) {
                    throw new Exception("Insufficient compound balance for this withdrawal.");
                }

                // Deduct balance
                $newBalance = $compoundBalance - $amount;
                $sql = "UPDATE compound_investments SET withdrawable_amount = ? WHERE id = ?";
                $updateStmt = $conn->prepare($sql);
                $updateStmt->bind_param("di", $newBalance, $compoundInvestmentId);
                if (!$updateStmt->execute()) {
                    throw new Exception("Failed to update compound balance.");
                }

                // Record the withdrawal
                $sql = "INSERT INTO withdrawals (client_id, amount, date, source) VALUES (?, ?, NOW(), 'compound')";
                $withdrawStmt = $conn->prepare($sql);
                $withdrawStmt->bind_param("id", $userId, $amount);
                if (!$withdrawStmt->execute()) {
                    throw new Exception("Failed to insert withdrawal record.");
                }

                $successMessage = "Withdrawal of $amount successfully processed from Compound Investment!";
            } elseif ($source === 'main') {
                if ($current_balance < $amount) {
                    throw new Exception("Insufficient main account balance for this withdrawal.");
                }

                // Deduct from main balance
                $newBalance = $current_balance - $amount;
                $sql = "UPDATE users SET balance = ? WHERE id = ?";
                $updateStmt = $conn->prepare($sql);
                $updateStmt->bind_param("di", $newBalance, $userId);
                if (!$updateStmt->execute()) {
                    throw new Exception("Failed to update main account balance.");
                }

                // Record the withdrawal
                $sql = "INSERT INTO withdrawals (client_id, amount, date, source) VALUES (?, ?, NOW(), 'main')";
                $withdrawStmt = $conn->prepare($sql);
                $withdrawStmt->bind_param("id", $userId, $amount);
                if (!$withdrawStmt->execute()) {
                    throw new Exception("Failed to insert withdrawal record.");
                }

                $successMessage = "Withdrawal of $amount successfully processed from Main Account!";
            } else {
                throw new Exception("Invalid withdrawal source.");
            }

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            $errorMessage = $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Withdraw Funds | DeltaOne Investment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1e90ff, #00c1d4);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }

        .withdraw-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            animation: fadeIn 1s ease-in-out;
            margin-top: 80px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-text {
            font-size: 0.9rem;
            color: #555;
        }

        .form-control::placeholder {
            color: #888;
        }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
<div class="withdraw-card">
    <h4 class="text-center mb-4">Withdraw Funds</h4>
    <h5 class="text-primary">Balance: <b><?php echo number_format($current_balance, 2); ?> RWF</b></h5>
    
    <p class="info-text">
        Use this form to request a withdrawal of funds from your account. Please ensure your withdrawal amount is between <strong>3,000</strong> and <strong>3,000,000</strong>. You can only make one withdrawal per day.
    </p>

    <!-- Display success or error message -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success">
            <?php echo $successMessage; ?>
        </div>
    <?php elseif (!empty($errorMessage)): ?>
        <div class="alert alert-danger">
            <?php echo $errorMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Withdrawal Form -->
    <form action="withdrawal.php" method="POST" id="withdrawal-form">
    <div class="mb-3">
        <label for="amount" class="form-label">Withdrawal Amount</label>
        <input type="number" name="amount" id="amount" class="form-control" required placeholder="Enter Amount (in RWF)" min="3000" max="3000000">
        <small class="form-text text-muted">Minimum: 3,000 | Maximum: 3,000,000</small>
    </div>

    <div class="mb-3">
        <label for="source" class="form-label">Withdrawal Source</label>
        <select name="source" id="source" class="form-control" required>
            <option value="compound">Compound Investment</option>
            <option value="main">Main Account</option>
        </select>
        <small class="form-text text-muted">Choose whether to withdraw from your Compound Investment or Main Account.</small>
    </div>

    <button type="submit" class="btn btn-primary w-100" id="submit-btn">Submit</button>
</form>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        $('[data-toggle="tooltip"]').tooltip();

        // Prevent multiple form submissions
        $('#withdrawal-form').on('submit', function() {
            $('#submit-btn').attr('disabled', true).text('Processing...');
        });
    });
</script>
</body>
</html>
