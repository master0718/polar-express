<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin']['logged_in']) || $_SESSION['admin']['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

try {
    $db = new SQLite3('admin_users.db');

    // Fetch all roles
    $query = "SELECT id, role_name FROM volunteer_roles";
    $result = $db->query($query);

    $roles = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $roles[] = $row;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Manage Volunteers</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Manage Volunteers</h1>
        <div class="list-group">
            <?php foreach ($roles as $role): ?>
                <a href="view_role.php?role_id=<?= $role['id'] ?>" class="list-group-item list-group-item-action">
                    <?= htmlspecialchars($role['role_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <a href="dashboard.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
