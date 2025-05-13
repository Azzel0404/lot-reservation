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
    
    // Validate middlename (no longer required)
    if (!empty($middlename)) {
        if (strlen($middlename) > 50) {
            $errors['middlename'] = 'Middle name must be less than 50 characters.';
        } elseif (!preg_match($namePattern, $middlename)) {
            $errors['middlename'] = 'Middle name can only contain letters. No numbers or special characters allowed.';
        }
    }

    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 100) {
        $errors['email'] = 'Email must be less than 100 characters.';
    } elseif (preg_match('/\.co$/i', $email)) {
        $errors['email'] = 'Emails ending in .co are not allowed.';
    } else {
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
    if (empty($address)) {
        $errors['address'] = 'Address is required.';
    } elseif (strlen($address) > 255) {
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
    <style>
        .error-input {
            border-color: #ff4444 !important;
        }
        .field-error {
            color: #ff4444;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .js-error {
            display: none;
            color: #ff4444;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            display: flex;
            align-items: center;
        }
        .success i {
            margin-right: 10px;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
        }
        .role-fields {
            margin-top: 15px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
        /* Autocomplete styles */
        .autocomplete {
            position: relative;
            display: inline-block;
            width: 100%;
        }
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        .autocomplete-active {
            background-color: #007bff !important;
            color: #ffffff;
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
                           class="<?= errorClass('firstname') ?>" required
                           onkeypress="return /[a-zA-Z\s'-]/i.test(event.key)">
                    <?= showError('firstname') ?>
                    <div id="firstname-error" class="js-error">First name can only contain letters. No numbers or special characters allowed.</div>
                </div>
                <div class="form-group required">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" 
                           value="<?= htmlspecialchars($formData['lastname']) ?>" 
                           class="<?= errorClass('lastname') ?>" required
                           onkeypress="return /[a-zA-Z\s'-]/i.test(event.key)">
                    <?= showError('lastname') ?>
                    <div id="lastname-error" class="js-error">Last name can only contain letters. No numbers or special characters allowed.</div>
                </div>
            </div>
            
            <!-- Row 2: Middle Name -->
            <div class="form-row">
                <div class="form-group">
                    <label for="middlename">Middle Name (Optional)</label>
                    <input type="text" id="middlename" name="middlename" 
                           value="<?= htmlspecialchars($formData['middlename']) ?>" 
                           class="<?= errorClass('middlename') ?>"
                           onkeypress="return /[a-zA-Z\s'-]/i.test(event.key)">
                    <?= showError('middlename') ?>
                    <div id="middlename-error" class="js-error">Middle name can only contain letters. No numbers or special characters allowed.</div>
                </div>
            </div>
            
            <!-- Row 3: Contact Info -->
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
                           class="<?= errorClass('phone') ?>" required
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                           maxlength="15">
                    <?= showError('phone') ?>
                </div>
            </div>
            
            <!-- Row 4: Security -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="password">Password</label>
                    <div class="password-container">
                        <input type="password" id="password" name="password" 
                               class="password-field <?= errorClass('password') ?>" required
                               onpaste="return false;" oncopy="return false;" oncut="return false;">
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
            
            <!-- Row 5: Additional Info -->
            <div class="form-row">
                <div class="form-group required">
                    <label for="address">Address</label>
                    <div class="autocomplete">
                        <input type="text" id="address" name="address" 
                               value="<?= htmlspecialchars($formData['address']) ?>" 
                               class="<?= errorClass('address') ?>"
                               placeholder="Start typing city or barangay..."
                               required>
                        <div id="autocomplete-results" class="autocomplete-items"></div>
                    </div>
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
       // Cebu address database
       const cebuAddresses = [
         // Cebu City
"Cebu City - Adlaon",
"Cebu City - Apas",
"Cebu City - Bacayan",
"Cebu City - Banilad",
"Cebu City - Basak Pardo",
"Cebu City - Basak San Nicolas",
"Cebu City - Binaliw",
"Cebu City - Bonbon",
"Cebu City - Budlaan",
"Cebu City - Buhisan",
"Cebu City - Bulacao",
"Cebu City - Buot-Taup",
"Cebu City - Busay",
"Cebu City - Calamba",
"Cebu City - Cambinocot",
"Cebu City - Capitol Site",
"Cebu City - Carreta",
"Cebu City - Cogon Pardo",
"Cebu City - Cogon Ramos",
"Cebu City - Day-as",
"Cebu City - Duljo Fatima",
"Cebu City - Ermita",
"Cebu City - Guadalupe",
"Cebu City - Guba",
"Cebu City - Hipodromo",
"Cebu City - Inayawan",
"Cebu City - Kalubihan",
"Cebu City - Kamagayan",
"Cebu City - Kamputhaw",
"Cebu City - Kasambagan",
"Cebu City - Kinasang-an",
"Cebu City - Labangon",
"Cebu City - Lahug",
"Cebu City - Lorega San Miguel",
"Cebu City - Lusaran",
"Cebu City - Luz",
"Cebu City - Mabini",
"Cebu City - Mabolo",
"Cebu City - Malubog",
"Cebu City - Mambaling",
"Cebu City - Pahina Central",
"Cebu City - Pahina San Nicolas",
"Cebu City - Pamutan",
"Cebu City - Parian",
"Cebu City - Pari-an",
"Cebu City - Pasil",
"Cebu City - Pit-os",
"Cebu City - Pulangbato",
"Cebu City - Pung-ol Sibugay",
"Cebu City - Punta Princesa",
"Cebu City - Quiot",
"Cebu City - Sambag I",
"Cebu City - Sambag II",
"Cebu City - San Antonio",
"Cebu City - San Jose",
"Cebu City - San Nicolas Central",
"Cebu City - San Roque",
"Cebu City - Santa Cruz",
"Cebu City - Santo Niño",
"Cebu City - Sapangdaku",
"Cebu City - Sawang Calero",
"Cebu City - Sinsin",
"Cebu City - Sirao",
"Cebu City - Suba",
"Cebu City - Sudlon I",
"Cebu City - Sudlon II",
"Cebu City - T. Padilla",
"Cebu City - Tabunan",
"Cebu City - Tagba-o",
"Cebu City - Talamban",
"Cebu City - Taptap",
"Cebu City - Tejero",
"Cebu City - Tinago",
"Cebu City - Tisa",
"Cebu City - Toong",
"Cebu City - Zapatera",
            
// Minglanilla
"Minglanilla - Cadulawan",
"Minglanilla - Calajo-an",
"Minglanilla - Camp 7",
"Minglanilla - Camp 8",
"Minglanilla - Cuanos",
"Minglanilla - Guindaruhan",
"Minglanilla - Linao",
"Minglanilla - Manduang",
"Minglanilla - Pakigne",
"Minglanilla - Poblacion Ward I",
"Minglanilla - Poblacion Ward II",
"Minglanilla - Poblacion Ward III",
"Minglanilla - Poblacion Ward IV",
"Minglanilla - Tubod",
"Minglanilla - Tulay",
"Minglanilla - Tunghaan",
"Minglanilla - Tungkil",
"Minglanilla - Tungkop",
"Minglanilla - Vito",

//lapu lapu 
"Lapu-Lapu City - Agus",
"Lapu-Lapu City - Babag",
"Lapu-Lapu City - Bankal",
"Lapu-Lapu City - Baring",
"Lapu-Lapu City - Basak",
"Lapu-Lapu City - Buaya",
"Lapu-Lapu City - Calawisan",
"Lapu-Lapu City - Canjulao",
"Lapu-Lapu City - Caw-oy",
"Lapu-Lapu City - Cawhagan",
"Lapu-Lapu City - Caubian",
"Lapu-Lapu City - Gun-ob",
"Lapu-Lapu City - Ibo",
"Lapu-Lapu City - Looc",
"Lapu-Lapu City - Mactan",
"Lapu-Lapu City - Maribago",
"Lapu-Lapu City - Marigondon",
"Lapu-Lapu City - Pajac",
"Lapu-Lapu City - Pajo",
"Lapu-Lapu City - Pangan-an",
"Lapu-Lapu City - Poblacion",
"Lapu-Lapu City - Punta Engaño",
"Lapu-Lapu City - Pusok",
"Lapu-Lapu City - Sabang",
"Lapu-Lapu City - Santa Rosa",
"Lapu-Lapu City - Suba-basbas",
"Lapu-Lapu City - Talima",
"Lapu-Lapu City - Tingo",
"Lapu-Lapu City - Tungasan",
"Lapu-Lapu City - San Vicente",

// mandaue 
"Mandaue City - Alang-alang",
"Mandaue City - Bakilid",
"Mandaue City - Banilad",
"Mandaue City - Basak",
"Mandaue City - Cabancalan",
"Mandaue City - Cambaro",
"Mandaue City - Canduman",
"Mandaue City - Casili",
"Mandaue City - Casuntingan",
"Mandaue City - Centro",
"Mandaue City - Cubacub",
"Mandaue City - Guizo",
"Mandaue City - Ibabao-Estancia",
"Mandaue City - Jagobiao",
"Mandaue City - Labogon",
"Mandaue City - Looc",
"Mandaue City - Maguikay",
"Mandaue City - Mantuyong",
"Mandaue City - Opao",
"Mandaue City - Pakna-an",
"Mandaue City - Pagsabungan",
"Mandaue City - Subangdaku",
"Mandaue City - Tabok",
"Mandaue City - Tawason",
"Mandaue City - Tingub",
"Mandaue City - Tipolo",
"Mandaue City - Umapad",

//talisay
"Talisay City - Biasong",
"Talisay City - Bulacao",
"Talisay City - Candulawan",
"Talisay City - Camp IV",
"Talisay City - Cansojong",
"Talisay City - Dumlog",
"Talisay City - Jaclupan",
"Talisay City - Lagtang",
"Talisay City - Lawaan I",
"Talisay City - Lawaan II",
"Talisay City - Lawaan III",
"Talisay City - Linao",
"Talisay City - Maghaway",
"Talisay City - Manipis",
"Talisay City - Mohon",
"Talisay City - Poblacion",
"Talisay City - Pooc",
"Talisay City - San Isidro",
"Talisay City - San Roque",
"Talisay City - Tabunok",
"Talisay City - Tangke",
"Talisay City - Tapul",

//bogo city

"Bogo City - Anonang Norte",
"Bogo City - Anonang Sur",
"Bogo City - Banban",
"Bogo City - Binabag",
"Bogo City - Bongdo",
"Bogo City - Bongdo Gua",
"Bogo City - Cabungahan",
"Bogo City - Cagay",
"Bogo City - Cansaga",
"Bogo City - Cantagay",
"Bogo City - Cayang",
"Bogo City - Dakit",
"Bogo City - Don Pedro Rodriguez",
"Bogo City - Gairan",
"Bogo City - Guadalupe",
"Bogo City - La Purisima Concepcion",
"Bogo City - Lapaz",
"Bogo City - Malingin",
"Bogo City - Marangog",
"Bogo City - Nailon",
"Bogo City - Odlot",
"Bogo City - Pandan",
"Bogo City - Polambato",
"Bogo City - Sambag",
"Bogo City - San Vicente",
"Bogo City - Santo Niño",
"Bogo City - Santo Rosario",
"Bogo City - Siocon",
"Bogo City - Taytayan",

//carcar city

"Carcar City - Bolinawan",
"Carcar City - Buenavista",
"Carcar City - Calidngan",
"Carcar City - Can-asujan",
"Carcar City - Guadalupe",
"Carcar City - Liburon",
"Carcar City - Napo",
"Carcar City - Ocana",
"Carcar City - Perrelos",
"Carcar City - Poblacion I",
"Carcar City - Poblacion II",
"Carcar City - Poblacion III",
"Carcar City - Tuyom",
"Carcar City - Valencia",
"Carcar City - Valladolid",

//Danao City

"Danao City - Balingsag",
"Danao City - Bayabas",
"Danao City - Binaliw",
"Danao City - Cabungahan",
"Danao City - Cagat-Lamac",
"Danao City - Cahumayhumayan",
"Danao City - Cambanay",
"Danao City - Cambubho",
"Danao City - Cogon-Cruz",
"Danao City - Danasan",
"Danao City - Dungga",
"Danao City - Dunggoan",
"Danao City - Guinacot",
"Danao City - Guinsay",
"Danao City - Ibo",
"Danao City - Langosig",
"Danao City - Lawaan",
"Danao City - Licos",
"Danao City - Looc",
"Danao City - Magtagobtob",
"Danao City - Malapoc",
"Danao City - Manlayag",
"Danao City - Mantija",
"Danao City - Maslog",
"Danao City - Nangka",
"Danao City - Oguis",
"Danao City - Pili",
"Danao City - Poblacion",
"Danao City - Quisol",
"Danao City - Sabang",
"Danao City - Sacsac",
"Danao City - Sandayong Sur",
"Danao City - Santa Rosa",
"Danao City - Santican",
"Danao City - Sibacan",
"Danao City - Suba",
"Danao City - Taboc",
"Danao City - Taytay",
"Danao City - Togonon",
"Danao City - Tuburan Sur",
"Danao City - Tuburan Norte",
"Danao City - Dungguan",

//Naga City 

"Naga City - Alpaco",
"Naga City - Bairan",
"Naga City - Balirong",
"Naga City - Cabungahan",
"Naga City - Cantao-an",
"Naga City - Central Poblacion",
"Naga City - Cogon",
"Naga City - Colon",
"Naga City - East Poblacion",
"Naga City - Inoburan",
"Naga City - Inayagan",
"Naga City - Jaguimit",
"Naga City - Lanas",
"Naga City - Langtad",
"Naga City - Lutac",
"Naga City - Mainit",
"Naga City - Mayana",
"Naga City - Naalad",
"Naga City - North Poblacion",
"Naga City - Pangdan",
"Naga City - Patag",
"Naga City - South Poblacion",
"Naga City - Tagjaguimit",
"Naga City - Tangke",
"Naga City - Tinaan",
"Naga City - Tuyan",
"Naga City - Uling",
"Naga City - West Poblacion",

//toldeo City

"Toledo City - Awihao",
"Toledo City - Bagakay",
"Toledo City - Bato",
"Toledo City - Biga",
"Toledo City - Bulongan",
"Toledo City - Bunga",
"Toledo City - Cabitoonan",
"Toledo City - Calongcalong",
"Toledo City - Cantabaco",
"Toledo City - Captain Claudio",
"Toledo City - Carmen",
"Toledo City - Daanglungsod",
"Toledo City - Don Andres Soriano",
"Toledo City - Dumlog",
"Toledo City - DAS",
"Toledo City - General Climaco",
"Toledo City - Ibo",
"Toledo City - Landahan",
"Toledo City - Loay",
"Toledo City - Luray II",
"Toledo City - Matab-ang",
"Toledo City - Media Once",
"Toledo City - Pangamihan",
"Toledo City - Poblacion",
"Toledo City - Poog",
"Toledo City - Putingbato",
"Toledo City - Sagay",
"Toledo City - Sam-ang",
"Toledo City - Sangi",
"Toledo City - Santo Niño",
"Toledo City - Sirao",
"Toledo City - Subayon",
"Toledo City - Talavera",
"Toledo City - Tungay",
"Toledo City - Tubod",
"Toledo City - Tugbongan",
"Toledo City - Ulbong",
"Toledo City - Villahermosa",

//Consolacion

"Consolacion - Cabangahan",
"Consolacion - Cansaga",
"Consolacion - Casili",
"Consolacion - Danglag",
"Consolacion - Garing",
"Consolacion - Jugan",
"Consolacion - Lamac",
"Consolacion - Lanipga",
"Consolacion - Nangka",
"Consolacion - Panas",
"Consolacion - Panoypoy",
"Consolacion - Pitogo",
"Consolacion - Poblacion Occidental",
"Consolacion - Poblacion Oriental",
"Consolacion - Polog",
"Consolacion - Pulpogan",
"Consolacion - Sacsac",
"Consolacion - Tayud",
"Consolacion - Tilhaong",
"Consolacion - Tolotolo",
"Consolacion - Tugbongan",

//Cordova

"Cordova - Alegria",
"Cordova - Bangbang",
"Cordova - Buagsong",
"Cordova - Catarman",
"Cordova - Cogon",
"Cordova - Dapitan",
"Cordova - Day-as",
"Cordova - Gabi",
"Cordova - Gilutongan",
"Cordova - Ibabao",
"Cordova - Pilipog",
"Cordova - Poblacion",
"Cordova - San Miguel",

//liloan 

"Liloan - Cabadiangan",
"Liloan - Calero",
"Liloan - Catarman",
"Liloan - Cotcot",
"Liloan - Jubay",
"Liloan - Lataban",
"Liloan - Mulao",
"Liloan - Poblacion",
"Liloan - San Roque",
"Liloan - San Vicente",
"Liloan - Santa Cruz",
"Liloan - Tabla",
"Liloan - Tayud",
"Liloan - Yati",

//Compostela

"Compostela - Bagalnga",
"Compostela - Basak",
"Compostela - Buluang",
"Compostela - Cabadiangan",
"Compostela - Cambayog",
"Compostela - Canamucan",
"Compostela - Cogon",
"Compostela - Dapdap",
"Compostela - Estaca",
"Compostela - Lagundi",
"Compostela - Mulao",
"Compostela - Panangban",
"Compostela - Poblacion",
"Compostela - Tag-ube",
"Compostela - Tamiao",
"Compostela - Tubigan",
"Compostela - Tubod",

//balamban 
"Balamban - Abucayan",
"Balamban - Aliwanay",
"Balamban - Arpili",
"Balamban - Bayong",
"Balamban - Buanoy",
"Balamban - Cabagdalan",
"Balamban - Cabasiangan",
"Balamban - Cambuhawe",
"Balamban - Cansomoroy",
"Balamban - Cantibas",
"Balamban - Cantuod",
"Balamban - Duangan",
"Balamban - Gaas",
"Balamban - Ginatilan",
"Balamban - Hingatmonan",
"Balamban - Lamesa",
"Balamban - Liki",
"Balamban - Luca",
"Balamban - Matun-og",
"Balamban - Nangka",
"Balamban - Pondol",
"Balamban - Prenza",
"Balamban - Singsing",
"Balamban - Sunog",
"Balamban - Vito",
"Balamban - Santa Cruz-Santo Niño",
"Balamban - Santa Cruz-San Isidro",
"Balamban - Poblacion",

//Bantayan 

"Bantayan - Atop-atop",
"Bantayan - Baigad",
"Bantayan - Baod",
"Bantayan - Binaobao",
"Bantayan - Botigues",
"Bantayan - Kabac",
"Bantayan - Doong",
"Bantayan - Hilotongan",
"Bantayan - Guiwanon",
"Bantayan - Kabangbang",
"Bantayan - Kampingganon",
"Bantayan - Kangkaibe",
"Bantayan - Lipayran",
"Bantayan - Luyongbaybay",
"Bantayan - Mojon",
"Bantayan - Obo-ob",
"Bantayan - Patao",
"Bantayan - Putian",
"Bantayan - Sillon",
"Bantayan - Sungko",
"Bantayan - Suba",
"Bantayan - Sulangan",
"Bantayan - Tamiao",
"Bantayan - Poblacion",
"Bantayan - Ticad",

// Daanbantayan

"Daanbantayan - Agujo",
"Daanbantayan - Bagay",
"Daanbantayan - Bakhawan",
"Daanbantayan - Bateria",
"Daanbantayan - Bitoon",
"Daanbantayan - Calape",
"Daanbantayan - Carnaza",
"Daanbantayan - Dalingding",
"Daanbantayan - Lanao",
"Daanbantayan - Logon",
"Daanbantayan - Malbago",
"Daanbantayan - Malingin",
"Daanbantayan - Maya",
"Daanbantayan - Pajo",
"Daanbantayan - Paypay",
"Daanbantayan - Poblacion",
"Daanbantayan - Talisay",
"Daanbantayan - Tapilon",
"Daanbantayan - Tinubdan",
"Daanbantayan - Tominjao",

// Madridejos

"Madridejos - Bunakan",
"Madridejos - Kangwayan",
"Madridejos - Kaongkod",
"Madridejos - Kodia",
"Madridejos - Maalat",
"Madridejos - Malbago",
"Madridejos - Mancilang",
"Madridejos - Pili",
"Madridejos - Poblacion",
"Madridejos - San Agustin",
"Madridejos - Tabagak",
"Madridejos - Talangnan",
"Madridejos - Tarong",
"Madridejos - Tugas",

//San Fernando

"San Fernando - Balud",
"San Fernando - Balungag",
"San Fernando - Basak",
"San Fernando - Bugho",
"San Fernando - Cabatbatan",
"San Fernando - Greenhills",
"San Fernando - Ilaya",
"San Fernando - Linao",
"San Fernando - Panadtaran",
"San Fernando - Pitalo",
"San Fernando - Poblacion North",
"San Fernando - Poblacion South",
"San Fernando - Sangat",
"San Fernando - Tabionan",
"San Fernando - Tananas",
"San Fernando - Tubod",
"San Fernando - Tubod-Bitoon",
"San Fernando - Magsico",
"San Fernando - Pitogo",
"San Fernando - South Poblacion",
"San Fernando - Tonggo",

//Argao

"Argao - Alambijud",
"Argao - Anajao",
"Argao - Apo",
"Argao - Balaas",
"Argao - Balisong",
"Argao - Binlod",
"Argao - Bogo",
"Argao - Butong",
"Argao - Bug-ot",
"Argao - Bulasa",
"Argao - Calagasan",
"Argao - Canbantug",
"Argao - Canbanua",
"Argao - Cansuje",
"Argao - Capio-an",
"Argao - Casay",
"Argao - Catang",
"Argao - Colawin",
"Argao - Conalum",
"Argao - Guiwanon",
"Argao - Gutlang",
"Argao - Jampang",
"Argao - Jomgao",
"Argao - Lamacan",
"Argao - Langtad",
"Argao - Langub",
"Argao - Lapay",
"Argao - Lengigon",
"Argao - Linut-od",
"Argao - Mabasa",
"Argao - Mandilikit",
"Argao - Mompeller",
"Argao - Panadtaran",
"Argao - Poblacion",
"Argao - Sua",
"Argao - Sumaguan",
"Argao - Tabayag",
"Argao - Talaga",
"Argao - Talaytay",
"Argao - Talo-ot",
"Argao - Tiguib",
"Argao - Tulang",
"Argao - Tulic",
"Argao - Ubaub",
"Argao - Usmad",

//Barili

"Barili - Azucena",
"Barili - Bagakay",
"Barili - Balao",
"Barili - Bolocboloc",
"Barili - Budbud",
"Barili - Bugtong Kawayan",
"Barili - Cabcaban",
"Barili - Campangga",
"Barili - Dakit",
"Barili - Giloctog",
"Barili - Guibuangan",
"Barili - Giwanon",
"Barili - Gunting",
"Barili - Hilasgasan",
"Barili - Japitan",
"Barili - Kangdolsam",
"Barili - Candugay",
"Barili - Luhod",
"Barili - Lupo",
"Barili - Luyo",
"Barili - Maghanoy",
"Barili - Maigang",
"Barili - Malolos",
"Barili - Mamampao",
"Barili - Mantayupan",
"Barili - Mayana",
"Barili - Minolos",
"Barili - Nabunturan",
"Barili - Nasipit",
"Barili - Pancil",
"Barili - Pangpang",
"Barili - Paril",
"Barili - Patupat",
"Barili - Poblacion",
"Barili - San Rafael",
"Barili - Santa Ana",
"Barili - Sayaw",
"Barili - Sogod",
"Barili - Tal-ot",
"Barili - Tubod",
"Barili - Vito",
"Barili - Pagsupan",

//Dumanjug

"Dumanjug - Balaygtiki",
"Dumanjug - Bitoon",
"Dumanjug - Bulak",
"Dumanjug - Calaboon",
"Dumanjug - Camboang",
"Dumanjug - Candabong",
"Dumanjug - Cogon",
"Dumanjug - Cotcoton",
"Dumanjug - Daantol",
"Dumanjug - Don Miguel",
"Dumanjug - Kabalaasnan",
"Dumanjug - Kabatbatan",
"Dumanjug - Kambanog",
"Dumanjug - Kang-actol",
"Dumanjug - Kanghalo",
"Dumanjug - Kanghumaod",
"Dumanjug - Kanguha",
"Dumanjug - Kantangkas",
"Dumanjug - Kanyuko",
"Dumanjug - Cawayan",
"Dumanjug - Lanao",
"Dumanjug - Lawaan",
"Dumanjug - Liong",
"Dumanjug - Manlapay",
"Dumanjug - Masa",
"Dumanjug - Matalao",
"Dumanjug - Paculob",
"Dumanjug - Panlaan",
"Dumanjug - Pawa",
"Dumanjug - Poblacion",
"Dumanjug - Tangil",
"Dumanjug - Tapon",
"Dumanjug - Tunga",
"Dumanjug - Ilaya",
"Dumanjug - Tubod-Bitoon",
"Dumanjug - Tubod-Dugoan",
"Dumanjug - Poblacion Looc",

//Ronda

"Ronda - Butong",
"Ronda - Can-abuhon",
"Ronda - Canduling",
"Ronda - Cansalonoy",
"Ronda - Cansayong",
"Ronda - Caputatan Norte",
"Ronda - Caputatan Sur",
"Ronda - Casay",
"Ronda - Caubayan",
"Ronda - Dugyan",
"Ronda - Libo-o",
"Ronda - Malalay",
"Ronda - Palanas",
"Ronda - Poblacion",
"Ronda - Santa Cruz",
"Ronda - Tupas",
"Ronda - Tuyom",
"Ronda - Vive",
"Ronda - Langin",
"Ronda - Langtad",

//Alcantara

"Alcantara - Cabadiangan",
"Alcantara - Cabil-isan",
"Alcantara - Candabong",
"Alcantara - Lawaan",
"Alcantara - Manga",
"Alcantara - Palanas",
"Alcantara - Poblacion",
"Alcantara - Polo",
"Alcantara - Salug",

//Aloguinsan

"Aloguinsan - Angilan",
"Aloguinsan - Bojo",
"Aloguinsan - Bonbon",
"Aloguinsan - Esperanza",
"Aloguinsan - Kandingan",
"Aloguinsan - Kantabogon",
"Aloguinsan - Kawasan",
"Aloguinsan - Olango",
"Aloguinsan - Poblacion",
"Aloguinsan - Punay",
"Aloguinsan - Rosario",
"Aloguinsan - Saksak",
"Aloguinsan - Tampa-an",
"Aloguinsan - Toyokon",
"Aloguinsan - Upling",

//Asturias

"Asturias - Agbanga",
"Asturias - Agtugop",
"Asturias - Bago",
"Asturias - Bairan",
"Asturias - Banban",
"Asturias - Baye",
"Asturias - Bog-o",
"Asturias - Kaluangan",
"Asturias - Lanao",
"Asturias - Langub",
"Asturias - Looc Norte",
"Asturias - Looc Sur",
"Asturias - Lunas",
"Asturias - Magcalape",
"Asturias - Manguiao",
"Asturias - New Bago",
"Asturias - Owak",
"Asturias - Poblacion",
"Asturias - Saksak",
"Asturias - San Isidro",
"Asturias - San Roque",
"Asturias - Santa Lucia",
"Asturias - Santa Rita",
"Asturias - Tag-amakan",
"Asturias - Tagbubonga",
"Asturias - Tubigagmanok",
"Asturias - Tubod",

//Badian

"Badian - Alawijao",
"Badian - Balhaan",
"Badian - Banhigan",
"Badian - Basak",
"Badian - Basiao",
"Badian - Bato",
"Badian - Bugas",
"Badian - Calangcang",
"Badian - Candiis",
"Badian - Dagatan",
"Badian - Dobdob",
"Badian - Ginablan",
"Badian - Lambug",
"Badian - Malabago",
"Badian - Malhiao",
"Badian - Manduyong",
"Badian - Matutinao",
"Badian - Patong",
"Badian - Poblacion",
"Badian - Sanlagan",
"Badian - Santa Cruz",
"Badian - Sohoton",
"Badian - Talo-ot",
"Badian - Tanghas",
"Badian - Taytay",
"Badian - Tigbao",
"Badian - Tiguib",
"Badian - Tubod",
"Badian - Zaragosa",
      
     
        ];

        function autocomplete(inp, arr) {
            let currentFocus;
            inp.addEventListener("input", function(e) {
                const resultsDiv = document.getElementById("autocomplete-results");
                resultsDiv.innerHTML = '';
                const val = this.value.toLowerCase();
                
                if (!val) return false;
                
                currentFocus = -1;
                
                const matches = arr.filter(item => 
                    item.toLowerCase().includes(val)
                ).slice(0, 10); // Show max 10 results
                
                matches.forEach(match => {
                    const div = document.createElement("div");
                    div.innerHTML = "<strong>" + match.substring(0, val.length) + "</strong>";
                    div.innerHTML += match.substring(val.length);
                    div.innerHTML += "<input type='hidden' value='" + match + "'>";
                    div.addEventListener("click", function() {
                        inp.value = this.getElementsByTagName("input")[0].value;
                        resultsDiv.innerHTML = '';
                    });
                    resultsDiv.appendChild(div);
                });
            });
            
            inp.addEventListener("keydown", function(e) {
                let items = document.getElementById("autocomplete-results").children;
                if (e.keyCode === 40) { // Down arrow
                    currentFocus++;
                    addActive(items);
                } else if (e.keyCode === 38) { // Up arrow
                    currentFocus--;
                    addActive(items);
                } else if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    if (currentFocus > -1) {
                        items[currentFocus].click();
                    }
                }
            });
            
            function addActive(items) {
                if (!items) return false;
                removeActive(items);
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = (items.length - 1);
                items[currentFocus].classList.add("autocomplete-active");
            }
            
            function removeActive(items) {
                Array.from(items).forEach(item => {
                    item.classList.remove("autocomplete-active");
                });
            }
            
            // Close the autocomplete when clicking elsewhere
            document.addEventListener("click", function(e) {
                if (e.target !== inp) {
                    document.getElementById("autocomplete-results").innerHTML = '';
                }
            });
        }

        function toggleFields() {
            const role = document.getElementById('role').value;
            const agentFields = document.getElementById('agent-fields');
            const clientFields = document.getElementById('client-fields');
            
            agentFields.style.display = (role === 'AGENT') ? 'block' : 'none';
            clientFields.style.display = (role === 'CLIENT') ? 'block' : 'none';
            
            const licenseField = document.getElementById('license_number');
            licenseField.required = (role === 'AGENT');
            
            if (role !== 'AGENT') {
                licenseField.classList.remove('error-input');
                const errorElement = licenseField.nextElementSibling;
                if (errorElement && errorElement.classList.contains('field-error')) {
                    errorElement.style.display = 'none';
                }
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize autocomplete
            autocomplete(document.getElementById("address"), cebuAddresses);
            
            // Password visibility toggle
            const passwordField = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            const passwordToggleIcon = document.getElementById('password-toggle-icon');
            
            passwordToggle.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
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
            
            nameFields.forEach(field => {
                const input = document.getElementById(field.id);
                const errorElement = document.getElementById(field.errorId);
                
                if (input && errorElement) {
                    input.addEventListener('blur', function() {
                        if (this.value.trim() !== '') {
                            if (!namePattern.test(this.value)) {
                                errorElement.style.display = 'block';
                                this.classList.add('error-input');
                            } else {
                                errorElement.style.display = 'none';
                                this.classList.remove('error-input');
                            }
                        } else {
                            errorElement.style.display = 'none';
                            this.classList.remove('error-input');
                        }
                    });
                    
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
                        
                        if (this.value.length < 8) {
                            isValid = false;
                            errorMessage = 'Password must be at least 8 characters long.';
                        }
                        else if (!/[A-Z]/.test(this.value)) {
                            isValid = false;
                            errorMessage = 'Password must contain at least one uppercase letter.';
                        }
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
                        passwordError.style.display = 'none';
                        this.classList.remove('error-input');
                    }
                });
                
                passwordField.addEventListener('focus', function() {
                    passwordError.style.display = 'none';
                });
            }
            
            // Phone number input trapping
            const phoneField = document.getElementById('phone');
            if (phoneField) {
                phoneField.addEventListener('keypress', function(e) {
                    const charCode = (e.which) ? e.which : e.keyCode;
                    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                        e.preventDefault();
                    }
                });
            }
            
            // Prevent form submission if there are errors
            document.getElementById('registrationForm').addEventListener('submit', function(e) {
                let hasErrors = false;
                
                // Validate all name fields before submission
                nameFields.forEach(field => {
                    const input = document.getElementById(field.id);
                    const errorElement = document.getElementById(field.errorId);
                    
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
                
                // Check required fields
                const requiredFields = ['firstname', 'lastname', 'email', 'phone', 'password', 'address'];
                requiredFields.forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && field.value.trim() === '') {
                        hasErrors = true;
                        if (!field.classList.contains('error-input')) {
                            field.classList.add('error-input');
                        }
                    }
                });
                
                // If there are client-side validation errors, prevent form submission
                if (hasErrors) {
                    e.preventDefault();
                    // Scroll to the first error
                    const firstError = document.querySelector('.error-input');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }
            });
            
            toggleFields();
        });
    </script>
</body>
</html>
