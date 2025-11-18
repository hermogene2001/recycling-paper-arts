<?php
// Database connection
require_once('../../includes/db_connection.php');

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $daily_earning = $_POST['daily_earning'];
    $cycle = $_POST['cycle'];
    $price = $_POST['price'];

    // Handle file upload
    $image = $_FILES['image']['name'];
    $target = "../../uploads/" . basename($image);

    // Move uploaded file to the server
    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        // Insert product into the database
        $insert_query = "INSERT INTO products (name, image, daily_earning, cycle, price) 
                         VALUES ('$name', '$image', '$daily_earning', '$cycle', '$price')";
        mysqli_query($conn, $insert_query);

        // Redirect with success message
        header('Location: manage_products.php?message=Product added successfully');
    } else {
        // Redirect with error message
        header('Location: admin_dashboard.php?message=Failed to upload image');
    }
}
?>
