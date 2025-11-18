<?php
// Pre-fill the invitation code from the URL if available
$referralCode = isset($_GET['referral_code']) ? $_GET['referral_code'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeltaOne Investment - Signup</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            padding: 20px;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1.5s ease-in-out;
            transform-origin: top;
        }
        .form-container h3 {
            text-align: center;
            color: #2575fc;
            font-weight: bold;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2575fc, #6a11cb);
        }
        @keyframes fadeIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .platform-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .platform-info h1 {
            color: white;
            font-size: 1.8rem;
        }
        .platform-info p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="platform-info">
            <h1>Welcome to DeltaOne Investment</h1>
            <p>Your trusted platform for smart investments</p>
        </div>
        <div class="form-container">
            <form method="POST" action="auth/register" id="signupForm">
                <h3>Signup</h3>
                <div class="mb-3">
                    <label for="signupName" class="form-label">Name</label>
                    <input type="text" class="form-control" id="signupName" name="name" placeholder="Enter your name" required>
                </div>
                <div class="mb-3">
                    <label for="signupPhone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="signupPhone" name="phone_number" placeholder="Enter your phone number" required>
                    <div id="phoneError" class="text-danger mt-1" style="display:none;">Invalid phone number. Must start with 078, 073, 072, 2507, or +2507 and be at least 10 digits.</div>
                </div>
                <div class="mb-3">
                    <label for="signupInvitationCode" class="form-label">Invitation Code</label>
                    <input type="text" class="form-control" id="signupInvitationCode" name="invitation_code" placeholder="Enter invitation code" required value="<?php echo htmlspecialchars($referralCode); ?>" readonly >
                </div>
                <div class="mb-3">
                    <label for="signupPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="signupPassword" name="password" placeholder="Create a password" min='6' required>
                </div>
                <div class="mb-3">
                    <label for="signupConfirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="signupConfirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Signup</button>
            </form>
            <div class="text-center mt-3">
                <a href="login" class="btn btn-link">Already have an account? Login</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Signup phone validation
        document.getElementById("signupForm").addEventListener("submit", function (e) {
            const phoneInput = document.getElementById("signupPhone").value.trim();
            const phoneError = document.getElementById("phoneError");
            const phonePattern = /^(078|073|072|2507|\+2507)\d{6,}$/;

            if (!phonePattern.test(phoneInput) || phoneInput.length < 10) {
                phoneError.style.display = "block";
                e.preventDefault();
            } else {
                phoneError.style.display = "none";
            }
        });
    </script>
</body>
</html>
