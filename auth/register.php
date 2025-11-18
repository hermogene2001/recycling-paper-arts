<?php
session_start();
include '../includes/db_connection.php';

// Function to generate a unique referral code
function generateReferralCode($length = 8) {
    return substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, $length);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'];
    $phoneNumber = $_POST['phone_number'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $invitationCode = $_POST['invitation_code'] ?? null; // This is the referrer's code
    $referralCode = generateReferralCode();

    // Validate password and confirm password
    if ($password !== $confirmPassword) {
        echo "<script>
                alert('Passwords do not match. Please try again.');
                window.location.href = '../signup.php'; // Replace with your registration page URL
              </script>";
        exit;
    }

    // Check if the phone number is valid
    if (!preg_match("/^(078|073|072|2507|\+2507)\d{6,10}$/", $phoneNumber)) {
        echo "<script>
                alert('Invalid phone number. Must start with 078, 073, 072, 2507, or +2507 and be at least 10 digits.');
                window.location.href = '../signup.php'; // Replace with your registration page URL
              </script>";
        exit;
    }

    // Hash the password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Check if the phone number already exists
    $sql = "SELECT * FROM users WHERE phone_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phoneNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Phone number already exists
        echo "<script>
                alert('Phone number already registered.');
                window.location.href = '../signup.php'; // Replace with your registration page URL
              </script>";
    } else {
        // Check if the invitation code (referrer's code) is valid
        $referrerId = null;
        if (!empty($invitationCode)) {
            $referrerQuery = "SELECT id FROM users WHERE referral_code = ?";
            $stmt = $conn->prepare($referrerQuery);
            $stmt->bind_param("s", $invitationCode);
            $stmt->execute();
            $stmt->bind_result($referrerId);
            $stmt->fetch();
            $stmt->close();

            if (!$referrerId) {
                // Invalid invitation code
                echo "<script>
                        alert('Invalid referral code. Please check and try again.');
                        window.location.href = '../signup.php'; // Replace with your registration page URL
                      </script>";
                exit;
            }
        }

        // Insert the new user into the database, with the referrer's ID in the invitation_code column
        $sql = "INSERT INTO users (first_name, phone_number, password, invitation_code, referral_code, balance, created_at, role) 
                VALUES (?, ?, ?, ?, ?, 0, NOW(), 'client')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $name, $phoneNumber, $passwordHash, $referrerId, $referralCode);

        if ($stmt->execute()) {
            // Registration successful
            $_SESSION['user_id'] = $conn->insert_id;
            $_SESSION['phone_number'] = $phoneNumber;
            $_SESSION['role'] = 'client';
            $_SESSION['referral_code'] = $referralCode;

            echo "<script>
                    alert('Registration successful. Your referral code: " . $referralCode . "');
                    window.location.href = '../dashboard/client_dashboard.php'; // Replace with your dashboard page URL
                  </script>";
        } else {
            // Registration failed
            echo "<script>
                    alert('Registration failed. Please try again.');
                    window.location.href = '../signup.php'; // Replace with your registration page URL
                  </script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>
