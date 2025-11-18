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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize user input
    $bankName = trim($_POST['bank_name']);
    $accountNumber = trim($_POST['account_number']);
    $accountHolder = trim($_POST['account_holder']);

    // Validate input
    if (empty($bankName) || empty($accountNumber) || empty($accountHolder)) {
        $errorMessage = "All fields are required.";
    } elseif (!preg_match("/^[0-9]{10,}$/", $accountNumber)) {
        $errorMessage = "Account number must be at least 10 digits.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $accountHolder)) {
        $errorMessage = "Account holder name can only contain letters and spaces.";
    } else {
        // Insert or update bank details
        $sql = "REPLACE INTO user_banks (user_id, bank_name, account_number, account_holder) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $userId, $bankName, $accountNumber, $accountHolder);

        if ($stmt->execute()) {
            $successMessage = "Bank details successfully updated!";
        } else {
            $errorMessage = "Failed to update bank details. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch existing bank details
$sql = "SELECT bank_name, account_number, account_holder FROM user_banks WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($bankName, $accountNumber, $accountHolder);
$stmt->fetch();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bind Bank Account</title>

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

        .bank-card {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-in-out;
            width: 100%;
            max-width: 500px;
            margin-top: 80px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .alert {
            animation: fadeIn 1s ease-in-out;
        }

        .info-text {
            background-color: #f8f9fa;
            border-left: 5px solid #17a2b8;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #495057;
            font-size: 14px;
        }
    </style>
</head>
<body>

  <!-- Include the navigation bar -->
  <?php include('nav.php'); ?>
  
<div class="bank-card">
    <h4 class="text-center">Bind Bank Account</h4>

    <div class="info-text">
        <strong>Why do we need this information?</strong>
        <p>Providing your bank details helps us securely process transactions, including deposits and withdrawals. This information is essential to ensure funds are transferred to the correct account and to comply with financial regulations.</p>
    </div>

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

    <!-- Bank Binding Form -->
    <form action="binding_bank.php" method="POST" onsubmit="return validateForm();">
        <div class="mb-3">
            <label for="bank_name" class="form-label">Bank Name</label>
            <select name="bank_name" id="bank_name" class="form-control" required>
                <option value="">Select Bank</option>
                <option value="MTN Mobile Money" <?= (isset($bankName) && $bankName == 'MTN Mobile Money') ? 'selected' : ''; ?>>MTN Mobile Money</option>
                <option value="Airtel Money" <?= (isset($bankName) && $bankName == 'Airtel Money') ? 'selected' : ''; ?>>Airtel Money</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="account_number" class="form-label">Account Number</label>
            <input type="text" name="account_number" id="account_number" minlength="10" class="form-control" required value="<?= isset($accountNumber) ? htmlspecialchars($accountNumber) : ''; ?>">
        </div>

        <div class="mb-3">
            <label for="account_holder" class="form-label">Account Holder Name</label>
            <input type="text" name="account_holder" id="account_holder" class="form-control" required value="<?= isset($accountHolder) ? htmlspecialchars($accountHolder) : ''; ?>">
        </div>

        <button type="submit" class="btn btn-primary w-100">Submit</button>
    </form>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    function validateForm() {
        const accountNumber = document.getElementById('account_number').value;
        const accountHolder = document.getElementById('account_holder').value;

        if (!/^[0-9]{10,}$/.test(accountNumber)) {
            alert('Account number must be at least 10 digits.');
            return false;
        }

        if (!/^[a-zA-Z\s]+$/.test(accountHolder)) {
            alert('Account holder name can only contain letters and spaces.');
            return false;
        }

        return true;
    }
</script>
</body>
</html>
