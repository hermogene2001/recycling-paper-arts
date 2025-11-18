<?php
// Include database connection
include('../includes/db_connection.php');

// Fetch Regular Products
$regularProductsQuery = "SELECT * FROM products WHERE status = 'active'";
$regularStmt = $conn->prepare($regularProductsQuery);
$regularStmt->execute();
$regularResult = $regularStmt->get_result();

$regularProducts = [];
if ($regularResult->num_rows > 0) {
    while ($row = $regularResult->fetch_assoc()) {
        // Calculate total income for each regular product
        $row['total_income'] = ($row['daily_earning'] * $row['cycle']) + $row['price'];
        $regularProducts[] = $row;
    }
}

// Fetch Compound Products
$compoundProductsQuery = "SELECT * FROM products_compound WHERE status = 'active'";
$compoundStmt = $conn->prepare($compoundProductsQuery);
$compoundStmt->execute();
$compoundResult = $compoundStmt->get_result();

$compoundProducts = [];
if ($compoundResult->num_rows > 0) {
    while ($row = $compoundResult->fetch_assoc()) {
        // Calculate total income for each compound product
        $row['total_income'] = ($row['daily_earning'] * $row['cycle']) + $row['price'];
        $compoundProducts[] = $row;
    }
}

$regularStmt->close();
$compoundStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Products | DeltaOne Investment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            font-family: Arial, sans-serif;
            margin: 0;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 1200px;
            margin-top: 80px;
            margin-bottom: 50px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }
        .info-section {
            background-color: #2575fc;
            color: #fff;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .info-section h4 {
            margin-bottom: 15px;
            font-size: 1.8rem;
        }
        .info-section p {
            font-size: 1rem;
            line-height: 1.6;
        }
        footer {
            background-color: #2575fc;
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        .icon {
            color: #f4f4f4;
            font-size: 20px;
            margin-right: 8px;
        }
        .card-img-top {
            border-radius: 10px 10px 0 0;
            max-width: 100%;
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include('nav.php'); ?>

    <div class="container">
        <!-- Compound Products Section -->
        <div class="info-section">
            <h4>What Are Compound Investments?</h4>
            <p>
                Compound investments allow you to earn daily profits based on your investment amount and profit rate. 
                Your earnings are calculated daily and are reinvested automatically, helping you achieve higher returns 
                over the investment cycle. This method is ideal for individuals looking for steady and growing returns 
                over time.
            </p>
            <p>
                <strong>How It Works:</strong>
                <ol>
                    <li>Choose a product with a suitable investment cycle and profit rate.</li>
                    <li>Invest the specified amount and start earning daily profits.</li>
                    <li>Your profits are compounded daily, increasing your total income.</li>
                    <li>And you are allowed to withdraw your total earnings or reinvest at the end.</li>
                </ol>
            </p>
        </div>
        <h3>Compound Products</h3>
        <div class="row">
            <?php if (empty($compoundProducts)): ?>
                <p>No compound products available at the moment.</p>
            <?php else: ?>
                <?php foreach ($compoundProducts as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><strong>Daily Earning:</strong> RWF <?php echo htmlspecialchars($product['daily_earning']); ?></p>
                                <p class="card-text"><strong>Investment Cycle:</strong> <?php echo htmlspecialchars($product['cycle']); ?> Days</p>
                                <p class="card-text"><strong>Price:</strong> RWF <?php echo htmlspecialchars($product['price']); ?></p>
                                <p class="card-text text-success"><strong>Total Income:</strong> RWF <?php echo number_format($product['total_income'], 2); ?></p>
                                <a href="purchase_compound.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-cart-plus icon"></i> Buy Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Regular Products Section -->
        <div class="info-section">
            <h4>What Are Regular Investments?</h4>
            <p>
                Regular investments offer straightforward and predictable returns. Unlike compound investments, 
                your daily profits are not reinvested but remain fixed throughout the investment cycle. This 
                approach is ideal for individuals who prefer simplicity and stability in their earnings.
            </p>
            <p>
                <strong>How It Works:</strong>
                <ol>
                    <li>Choose a product with a fixed investment cycle and profit rate.</li>
                    <li>Invest the specified amount and start earning daily profits immediately.</li>
                    <li>Your daily profits remain constant throughout the investment cycle.</li>
                    <li>At the end of the cycle, withdraw your initial investment along with the accumulated profit.</li>
                </ol>
            </p>
        </div>
        <h3>Regular Products</h3>
        <div class="row">
            <?php if (empty($regularProducts)): ?>
                <p>No regular products available at the moment.</p>
            <?php else: ?>
                <?php foreach ($regularProducts as $product): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                <p class="card-text"><strong>Daily Earning:</strong> RWF <?php echo htmlspecialchars($product['daily_earning']); ?></p>
                                <p class="card-text"><strong>Investment Cycle:</strong> <?php echo htmlspecialchars($product['cycle']); ?> Days</p>
                                <p class="card-text"><strong>Price:</strong> RWF <?php echo htmlspecialchars($product['price']); ?></p>
                                <p class="card-text text-success"><strong>Total Income:</strong> RWF <?php echo number_format($product['total_income'], 2); ?></p>
                                <a href="purchase_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-cart-plus icon"></i> Buy Now
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
