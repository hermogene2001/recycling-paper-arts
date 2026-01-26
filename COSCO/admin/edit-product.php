<?php
require_once '../includes/db.php';

if (!isset($_GET['id'])) {
    die("Product ID not specified.");
}

$productId = (int)$_GET['id'];
$product = $pdo->query("SELECT * FROM products WHERE id = $productId")->fetch();

if (!$product) {
    die("Product not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $profit_rate = $_POST['profit_rate'];
    $status = $_POST['status'];

    // Handle image upload (optional)
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/products/';
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename);
        $image = $filename;
    }

    $pdo->prepare("
        UPDATE products
        SET name = ?, price = ?, profit_rate = ?, status = ?, image = ?
        WHERE id = ?
    ")->execute([$name, $price, $profit_rate, $status, $image, $productId]);

    header("Location: manage-products.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Recycling Paper Arts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 20px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            color: white;
            font-size: 28px;
            font-weight: bold;
            text-decoration: none;
        }

        .logo i {
            margin-right: 12px;
            font-size: 32px;
        }

        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #ffd700;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-title h1 {
            color: #1e3c72;
            font-size: 32px;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }

        .page-title h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border-radius: 2px;
        }

        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 40px;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e3c72;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2a5298;
            background: white;
            box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
        }

        .file-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border: 2px dashed #2a5298;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: #e9ecef;
            border-color: #1e3c72;
        }

        .file-upload-label i {
            margin-right: 10px;
            color: #2a5298;
            font-size: 20px;
        }

        .current-image {
            margin-top: 10px;
            padding: 10px;
            background: #e8f4f8;
            border-radius: 6px;
            border-left: 4px solid #2a5298;
        }

        .current-image small {
            color: #1e3c72;
            font-weight: 500;
        }

        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .btn i {
            margin-right: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(30, 60, 114, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            flex: 1;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
        }

        .product-info {
            background: linear-gradient(135deg, #e8f4f8, #f0f8ff);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #2a5298;
        }

        .product-info h2 {
            color: #1e3c72;
            margin-bottom: 10px;
            font-size: 24px;
        }

        .product-info p {
            color: #666;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .container {
                padding: 0 15px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                gap: 15px;
            }
        }

        .loading {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }

        .loading.show {
            display: flex;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #2a5298;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <header class="header">
        <div class="header-content">
            <a href="#" class="logo">
                <i class="fas fa-newspaper"></i>
                Recycling Paper Arts
            </a>
            <!-- <nav class="nav-links">
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage-products.php"><i class="fas fa-boxes"></i> Products</a>
                <a href="orders.php"><i class="fas fa-clipboard-list"></i> Orders</a>
                <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
            </nav> -->
        </div>
    </header>

    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-edit"></i> Edit Product</h1>
        </div>

        <div class="product-info">
            <h2><?= htmlspecialchars($product['name']) ?></h2>
            <p><strong>Current Price:</strong> $<?= number_format($product['price'], 2) ?></p>
            <p><strong>Profit Rate:</strong> <?= $product['profit_rate'] ?>%</p>
            <p><strong>Status:</strong> 
                <span class="status-badge <?= $product['status'] === 'active' ? 'status-active' : 'status-inactive' ?>">
                    <?= ucfirst($product['status']) ?>
                </span>
            </p>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="editForm">
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-tag"></i> Product Name
                    </label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">
                            <i class="fas fa-dollar-sign"></i> Price
                        </label>
                        <input type="number" id="price" name="price" step="0.01" value="<?= $product['price'] ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="profit_rate">
                            <i class="fas fa-percentage"></i> Profit Rate (%)
                        </label>
                        <input type="number" id="profit_rate" name="profit_rate" step="0.01" value="<?= $product['profit_rate'] ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">
                        <i class="fas fa-toggle-on"></i> Status
                    </label>
                    <select id="status" name="status">
                        <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>
                            <i class="fas fa-check"></i> Active
                        </option>
                        <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>
                            <i class="fas fa-times"></i> Inactive
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="image">
                        <i class="fas fa-image"></i> Product Image
                    </label>
                    <div class="file-upload">
                        <input type="file" id="image" name="image" accept="image/*">
                        <div class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload new image or drag and drop</span>
                        </div>
                    </div>
                    <?php if ($product['image']): ?>
                        <div class="current-image">
                            <small><i class="fas fa-image"></i> Current Image: <?= htmlspecialchars($product['image']) ?></small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Product
                    </button>
                    <a href="manage-products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Products
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('editForm').addEventListener('submit', function(e) {
            document.getElementById('loading').classList.add('show');
        });

        // File upload enhancement
        const fileInput = document.getElementById('image');
        const fileLabel = document.querySelector('.file-upload-label span');
        
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                fileLabel.textContent = e.target.files[0].name;
            } else {
                fileLabel.textContent = 'Click to upload new image or drag and drop';
            }
        });

        // Drag and drop functionality
        const fileUpload = document.querySelector('.file-upload');
        
        fileUpload.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#1e3c72';
            this.style.backgroundColor = '#e9ecef';
        });
        
        fileUpload.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '#2a5298';
            this.style.backgroundColor = '#f8f9fa';
        });
        
        fileUpload.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '#2a5298';
            this.style.backgroundColor = '#f8f9fa';
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileLabel.textContent = files[0].name;
            }
        });
    </script>
</body>
</html>