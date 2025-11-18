<?php
session_start();
include '../../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $conn->real_escape_string($_POST['first_name']);
    $last_name = $conn->real_escape_string($_POST['last_name']);
    $user_id = $_SESSION['user_id'];

    // Update the name in the database
    $sql = "UPDATE users SET first_name = '$first_name', last_name = '$last_name' WHERE id = $user_id";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Name updated successfully.";
        header('Location: update_name.php');
    } else {
        $_SESSION['error'] = "Error updating name: " . $conn->error;
        header('Location: update_name.php');
    }
}


// Fetch current user details if needed (optional)
$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT first_name, last_name FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Name</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        nav {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .container {
            margin-top: 100px; /* To ensure content doesn't overlap with the navbar */
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 70%;
            max-width: 800px;
        }

        .card-header {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .btn {
            text-transform: uppercase;
        }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
    <div class="container mt-4">
        <h2>Update Name</h2>
        <form method="POST" action="update_name.php">
            <div class="mb-3">
                <label for="firstName" class="form-label">First Name</label>
                <input type="text" class="form-control" id="firstName" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="lastName" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="lastName" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Name</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
