<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DeltaOne Investment - Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            padding: 20px;
            width: 100%;
            max-width: 400px;
            animation: fadeIn 1.5s ease-in-out;
            transform-origin: top;
        }
        .form-container h3 {
            text-align: center;
            color: #2575fc;
            font-weight: bold;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2575fc, #6a11cb);
        }
        @keyframes fadeIn {
            from {
                transform: scale(0.8);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
        .platform-info {
            text-align: center;
            margin-bottom: 20px;
        }
        .platform-info h1 {
            color: white;
            font-size: 1.8rem;
        }
        .platform-info p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="platform-info">
            <h1>Welcome to DeltaOne Investment</h1>
            <p>Your trusted platform for smart investments</p>
        </div>
        <div class="form-container">
            <form method="POST" action="auth/login">
                <h3>Login</h3>
                <div class="mb-3">
                    <label for="loginPhone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="loginPhone" name="phone_number" placeholder="Enter your phone number" required>
                </div>
                <div class="mb-3">
                    <label for="loginPassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="loginPassword" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <a href="signup" class="btn btn-link">Don't have an account? Sign up</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
