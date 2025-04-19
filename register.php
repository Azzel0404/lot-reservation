<!--register.php-->

<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'CLIENT'; // Default role

    // Basic validation
    if (empty($email) || empty($password) || empty($phone)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if the email or phone already exists
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email or phone already exists.';
        } else {
            // Insert into database
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO user (email, password, role, phone, address) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssss", $email, $hashedPassword, $role, $phone, $address);

            if ($insertStmt->execute()) {
                $success = 'Account successfully created!';
            } else {
                $error = 'Registration failed. Please try again later.';
            }
            $insertStmt->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Lot Reservation System</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header style="display: flex; align-items: center; justify-content: space-between;">
        <a href="index.php" style="text-decoration: none;">
            <button type="button">← Back to Home</button>
        </a>
        <h1 style="margin: 0 auto;">Create Your Account</h1>
    </header>

    <section style="max-width: 400px; margin: 0 auto; padding: 20px;">
        <?php if (!empty($error)): ?>
            <p style="color:red; font-weight:bold;"><?= htmlspecialchars($error) ?></p>
        <?php elseif (!empty($success)): ?>
            <p style="color:green; font-weight:bold;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <input type="email" name="email" placeholder="Email" required><br><br>
            <input type="password" name="password" placeholder="Password" required><br><br>
            <input type="text" name="phone" placeholder="Phone Number" required><br><br>
            <input type="text" name="address" placeholder="Address (Optional)"><br><br>
            <select name="role" required>
                <option value="CLIENT" selected>Client</option>
                <option value="AGENT">Agent</option>
            </select><br><br>
            <button type="submit">Register</button>
        </form>

        <p style="margin-top: 10px;">Already have an account? <a href="login.php">Log in here</a></p>
    </section>
</body>
</html>
