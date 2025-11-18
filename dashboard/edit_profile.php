<?php
session_start();

// Check if the user is logged in and has the 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'client') {
    header("Location: ../index.php"); // Redirect to login page if not logged in or not a client
    exit();
}

// Include your database connection
include('../includes/db_connection.php');

// Fetch user information from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$message = "";

// Handle form submission for profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newFirstName = $_POST['first_name'];
    $newLastName = $_POST['last_name'];
    $newPhoneNumber = $_POST['phone_number'];
    $newPassword = $_POST['password'];
    $profileImage = $_FILES['profile_image'];

    // Validate phone number format
    if (!preg_match('/^(078|073|072|2507|\\+2507)\\d{6,10}$/', $newPhoneNumber)) {
        $message = "Invalid phone number format. Please check the number.";
    } elseif (!empty($newPassword) && strlen($newPassword) < 8) {
        $message = "Password must be at least 8 characters long.";
    } else {
        // Prepare for updates
        $updateFields = "first_name = ?, last_name = ?, phone_number = ?";
        $params = [$newFirstName, $newLastName, $newPhoneNumber];
        $types = "sss";

        // Update password if provided
        if (!empty($newPassword)) {
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateFields .= ", password = ?";
            $params[] = $newPasswordHash;
            $types .= "s";
        }

        // Handle profile image upload
        if ($profileImage['size'] > 0) {
            $targetDir = "../assets/images/";
            $fileName = basename($profileImage['name']);
            $imageFileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            $newFileName = uniqid() . '.' . $imageFileType; // Generate a unique file name
            $targetFile = $targetDir . $newFileName;

            // Check if file type is allowed
            if (in_array($imageFileType, $allowedTypes)) {
                // Check file size (limit to 5MB)
                if ($profileImage['size'] <= 5 * 1024 * 1024) {
                    // Move the uploaded file to the target directory
                    if (move_uploaded_file($profileImage['tmp_name'], $targetFile)) {
                        $updateFields .= ", profile_picture = ?";
                        $params[] = $newFileName; // Save only the unique file name in the database
                        $types .= "s";
                    } else {
                        $message = "Failed to upload the image. Please try again.";
                    }
                } else {
                    $message = "File size exceeds the maximum limit of 5MB.";
                }
            } else {
                $message = "Invalid image format. Only JPG, JPEG, PNG, and GIF are allowed.";
            }
        }

        // Update the user's profile in the database
        if (empty($message)) {
            $updateSql = "UPDATE users SET $updateFields WHERE id = ?";
            $params[] = $user_id;
            $types .= "i";

            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param($types, ...$params);

            if ($updateStmt->execute()) {
                $message = "Profile updated successfully!";
            } else {
                $message = "Failed to update profile. Please try again.";
            }

            $updateStmt->close();
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | DeltaOne Investment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        .card-header {
            background-color: #2575fc;
            color: #fff;
        }
    </style>
</head>
<body>
<?php include('nav.php'); ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>Edit Profile</h3>
        </div>
        <div class="card-body">
            <?php if (!empty($message)): ?>
                <div class="alert alert-info">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_profile.php" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="first_name" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="last_name" class="form-label">Last Name</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone_number" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current password">
                </div>
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image">
                </div>
                <button type="submit" class="btn btn-primary w-100">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
