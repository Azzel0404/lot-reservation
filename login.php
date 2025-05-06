<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'ADMIN': header("Location: admin/dashboard/index.php"); exit();
        case 'AGENT': header("Location: agent/index.php"); exit();
        case 'CLIENT': header("Location: client/available_lots.php"); exit();
        default: header("Location: index.php"); exit();
    }
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please enter both email and password.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                switch ($user['role']) {
                    case 'ADMIN': header("Location: admin/dashboard/index.php"); exit();
                    case 'AGENT': header("Location: agent/index.php"); exit();
                    case 'CLIENT': header("Location: client/index.php"); exit();
                    default: $errorMessage = 'Unknown role. Access denied.'; break;
                }
            } else {
                $errorMessage = 'Invalid credentials.';
            }
        } else {
            $errorMessage = 'Invalid credentials.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lot Reservation System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --secondary: #10b981;
            --dark: #1e293b;
            --light: #f8fafc;
            --gray: #94a3b8;
            --background: #f1f5f9;
            --error: #ef4444;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-image: url('https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            background-blend-mode: overlay;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .container {
            width: 900px;
            max-width: 90%;
            display: flex;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border-radius: 15px;
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .left {
            width: 40%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px;
            text-align: center;
        }

        .left .logo {
            font-size: 2.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .left .logo i {
            margin-right: 15px;
        }

        .left h2 {
            font-size: 1.8rem;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .left p {
            font-size: 0.9rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .right {
            width: 60%;
            background-color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right h2 {
            margin-bottom: 30px;
            text-align: center;
            font-size: 1.8rem;
            color: var(--dark);
            position: relative;
            padding-bottom: 15px;
        }

        .right h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background-color: #f8fafc;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .login-btn {
            background-color: var(--primary);
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
        }

        .form-footer {
            display: flex;
            flex-direction: column;
            gap: 15px;
            justify-content: center;
            align-items: center;
            margin-top: 25px;
        }

        .form-footer a {
            color: var(--primary);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .form-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .error {
            color: var(--error);
            background-color: rgba(239, 68, 68, 0.1);
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
            margin-bottom: 15px;
            border-left: 4px solid var(--error);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: var(--gray);
            font-size: 0.8rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e2e8f0;
        }

        .divider::before {
            margin-right: 10px;
        }

        .divider::after {
            margin-left: 10px;
        }

        .social-login {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .social-btn:hover {
            transform: translateY(-3px);
        }

        .facebook {
            background-color: #3b5998;
        }

        .google {
            background-color: #db4437;
        }

        .linkedin {
            background-color: #0077b5;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
                width: 95%;
            }

            .left, .right {
                width: 100%;
            }

            .left {
                padding: 30px 20px;
            }

            .right {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="logo">
                <i class="fas fa-map-marked-alt"></i>
                <span>ReserveIt</span>
            </div>
            <h2>Welcome Back!</h2>
            <p>Access your account to manage lot reservations, view availability, and more.</p>
        </div>

        <div class="right">
            <h2>Login to Your Account</h2>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="error">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="input-group">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="Email Address" required>
                </div>
                
                <div class="input-group">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="divider">or continue with</div>

            <div class="social-login">
                <div class="social-btn facebook">
                    <i class="fab fa-facebook-f"></i>
                </div>
                <div class="social-btn google">
                    <i class="fab fa-google"></i>
                </div>
                <div class="social-btn linkedin">
                    <i class="fab fa-linkedin-in"></i>
                </div>
            </div>

            <div class="form-footer">
                <a href="register.php">Don't have an account? Register</a>
                <a href="forgot-password.php">Forgot your password?</a>
                <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>
</body>
</html>