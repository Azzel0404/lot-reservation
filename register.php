<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Check for success message from session
$registrationSuccess = $_SESSION['registration_success'] ?? false;
if ($registrationSuccess) {
    unset($_SESSION['registration_success']);
}

// Initialize variables
$error = '';
$formData = [
    'email' => '',
    'phone' => '',
    'address' => '',
    'firstname' => '',
    'middlename' => '',
    'lastname' => '',
    'license_number' => '',
    'agent_id' => ''
];

// Fetch agents for client dropdown
$agents = [];
$agentQuery = $conn->query("SELECT agent.agent_id, CONCAT(agent.firstname, ' ', agent.lastname) AS full_name FROM agent INNER JOIN user ON agent.user_id = user.user_id");
if ($agentQuery && $agentQuery->num_rows > 0) {
    while ($row = $agentQuery->fetch_assoc()) {
        $agents[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', trim($_POST['phone'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $role = in_array($_POST['role'] ?? '', ['CLIENT', 'AGENT']) ? $_POST['role'] : 'CLIENT';

    $firstname = htmlspecialchars(trim($_POST['firstname'] ?? ''));
    $middlename = htmlspecialchars(trim($_POST['middlename'] ?? ''));
    $lastname = htmlspecialchars(trim($_POST['lastname'] ?? ''));
    $license = htmlspecialchars(trim($_POST['license_number'] ?? ''));
    $assigned_agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;

    $formData = [
        'email' => $email,
        'phone' => $phone,
        'address' => $address,
        'firstname' => $firstname,
        'middlename' => $middlename,
        'lastname' => $lastname,
        'license_number' => $license,
        'agent_id' => $assigned_agent_id
    ];

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one uppercase letter and one number.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($phone) < 10) {
        $error = 'Please enter a valid phone number.';
    } elseif (empty($firstname) || empty($lastname)) {
        $error = 'Please fill in all required fields.';
    } else {
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ? OR phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An account with this email or phone already exists.';
        } else {
            $conn->begin_transaction();

            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $insertStmt = $conn->prepare("INSERT INTO user (email, password, role, phone, address) VALUES (?, ?, ?, ?, ?)");
                $insertStmt->bind_param("sssss", $email, $hashedPassword, $role, $phone, $address);

                if ($insertStmt->execute()) {
                    $user_id = $insertStmt->insert_id;

                    if ($role === 'AGENT') {
                        if (empty($license)) {
                            throw new Exception("License number is required for agents.");
                        }
                        $agentStmt = $conn->prepare("INSERT INTO agent (user_id, firstname, lastname, middlename, license_number) VALUES (?, ?, ?, ?, ?)");
                        $agentStmt->bind_param("issss", $user_id, $firstname, $lastname, $middlename, $license);
                        $agentStmt->execute();
                        $agentStmt->close();
                    } elseif ($role === 'CLIENT') {
                        if ($assigned_agent_id === 0) $assigned_agent_id = null;
                        $clientStmt = $conn->prepare("INSERT INTO client (user_id, agent_id, firstname, lastname, middlename) VALUES (?, ?, ?, ?, ?)");
                        $clientStmt->bind_param("iisss", $user_id, $assigned_agent_id, $firstname, $lastname, $middlename);
                        $clientStmt->execute();
                        $clientStmt->close();
                    }

                    $conn->commit();
                    $_SESSION['registration_success'] = true;
                    header("Location: register.php");
                    exit();
                } else {
                    throw new Exception("Registration failed. Please try again.");
                }
                $insertStmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Lot Reservation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* same CSS as before, omitted here for brevity */
    </style>
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">Create Account</h2>

        <?php if ($registrationSuccess): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> Registration successful! You can now login.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php">
            <!-- Name -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($formData['firstname']) ?>" required>
                </div>
                <div class="form-group required">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($formData['lastname']) ?>" required>
                </div>
            </div>

            <!-- Contact Info -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($formData['email']) ?>" required>
                </div>
                <div class="form-group required">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($formData['phone']) ?>" required>
                </div>
            </div>

            <!-- Security -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" onchange="toggleFields()" required>
                        <option value="CLIENT" selected>Client</option>
                        <option value="AGENT">Agent</option>
                    </select>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="form-row">
                <div class="form-group">
                    <label for="middlename">Middle Name</label>
                    <input type="text" id="middlename" name="middlename" value="<?= htmlspecialchars($formData['middlename']) ?>">
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($formData['address']) ?>">
                </div>
            </div>

            <!-- Agent Fields -->
            <div id="agent-fields" class="role-fields">
                <div class="form-row">
                    <div class="form-group required">
                        <label for="license_number">License Number</label>
                        <input type="text" id="license_number" name="license_number" value="<?= htmlspecialchars($formData['license_number']) ?>">
                    </div>
                </div>
            </div>

            <!-- Client Fields -->
            <div id="client-fields" class="role-fields">
                <div class="form-row">
                    <div class="form-group">
                        <label for="agent_id">Select Agent</label>
                        <select id="agent_id" name="agent_id">
                            <option value="">-- Optional: Choose an Agent --</option>
                            <?php foreach ($agents as $agent): ?>
                                <option value="<?= $agent['agent_id'] ?>" <?= ($formData['agent_id'] == $agent['agent_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($agent['full_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn">Register Now</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="login.php">Sign in here</a>
        </div>
    </div>

    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            document.getElementById('agent-fields').style.display = (role === 'AGENT') ? 'block' : 'none';
            document.getElementById('client-fields').style.display = (role === 'CLIENT') ? 'block' : 'none';
            document.getElementById('license_number').required = (role === 'AGENT');
        }
        window.onload = function () {
            toggleFields();
            const formDataRole = "<?= htmlspecialchars($_POST['role'] ?? 'CLIENT') ?>";
            if (formDataRole) {
                document.getElementById('role').value = formDataRole;
                toggleFields();
            }
        };
    </script>
</body>
</html>
