<?php
session_start();
if ($_SESSION['role'] !== 'client') {
    header("Location: ../index.php");
    exit;
}

// Database connection
require_once('../includes/db_connection.php');

// Fetch client information
$client_id = $_SESSION['user_id'];
// Fetch active regular investments
$active_regular_query = "
    SELECT products.name, products.image, products.price, products.daily_earning, products.cycle, purchases.purchase_date 
    FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE purchases.client_id = ? 
    AND (CURDATE() <= DATE_ADD(purchases.purchase_date, INTERVAL products.cycle DAY))
    ORDER BY purchases.purchase_date DESC
";
$active_regular_stmt = $conn->prepare($active_regular_query);
$active_regular_stmt->bind_param("i", $client_id);
$active_regular_stmt->execute();
$active_regular_result = $active_regular_stmt->get_result();

// Fetch completed regular investments
$completed_regular_query = "
    SELECT products.name, products.image, products.price, products.daily_earning, products.cycle, purchases.purchase_date 
    FROM purchases 
    JOIN products ON purchases.product_id = products.id 
    WHERE purchases.client_id = ? 
    AND (CURDATE() > DATE_ADD(purchases.purchase_date, INTERVAL products.cycle DAY))
    ORDER BY purchases.purchase_date DESC
";
$completed_regular_stmt = $conn->prepare($completed_regular_query);
$completed_regular_stmt->bind_param("i", $client_id);
$completed_regular_stmt->execute();
$completed_regular_result = $completed_regular_stmt->get_result();

// Fetch active compound investments
$active_compound_query = "
    SELECT products_compound.name, products_compound.image, products_compound.price, products_compound.daily_earning, products_compound.cycle, compound_investments.maturity_date 
    FROM compound_investments 
    JOIN products_compound ON compound_investments.product_id = products_compound.id 
    WHERE compound_investments.client_id = ? 
    AND (CURDATE() <= DATE_ADD(compound_investments.maturity_date, INTERVAL products_compound.cycle DAY))
    ORDER BY compound_investments.maturity_date DESC
";
$active_compound_stmt = $conn->prepare($active_compound_query);
$active_compound_stmt->bind_param("i", $client_id);
$active_compound_stmt->execute();
$active_compound_result = $active_compound_stmt->get_result();

// Fetch completed compound investments
$completed_compound_query = "
    SELECT products_compound.name, products_compound.image, products_compound.price, products_compound.daily_earning, products_compound.cycle, compound_investments.maturity_date 
    FROM compound_investments 
    JOIN products_compound ON compound_investments.product_id = products_compound.id 
    WHERE compound_investments.client_id = ? 
    AND (CURDATE() > DATE_ADD(compound_investments.maturity_date, INTERVAL products_compound.cycle DAY))
    ORDER BY compound_investments.maturity_date DESC
";
$completed_compound_stmt = $conn->prepare($completed_compound_query);
$completed_compound_stmt->bind_param("i", $client_id);
$completed_compound_stmt->execute();
$completed_compound_result = $completed_compound_stmt->get_result();

// Close the database connection
mysqli_close($conn);
?>
