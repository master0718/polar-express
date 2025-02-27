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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Volunteer Login</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white text-center">
                        <h3>Volunteer Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errorMessage)): ?>
                            <div class="alert alert-danger">
                                <?= htmlspecialchars($errorMessage) ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="password" id="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Log In</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>