<?php
session_start();
include('../includes/db_connection.php'); // Assuming your DB connection is here

$error_message = ''; // Variable to store the error message

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];

    // Validate phone number (must start with 078, 073, 072, 2507, or +2507 and be at least 10 digits)
    if (!preg_match("/^(078|073|072|2507|\+2507)\d{6,10}$/", $phone_number)) {
        $error_message = 'Invalid phone number. Must start with 078, 073, 072, 2507, or +2507 and be at least 10 digits.';
    } else {
        // Query to check if the phone number exists in the database
        $sql = "SELECT * FROM users WHERE phone_number = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $phone_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Check if user is active
            if ($user['status'] !== 'active') {
                $error_message = 'Your account is inactive. Please contact the admin.';
            }
            // Verify the password using password_verify
            elseif (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['referral_code'] = $user['referral_code'];
                $_SESSION['phone_number'] = $user['phone_number'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name']; // Combine first and last name
                $_SESSION['profile_picture'] = !empty($user['profile_picture']) ? $user['profile_picture'] : '../assets/images/default-profile.jpg'; // Use default if no profile picture

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../dashboard/admin/admin_dashboard.php");
                } elseif ($user['role'] === 'agent') {
                    header("Location: ../dashboard/agent/agent_dashboard.php");
                } else {
                    header("Location: ../dashboard/client_dashboard.php");
                }
                exit();
            } else {
                $error_message = 'Invalid phone number or password!'; // Password mismatch
            }
        } else {
            $error_message = 'Invalid phone number or password!'; // Phone number not found
        }
        $stmt->close();
    }
}

?>

<!-- Pass the error message to the frontend -->
<script type="text/javascript">
    <?php if (!empty($error_message)): ?>
        alert("<?php echo $error_message; ?>");
        window.location.href = '../index.php';  // Redirect to index.php after alert
    <?php endif; ?>
</script>
