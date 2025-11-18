<?php include('investments.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile | DeltaOne Investment</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            padding: 10px;
            margin: 0;
        }
        .container {
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1200px;
            margin-top: 80px;
            margin-bottom: 80px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .card-header {
            background-color: #2575fc;
            color: #fff;
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
        
        /* Media Queries for responsiveness */
        @media (max-width: 767px) {
            .container {
                padding: 20px;
                margin-top: 40px;
                margin-bottom: 40px;
            }
            .card-body {
                padding: 15px;
            }
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 15px;
            }
            .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
<?php include('nav.php'); ?>
<div class="container mt-7">
    <h2 class="text-center mb-4">Your Investments</h2>

    <div class="row">
        <!-- Regular Investments Column -->
        <div class="col-md-6">
            <h4>Active Regular Investments</h4>
            <div class="row">
                <?php if ($active_regular_result->num_rows > 0): ?>
                    <?php while ($active_regular = $active_regular_result->fetch_assoc()): ?>
                        <div class="col-md-4 d-flex justify-content-center">
                            <div class="card">
                                <div class="card-header">
                                    <strong><?php echo htmlspecialchars($active_regular['name']); ?></strong>
                                </div>
                                <div class="card-body">
                                    <p><strong>Price:</strong> RWF <?php echo number_format($active_regular['price'], 2); ?></p>
                                    <p><strong>Daily Earning:</strong> RWF <?php echo number_format($active_regular['daily_earning'], 2); ?></p>
                                    <p><strong>Cycle:</strong> <?php echo htmlspecialchars($active_regular['cycle']); ?> days</p>
                                    <p><strong>Purchased On:</strong> <?php echo date('F j, Y', strtotime($active_regular['purchase_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No active regular investments.</p>
                <?php endif; ?>
            </div>

            <hr>

            <h4>Completed Regular Investments</h4>
            <div class="row">
                <?php if ($completed_regular_result->num_rows > 0): ?>
                    <?php while ($completed_regular = $completed_regular_result->fetch_assoc()): ?>
                        <div class="col-md-4 d-flex justify-content-center">
                            <div class="card">
                                <div class="card-header">
                                    <strong><?php echo htmlspecialchars($completed_regular['name']); ?></strong>
                                </div>
                                <div class="card-body">
                                    <p><strong>Price:</strong> RWF <?php echo number_format($completed_regular['price'], 2); ?></p>
                                    <p><strong>Daily Earning:</strong> RWF <?php echo number_format($completed_regular['daily_earning'], 2); ?></p>
                                    <p><strong>Cycle:</strong> <?php echo htmlspecialchars($completed_regular['cycle']); ?> days</p>
                                    <p><strong>Purchased On:</strong> <?php echo date('F j, Y', strtotime($completed_regular['purchase_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No completed regular investments.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Compound Investments Column -->
        <div class="col-md-6">
            <h4>Active Compound Investments</h4>
            <div class="row">
                <?php if ($active_compound_result->num_rows > 0): ?>
                    <?php while ($active_compound = $active_compound_result->fetch_assoc()): ?>
                        <div class="col-md-4 d-flex justify-content-center">
                            <div class="card">
                                <div class="card-header">
                                    <strong><?php echo htmlspecialchars($active_compound['name']); ?></strong>
                                </div>
                                <div class="card-body">
                                    <p><strong>Price:</strong> RWF <?php echo number_format($active_compound['price'], 2); ?></p>
                                    <p><strong>Daily Earning:</strong> RWF <?php echo number_format($active_compound['daily_earning'], 2); ?></p>
                                    <p><strong>Cycle:</strong> <?php echo htmlspecialchars($active_compound['cycle']); ?> days</p>
                                    <p><strong>Purchased On:</strong> <?php echo date('F j, Y', strtotime($active_compound['maturity_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No active compound investments.</p>
                <?php endif; ?>
            </div>

            <hr>

            <h4>Completed Compound Investments</h4>
            <div class="row">
                <?php if ($completed_compound_result->num_rows > 0): ?>
                    <?php while ($completed_compound = $completed_compound_result->fetch_assoc()): ?>
                        <div class="col-md-4 d-flex justify-content-center">
                            <div class="card">
                                <div class="card-header">
                                    <strong><?php echo htmlspecialchars($completed_compound['name']); ?></strong>
                                </div>
                                <div class="card-body">
                                    <p><strong>Price:</strong> RWF <?php echo number_format($completed_compound['price'], 2); ?></p>
                                    <p><strong>Daily Earning:</strong> RWF <?php echo number_format($completed_compound['daily_earning'], 2); ?></p>
                                    <p><strong>Cycle:</strong> <?php echo htmlspecialchars($completed_compound['cycle']); ?> days</p>
                                    <p><strong>Purchased On:</strong> <?php echo date('F j, Y', strtotime($completed_compound['maturity_date'])); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No completed compound investments.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2025 DeltaOneInvestment. All Rights Reserved.</p>
</footer>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
