<?php
session_start();
require_once('../../includes/db_connection.php');

// Check if the user is logged in as an admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Fetch current settings from the database (example table: settings)
$query = "SELECT * FROM settings";
$settingsResult = $conn->query($query);

// Initialize variables
$investmentCycle = '';
$dailyProfitRate = '';

if ($settingsResult->num_rows > 0) {
    $settings = $settingsResult->fetch_assoc();
    $investmentCycle = $settings['investment_cycle'];
    $dailyProfitRate = $settings['daily_profit_rate'];
}

// Handle form submission for updating settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newCycle = $_POST['investment_cycle'];
    $newRate = $_POST['daily_profit_rate'];

    $updateQuery = "UPDATE settings SET investment_cycle = ?, daily_profit_rate = ? WHERE id = 1";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('id', $newCycle, $newRate);

    if ($stmt->execute()) {
        $successMessage = "Settings updated successfully!";
        $investmentCycle = $newCycle;
        $dailyProfitRate = $newRate;
    } else {
        $errorMessage = "Failed to update settings.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
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
    </style>
</head>
<body>

    <!-- Fixed Navigation Bar -->
    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-5">
        <h2 class="text-center">Admin Settings</h2>

        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-success"><?= $successMessage ?></div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-danger"><?= $errorMessage ?></div>
        <?php endif; ?>

        <form method="POST" action="settings.php">
            <div class="mb-3">
                <label for="investment_cycle" class="form-label">Default Investment Cycle (in days)</label>
                <input type="number" class="form-control" id="investment_cycle" name="investment_cycle" value="<?= htmlspecialchars($investmentCycle) ?>" required>
            </div>
            <div class="mb-3">
                <label for="daily_profit_rate" class="form-label">Daily Profit Rate (%)</label>
                <input type="number" step="0.01" class="form-control" id="daily_profit_rate" name="daily_profit_rate" value="<?= htmlspecialchars($dailyProfitRate) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
