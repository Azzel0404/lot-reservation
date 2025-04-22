<!-- login.php -->
<?php
session_start();

// Optional: ensures session cookie is valid across subfolders
ini_set('session.cookie_path', '/');

require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Redirect if user is already logged in
if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'ADMIN':
            header("Location: admin/index.php");
            exit();
        case 'AGENT':
            header("Location: agent/index.php");
            exit();
        case 'CLIENT':
            header("Location: client/index.php");
            exit();
        default:
            header("Location: index.php");
            exit();
    }
}

// Initialize error message variable
$errorMessage = '';

// Process login
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
                // Set session values
                session_regenerate_id(true); // Prevent session fixation
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'ADMIN':
                        header("Location: admin/index.php");
                        exit();
                    case 'AGENT':
                        header("Location: agent/index.php");
                        exit();
                    case 'CLIENT':
                        header("Location: client/index.php");
                        exit();
                    default:
                        $errorMessage = 'Unknown role. Access denied.';
                        break;
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
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header style="display: flex; align-items: center; justify-content: space-between;">
        <a href="index.php" style="text-decoration: none;">
            <button type="button">← Back to Home</button>
        </a>
        <h1 style="margin: 0 auto;">Login to Your Account</h1>
    </header>

    <section>
        <?php if ($errorMessage): ?>
            <p style="color:red; font-weight:bold;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Log In</button>
        </form>

        <p>Don't have an account? <a href="register.php">Create Account</a></p>
    </section>
</body>
</html>
