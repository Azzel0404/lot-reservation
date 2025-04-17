<?php
session_start();

require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form inputs
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'CLIENT'; // Default role

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check if the email already exists
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Email already exists
            $error = 'An account with this email already exists.';
        } else {
            // Email is unique, proceed with insertion
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO user (email, password, role) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $email, $hashedPassword, $role);

            if ($insertStmt->execute()) {
                // Registration successful
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;

                // Redirect based on role
                switch ($role) {
                    case 'ADMIN':
                        header("Location: admin/index.php");
                        break;
                    case 'AGENT':
                        header("Location: agent/index.php");
                        break;
                    case 'CLIENT':
                    default:
                        header("Location: user/index.php");
                        break;
                }
                exit();
            } else {
                // Handle insertion error
                $error = 'Registration failed. Please try again later.';
            }
        }
        $stmt->close();
    }
}
?>
