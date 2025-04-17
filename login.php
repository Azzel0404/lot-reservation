<!-- login.php -->
<?php
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . '/LOT-RESERVATION-SYSTEM-main/includes/db.php';

// Check if the user is already logged in
if (isset($_SESSION['email']) && isset($_SESSION['role'])) {
    // Redirect based on user role
    switch ($_SESSION['role']) {
        case 'ADMIN':
            header("Location: admin/index.php");
            break;
        case 'AGENT':
            header("Location: agent/index.php");
            break;
        case 'CLIENT':
            header("Location: user/index.php");
            break;
        default:
            header("Location: index.php");
            break;
    }
    exit();
}

// Initialize error message variable
$errorMessage = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please enter both email and password.';
    } else {
        require_once 'includes/config.php'; // Ensure this path is correct

        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Assuming passwords are hashed using password_hash()
            if (password_verify($password, $user['password'])) {
                // Assign values to session variables
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                switch ($user['role']) {
                    case 'ADMIN':
                        header("Location: admin/index.php");
                        break;
                    case 'AGENT':
                        header("Location: agent/index.php");
                        break;
                    case 'CLIENT':
                        header("Location: user/index.php");
                        break;
                    default:
                        $errorMessage = 'Unknown role. Access denied.';
                        break;
                }
                exit();
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
    <header>
        <h1>Login to Your Account</h1>
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
