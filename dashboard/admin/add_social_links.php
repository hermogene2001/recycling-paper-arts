<?php
include '../../includes/db_connect.php'; // Adjust this path to your database connection file

if (isset($_POST['add_social_links'])) {
    $whatsapp = $_POST['whatsapp'];
    $telegram = $_POST['telegram'];
    $facebook = $_POST['facebook'];
    $twitter = $_POST['twitter'];

    $query = "INSERT INTO social_links (whatsapp, telegram, facebook, twitter) 
              VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $whatsapp, $telegram, $facebook, $twitter);

    if ($stmt->execute()) {
        header("Location: manage_social_links.php?success=1");
    } else {
        header("Location: manage_social_links.php?error=1");
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>
