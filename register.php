<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Check for success message from session
$registrationSuccess = $_SESSION['registration_success'] ?? false;
if ($registrationSuccess) {
    unset($_SESSION['registration_success']); // Clear the session flag
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', trim($_POST['phone'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $role = in_array($_POST['role'] ?? '', ['CLIENT', 'AGENT']) ? $_POST['role'] : 'CLIENT';

    // Name details
    $firstname = htmlspecialchars(trim($_POST['firstname'] ?? ''));
    $middlename = htmlspecialchars(trim($_POST['middlename'] ?? ''));
    $lastname = htmlspecialchars(trim($_POST['lastname'] ?? ''));

    // Role-specific
    $license = htmlspecialchars(trim($_POST['license_number'] ?? ''));
    $assigned_agent_id = !empty($_POST['agent_id']) ? (int)$_POST['agent_id'] : null;

    // Store form data for repopulation
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

    // Validate password strength
    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must contain at least one uppercase letter and one number.';
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    }
    // Validate phone number
    elseif (strlen($phone) < 10) {
        $error = 'Please enter a valid phone number.';
    }
    // Required fields check
    elseif (empty($firstname) || empty($lastname)) {
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
            // Begin transaction for atomic operations
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
                        $clientStmt = $conn->prepare("INSERT INTO client (user_id, agent_id, firstname, lastname, middlename) VALUES (?, ?, ?, ?, ?)");
                        if ($assigned_agent_id === 0) $assigned_agent_id = null;
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lot Reservation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .form-container {
            background: white;
            width: 100%;
            max-width: 500px;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-title {
            text-align: center;
            margin: 0 0 20px 0;
            color: #2c3e50;
            font-size: 1.5rem;
        }
        .form-row {
            display: flex;
            margin-bottom: 15px;
            gap: 15px;
        }
        .form-group {
            flex: 1;
        }
        label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #495057;
            font-weight: 500;
        }
        input, select {
            width: 100%;
            padding: 10px;
            font-size: 0.95rem;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.2s;
        }
        input:focus, select:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        .required label:after {
            content: " *";
            color: #e74c3c;
        }
        .btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            margin-top: 10px;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .error {
            color: #e74c3c;
            font-size: 0.9rem;
            margin: 0 0 15px 0;
            text-align: center;
            padding: 10px;
            background: #fdecea;
            border-radius: 4px;
        }
        .success {
            color: #28a745;
            font-size: 0.9rem;
            margin: 0 0 15px 0;
            text-align: center;
            padding: 10px;
            background: #e6f7e6;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
        }
        .login-link {
            font-size: 0.9rem;
            text-align: center;
            margin-top: 15px;
            color: #6c757d;
        }
        .login-link a {
            color: #3498db;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .role-fields {
            margin-top: 10px;
            display: none;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
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
            <!-- Row 1: Name -->
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
            
            <!-- Row 2: Contact Info -->
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
            
            <!-- Row 3: Security -->
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
            
            <!-- Row 4: Additional Info -->
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
            
            <!-- Dynamic Fields -->
            <div id="agent-fields" class="role-fields">
                <div class="form-row">
                    <div class="form-group required">
                        <label for="license_number">License Number</label>
                        <input type="text" id="license_number" name="license_number" value="<?= htmlspecialchars($formData['license_number']) ?>">
                    </div>
                    <div class="form-group"></div>
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
            document.getElementById('license_number').required = (role === 'AGENT');
        }
        window.onload = function() {
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