<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'ADMIN': header("Location: admin/index.php"); exit();
        case 'AGENT': header("Location: agent/index.php"); exit();
        case 'CLIENT': header("Location: client/index.php"); exit();
        default: header("Location: index.php"); exit();
    }
}

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please enter both email and password.';
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
                    case 'ADMIN': header("Location: admin/index.php"); exit();
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
    <title>Login - Lot Reservation System</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Optional if you want to extract shared styles -->
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: #f2f4f8; height: 100vh; display: flex; justify-content: center; align-items: center; }

        .container {
            width: 850px;
            height: 450px;
            display: flex;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }

        .left {
            width: 35%;
            background-color: #1e3a5f;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 30px;
        }

        .left h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .right {
            width: 65%;
            background-color: white;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right h2 {
            margin-bottom: 30px;
            text-align: center;
            font-size: 24px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        input[type="email"],
        input[type="password"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .login-btn {
            background-color: #28a745;
            color: white;
            padding: 12px;
            border: none;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
        }

        .register-btn {
            background-color: #5e2ced;
            color: white;
            padding: 10px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .error {
            color: red;
            font-weight: bold;
            text-align: center;
            margin-bottom: 10px;
        }

        .form-footer {
            display: flex;
            justify-content: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left">
            <h2>WELCOME!</h2>
            <p style="text-align:center;">Lot Reservation System</p>
        </div>

        <div class="right">
            <h2>LOGIN</h2>
            <?php if (!empty($errorMessage)): ?>
                <p class="error"><?= htmlspecialchars($errorMessage) ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="login-btn">Log In</button>
            </form>

            <div class="form-footer">
                <form action="register.php" method="get">
                    <button class="register-btn" type="submit">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
