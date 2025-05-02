<!--client/profile.php-->
<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/lot-reservation/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'CLIENT') {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';
$pw_success = '';
$pw_error = '';
$show_edit_profile = false;
$show_change_password = false;

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $firstname = trim($_POST['firstname']);
    $middlename = trim($_POST['middlename']);
    $lastname = trim($_POST['lastname']);

    $stmtUser = $conn->prepare("UPDATE user SET email = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmtUser->bind_param("sssi", $email, $phone, $address, $user_id);
    $userUpdated = $stmtUser->execute();
    $stmtUser->close();

    $stmtClient = $conn->prepare("UPDATE client SET firstname = ?, middlename = ?, lastname = ? WHERE user_id = ?");
    $stmtClient->bind_param("sssi", $firstname, $middlename, $lastname, $user_id);
    $clientUpdated = $stmtClient->execute();
    $stmtClient->close();

    if ($userUpdated && $clientUpdated) {
        $success = "Profile updated successfully!";
    } else {
        $error = "No changes were made or failed to update profile.";
    }

    $show_edit_profile = true;
    $show_change_password = false;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM user WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (password_verify($old_password, $hashed_password)) {
        if ($new_password === $confirm_password) {
            $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE user SET password = ? WHERE user_id = ?");
            $stmt->bind_param("si", $new_hashed, $user_id);
            if ($stmt->execute()) {
                $pw_success = "Password changed successfully!";
            } else {
                $pw_error = "Failed to change password.";
            }
            $stmt->close();
        } else {
            $pw_error = "New passwords do not match.";
        }
    } else {
        $pw_error = "Old password is incorrect.";
    }

    $show_edit_profile = false;
    $show_change_password = true;
}

// Fetch current profile
$stmt = $conn->prepare("SELECT u.email, u.phone, u.address, c.firstname, c.middlename, c.lastname
                        FROM user u JOIN client c ON u.user_id = c.user_id WHERE u.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email, $phone, $address, $firstname, $middlename, $lastname);
$stmt->fetch();
$stmt->close();

// Handle view switching
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_profile'])) {
        $show_edit_profile = true;
        $show_change_password = false;
    } elseif (isset($_POST['change_password_button'])) {
        $show_edit_profile = false;
        $show_change_password = true;
    } elseif (isset($_POST['back_to_profile'])) {
        $show_edit_profile = false;
        $show_change_password = false;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Profile</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>

    <h2>My Profile (Client)</h2>
    <a href="index.php">← Back to Dashboard</a>

    <!-- View Profile -->
    <?php if (!$show_edit_profile && !$show_change_password) : ?>
        <h3>Profile Details</h3>
        <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></p>
        <p><strong>Address:</strong> <?= htmlspecialchars($address) ?></p>
        <p><strong>First Name:</strong> <?= htmlspecialchars($firstname) ?></p>
        <p><strong>Middle Name:</strong> <?= htmlspecialchars($middlename) ?></p>
        <p><strong>Last Name:</strong> <?= htmlspecialchars($lastname) ?></p>

        <form method="POST">
            <button type="submit" name="edit_profile">Edit Profile</button>
            <button type="submit" name="change_password_button">Change Password</button>
        </form>
    <?php endif; ?>

    <!-- Edit Profile Form -->
    <?php if ($show_edit_profile) : ?>
        <h3>Edit Profile</h3>
        <?php if ($success) : ?><p style="color: green;"><?= $success ?></p><?php endif; ?>
        <?php if ($error) : ?><p style="color: red;"><?= $error ?></p><?php endif; ?>

        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required><br>

            <label>Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required><br>

            <label>Address:</label>
            <input type="text" name="address" value="<?= htmlspecialchars($address) ?>"><br>

            <label>First Name:</label>
            <input type="text" name="firstname" value="<?= htmlspecialchars($firstname) ?>" required><br>

            <label>Middle Name:</label>
            <input type="text" name="middlename" value="<?= htmlspecialchars($middlename) ?>"><br>

            <label>Last Name:</label>
            <input type="text" name="lastname" value="<?= htmlspecialchars($lastname) ?>" required><br>

            <button type="submit" name="update_profile">Update Profile</button>
        </form>

        <form method="POST">
            <button type="submit" name="back_to_profile">Back to Profile</button>
        </form>
    <?php endif; ?>

    <!-- Change Password Form -->
    <?php if ($show_change_password) : ?>
        <h3>Change Password</h3>
        <?php if ($pw_success) : ?><p style="color: green;"><?= $pw_success ?></p><?php endif; ?>
        <?php if ($pw_error) : ?><p style="color: red;"><?= $pw_error ?></p><?php endif; ?>

        <form method="POST">
            <label>Old Password:</label>
            <input type="password" name="old_password" required><br>

            <label>New Password:</label>
            <input type="password" name="new_password" required><br>

            <label>Confirm New Password:</label>
            <input type="password" name="confirm_password" required><br>

            <button type="submit" name="change_password">Change Password</button>
        </form>

        <form method="POST">
            <button type="submit" name="back_to_profile">Back to Profile</button>
        </form>
    <?php endif; ?>

</body>
</html>
