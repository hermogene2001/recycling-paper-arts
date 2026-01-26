<?php
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $profit_rate = $_POST['profit_rate'];
    $cycle = $_POST['cycle'];
    
    // Handle file upload with validation
    $image = 'default.png';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowedTypes) && $_FILES['image']['size'] <= 5000000) { // 5MB limit
            $filename = 'product_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = $filename;
            }
        }
    }

    // Calculate daily earning based on cycle and profit rate
    $daily_earning = ($profit_rate / 100) * $price / $cycle; // Calculate based on cycle period

    $stmt = $pdo->prepare("INSERT INTO products (name, image, daily_earning, cycle, price, profit_rate) VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $name, $image, $daily_earning, $cycle, $price, $profit_rate
    ]);

    header("Location: products.php?success=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Investment Product | Admin Panel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .header p {
            font-size: 1.1em;
            opacity: 0.9;
        }

        .form-container {
            padding: 40px 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 1em;
        }

        .form-group label i {
            margin-right: 8px;
            color: #4facfe;
        }

        .form-control {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #4facfe;
            background: white;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .input-group input {
            flex: 1;
        }

        .input-group select {
            flex: 0 0 120px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
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
            display: block;
            padding: 15px;
            border: 2px dashed #4facfe;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .file-upload-label:hover {
            background: #e3f2fd;
            border-color: #2196f3;
        }

        .file-upload-label i {
            font-size: 2em;
            color: #4facfe;
            margin-bottom: 10px;
        }

        .calculator-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .calculator-section h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .calculation-result {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .calc-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #4facfe;
        }

        .calc-item .value {
            font-size: 1.5em;
            font-weight: bold;
            color: #1976d2;
        }

        .calc-item .label {
            font-size: 0.9em;
            color: #666;
            margin-top: 5px;
        }

        .submit-btn {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 50px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 30px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.3);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Investment Product Creator</h1>
            <p>Create new investment products for your clients</p>
        </div>

        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-grid">
                    <!-- Product Image -->
                    <div class="form-group full-width">
                        <label><i class="fas fa-image"></i> Product Image</label>
                        <div class="file-upload">
                            <input type="file" name="image" accept="image/*" id="imageInput">
                            <label for="imageInput" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div>Click to upload product image</div>
                                <small>Supported: JPG, PNG, GIF, WebP (Max 5MB)</small>
                            </label>
                        </div>
                    </div>

                    <!-- Product Name -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Product Name</label>
                        <input type="text" name="name" class="form-control" required placeholder="e.g., Premium Growth Plan">
                    </div>

                    <!-- Price -->
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Product Price ($)</label>
                        <input type="number" name="price" class="form-control" step="0.01" required placeholder="1000.00" id="priceInput">
                    </div>

                    <!-- Profit Rate -->
                    <div class="form-group">
                        <label><i class="fas fa-percentage"></i> Annual Profit Rate (%)</label>
                        <input type="number" name="profit_rate" class="form-control" step="0.01" required placeholder="12.50" id="profitInput">
                    </div>

                    <!-- Investment Cycle -->
                    <div class="form-group">
                        <label><i class="fas fa-calendar-alt"></i> Investment Cycle (Days)</label>
                        <input type="number" name="cycle" class="form-control" required placeholder="365" id="cycleInput">
                    </div>
                </div> 

                <!-- Investment Calculator -->
                <div class="calculator-section">
                    <h3><i class="fas fa-calculator"></i> Investment Calculator Preview</h3>
                    <p>See how your product will perform with these settings:</p>
                    <div class="calculation-result" id="calculationResult">
                        <div class="calc-item">
                            <div class="value" id="dailyEarning">$0.00</div>
                            <div class="label">Daily Earning</div>
                        </div>
                        <div class="calc-item">
                            <div class="value" id="totalReturn">$0.00</div>
                            <div class="label">Total Return</div>
                        </div>
                        <div class="calc-item">
                            <div class="value" id="roi">$0.00</div>
                            <div class="label">ROI (%)</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-plus-circle"></i> Create Investment Product
                </button>
            </form>
        </div>
    </div>

    <script>
        // Real-time calculation updates
        function updateCalculations() {
            const price = parseFloat(document.getElementById('priceInput').value) || 0;
            const profitRate = parseFloat(document.getElementById('profitInput').value) || 0;
            const cycle = parseInt(document.getElementById('cycleInput').value) || 1;
            
            if (price > 0 && profitRate > 0) {
                const dailyEarning = (profitRate / 100) * price / cycle;
                const totalReturn = price + ((profitRate / 100) * price);
                const roi = profitRate;
                
                // Update display
                document.getElementById('dailyEarning').textContent = '$' + dailyEarning.toFixed(2);
                document.getElementById('totalReturn').textContent = '$' + totalReturn.toFixed(2);
                document.getElementById('roi').textContent = roi.toFixed(2) + '%';
            }
        }

        // Add event listeners for real-time updates
        document.getElementById('priceInput').addEventListener('input', updateCalculations);
        document.getElementById('profitInput').addEventListener('input', updateCalculations);
        document.getElementById('cycleInput').addEventListener('input', updateCalculations);

        // File upload preview
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const label = document.querySelector('.file-upload-label');
                label.innerHTML = `
                    <i class="fas fa-check-circle" style="color: #4caf50;"></i>
                    <div>${file.name}</div>
                    <small>File selected successfully</small>
                `;
            }
        });

        // Initialize calculations on page load
        updateCalculations();
    </script>
</body>
</html>