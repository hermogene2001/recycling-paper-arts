<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}
require_once('../includes/db.php');
include('../includes/menu.php');

$products_query = "SELECT * FROM products";
$products_result = mysqli_query($conn, $products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Cosco Style</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --magazine-navy: #1e4d6b;
            --magazine-light-blue: #2980b9;
            --magazine-dark-navy: #0d2636;
            --magazine-accent: #3498db;
            --magazine-gray: #ecf0f1;
            --magazine-white: #ffffff;
        }

        body {
            background: linear-gradient(135deg, var(--magazine-gray) 0%, #ffffff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--magazine-dark-navy);
        }

        .magazine-header {
            background: linear-gradient(135deg, var(--magazine-navy) 0%, var(--magazine-light-blue) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(30, 77, 107, 0.3);
        }

        .magazine-header h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .magazine-header .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .magazine-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .magazine-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(30, 77, 107, 0.1);
            overflow: hidden;
            border: none;
            transition: all 0.3s ease;
        }

        .magazine-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(30, 77, 107, 0.2);
        }

        .magazine-table {
            margin: 0;
            border: none;
        }

        .magazine-table thead {
            background: linear-gradient(135deg, var(--magazine-navy) 0%, var(--magazine-light-blue) 100%);
            color: white;
        }

        .magazine-table thead th {
            border: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            position: relative;
        }

        .magazine-table thead th:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--magazine-accent);
        }

        .magazine-table tbody tr {
            border-bottom: 1px solid #e8f4f8;
            transition: all 0.3s ease;
        }

        .magazine-table tbody tr:hover {
            background: linear-gradient(135deg, #f8fbff 0%, #e8f4f8 100%);
            transform: scale(1.01);
        }

        .magazine-table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border: none;
            font-weight: 500;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 3px solid var(--magazine-accent);
            transition: all 0.3s ease;
        }

        .product-image:hover {
            transform: scale(1.2);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .magazine-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid;
        }

        .magazine-badge.active {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            border-color: #27ae60;
        }

        .magazine-badge.inactive {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            border-color: #e74c3c;
        }

        .magazine-btn {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            transition: all 0.3s ease;
            margin: 0 2px;
            position: relative;
            overflow: hidden;
        }

        .magazine-btn:before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transition: all 0.3s ease;
            transform: translate(-50%, -50%);
        }

        .magazine-btn:hover:before {
            width: 300px;
            height: 300px;
        }

        .magazine-btn-edit {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
        }

        .magazine-btn-edit:hover {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.4);
        }

        .magazine-btn-delete {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .magazine-btn-delete:hover {
            background: linear-gradient(135deg, #c0392b 0%, #a93226 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .magazine-btn-toggle {
            background: linear-gradient(135deg, var(--magazine-accent) 0%, var(--magazine-light-blue) 100%);
            color: white;
        }

        .magazine-btn-toggle:hover {
            background: linear-gradient(135deg, var(--magazine-light-blue) 0%, var(--magazine-navy) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .shipping-icon {
            color: var(--magazine-accent);
            margin-right: 0.5rem;
        }

        .stats-row {
            background: linear-gradient(135deg, var(--magazine-navy) 0%, var(--magazine-light-blue) 100%);
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(30, 77, 107, 0.3);
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            .magazine-header h1 {
                font-size: 2rem;
            }
            
            .magazine-table {
                font-size: 0.9rem;
            }
            
            .magazine-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="magazine-header">
        <div class="magazine-container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-newspaper magazine-icon"></i>Product Management</h1>
                    <p class="subtitle">Recycling Paper Arts - Investment Product Management Dashboard</p>
                </div>
                <div class="col-md-4 text-end">
                    <i class="fas fa-book" style="font-size: 4rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
<a href="add-product.php" class="btn btn-primary"> Add Product</a>
    <div class="magazine-container">
        <!-- Statistics Row -->
        <div class="stats-row">
            <div class="row">
                <div class="col-md-3 stat-item">
                    <span class="stat-number"><?= mysqli_num_rows($products_result); ?></span>
                    <span class="stat-label">Total Products</span>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">
                        <?php
                        mysqli_data_seek($products_result, 0);
                        $active_count = 0;
                        while($p = mysqli_fetch_assoc($products_result)) {
                            if($p['status'] === 'active') $active_count++;
                        }
                        echo $active_count;
                        ?>
                    </span>
                    <span class="stat-label">Active Products</span>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">
                        <?php
                        mysqli_data_seek($products_result, 0);
                        $total_value = 0;
                        while($p = mysqli_fetch_assoc($products_result)) {
                            $total_value += $p['price'];
                        }
                        echo '$' . number_format($total_value);
                        ?>
                    </span>
                    <span class="stat-label">Total Value</span>
                </div>
                <div class="col-md-3 stat-item">
                    <span class="stat-number">
                        <i class="fas fa-globe"></i>
                    </span>
                    <span class="stat-label">Global Fleet</span>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="magazine-card">
            <div class="table-responsive">
                <table class="table magazine-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID</th>
                            <th><i class="fas fa-tag"></i> Product Name</th>
                            <th><i class="fas fa-image"></i> Image</th>
                            <th><i class="fas fa-dollar-sign"></i> Daily Earning</th>
                            <th><i class="fas fa-percentage"></i> Profit Rate</th>
                            <th><i class="fas fa-clock"></i> Cycle</th>
                            <th><i class="fas fa-money-bill-wave"></i> Price</th>
                            <th><i class="fas fa-signal"></i> Status</th>
                            <th><i class="fas fa-cogs"></i> Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($products_result, 0);
                        while($product = mysqli_fetch_assoc($products_result)) { ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--magazine-navy);">#<?= $product['id']; ?></strong>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($product['name']); ?></strong>
                                </td>
                                <td>
                                    <img src="../uploads/<?= htmlspecialchars($product['image']); ?>" 
                                         class="product-image" 
                                         alt="<?= htmlspecialchars($product['name']); ?>">
                                </td>
                                <td>
                                    <strong style="color: var(--magazine-navy);">$<?= number_format($product['daily_earning'], 2); ?></strong>
                                </td>
                                <td>
                                    <strong style="color: var(--magazine-accent);"><?= $product['profit_rate']; ?>%</strong>
                                </td>
                                <td>
                                    <span style="color: var(--magazine-navy);">
                                        <i class="fas fa-calendar-alt"></i> <?= $product['cycle']; ?> days
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: var(--magazine-navy); font-size: 1.1rem;">$<?= number_format($product['price'], 2); ?></strong>
                                </td>
                                <td>
                                    <span class="magazine-badge <?= $product['status'] === 'active' ? 'active' : 'inactive'; ?>">
                                        <i class="fas fa-<?= $product['status'] === 'active' ? 'check-circle' : 'times-circle'; ?>"></i>
                                        <?= ucfirst($product['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <a href="../views/edit_product.php?id=<?= $product['id']; ?>" 
                                           class="magazine-btn magazine-btn-edit
                                           title="Edit Product">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="../actions/delete_product.php?id=<?= $product['id']; ?>" 
                                           class="magazine-btn magazine-btn-delete
                                           onclick="return confirm('Are you sure you want to delete this product?')"
                                           title="Delete Product">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        <a href="../actions/toggle_product_status.php?id=<?= $product['id']; ?>" 
                                           class="magazine-btn magazine-btn-toggle
                                           title="Toggle Status">
                                            <i class="fas fa-<?= $product['status'] === 'active' ? 'pause' : 'play'; ?>"></i>
                                            <?= $product['status'] === 'active' ? 'Stop' : 'Activate'; ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>