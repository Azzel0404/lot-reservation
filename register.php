<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

// Check for success message from session
$registrationSuccess = $_SESSION['registration_success'] ?? false;
if ($registrationSuccess) {
    unset($_SESSION['registration_success']); // Clear the session flag
}

// Initialize variables
$errors = [];
$formData = [
    'email' => '',
    'phone' => '',
    'address' => '',
    'firstname' => '',
    'middlename' => '',
    'lastname' => '',
    'license_number' => '',
    'agent_id' => '',
    'role' => 'CLIENT'
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
    // Sanitize and validate input
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $phone = preg_replace('/[^0-9]/', '', trim($_POST['phone'] ?? ''));
    $address = htmlspecialchars(trim($_POST['address'] ?? ''));
    $role = in_array($_POST['role'] ?? '', ['CLIENT', 'AGENT']) ? $_POST['role'] : 'CLIENT';

    // Name details - only sanitize initially, we'll validate format separately
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
        'agent_id' => $assigned_agent_id,
        'role' => $role
    ];

    // Name validation pattern - only allow letters, spaces, hyphens, and apostrophes
    $namePattern = '/^[a-zA-ZÀ-ÖØ-öø-ÿ\s\'-]+$/';

    // Validate firstname
    if (empty($firstname)) {
        $errors['firstname'] = 'First name is required.';
    } elseif (strlen($firstname) > 50) {
        $errors['firstname'] = 'First name must be less than 50 characters.';
    } elseif (!preg_match($namePattern, $firstname)) {
        $errors['firstname'] = 'First name can only contain letters. No numbers or special characters allowed.';
    }
    
    // Validate lastname
    if (empty($lastname)) {
        $errors['lastname'] = 'Last name is required.';
    } elseif (strlen($lastname) > 50) {
        $errors['lastname'] = 'Last name must be less than 50 characters.';
    } elseif (!preg_match($namePattern, $lastname)) {
        $errors['lastname'] = 'Last name can only contain letters. No numbers or special characters allowed.';
    }
    
    // Validate middlename (if provided)
    if (!empty($middlename)) {
        if (strlen($middlename) > 50) {
            $errors['middlename'] = 'Middle name must be less than 50 characters.';
        } elseif (!preg_match($namePattern, $middlename)) {
            $errors['middlename'] = 'Middle name can only contain letters. No numbers or special characters allowed.';
        }
    }

    // Validate email
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must be less than 100 characters.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['email'] = 'This email address is already registered.';
        }
        $stmt->close();
    }

    // Validate phone
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (strlen($phone) < 10 || strlen($phone) > 15) {
        $errors['phone'] = 'Please enter a valid phone number (10-15 digits).';
    } else {
        // Check if phone already exists
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors['phone'] = 'This phone number is already registered.';
        }
        $stmt->close();
    }

    // Validate password strength
    if (empty($password)) {
        $errors['password'] = 'Password is required.';
    } elseif (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $errors['password'] = 'Password must contain at least one uppercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must contain at least one number.';
    } elseif (strlen($password) > 72) {
        $errors['password'] = 'Password must be less than 72 characters.';
    }

    // Validate address
    if (!empty($address) && strlen($address) > 255) {
        $errors['address'] = 'Address must be less than 255 characters.';
    }

    // Role-specific validation
    if ($role === 'AGENT') {
        if (empty($license)) {
            $errors['license_number'] = 'License number is required for agents.';
        } elseif (strlen($license) > 50) {
            $errors['license_number'] = 'License number must be less than 50 characters.';
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Begin transaction for atomic operations
            $conn->begin_transaction();
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertStmt = $conn->prepare("INSERT INTO user (email, password, role, phone, address) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("sssss", $email, $hashedPassword, $role, $phone, $address);

            if ($insertStmt->execute()) {
                $user_id = $insertStmt->insert_id;

                if ($role === 'AGENT') {
                    $agentStmt = $conn->prepare("INSERT INTO agent (user_id, firstname, lastname, middlename, license_number) VALUES (?, ?, ?, ?, ?)");
                    $agentStmt->bind_param("issss", $user_id, $firstname, $lastname, $middlename, $license);
                    if (!$agentStmt->execute()) {
                        throw new Exception("Failed to create agent profile: " . $agentStmt->error);
                    }
                    $agentStmt->close();
                } elseif ($role === 'CLIENT') {
                    $clientStmt = $conn->prepare("INSERT INTO client (user_id, agent_id, firstname, lastname, middlename) VALUES (?, ?, ?, ?, ?)");
                    if ($assigned_agent_id === 0) $assigned_agent_id = null;
                    $clientStmt->bind_param("iisss", $user_id, $assigned_agent_id, $firstname, $lastname, $middlename);
                    if (!$clientStmt->execute()) {
                        throw new Exception("Failed to create client profile: " . $clientStmt->error);
                    }
                    $clientStmt->close();
                }

                $conn->commit();
                $_SESSION['registration_success'] = true;
                header("Location: register.php");
                exit();
            } else {
                throw new Exception("Registration failed: " . $insertStmt->error);
            }
            $insertStmt->close();
        } catch (Exception $e) {
            $conn->rollback();
            $errors['system'] = $e->getMessage();
        }
    }
}

// Function to display field error
function showError($field) {
    global $errors;
    if (isset($errors[$field])) {
        return '<div class="field-error">' . htmlspecialchars($errors[$field]) . '</div>';
    }
    return '';
}

// Function to add error class to input
function errorClass($field) {
    global $errors;
    return isset($errors[$field]) ? 'error-input' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Lot Reservation</title>
    <link rel="stylesheet" href="register.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="form-container">
        <h2 class="form-title">Create Account</h2>
        
        <?php if ($registrationSuccess): ?>
            <div class="success">
                <i class="fas fa-check-circle"></i> Registration successful! You can now login.
            </div>
        <?php endif; ?>
        
        <?php if (isset($errors['system'])): ?>
            <div class="error"><?= htmlspecialchars($errors['system']) ?></div>
        <?php endif; ?>

        <form method="post" action="register.php" id="registrationForm" novalidate>
            <!-- Row 1: Name -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" 
                           value="<?= htmlspecialchars($formData['firstname']) ?>" 
                           class="<?= errorClass('firstname') ?>" required>
                    <?= showError('firstname') ?>
                    <div id="firstname-error" class="js-error">First name can only contain letters. No numbers or special characters allowed.</div>
                </div>
                <div class="form-group required">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" 
                           value="<?= htmlspecialchars($formData['lastname']) ?>" 
                           class="<?= errorClass('lastname') ?>" required>
                    <?= showError('lastname') ?>
                    <div id="lastname-error" class="js-error">Last name can only contain letters. No numbers or special characters allowed.</div>
                </div>
            </div>
            
            <!-- Row 2: Contact Info -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($formData['email']) ?>" 
                           class="<?= errorClass('email') ?>" required>
                    <?= showError('email') ?>
                </div>
                <div class="form-group required">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?= htmlspecialchars($formData['phone']) ?>" 
                           class="<?= errorClass('phone') ?>" required>
                    <?= showError('phone') ?>
                </div>
            </div>
            
            <!-- Row 3: Security -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" 
                               class="password-field <?= errorClass('password') ?>" required>
                        <button type="button" id="password-toggle" class="password-toggle" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="password-toggle-icon"></i>
                        </button>
                    </div>
                    <?= showError('password') ?>
                    <div id="password-error" class="js-error">Password must be at least 8 characters with one uppercase letter and one number.</div>
                </div>
                <div class="form-group">
                    <label for="role">Account Type</label>
                    <select id="role" name="role" onchange="toggleFields()" required>
                        <option value="CLIENT" <?= $formData['role'] === 'CLIENT' ? 'selected' : '' ?>>Client</option>
                        <option value="AGENT" <?= $formData['role'] === 'AGENT' ? 'selected' : '' ?>>Agent</option>
                    </select>
                </div>
            </div>
            
            <!-- Row 4: Additional Info -->
            <div class="form-row">
                <div class="form-group">
                    <label for="middlename">Middle Name</label>
                    <input type="text" id="middlename" name="middlename" 
                           value="<?= htmlspecialchars($formData['middlename']) ?>" 
                           class="<?= errorClass('middlename') ?>">
                    <?= showError('middlename') ?>
                    <div id="middlename-error" class="js-error">Middle name can only contain letters. No numbers or special characters allowed.</div>
                </div>
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" id="address" name="address" 
                           value="<?= htmlspecialchars($formData['address']) ?>" 
                           class="<?= errorClass('address') ?>">
                    <?= showError('address') ?>
                </div>
            </div>
            
            <!-- Agent Fields -->
            <div id="agent-fields" class="role-fields">
                <div class="form-row">
                    <div class="form-group required">
                        <label for="license_number">License Number</label>
                        <input type="text" id="license_number" name="license_number" 
                               value="<?= htmlspecialchars($formData['license_number']) ?>" 
                               class="<?= errorClass('license_number') ?>">
                        <?= showError('license_number') ?>
                    </div>
                    <div class="form-group"></div>
                </div>
            </div>

            <!-- Client Fields -->
            <div id="client-fields" class="role-fields" style="display: none;">
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
            const agentFields = document.getElementById('agent-fields');
            const clientFields = document.getElementById('client-fields');
            
            // Show agent fields if the role is AGENT
            agentFields.style.display = (role === 'AGENT') ? 'block' : 'none';
            
            // Show client fields if the role is CLIENT
            clientFields.style.display = (role === 'CLIENT') ? 'block' : 'none';
            
            // Set license field as required only for AGENT
            const licenseField = document.getElementById('license_number');
            licenseField.required = (role === 'AGENT');
            
            // Clear validation styling when switching roles
            if (role !== 'AGENT') {
                licenseField.classList.remove('error-input');
                const errorElement = licenseField.nextElementSibling;
                if (errorElement && errorElement.classList.contains('field-error')) {
                    errorElement.style.display = 'none';
                }
            }
        }
        
        // Client-side validation
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const passwordField = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            const passwordToggleIcon = document.getElementById('password-toggle-icon');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Toggle icon
                if (type === 'password') {
                    passwordToggleIcon.classList.remove('fa-eye-slash');
                    passwordToggleIcon.classList.add('fa-eye');
                } else {
                    passwordToggleIcon.classList.remove('fa-eye');
                    passwordToggleIcon.classList.add('fa-eye-slash');
                }
            });
            
            // Name fields validation
            const nameFields = [
                { id: 'firstname', errorId: 'firstname-error' },
                { id: 'middlename', errorId: 'middlename-error' },
                { id: 'lastname', errorId: 'lastname-error' }
            ];
            const namePattern = /^[a-zA-ZÀ-ÖØ-öø-ÿ\s\'-]+$/;
            
            // Validate name fields on blur (when user leaves the field)
            nameFields.forEach(field => {
                const input = document.getElementById(field.id);
                const errorElement = document.getElementById(field.errorId);
                
                if (input && errorElement) {
                    input.addEventListener('blur', function() {
                        // Only validate if there's a value (middlename is optional)
                        if (this.value.trim() !== '') {
                            if (!namePattern.test(this.value)) {
                                // Show error message
                                errorElement.style.display = 'block';
                                this.classList.add('error-input');
                            } else {
                                // Hide error message
                                errorElement.style.display = 'none';
                                this.classList.remove('error-input');
                            }
                        } else {
                            // Empty field, hide error
                            errorElement.style.display = 'none';
                            this.classList.remove('error-input');
                        }
                    });
                    
                    // Clear error when user starts typing again
                    input.addEventListener('focus', function() {
                        errorElement.style.display = 'none';
                    });
                }
            });
            
            // Password validation
            const passwordError = document.getElementById('password-error');
            
            if (passwordField && passwordError) {
                passwordField.addEventListener('blur', function() {
                    if (this.value.trim() !== '') {
                        let isValid = true;
                        let errorMessage = '';
                        
                        // Check password length
                        if (this.value.length < 8) {
                            isValid = false;
                            errorMessage = 'Password must be at least 8 characters long.';
                        }
                        // Check for uppercase letter
                        else if (!/[A-Z]/.test(this.value)) {
                            isValid = false;
                            errorMessage = 'Password must contain at least one uppercase letter.';
                        }
                        // Check for number
                        else if (!/[0-9]/.test(this.value)) {
                            isValid = false;
                            errorMessage = 'Password must contain at least one number.';
                        }
                        
                        if (!isValid) {
                            passwordError.textContent = errorMessage;
                            passwordError.style.display = 'block';
                            this.classList.add('error-input');
                        } else {
                            passwordError.style.display = 'none';
                            this.classList.remove('error-input');
                        }
                    } else {
                        // Empty field, hide error
                        passwordError.style.display = 'none';
                        this.classList.remove('error-input');
                    }
                });
                
                // Clear error when user starts typing again
                passwordField.addEventListener('focus', function() {
                    passwordError.style.display = 'none';
                });
            }
            
            // Form submission validation
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                let hasErrors = false;
                
                // Validate all name fields before submission
                nameFields.forEach(field => {
                    const input = document.getElementById(field.id);
                    const errorElement = document.getElementById(field.errorId);
                    
                    // Skip validation for empty optional fields (middlename)
                    if (input && input.value.trim() !== '' && !namePattern.test(input.value)) {
                        errorElement.style.display = 'block';
                        input.classList.add('error-input');
                        hasErrors = true;
                    }
                });
                
                // Validate password
                if (passwordField && passwordField.value.trim() !== '') {
                    let isPasswordValid = true;
                    
                    if (passwordField.value.length < 8) {
                        isPasswordValid = false;
                    } else if (!/[A-Z]/.test(passwordField.value)) {
                        isPasswordValid = false;
                    } else if (!/[0-9]/.test(passwordField.value)) {
                        isPasswordValid = false;
                    }
                    
                    if (!isPasswordValid) {
                        passwordError.style.display = 'block';
                        passwordField.classList.add('error-input');
                        hasErrors = true;
                    }
                }
                
                // If there are client-side validation errors, prevent form submission
                if (hasErrors) {
                    e.preventDefault();
                }
            });
            
            toggleFields();
        });
    </script>
</body>
</html>