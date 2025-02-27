<?php
require 'db_connection.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $password = trim($_POST['password']);

    try {
        $stmt = $db->prepare("SELECT * FROM volunteers WHERE LOWER(email) = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if (!$result || !password_verify($password, $result['password'])) {
            $errorMessage = "Invalid email or password.";
        } else {
            // Set session for successful login
            $_SESSION['volunteer_logged_in'] = true;
            $_SESSION['volunteer_id'] = $result['id'];
            $_SESSION['volunteer_email'] = $result['email'];
            $_SESSION['volunteer_name'] = $result['name'];
            echo $_SESSION['volunteer_name'];

            header('Location: role_selection.php'); // Redirect to role selection
            exit;
        }
    } catch (Exception $e) {
        $errorMessage = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Login</title>
    <link rel="stylesheet" href="./master.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <h2 id="login-header" class="logo-font">Volunteer Login</h2>
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
            <?php endif; ?>
            <form class="my-3" method="POST">
                <input class="input text-pale-light full-opacity-bg rounded-custom" type="email" name="email" id="email" autocomplete="username" placeholder="Email:" required /><br />
                <input class="input text-pale-light full-opacity-bg rounded-custom" type="password" name="password" id="password" autocomplete="current-password" placeholder="Password:" required /><br />
                <div class="remember-section" style="flex:right">
                    <input type="checkbox" name="remember" id="remember" />
                    <label>Remember me</label>
                </div>
                <div class="row form-group">
                    <div class="form-controller ">
                        <button class="btn-hover  w-100" type="submit" style="margin : 0 ;background-color:#3D4267">Log In</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>