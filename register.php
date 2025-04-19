<!--register.php-->
<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // General user input
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $role = $_POST['role'] ?? 'CLIENT';

    // Name details
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');

    // Role-specific
    $license = trim($_POST['license_number'] ?? '');
    $assigned_agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;

    // Required fields check
    if (empty($email) || empty($password) || empty($phone) || empty($firstname) || empty($lastname)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Check for duplicate email or phone
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email or phone already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO user (email, password, role, phone, address) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssss", $email, $hashedPassword, $role, $phone, $address);

            if ($insertStmt->execute()) {
                $user_id = $insertStmt->insert_id;

                if ($role === 'AGENT') {
                    $agentStmt = $conn->prepare("INSERT INTO agent (user_id, firstname, lastname, middlename, license_number) VALUES (?, ?, ?, ?, ?)");
                    $agentStmt->bind_param("issss", $user_id, $firstname, $lastname, $middlename, $license);
                    $agentStmt->execute();
                    $agentStmt->close();
                } elseif ($role === 'CLIENT') {
                    $clientStmt = $conn->prepare("INSERT INTO client (user_id, agent_id, firstname, lastname, middlename) VALUES (?, ?, ?, ?, ?)");
                    if ($assigned_agent_id === 0) $assigned_agent_id = null;
                    $clientStmt->bind_param("iisss", $user_id, $assigned_agent_id, $firstname, $lastname, $middlename);
                    $clientStmt->execute();
                    $clientStmt->close();
                }

                $success = 'Account successfully created!';
            } else {
                $error = 'Registration failed. Please try again.';
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
    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            document.getElementById('agent-fields').style.display = (role === 'AGENT') ? 'block' : 'none';
            document.getElementById('client-fields').style.display = (role === 'CLIENT') ? 'block' : 'none';
        }
        window.onload = toggleFields; // To apply visibility when page reloads
    </script>
</head>
<body>
    <header style="display: flex; align-items: center; justify-content: space-between;">
        <a href="index.php"><button>← Back to Home</button></a>
        <h1 style="margin: 0 auto;">Create Your Account</h1>
    </header>

    <section style="max-width: 500px; margin: auto; padding: 20px;">
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

            <input type="text" name="firstname" placeholder="First Name" required><br><br>
            <input type="text" name="middlename" placeholder="Middle Name"><br><br>
            <input type="text" name="lastname" placeholder="Last Name" required><br><br>

            <select name="role" id="role" onchange="toggleFields()" required>
                <option value="CLIENT" selected>Client</option>
                <option value="AGENT">Agent</option>
            </select><br><br>

            <div id="agent-fields" style="display: none;">
                <input type="text" name="license_number" placeholder="License Number"><br><br>
            </div>

            <div id="client-fields" style="display: none;">
                <!-- Uncomment below if you want to assign client to a specific agent -->
                <!-- <input type="number" name="agent_id" placeholder="Assign Agent ID (Optional)"><br><br> -->
            </div>

            <button type="submit">Register</button>
        </form>

        <p style="margin-top: 10px;">Already have an account? <a href="login.php">Log in here</a></p>
    </section>
</body>
</html>
