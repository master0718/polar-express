<?php
session_start();

// Check if the volunteer is logged in
if (!isset($_SESSION['volunteer_logged_in']) || $_SESSION['volunteer_logged_in'] !== true) {
    header('Location: volunteer_login.php'); // Redirect to login if not logged in
    exit;
}

try {
    $db = new SQLite3('admin_users.db');
    $query = "SELECT id, role_name FROM volunteer_roles ORDER BY role_name ASC";
    $roles = $db->query($query);
} catch (Exception $e) {
    echo "Error fetching roles: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Role Selection</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Select a Role to Volunteer For</h1>
        <div class="list-group">
            <?php
            // Map role IDs to the specific pages
            $role_pages = [
                1 => 'signup_jolly_people.php',
                2 => 'signup_elves.php',
                3 => 'signup_chefs.php',
                4 => 'signup_conductors.php',
            ];

            // Display role links dynamically
            while ($role = $roles->fetchArray(SQLITE3_ASSOC)) {
                $role_name = htmlspecialchars($role['role_name']);
                $role_id = $role['id'];

                // Ensure there is a corresponding page for the role
                $page = $role_pages[$role_id] ?? '#';

                // Include session ID to pass login info
                echo "<a href=\"$page\" class=\"list-group-item list-group-item-action\">
                        Sign Up for $role_name
                      </a>";
            }
            ?>
        </div>
        <a href="volunteer_dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
