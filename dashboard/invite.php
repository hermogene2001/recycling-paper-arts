<?php
session_start();
if ($_SESSION['role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

// Get the referral code from session
$referralCode = $_SESSION['referral_code'];

// Generate the referral URL with the referral code
$baseURL = "https://deltaoneinvestment.x10.mx/signup.php"; // Replace with your actual registration page URL
$inviteLink = $baseURL . "?referral_code=" . urlencode($referralCode);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Invitation Code</title>

    <!-- Bootstrap CSS (CDN via jsDelivr) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome for icons (CDN via jsDelivr) -->
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.1.2/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
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
            max-width: 800px;
            margin-top: 80px;
        }
        .card {
            max-width: 100%;
            margin: 20px auto;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
        }
        .card-body {
            padding: 1.5rem;
            text-align: center;
        }
        .referral-code {
            font-size: 1.5rem;
            color: #007bff;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .share-message {
            font-size: 1rem;
            color: #343a40;
        }
        .back-link {
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .copy-button {
            margin-top: 10px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
    <?php include('nav.php'); ?>
        <h2 class="text-center text-dark">Bonus Rules</h2>
        <p class="text-center text-dark">
           <li>Level 1 subordinates to invest and you will receive a bonus of 6% of the investment amount.</li> 
           <li>Level 2 subordinates to invest and you will receive a bonus of 3% of the investment amount.</li>  
            After the subordinate invests, the bonus will automatically enter the balance; there is no limit, and you can withdraw it at any time.
        </p>

        <div class="card">
            <div class="card-header text-center">
                <h3>Your Invitation Code</h3>
            </div>
            <div class="card-body">
                <div class="referral-code"><?php echo $referralCode; ?></div>
                <div class="share-message">
                    Share this code with others to invite them to join!
                </div>
                <!-- Display the invite link -->
                <div class="share-message">
                    Your shareable link: 
                    <a href="<?php echo $inviteLink; ?>" id="invite-link" target="_blank"><?php echo $inviteLink; ?></a>
                </div>
                <button id="copyButton" class="btn btn-primary copy-button"><i class="fas fa-copy"></i> Copy Link</button>
                <div class="already-account mt-3">
                    <a href="client_dashboard.php" class="back-link">Go to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle (CDN via jsDelivr) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- Copy Referral Link Script -->
    <script>
        document.getElementById("copyButton").addEventListener("click", function() {
            var copyText = document.getElementById("invite-link").href;
            var tempInput = document.createElement("input");
            document.body.appendChild(tempInput);
            tempInput.value = copyText;
            tempInput.select();
            tempInput.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            document.body.removeChild(tempInput);
            alert("Referral link copied: " + copyText);
        });
    </script>
</body>
</html>
