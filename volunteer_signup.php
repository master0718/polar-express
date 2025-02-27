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
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Volunteer Sign Up</h1>
        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <form method="POST" class="w-50 mx-auto">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone (Optional)</label>
                <input type="text" name="phone" id="phone" class="form-control">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign Up</button>
        </form>
    </div>
</body>
</html>
