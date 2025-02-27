<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['admin']['logged_in']) || $_SESSION['admin']['logged_in'] !== true) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit; // Stop further execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Manage Site Content</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center">Manage Site Content</h1>
        <div class="alert alert-info text-center">
            This feature is coming soon. Placeholder for content management.
        </div>
        <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</body>
</html>
