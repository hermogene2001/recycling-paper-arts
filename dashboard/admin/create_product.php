<?php
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../../index.php');
    exit();
}

// Include database connection
require_once('../../includes/db.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_product'])) {
    // Get form data and sanitize it
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $daily_earning = mysqli_real_escape_string($conn, $_POST['daily_earning']);
    $cycle = mysqli_real_escape_string($conn, $_POST['cycle']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $profit_rate = !empty($_POST['profit_rate']) ? mysqli_real_escape_string($conn, $_POST['profit_rate']) : null;
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle file upload for the product image
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $image_tmp_name = $_FILES['product_image']['tmp_name'];
        $image_name = basename($_FILES['product_image']['name']);
        $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

        // Allowed file extensions
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_extension, $allowed_extensions)) {
            header('Location: manage_products.php?message=Invalid file type for image&message_type=danger');
            exit();
        }

        // Generate unique name for the image
        $image_new_name = uniqid('product_', true) . '.' . $image_extension;

        // Define the upload directory
        $upload_directory = '../../uploads/';
        $image_path = $upload_directory . $image_new_name;

        // Move the uploaded file to the upload directory
        if (!move_uploaded_file($image_tmp_name, $image_path)) {
            header('Location: manage_products.php?message=Error uploading image&message_type=danger');
            exit();
        }
    } else {
        header('Location: manage_products.php?message=Product image is required&message_type=danger');
        exit();
    }

    // Insert product into the database
    $insert_query = "INSERT INTO products (name, image, daily_earning, cycle, price, profit_rate, status) 
                     VALUES ('$product_name', '$image_new_name', '$daily_earning', '$cycle', '$price', 
                             '$profit_rate', '$status')";

    if (mysqli_query($conn, $insert_query)) {
        // Redirect with success message
        header('Location: manage_products.php?message=Product created successfully&message_type=success');
    } else {
        // Redirect with error message
        header('Location: manage_products.php?message=Error creating product&message_type=danger');
    }
} else {
    // Redirect if the form is not submitted properly
    header('Location: manage_products.php?message=Invalid request&message_type=danger');
}
?>
