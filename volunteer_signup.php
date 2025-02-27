<?php
require 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Basic validation
    if ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email address.";
    } else {
        try {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            // Insert new volunteer into the database
            $stmt = $db->prepare("
                INSERT INTO volunteers (email, name, phone, password)
                VALUES (:email, :name, :phone, :password)
            ");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $stmt->bindValue(':name', $name, SQLITE3_TEXT);
            $stmt->bindValue(':phone', $phone, SQLITE3_TEXT);
            $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
            $stmt->execute();

            // Automatically log the volunteer in
            $_SESSION['volunteer_logged_in'] = true;
            $_SESSION['volunteer_email'] = $email;
            $_SESSION['volunteer_name'] = $name;

            // Redirect to role selection page
            header('Location: role_selection.php');
            exit;
        } catch (Exception $e) {
            if (str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                $errorMessage = "An account with this email already exists.";
            } else {
                $errorMessage = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Volunteer Sign Up</title>
    <link rel="stylesheet" href="./master.css">
    <style>
        input::placeholder {
            color: white;
            /* Placeholder text color */
            opacity: 1;
            /* Ensure full visibility in some browsers */
        }
    </style>
</head>

<body>
    <div id="login-window">
        <div id="login-box" class="glassy-dark-bg rounded-custom">
            <div id='logo'>
                <!-- <img src="./images/dummy_logo.png" alt="logo" height="64" /> -->
            </div>
            <h2 id="login-header" class="logo-font">Volunteer Sign Up</h2>
            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
            <?php endif; ?>
            <form class="my-3" method="POST">
                <input class="input text-pale-light full-opacity-bg rounded-custom mt-3" type="email" name="email" id="email" placeholder="Email:" required /><br />
                <input class="input text-pale-light full-opacity-bg rounded-custom mt-3" type="text" name="name" id="name" placeholder="Name:" required /><br />
                <input class="input text-pale-light full-opacity-bg rounded-custom mt-3" type="text" name="phone" id="phone" placeholder="Phone (Optional):" /><br />
                <input class="input text-pale-light full-opacity-bg rounded-custom mt-3" type="password" name="password" id="password" placeholder="Password:" required /><br />
                <input class="input text-pale-light full-opacity-bg rounded-custom mt-3" type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password:" required /><br />
                <button class="btn-hover  w-100 mt-5" type="submit" style="margin : 0; background-color:#3D4267">Sign Up</button>
            </form>
        </div>
    </div>
</body>

</html>