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
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .form-container {
            background: white;
            width: 100%;
            max-width: 700px;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-container:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .form-title {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 25px;
            text-align: center;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group.required label:after {
            content: '*';
            color: #e74c3c;
            margin-left: 4px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
            font-size: 14px;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: 'Open Sans', sans-serif;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        input:hover, select:hover {
            border-color: #bbb;
        }

        .role-fields {
            display: none;
            margin-top: 15px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #7f8c8d;
        }

        .login-link a {
            color: #3498db;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #2980b9;
            text-decoration: underline;
        }

        .success, .error {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            font-size: 14px;
        }

        .success {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border-left: 4px solid #27ae60;
        }

        .error {
            background-color: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            border-left: 4px solid #e74c3c;
        }

        .success i, .error i {
            margin-right: 10px;
            font-size: 18px;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .form-group {
                min-width: 100%;
            }
        }
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