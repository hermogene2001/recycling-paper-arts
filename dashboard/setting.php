<?php
session_start();

// Ensure the user is logged in and has a 'client' role
if ($_SESSION['role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

// Fetch the user's details from the session
// $phoneNumber = $_SESSION['phone_number'];
$referralCode = $_SESSION['referral_code'];
$userId = $_SESSION['user_id'];

include '../includes/db_connection.php'; // Include database connection
include '../includes/function.php'; // Include helper functions

// Fetch the user's balance, referral bonus, first name, last name, and profile picture from the users table
$sql = "SELECT balance, referral_bonus, profile_picture, first_name, last_name, phone_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($balance, $referralBonus, $profilePicture, $fname, $lname, $phoneNumber);
$stmt->fetch();
$stmt->close();


// Fetch the total profit (daily income) from the investments table
$sql = "SELECT SUM(daily_profit) as total_daily_income FROM investments WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($totalDailyIncome);
$stmt->fetch();
$stmt->close();

// Fetch the user's social media links from the database
$sql = "SELECT facebook, twitter, telegram, whatsapp FROM social_links";
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($facebookLink, $twitterLink, $telegramLink, $whatsappLink);
$stmt->fetch();
$stmt->close();

// Fetch the total balance for investments eligible for withdrawal
$sql = "SELECT SUM(withdrawable_amount) AS withdrawable_amount 
        FROM compound_investments 
        WHERE client_id = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($withdrawableAmount);
$stmt->fetch();
$stmt->close();

// Output the withdrawable amount


// Close the connection
$conn->close();

// Check if name is missing
$nameMissing = empty($fname) || empty($lname);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Information | DeltaOne Investment</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

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
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            margin-top: 80px;
            max-width: 800px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #2575fc;
            color: #fff;
        }
        footer {
            background-color: #2575fc;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        .profile-card {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
        }
        .profile-info {
            font-size: 16px;
            margin-bottom: 15px;
        }
        .account-action {
            padding: 10px;
        }
        .action-link {
            display: block;
            margin-bottom: 10px;
            font-size: 16px;
            text-decoration: none;
        }
        .action-link:hover {
            text-decoration: underline;
        }
        .balance {
            font-size: 22px;
            font-weight: bold;
        }
        .logout-btn {
            margin-top: 20px;
        }
        .container {
            margin-bottom: 50px;
        }
        /* Profile picture */
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }
        /* Progress Bar for Profile Completion */
        .progress-bar {
            width: 75%; /* Example percentage for profile completion */
            height: 10px;
            background-color: #28a745;
        }
        /* Social Media Links */
        .social-media-links {
            position: fixed;
            bottom: 70px;
            right: 0%;
            transform: translateX(-50%);
            z-index: 1000;
        }
        .social-media-links a {
            margin: 0 10px;
            font-size: 24px;
            color: #dfj;
            padding: 10px;
            border-radius: 50%;
        }
        .social-media-links a:hover {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <?php include('nav.php'); ?>

    <div class="profile-card">
        <h2 class="text-center">Account Information</h2>

        <!-- Profile Picture -->
        <div class="text-center mb-3">
            <?php if ($profilePicture): ?>
                <img src="../assets/images/<?php echo htmlspecialchars($profilePicture); ?>" class="profile-picture" alt="Profile Picture">
            <?php else: ?>
                <img src="../assets/images/default_profile.jpg" class="profile-picture" alt="Default Profile Picture">
            <?php endif; ?>
        </div>

        <!-- Profile Completeness Progress -->
        <div class="progress mb-3">
            <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <?php if ($nameMissing): ?>
            <div class="alert alert-warning text-center" role="alert">
                Your name is missing. Please <a href="profile" class="alert-link">update your profile</a>.
            </div>
        <?php endif; ?>

        <div class="profile-info">
            <strong>Names:</strong> 
            <?php 
            echo $nameMissing ? "Please update your name" : htmlspecialchars($fname) . " " . htmlspecialchars($lname); 
            ?>
        </div>

        <div class="profile-info">
    <strong>Phone Number:</strong> <span class="balance text-primary"><?php echo htmlspecialchars($phoneNumber); ?></span>
</div>

        <div class="profile-info">
            <strong>Referral Code:</strong> <?php echo htmlspecialchars($referralCode); ?>
        </div>

        <div class="profile-info">
            <strong>Account Balance:</strong>
            <span class="balance">RWF <?php echo number_format($balance, 2); ?></span>
        </div>

        <div class="profile-info">
            <strong>Project Revenue (Daily Income):</strong>
            <span class="balance">RWF <?php echo number_format($totalDailyIncome, 2); ?></span>
        </div>
        
        <div class="profile-info">
            <strong>Invitation Income:</strong>
            <span class="balance">RWF <?php echo number_format($referralBonus, 2); ?></span>
        </div>
        <div class="profile-info">
    <strong>Current Compound Balance:</strong>
    <span class="balance text-success">RWF <?php echo number_format($withdrawableAmount, 2); ?></span>
</div>

<!-- Transfer Button -->
<div class="text-center mt-3">
    <form action="transfer_balance" method="POST" id="transferForm">
        <input type="hidden" name="withdrawable_amount" value="<?php echo $withdrawableAmount; ?>">
        <button type="button" class="btn btn-primary" onclick="confirmTransfer()">
            Transfer to Main Balance
        </button>
    </form>
</div>
        <hr>

        <!-- Account actions -->
        <div class="account-action row">
            <div class="col-md-4">
                <a href="profile" class="action-link"><i class="fas fa-lock"></i> Change Password</a>
                <a href="binding_bank" class="action-link"><i class="fas fa-university"></i> Manage Bank Details</a>
                <a href="transaction_history" class="action-link"><i class="fas fa-history"></i> View Transaction History</a>
            </div>
            <div class="col-md-4">
                <a href="invite" class="action-link"><i class="fas fa-user-friends"></i> Invite Friends</a>
                <a href="recharge" class="action-link"><i class="fas fa-plus-circle"></i> Recharge</a>
                <a href="withdrawal" class="action-link"><i class="fas fa-minus-circle"></i> Withdrawal</a>
            </div>
            <div class="col-md-4">
                <a href="my_wallet" class="action-link"><i class="fas fa-wallet"></i> My Wallet</a>
                <a href="my_team" class="action-link"><i class="fas fa-users"></i> My Team</a>
                <a href="edit_profile" class="action-link"><i class="fas fa-user-edit"></i> Edit Profile</a>
            </div>
        </div>

        <!-- Logout Button -->
        <div class="text-center logout-btn">
            <a href="../auth/logout" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>
</div>

<!-- Fixed Social Media Links -->
<!-- Call Center Icon -->
<div class="call-center-icon" style="position: fixed; bottom: 20px; right: 20px; z-index: 1000;">
    <button class="btn btn-primary rounded-circle" id="showSocialLinks" style="width: 60px; height: 60px;">
        <i class="fas fa-headset"></i>
    </button>
</div>

<!-- Social Media Modal -->
<div class="modal fade" id="socialLinksModal" tabindex="-1" aria-labelledby="socialLinksModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="socialLinksModalLabel">Contact Us on Social Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <?php if (!empty($facebookLink)): ?>
                    <a href="<?php echo htmlspecialchars($facebookLink); ?>" target="_blank" class="btn btn-outline-primary btn-sm m-2">
                        <i class="fab fa-facebook"></i> Facebook
                    </a>
                <?php endif; ?>
                <?php if (!empty($twitterLink)): ?>
                    <a href="<?php echo htmlspecialchars($twitterLink); ?>" target="_blank" class="btn btn-outline-info btn-sm m-2">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                <?php endif; ?>
                <?php if (!empty($telegramLink)): ?>
                    <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" class="btn btn-outline-primary btn-sm m-2">
                        <i class="fab fa-telegram"></i> Telegram
                    </a>
                <?php endif; ?>
                <?php if (!empty($whatsappLink)): ?>
                    <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" class="btn btn-outline-success btn-sm m-2">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Show the modal when the Call Center icon is clicked
    document.getElementById('showSocialLinks').addEventListener('click', function () {
        var socialLinksModal = new bootstrap.Modal(document.getElementById('socialLinksModal'), {});
        socialLinksModal.show();
    });
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    function confirmTransfer() {
        var withdrawableAmount = <?php echo $withdrawableAmount; ?>;
        
        // Prompt confirmation before proceeding with the transfer
        var confirmation = confirm("Are you sure you want to transfer " + withdrawableAmount + " RWF to your main balance?");
        
        if (confirmation) {
            // If the user confirms, submit the form
            document.getElementById('transferForm').submit();
        } else {
            // If the user cancels, do nothing
            return false;
        }
    }
</script>
</body>
</html>
