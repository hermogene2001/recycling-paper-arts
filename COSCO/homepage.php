<?php
// Database connection
require_once('includes/db.php');

// Fetch products
$products_query = "SELECT * FROM products WHERE status = 'active' ORDER BY id DESC";
$products_result = mysqli_query($conn, $products_query);

// Count products
$product_count = mysqli_num_rows($products_result);

// Fetch first 3 products for display
$limited_products_query = "SELECT * FROM products WHERE status = 'active' ORDER BY id DESC LIMIT 3";
$limited_products_result = mysqli_query($conn, $limited_products_query);

// Check if user is logged in
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recycling Paper Arts - Creative Paper Craft Community</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600;700&family=Playfair+Display:wght@400;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-main: #f9f5f0; /* Warm off-white background */
            --text-main: #3e3323; /* Dark brown-gray text */
            --text-muted: #7d6e5c; /* Muted brown text */
            --primary: #6b8e23; /* Olive green */
            --secondary: #8b6f47; /* Earthy brown */
            --section-bg: #f0e6d2; /* Light paper-like background */
            --accent: #a89f8c; /* Warm gray */
            --accent-light: #d4c8b0; /* Lighter accent */
            --paper-border: #d1c3a9; /* Paper-like border */
            
            /* Nature-inspired variables */
            --nature-green: #8a9a5b;
            --leaf-green: #5a7d50;
            --wood-brown: #8b6f47;
            --earth-brown: #7d6e5c;
            --paper-tan: #f5f0e6;
            --dark: #3e3323;
            --text: #3e3323;
            --text-light: #7d6e5c;
            --bg: #f9f5f0;
            --surface: #fefdfb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Lora', serif;
            background: var(--bg-main);
            color: var(--text-main);
            min-height: 100vh;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(212, 200, 176, 0.1) 1.5%, transparent 2%),
                radial-gradient(circle at 90% 80%, rgba(107, 142, 35, 0.1) 1.5%, transparent 2%);
        }
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--dark);
        }

        /* Paper texture effect */
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

        /* Main content wrapper */
        .container-fluid {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .content-wrapper {
            max-width: 1200px;
            width: 100%;
            background: var(--surface);
            border-radius: 16px;
            padding: 40px 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--paper-border);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        /* Paper texture background */
        .content-wrapper::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d1c3a9' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            border-radius: 16px;
            z-index: -1;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            padding-bottom: 15px;
        }
        
        .section-header {
            text-align: center;
            margin-bottom: 50px;
            position: relative;
            padding-bottom: 20px;
        }
        
        .section-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }
        
        .section-header::before {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 4px;
            background: linear-gradient(to right, transparent, var(--accent-light), transparent);
        }
        
        .featured-article {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            margin: 20px 0;
            transition: all 0.3s ease;
            border: 1px solid var(--paper-border);
            position: relative;
        }
        
        .featured-article::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d1c3a9' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            border-radius: 12px;
            z-index: -1;
        }
        
        .featured-article:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
        }
        
        .magazine-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }
        
        .magazine-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid var(--paper-border);
            position: relative;
        }
        
        .magazine-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d1c3a9' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            border-radius: 12px;
            z-index: -1;
        }
        
        .magazine-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        
        .btn-magazine {
            background: linear-gradient(135deg, var(--primary), var(--leaf-green));
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(107, 142, 35, 0.25);
        }
        
        .btn-magazine:hover {
            background: linear-gradient(135deg, var(--leaf-green), var(--nature-green));
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(107, 142, 35, 0.35);
        }
        
        .sidebar {
            background: var(--section-bg);
            border-radius: 16px;
            padding: 30px;
            height: fit-content;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
            border: 1px solid var(--paper-border);
            position: relative;
        }
        
        .sidebar::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d1c3a9' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            border-radius: 16px;
            z-index: -1;
        }
        
        .sidebar h5 {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--primary);
            position: relative;
            color: var(--dark);
        }
        
        .sidebar h5::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background: var(--leaf-green);
        }
        
        .sidebar-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .sidebar-item:last-child {
            border-bottom: none;
        }
        
        .recent-post {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .recent-post:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .post-thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .post-title {
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .post-meta {
            font-size: 0.8rem;
            color: var(--text-muted);
        }
        
        .footer {
            background: linear-gradient(135deg, var(--wood-brown), #5a4e3d);
            color: white;
            padding: 60px 0 30px;
            margin-top: 80px;
        }
        
        .footer-widget {
            margin-bottom: 30px;
        }
        
        .footer-links a {
            color: #bbb;
            text-decoration: none;
            display: block;
            margin-bottom: 8px;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--primary);
        }
        
        .social-icons {
            display: flex;
            gap: 15px;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .social-icons a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 40px;
            text-align: center;
            color: #bbb;
        }
        
        .hero-section {
            background: linear-gradient(rgba(62, 51, 35, 0.7), rgba(62, 51, 35, 0.7)), url('https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80') center/cover;
            color: white;
            padding: 150px 0 100px;
            text-align: center;
            position: relative;
            margin-bottom: 60px;
            border-radius: 20px;
            overflow: hidden;
        }
        
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            z-index: 1;
        }
        
        .article-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
        }
        
        .article-content {
            padding: 25px;
        }
        
        .article-meta {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .magazine-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .magazine-info {
            padding: 20px;
        }

        .content-header {
            margin-bottom: 40px;
        }

        .content-title {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 16px;
            background: linear-gradient(135deg, var(--dark), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .content-subtitle {
            color: var(--text-light);
            font-size: 1.1rem;
            margin-bottom: 40px;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .feature-card {
            background: linear-gradient(135deg, #f8fafc, white);
            padding: 32px 24px;
            border-radius: 16px;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
            border-color: var(--primary);
        }

        .feature-card i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            display: block;
        }

        .feature-card h4 {
            color: var(--dark);
            margin-bottom: 12px;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .feature-card p {
            color: var(--text-light);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .cta-buttons {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-primary {
            height: 56px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            padding: 0 32px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.3);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(16, 185, 129, 0.4);
        }

        .btn-primary:hover::before {
            left: 100%;
        }

        .btn-secondary {
            height: 56px;
            background: linear-gradient(135deg, var(--secondary), #4f46e5);
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            padding: 0 32px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(99, 102, 241, 0.4);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 40px 24px;
                margin: 20px;
            }
            
            .content-title {
                font-size: 2rem;
            }
            
            .cta-buttons {
                flex-direction: column;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
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

    <div class="container-fluid">
        <div class="content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light mb-5">
                <div class="container">
                    <a class="navbar-brand d-flex align-items-center" href="#">
                        <i class="fas fa-leaf me-2" style="color: var(--leaf-green);"></i>
                        <span class="fw-bold" style="font-size: 1.5rem; background: linear-gradient(135deg, var(--leaf-green), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">RECYCLING PAPER ARTS</span>
                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="#home" style="color: var(--text); font-weight: 500;">Home</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#featured" style="color: var(--text); font-weight: 500;">Featured</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#magazines" style="color: var(--text); font-weight: 500;">Magazines</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="#articles" style="color: var(--text); font-weight: 500;">Articles</a>
                            </li>
                            <?php if ($isLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="views/account.php" style="color: var(--text); font-weight: 500;">My Account</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="actions/logout.php" style="color: var(--text); font-weight: 500;">Logout</a>
                            </li>
                            <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="index.php" style="color: var(--text); font-weight: 500;">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="views/register.php" style="color: var(--text); font-weight: 500;">Subscribe</a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <section id="home" class="mb-5">
                <div class="text-center">
                    <h1 class="display-3 fw-bold mb-4" style="background: linear-gradient(135deg, var(--leaf-green), var(--primary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">SUSTAINABLE PAPER CREATIONS</h1>
                    <p class="lead mb-4">Transform ordinary paper into extraordinary art. Learn techniques from our community of eco-conscious crafters.</p>
                    <div class="cta-buttons">
                        <a href="#magazines" class="btn btn-primary">EXPLORE CREATIONS</a>
                        <a href="#articles" class="btn btn-outline-light border-white">LEARN TECHNIQUES</a>
                    </div>
                </div>
            </section>

            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Featured Article Section -->
                        <section id="featured" class="mb-5">
                            <h2 class="section-header mb-4 text-center" style="color: var(--dark); font-size: 1.8rem; font-weight: 700;">FEATURED TUTORIAL</h2>
                            <div class="featured-article bg-white rounded-xl shadow-lg overflow-hidden mb-4" style="border-radius: 16px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;">
                                <img src="https://images.unsplash.com/photo-1543269865-cbf4ce699d0d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80" alt="Featured Tutorial" class="article-image w-100" style="height: 300px; object-fit: cover;">
                                <div class="article-content p-4">
                                    <div class="article-meta text-muted mb-2" style="color: var(--text-light) !important;">PAPER CRAFTS • April 12, 2024</div>
                                    <h3 class="mb-3" style="color: var(--dark);">Origami Birds from Recycled Paper</h3>
                                    <p class="text-muted mb-3">Step-by-step tutorial on creating beautiful origami birds using only recycled paper materials.</p>
                                    <a href="#" class="btn btn-primary">VIEW TUTORIAL</a>
                                </div>
                            </div>
                        </section>

                        <!-- Latest Articles Section -->
                        <section id="articles" class="mb-5">
                            <h2 class="section-header mb-4 text-center" style="color: var(--dark); font-size: 1.8rem; font-weight: 700;">LATEST ARTICLES</h2>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="featured-article bg-white rounded-xl shadow-lg overflow-hidden" style="border-radius: 16px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;">
                                        <img src="https://images.unsplash.com/photo-1543269865-cbf4ce699d0d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80" alt="Article" class="article-image w-100" style="height: 200px; object-fit: cover;">
                                        <div class="article-content p-4">
                                            <div class="article-meta text-muted mb-2" style="color: var(--text-light) !important;">CRAFTS • March 10, 2024</div>
                                            <h4 class="mb-3" style="color: var(--dark);">Paper Flowers Tutorial</h4>
                                            <p class="text-muted mb-0">Step-by-step guide to creating realistic paper flowers for home decoration.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="featured-article bg-white rounded-xl shadow-lg overflow-hidden" style="border-radius: 16px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;">
                                        <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80" alt="Article" class="article-image w-100" style="height: 200px; object-fit: cover;">
                                        <div class="article-content p-4">
                                            <div class="article-meta text-muted mb-2" style="color: var(--text-light) !important;">ENVIRONMENT • March 8, 2024</div>
                                            <h4 class="mb-3" style="color: var(--dark);">Impact of Recycling</h4>
                                            <p class="text-muted mb-0">Understanding how paper recycling contributes to environmental sustainability.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Sidebar -->
                        <div class="sidebar bg-white rounded-xl p-4 shadow-lg" style="border-radius: 16px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;">
                            <h5 class="mb-4 pb-2" style="color: var(--dark); border-bottom: 2px solid var(--primary); display: inline-block;">ABOUT US</h5>
                            <p class="text-muted mb-4">Recycling Paper Arts is dedicated to promoting sustainable arts and crafts. We provide exclusive content about innovative ways to transform recycled paper into beautiful and functional art pieces.</p>
                            
                            <h5 class="mt-4 mb-4 pb-2" style="color: var(--dark); border-bottom: 2px solid var(--primary); display: inline-block;">RECENT TUTORIALS</h5>
                            <div class="recent-post d-flex gap-3 mb-3 pb-3" style="border-bottom: 1px solid #eee;">
                                <img src="https://images.unsplash.com/photo-1543269865-cbf4ce699d0d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80" alt="Tutorial" class="post-thumbnail" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <div class="post-title mb-1" style="color: var(--dark); font-weight: 500;">3D Paper Flowers</div>
                                    <div class="post-meta" style="color: var(--text-light) !important;">April 10, 2024</div>
                                </div>
                            </div>
                            <div class="recent-post d-flex gap-3 mb-3 pb-3" style="border-bottom: 1px solid #eee;">
                                <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80" alt="Tutorial" class="post-thumbnail" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <div class="post-title mb-1" style="color: var(--dark); font-weight: 500;">Cardboard Furniture</div>
                                    <div class="post-meta" style="color: var(--text-light) !important;">April 5, 2024</div>
                                </div>
                            </div>
                            <div class="recent-post d-flex gap-3 mb-3" style="border-bottom: none;">
                                <img src="https://images.unsplash.com/photo-1519681393784-d120267933ba?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1740&q=80" alt="Tutorial" class="post-thumbnail" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                                <div>
                                    <div class="post-title mb-1" style="color: var(--dark); font-weight: 500;">Paper Mache Basics</div>
                                    <div class="post-meta" style="color: var(--text-light) !important;">April 1, 2024</div>
                                </div>
                            </div>
                            
                            <h5 class="mt-4 mb-4 pb-2" style="color: var(--dark); border-bottom: 2px solid var(--primary); display: inline-block;">CATEGORIES</h5>
                            <div class="sidebar-item pb-2" style="border-bottom: 1px solid #eee;">
                                <a href="#" class="text-decoration-none" style="color: var(--text);">Origami Creations</a>
                            </div>
                            <div class="sidebar-item pb-2" style="border-bottom: 1px solid #eee;">
                                <a href="#" class="text-decoration-none" style="color: var(--text);">Upcycled Art</a>
                            </div>
                            <div class="sidebar-item pb-2" style="border-bottom: 1px solid #eee;">
                                <a href="#" class="text-decoration-none" style="color: var(--text);">Eco-Friendly DIY</a>
                            </div>
                            <div class="sidebar-item" style="border-bottom: none;">
                                <a href="#" class="text-decoration-none" style="color: var(--text);">Paper Recycling Tips</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Creations Section -->
            <section id="magazines" class="py-5 mt-5">
                <h2 class="section-header mb-4 text-center" style="color: var(--dark); font-size: 1.8rem; font-weight: 700;">PAPER ART COLLECTIONS</h2>
                <p class="text-center text-muted mb-5">Explore our curated collection of sustainable paper art projects</p>
                
                <div class="magazine-grid row g-4">
                    <?php
                    // Reset result pointer
                    mysqli_data_seek($limited_products_result, 0);
                    // Display limited products
                    while($product = mysqli_fetch_assoc($limited_products_result)):
                        $total_income = ($product['daily_earning'] * $product['cycle']) + $product['price'];
                        $roi_percentage = (($total_income - $product['price']) / $product['price']) * 100;
                    ?>
                    <div class="col-md-4">
                        <div class="magazine-card bg-white rounded-xl overflow-hidden shadow-lg" style="border-radius: 16px !important; box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important; height: 100%;">
                            <div class="position-relative">
                                <img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="magazine-image w-100" style="height: 200px; object-fit: cover;">
                                <div class="position-absolute top-0 end-0 m-3">
                                    <span class="badge bg-success px-3 py-2" style="border-radius: 20px; font-weight: 600;">
                                        <i class="fas fa-chart-line me-1"></i><?php echo number_format($roi_percentage, 1); ?>% ROI
                                    </span>
                                </div>
                            </div>
                            <div class="magazine-info p-4">
                                <h5 class="mb-3" style="color: var(--dark); font-weight: 700;"><?php echo htmlspecialchars($product['name']); ?></h5>
                                
                                <div class="product-details mb-4">
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i>Duration</small>
                                        <strong><?php echo $product['cycle']; ?> days</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <small class="text-muted"><i class="fas fa-percentage me-1"></i>Daily Return</small>
                                        <strong class="text-success">$<?php echo number_format($product['daily_earning'], 2); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <small class="text-muted"><i class="fas fa-trophy me-1"></i>Total Return</small>
                                        <strong class="text-primary">$<?php echo number_format($total_income, 2); ?></strong>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="h5 mb-0 text-dark" style="font-weight: 800;">$<?php echo number_format($product['price'], 2); ?></div>
                                        <small class="text-muted">Starting Investment</small>
                                    </div>
                                    <?php if ($isLoggedIn): ?>
                                    <a href="actions/buy_product.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary btn-lg px-4 py-2" style="border-radius: 12px; font-weight: 600;">
                                        <i class="fas fa-shopping-cart me-2"></i>GET KIT
                                    </a>
                                    <?php else: ?>
                                    <a href="index.php" class="btn btn-primary btn-lg px-4 py-2" style="border-radius: 12px; font-weight: 600;">
                                        <i class="fas fa-lock me-2"></i>Login to Buy
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <?php if ($product_count > 3): ?>
                <div class="text-center mt-5">
                    <a href="#" class="btn btn-outline-primary btn-lg">VIEW ALL PROJECTS</a>
                </div>
                <?php endif; ?>
            </section>

            <!-- Newsletter Section -->
            <section class="py-5 mt-5 bg-light rounded-xl" style="background: linear-gradient(135deg, var(--section-bg), white) !important; border-radius: 16px !important; border: 1px solid var(--paper-border);">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="mb-2" style="color: var(--dark);">Join Our Paper Art Community</h3>
                            <p class="text-muted mb-0">Receive weekly tutorials, tips, and inspiration for sustainable crafting.</p>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="email" class="form-control py-3" placeholder="Enter your email" style="border-radius: 12px; border: 1px solid var(--paper-border);">
                                <button class="btn btn-primary" type="button" style="border-radius: 12px;">Subscribe</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-5" style="background: var(--dark) !important;">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-4">
                    <h5 class="mb-4 text-white">RECYCLING PAPER ARTS</h5>
                    <p class="text-muted">Promoting sustainable arts and crafts through premium magazine content and educational resources.</p>
                    <div class="social-icons mt-4">
                        <a href="#" class="me-3 text-white" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: all 0.3s ease;">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="me-3 text-white" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: all 0.3s ease;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="me-3 text-white" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: all 0.3s ease;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-white" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; transition: all 0.3s ease;">
                            <i class="fab fa-pinterest"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-4 text-white">QUICK LINKS</h5>
                    <div class="footer-links">
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Home</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">About Us</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Projects</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Tutorials</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Contact</a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-4 text-white">CATEGORIES</h5>
                    <div class="footer-links">
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Origami</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Paper Sculptures</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Eco Art</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Recycling Tips</a>
                        <a href="#" class="d-block text-muted mb-2 text-decoration-none" style="transition: color 0.3s ease;">Upcycling Ideas</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <h5 class="mb-4 text-white">CONTACT INFO</h5>
                    <p class="text-muted mb-2"><i class="fas fa-map-marker-alt me-2"></i> 456 Eco Craft Center, Green Valley</p>
                    <p class="text-muted mb-2"><i class="fas fa-phone me-2"></i> +1 (555) 456-7890</p>
                    <p class="text-muted mb-0"><i class="fas fa-envelope me-2"></i> hello@recyclingpaperarts.com</p>
                </div>
            </div>
            <div class="copyright mt-5 pt-4 border-top border-secondary">
                <p class="text-center text-muted mb-0">&copy; 2024 Recycling Paper Arts. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to feature cards
            const featureCards = document.querySelectorAll('.magazine-card, .featured-article');
            featureCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>