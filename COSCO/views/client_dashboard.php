<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and has a 'client' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's ID
$clientId = $_SESSION['user_id'];

include '../includes/db.php';

// Fetch user balance
$user_query = "SELECT balance FROM users WHERE id = '$clientId'";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);
$user_balance = $user['balance'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts - Subscription Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #10b981;
            --primary-dark: #059669;
            --secondary: #6366f1;
            --accent: #f59e0b;
            --dark: #0f172a;
            --dark-light: #1e293b;
            --text: #334155;
            --text-light: #64748b;
            --bg: #f8fafc;
            --surface: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Animated gradient background */
        .gradient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #4facfe 75%, #00f2fe 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: -2;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glass morphism overlay */
        .glass-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(100px);
            z-index: -1;
        }

        /* Floating particles */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: float-particle 20s infinite;
        }

        .particle:nth-child(1) { width: 80px; height: 80px; left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { width: 60px; height: 60px; left: 30%; animation-delay: 2s; }
        .particle:nth-child(3) { width: 100px; height: 100px; left: 50%; animation-delay: 4s; }
        .particle:nth-child(4) { width: 70px; height: 70px; left: 70%; animation-delay: 6s; }
        .particle:nth-child(5) { width: 90px; height: 90px; left: 85%; animation-delay: 8s; }

        @keyframes float-particle {
            0%, 100% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 0.3; }
            90% { opacity: 0.3; }
            100% { transform: translateY(-100px) scale(1); }
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, var(--dark) 0%, var(--dark-light) 100%);
            color: white;
            padding: 20px 0;
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary);
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .company-name {
            font-size: 28px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Navigation */
        .side-nav {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            z-index: 1000;
            padding-top: 100px;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .side-nav.active {
            transform: translateX(0);
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-item i {
            margin-right: 15px;
            font-size: 20px;
            width: 24px;
            text-align: center;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 255, 255, 0.25);
            border-left: 4px solid var(--primary);
            color: white;
        }

        .nav-item.active {
            font-weight: bold;
        }

        .nav-toggle {
            position: fixed;
            left: 20px;
            top: 20px;
            z-index: 1100;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 24px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        /* Main Content */
        .main-content {
            margin-left: 0;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .main-content.shifted {
            margin-left: 250px;
        }

        /* Balance Card */
        .balance-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        .balance-amount {
            color: var(--primary);
            font-size: 32px;
            font-weight: bold;
        }

        /* Promo Banner */
        .promo-banner {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 100px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
    width: 100%;
    height: 180px;
    object-fit: cover;
    object-position: center;
    background-color: #f8f9fa;
}

        .product-content {
            padding: 20px;
        }

        .product-title {
            color: var(--dark);
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .product-stats {
            margin-bottom: 20px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .stat-value {
            font-weight: bold;
            color: var(--text);
        }

        .price-highlight {
            color: var(--primary);
        }

        .buy-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .buy-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .buy-btn:hover::before {
            left: 100%;
        }

        .buy-btn:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary));
            transform: translateY(-2px);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            .product-image {
                height: 180px;
            }
        }

        @media (max-width: 576px) {
            .side-nav {
                width: 200px;
            }
            
            .main-content.shifted {
                margin-left: 200px;
            }
            
            .product-image {
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="gradient-bg"></div>
    <div class="glass-overlay"></div>
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Navigation Toggle -->
    <button class="nav-toggle" id="navToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Side Navigation -->
    <nav class="side-nav" id="sideNav">
        <a href="client_dashboard.php" class="nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="purchased.php" class="nav-item">
            <i class="fas fa-chart-line"></i>
            <span>Income</span>
        </a>
        <a href="invite.php" class="nav-item">
            <i class="fas fa-users"></i>
            <span>Agent</span>
        </a>
        <a href="account.php" class="nav-item">
            <i class="fas fa-user"></i>
            <span>Personal</span>
        </a>
        <a href="../homepage.php" class="nav-item">
            <i class="fas fa-home"></i>
            <span>Homepage</span>
        </a>
        <a href="../actions/logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Header -->
        <div class="header">
            <div class="container">
                <div class="logo-container">
                    <div class="logo">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="company-name">Recycling Paper Arts Magazine</div>
                </div>
            </div>
        </div>

        <div class="container">
            <!-- Promo Banner -->
            <div class="promo-banner">
                <i class="fas fa-gift"></i> SPECIAL OFFER: Subscribe today and get 5% discount on your first magazine package!
            </div>
<?php
// Include database connection
include '../includes/db.php'; 

// Query to fetch social media links
$sql = "SELECT facebook, twitter, whatsapp, telegram FROM social_links LIMIT 1"; // Ensure only one record is fetched
$stmt = $conn->prepare($sql);
$stmt->execute();
$stmt->bind_result($facebookLink, $twitterLink, $whatsappLink, $telegramLink);
$stmt->fetch();
$stmt->close();

?>
    <style>
        .social-media-links a {
            margin: 0 10px;
            font-size: 24px;
            color: inherit;
            text-decoration: none;
        }
        .social-media-links a:hover {
            opacity: 0.8;
        }
    </style>
    <div class="social-media-links">
       <a href="tel:250781194394" target="_blank" aria-label="Call 250781194394">
    <i class="fas fa-phone" style="color:rgb(34, 87, 201);"></i>
    CallUs On
</a>
        <?php if (!empty($facebookLink)): ?>
            <a href="<?php echo htmlspecialchars($facebookLink); ?>" target="_blank" aria-label="Facebook">
                <i class="fab fa-facebook-f" style="color: #3b5998;"></i>
                facebook
            </a>
        <?php endif; ?>

        <?php if (!empty($twitterLink)): ?>
            <a href="<?php echo htmlspecialchars($twitterLink); ?>" target="_blank" aria-label="Twitter">
                <i class="fab fa-twitter" style="color: #1da1f2;"></i>
                X
            </a>
        <?php endif; ?>

        <?php if (!empty($whatsappLink)): ?>
            <a href="<?php echo htmlspecialchars($whatsappLink); ?>" target="_blank" aria-label="WhatsApp">
                <i class="fab fa-whatsapp" style="color: green;"></i>
                Whatsapp
            </a>
        <?php endif; ?>

        <?php if (!empty($telegramLink)): ?>
            <a href="<?php echo htmlspecialchars($telegramLink); ?>" target="_blank" aria-label="Telegram">
                <i class="fab fa-telegram" style="color: #0088cc;"></i>
                Telegram
            </a>
        <?php endif; ?>
    </div>
            <!-- Balance Card -->
            <div class="balance-card">
                <div class="balance-content">
                    <div class="balance-label">
                        <i class="fas fa-wallet"></i> Current Balance
                    </div>
                    <div class="balance-amount" id="userBalance"><?php echo number_format($user_balance, 2); ?> $</div>
                </div>
            </div>

            <!-- Products Grid -->
            <h2 class="text-dark mb-4"><i class="fas fa-newspaper"></i> Available Magazine Issues</h2>
            <h2 class="text-dark mb-4"><i class="fas fa-book"></i> Premium Magazine Collections</h2>
            <div class="products-grid">
                <?php
                // Fetch available products from the database
                $product_query = "SELECT id, name, daily_earning, cycle, price, image FROM products WHERE status = 'active'";
                $product_result = mysqli_query($conn, $product_query);
                
                while ($product = mysqli_fetch_assoc($product_result)):
                    $total_income = ($product['daily_earning'] * $product['cycle']) + $product['price'];
                ?>
                
                <div class="product-card">
                    <img src="../uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
                    <div class="product-content">
                        <h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        
                        <div class="product-stats">
                            <div class="stat-item">
                                <span>Price:</span>
                                <span class="stat-value price-highlight"><?php echo number_format($product['price'], 0); ?> $</span>
                            </div>
                            <div class="stat-item">
                                <span>Weekly Issue:</span>
                                <span class="stat-value"><?php echo htmlspecialchars($product['daily_earning'], 2); ?> $</span>
                            </div>
                            <div class="stat-item">
                                <span>Total Return:</span>
                                <span class="stat-value price-highlight"><?php echo number_format($total_income, 0); ?> $</span>
                            </div>
                            <div class="stat-item">
                                <span>Issue Period:</span>
                                <span class="stat-value"><?php echo htmlspecialchars($product['cycle']); ?> days</span>
                            </div>
                        </div>
                        
                        <button class="buy-btn" onclick="buyProduct(<?php echo $product['id']; ?>, <?php echo $product['price']; ?>)">
                            <i class="fas fa-shopping-cart"></i> Subscribe Now
                        </button>
                    </div>
                </div>
                
                <?php endwhile; ?>
            </div>
            <?php include 'products_display.php'; ?>
        </div>
        <?php include "about.php"; ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Navigation Toggle
        $(document).ready(function() {
            $('#navToggle').click(function() {
                $('#sideNav').toggleClass('active');
                $('#mainContent').toggleClass('shifted');
            });

            // Buy Product Function
            window.buyProduct = function(productId, price) {
                const userBalance = <?php echo $user_balance; ?>;
                if (userBalance >= price) {
                    if (confirm("Are you sure you want to subscribe to this magazine issue?")) {
                        window.location.href = "../actions/buy_product.php?product_id=" + productId;
                    }
                } else {
                    alert("Insufficient balance. Please add credits to continue your subscription.");
                }
            }

            // Daily earnings credit
            $.ajax({
                url: 'credit_daily_earnings.php',
                type: 'GET',
                success: function(response) {
                    console.log("Daily earnings processed");
                }
            });
        });
    </script>
</body>
</html>