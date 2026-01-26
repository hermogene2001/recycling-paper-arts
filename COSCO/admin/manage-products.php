<?php
require_once '../includes/db.php';

// Delete product if requested
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: manage-products.php?success=1");
    exit;
}

// Fetch all products
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts - Manage Products</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    
    <style>
        /* Recycling Paper Arts Color Scheme */
        :root {
            --magazine-red: #E31E24;
            --magazine-navy: #003366;
            --magazine-light-blue: #0066CC;
            --magazine-gray: #F5F5F5;
            --magazine-dark-gray: #666666;
            --magazine-gold: #FFD700;
        }

        /* Magazine Navigation Styling */
        .magazine-navbar {
            background: linear-gradient(135deg, var(--magazine-navy), var(--magazine-red)) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            padding: 0.8rem 0;
            position: relative;
            overflow: visible;
            z-index: 1000;
        }

        .magazine-navbar::before {
            content: ''; 
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="waves" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M0,10 Q5,0 10,10 T20,10" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" fill="none"/></pattern></defs><rect width="100" height="100" fill="url(%23waves)"/></svg>') repeat;
            opacity: 0.3;
            z-index: 1;
        }

        .magazine-navbar .navbar-content {
            position: relative;
            z-index: 1001;
        }

        .magazine-navbar .navbar-brand {
            font-size: 1.8rem;
            font-weight: 700;
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            transition: all 0.3s ease;
        }

        .magazine-navbar .navbar-brand:hover {
            transform: scale(1.05);
            text-shadow: 2px 2px 8px rgba(0,0,0,0.5);
        }

        .magazine-navbar .navbar-brand i {
            color: var(--magazine-gold);
            margin-right: 0.5rem;
            animation: gentle-float 3s ease-in-out infinite;
        }

        @keyframes gentle-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-3px); }
        }

        .magazine-navbar .nav-link {
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease;
            margin: 0 0.3rem;
            border-radius: 8px;
            padding: 0.7rem 1.2rem !important;
            position: relative;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .magazine-navbar .nav-link::before {
            content: ''; 
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            transform: scaleX(0);
            transition: transform 0.3s ease;
            z-index: -1;
        }

        .magazine-navbar .nav-link:hover::before {
            transform: scaleX(1);
        }

        .magazine-navbar .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .magazine-navbar .nav-link i {
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        /* Dropdown specific styles */
        .magazine-navbar .dropdown {
            position: static;
        }

        .magazine-navbar .dropdown-toggle::after {
            margin-left: 0.5rem;
            transition: transform 0.3s ease;
        }

        .magazine-navbar .dropdown-toggle[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .magazine-navbar .dropdown-menu {
            background: linear-gradient(135deg, var(--magazine-navy), var(--magazine-red));
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            margin-top: 0.5rem;
            min-width: 200px;
            animation: dropdown-fade-in 0.3s ease;
            z-index: 9999;
            position: absolute;
        }

        @keyframes dropdown-fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .magazine-navbar .dropdown-item {
            color: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease;
            padding: 0.8rem 1.5rem;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-radius: 8px;
            margin: 0.2rem 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .magazine-navbar .dropdown-item::before {
            content: ''; 
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .magazine-navbar .dropdown-item:hover::before {
            left: 100%;
        }

        .magazine-navbar .dropdown-item:hover {
            background: rgba(255,255,255,0.15) !important;
            color: white !important;
            transform: translateX(5px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .magazine-navbar .dropdown-item i {
            margin-right: 0.7rem;
            font-size: 1rem;
            color: var(--magazine-gold);
        }

        .magazine-navbar .btn-danger {
            background: linear-gradient(135deg, var(--magazine-red), #c41e24) !important;
            border: 2px solid rgba(255,255,255,0.3) !important;
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .magazine-navbar .btn-danger::before {
            content: ''; 
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .magazine-navbar .btn-danger:hover::before {
            left: 100%;
        }

        .magazine-navbar .btn-danger:hover {
            background: linear-gradient(135deg, #c41e24, var(--magazine-red)) !important;
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(196, 30, 36, 0.4);
            border-color: rgba(255,255,255,0.6) !important;
        }

        /* Active nav item */
        .magazine-navbar .nav-link.active {
            background: rgba(255,255,255,0.15) !important;
            color: white !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        /* Body and Page Styling */
        body {
            background: linear-gradient(135deg, var(--magazine-gray), #e9ecef);
            min-height: 100vh;
            font-family: 'Arial', sans-serif;
        }

        .main-content {
            padding: 2rem 0;
            min-height: calc(100vh - 100px);
        }

        .page-header {
            background: linear-gradient(135deg, var(--magazine-navy), var(--magazine-red));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="waves" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><path d="M0,10 Q5,0 10,10 T20,10" stroke="rgba(255,255,255,0.05)" stroke-width="0.5" fill="none"/></pattern></defs><rect width="100" height="100" fill="url(%23waves)"/></svg>') repeat;
            opacity: 0.3;
        }

        .page-header .container {
            position: relative;
            z-index: 2;
        }

        .page-header h1 {
            font-weight: 700;
            margin: 0;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-header .subtitle {
            opacity: 0.9;
            font-size: 1.1rem;
            margin-top: 0.5rem;
        }

        /* Success Alert */
        .alert-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            animation: slide-in 0.5s ease;
        }

        @keyframes slide-in {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, var(--magazine-navy), var(--magazine-light-blue));
            color: white;
            border: none;
            padding: 1.5rem;
            font-weight: 600;
        }

        /* Add Product Button */
        .btn-add-product {
            background: linear-gradient(135deg, var(--magazine-red), #c41e24);
            color: white;
            border: none;
            padding: 0.8rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-add-product::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn-add-product:hover::before {
            left: 100%;
        }

        .btn-add-product:hover {
            background: linear-gradient(135deg, #c41e24, var(--magazine-red));
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(196, 30, 36, 0.4);
            color: white;
        }

        /* Table Styling */
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .table thead th {
            background: linear-gradient(135deg, var(--magazine-navy), var(--magazine-light-blue));
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }

        .table tbody tr:hover {
            background: rgba(0, 102, 204, 0.05);
            transform: scale(1.01);
            transition: all 0.3s ease;
        }

        /* Status Badges */
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-active {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .status-inactive {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            margin: 0.2rem;
        }

        .btn-edit {
            background: linear-gradient(135deg, #ffc107, #ffca2c);
            color: #333;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #ffca2c, #ffc107);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.4);
            color: #333;
        }

        .btn-delete {
            background: linear-gradient(135deg, #dc3545, #c82333);
            color: white;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #c82333, #dc3545);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
            color: white;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 25px;
            border: 2px solid var(--magazine-light-blue);
            padding: 0.5rem 1rem;
            margin-left: 0.5rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 20px;
            border: 2px solid var(--magazine-light-blue);
            padding: 0.3rem 0.8rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            border-radius: 20px !important;
            margin: 0.2rem !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, var(--magazine-navy), var(--magazine-red)) !important;
            color: white !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 1.8rem;
            }
            
            .btn-add-product {
                padding: 0.6rem 1.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <!-- Recycling Paper Arts Navigation -->
    <?php include '../includes/menu.php' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="container">
                    <h1><i class="fas fa-box me-3"></i>Investment Products Management</h1>
                    <p class="subtitle">Manage your recycling paper arts investment portfolio with precision and control</p>
                </div>
            </div>

            <!-- Success Alert -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> Operation completed successfully.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Add Product Button -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="text-dark"><i class="fas fa-chart-line me-2"></i>Active Investment Products</h3>
                <a href="add-product.php" class="btn btn-add-product">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </a>
            </div>

            <!-- Products Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Products Overview</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="productsTable">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                    <th><i class="fas fa-tag me-2"></i>Product Name</th>
                                    <th><i class="fas fa-percentage me-2"></i>Profit Rate</th>
                                    <th><i class="fas fa-clock me-2"></i>Investment Cycle</th>
                                    <th><i class="fas fa-dollar-sign me-2"></i>Min Investment</th>
                                    <th><i class="fas fa-users me-2"></i>Total Investors</th>
                                    <th><i class="fas fa-toggle-on me-2"></i>Status</th>
                                    <th><i class="fas fa-cogs me-2"></i>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><strong>#<?= $product['id'] ?></strong></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-newspaper text-primary me-2"></i>
                                                <strong><?= htmlspecialchars($product['name']) ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-success fs-6">
                                                <i class="fas fa-chart-line me-1"></i><?= $product['profit_rate'] ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <i class="fas fa-calendar-alt text-info me-2"></i>
                                            <?= $product['cycle'] ?> <?= $product['cycle_unit'] ?>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success">
                                                <i class="fas fa-dollar-sign me-1"></i><?= number_format($product['min_investment'], 2) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info fs-6">
                                                <i class="fas fa-users me-1"></i><?= $product['total_investors'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge <?= $product['status'] == 'active' ? 'status-active' : 'status-inactive' ?>">
                                                <i class="fas fa-<?= $product['status'] == 'active' ? 'check-circle' : 'times-circle' ?> me-1"></i>
                                                <?= ucfirst($product['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="edit-product.php?id=<?= $product['id'] ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit me-1"></i>Edit
                                            </a>
                                            <a href="?delete=<?= $product['id'] ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Are you sure you want to deactivate this product?')">
                                                <i class="fas fa-ban me-1"></i>Deactivate
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#productsTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [[0, 'desc']],
                language: {
                    search: "Search Products:",
                    lengthMenu: "Show _MENU_ products per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ products",
                    infoEmpty: "No products available",
                    infoFiltered: "(filtered from _MAX_ total products)"
                },
                columnDefs: [
                    { orderable: false, targets: [7] } // Actions column not sortable
                ]
            });

            // Auto-hide success alert after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });
    </script>
</body>
</html>