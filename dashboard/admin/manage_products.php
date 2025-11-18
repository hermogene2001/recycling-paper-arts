<?php
session_start();
require_once('../../includes/db_connection.php');

// Check admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Fetch products
$query = "SELECT * FROM products";
$productsResult = $conn->query($query);

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $profitRate = $_POST['profit_rate'];
    $dailyEarning = ($price * $profitRate) / 100;
    $cycle = $_POST['cycle'];

    // Image upload
    $image = $_FILES['image']['name'];
    $targetDir = "../../uploads/";
    $targetFile = $targetDir . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

    // Insert product
    $addQuery = "INSERT INTO products (name, image, daily_earning, cycle, price, profit_rate, status) VALUES (?, ?, ?, ?, ?, ?, 'active')";
    $stmt = $conn->prepare($addQuery);
    $stmt->bind_param('ssdiii', $name, $image, $dailyEarning, $cycle, $price, $profitRate);
    $stmt->execute();
    header("Location: manage_products.php");
}

// Handle Edit Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    $id = $_POST['product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $profitRate = $_POST['profit_rate'];
    $dailyEarning = ($price * $profitRate) / 100;
    $cycle = $_POST['cycle'];

    // Get the current product details to retain the image if not updated
    $getProductQuery = "SELECT image FROM products WHERE id = ?";
    $stmt = $conn->prepare($getProductQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentImage = $result->fetch_assoc()['image'];

    // Check if an image is uploaded
    if (!empty($_FILES['image']['name'])) {
        // If a new image is uploaded, set it
        $image = $_FILES['image']['name'];
        $targetDir = "../../uploads/";
        $targetFile = $targetDir . basename($image);

        // Move the uploaded file to the server
        move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
    } else {
        // If no new image is uploaded, keep the current image
        $image = $currentImage;
    }

    // Update the product details with or without the image
    $updateQuery = "UPDATE products SET name = ?, image = ?, daily_earning = ?, price = ?, profit_rate = ?, cycle = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('ssdiisi', $name, $image, $dailyEarning, $price, $profitRate, $cycle, $id);

    // Execute the query
    $stmt->execute();
    header("Location: manage_products.php");
}

// Handle Status Toggle
if (isset($_GET['toggle_status'])) {
    $id = $_GET['id'];
    $currentStatus = $_GET['status'];
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';

    $statusQuery = "UPDATE products SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param('si', $newStatus, $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}
// Handle Status Toggle products_compound
if (isset($_GET['toggle_status1'])) {
    $id = $_GET['id'];
    $currentStatus = $_GET['status'];
    $newStatus = $currentStatus === 'active' ? 'inactive' : 'active';

    $statusQuery = "UPDATE products_compound SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param('si', $newStatus, $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}

// Handle Create Compound Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_product'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $profitRate = $_POST['profit_rate'];
    $cycle = $_POST['cycle'];  // Maturity period in days

    // Calculate the daily earning
    $dailyEarning = ($price * $profitRate) / 100;

    // Image upload
    $image = $_FILES['image']['name'];
    $targetDir = "../../uploads/";
    $targetFile = $targetDir . basename($image);
    move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);

    // Insert into the products table
    $query = "INSERT INTO products_compound (name, price, profit_rate, cycle, daily_earning, image) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sdidss', $name, $price, $profitRate, $cycle, $dailyEarning, $image);
    $stmt->execute();
    header("Location: manage_products.php"); // Redirect to product management page
}

// Handle Edit Compound Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_compound_product'])) {
    $id = $_POST['compound_product_id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $profitRate = $_POST['profit_rate'];
    $cycle = $_POST['cycle'];
    $dailyEarning = ($price * $profitRate) / 100;

    // Get current product details
    $getProductQuery = "SELECT image FROM products_compound WHERE id = ?";
    $stmt = $conn->prepare($getProductQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentImage = $result->fetch_assoc()['image'];

    // Handle uploaded image
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image']['name'];
        $targetDir = "../../uploads/";
        $targetFile = $targetDir . basename($image);

        // Move file to target directory
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $uploadedImage = $image;
        } else {
            $uploadedImage = $currentImage; // Retain old image if upload fails
        }
    } else {
        $uploadedImage = $currentImage;
    }

    // Update product in the database
    $updateQuery = "UPDATE products_compound SET name = ?, price = ?, profit_rate = ?, cycle = ?, daily_earning = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('sdidssi', $name, $price, $profitRate, $cycle, $dailyEarning, $uploadedImage, $id);
    $stmt->execute();

    // Redirect to manage products page
    header("Location: manage_products.php");
    exit();
}


// Handle Delete Product
if (isset($_GET['delete_product'])) {
    $id = $_GET['id'];

    $deleteQuery = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}
// Handle Delete products_compound
if (isset($_GET['delete_product_compound'])) {
    $id = $_GET['id'];

    $deleteQuery = "DELETE FROM products_compound WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param('i', $id);
    $stmt->execute();

    header("Location: manage_products.php");
    exit();
}
// Fetch compound products
$compoundQuery = "SELECT * FROM products_compound";
$compoundProductsResult = $conn->query($compoundQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
        }
        .product-card {
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .product-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .navbar {
            z-index: 1000;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
        }
    </style>
</head>
<body>
    <?php include('../../includes/admin_nav.php'); ?>

    <div class="container mt-4">
        <h2 class="text-white text-center mb-4">Manage Products</h2>

        <!-- Add Product Button -->
        <button class="btn btn-success mb-4" data-bs-toggle="modal" data-bs-target="#addProductModal">Add New Product</button>
        <!-- Create Compound Product Button -->
        <!-- <button class="btn btn-warning mb-4" data-bs-toggle="modal" data-bs-target="#createCompoundProductModal">Create Compound Product</button> -->

        <!-- Product Cards -->
        <div class="row">
            <!-- Regular Product Cards -->
    <h4 class="text-white text-center mb-4">Regular Products</h4>
            <?php while ($product = $productsResult->fetch_assoc()) : ?>
                <div class="col-md-4 mb-3">
                    <div class="card product-card">
                        <img src="../../uploads/<?= $product['image'] ?>" class="card-img-top" alt="Product Image">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fa-solid fa-cart-shopping"></i> <?= htmlspecialchars($product['name']) ?></h5>
                            <p><i class="fas fa-tag"></i> Price: <?= htmlspecialchars($product['price']) ?> RWF</p>
                            <p><i class="fas fa-coins"></i> Daily Earning: <?= htmlspecialchars($product['daily_earning']) ?> RWF</p>
                            <p><i class="fa-solid fa-calendar-days"></i> Days (Cycle): <?= htmlspecialchars($product['cycle']) ?></p>
                            <p><i class="fas fa-info-circle"></i> Status: <?= htmlspecialchars($product['status']) ?></p>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal<?= $product['id'] ?>"><i class="fas fa-edit"></i> Edit </button>
                            <a href="manage_products.php?toggle_status=1&id=<?= $product['id'] ?>&status=<?= $product['status'] ?>" class="btn btn-warning btn-sm">
                                <i class="fas <?= $product['status'] === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i> 
                                <?= $product['status'] === 'active' ? ' Deactivate' : ' Activate' ?>
                            </a>
                            <a href="manage_products.php?delete_product=1&id=<?= $product['id'] ?>" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Edit Product Modal -->
                <div class="modal fade" id="editProductModal<?= $product['id'] ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Price</label>
                                        <input type="number" name="price" value="<?= $product['price'] ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Profit Rate (%)</label>
                                        <input type="number" name="profit_rate" value="<?= $product['profit_rate'] ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Days (Cycle)</label>
                                        <input type="number" name="cycle" value="<?= $product['cycle'] ?>" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="edit_product" class="btn btn-primary">Save Changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <div class="container mt-4">

    <button class="btn btn-warning mb-4" data-bs-toggle="modal" data-bs-target="#createCompoundProductModal">Create Compound Product</button>

    
    <!-- Compound Product Cards -->
    <h4 class="text-white text-center mb-4">Compound Products</h4>
    <div class="row">
        <?php while ($product = $compoundProductsResult->fetch_assoc()) : ?>
            <div class="col-md-4 mb-3">
                <div class="card product-card">
                    <img src="../../uploads/<?= $product['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($product['name']) ?>" style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fa-solid fa-cart-shopping"></i> <?= htmlspecialchars($product['name']) ?></h5>
                        <p><i class="fas fa-tag"></i> Price: <?= htmlspecialchars($product['price']) ?> RWF</p>
                        <p><i class="fas fa-coins"></i> Daily Earning: <?= htmlspecialchars($product['daily_earning']) ?> RWF</p>
                        <p><i class="fa-solid fa-calendar-days"></i> Days (Cycle): <?= htmlspecialchars($product['cycle']) ?></p>
                        <p><i class="fas fa-info-circle"></i> Status: <?= htmlspecialchars($product['status']) ?></p>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editCompoundProductModal<?= $product['id'] ?>"><i class="fas fa-edit"></i> Edit </button>
                        <a href="manage_products.php?toggle_status1=1&id=<?= $product['id'] ?>&status=<?= $product['status'] ?>" class="btn btn-warning btn-sm">
                            <i class="fas <?= $product['status'] === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i> 
                            <?= $product['status'] === 'active' ? 'Deactivate' : 'Activate' ?>
                        </a>
                        <a href="manage_products.php?delete_product_compound=1&id=<?= $product['id'] ?>" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </div>
                </div>
            </div>

            <!-- Edit Modal for Each Product -->
            <div class="modal fade" id="editCompoundProductModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Compound Product</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="compound_product_id" value="<?= $product['id'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Price</label>
                                    <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Profit Rate (%)</label>
                                    <input type="number" name="profit_rate" value="<?= htmlspecialchars($product['profit_rate']) ?>" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Days (Cycle)</label>
                                    <input type="number" name="cycle" value="<?= htmlspecialchars($product['cycle']) ?>" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Image</label>
                                    <input type="file" name="image" class="form-control">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" name="edit_compound_product" class="btn btn-primary">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</div>

    <!-- Add Product Modal -->
    <?php include 'models.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
