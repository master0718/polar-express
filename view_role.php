<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['admin']['logged_in']) || $_SESSION['admin']['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['role_id'])) {
    echo "Error: Role ID not provided.";
    exit;
}

$role_id = intval($_GET['role_id']);

try {
    $db = new SQLite3('admin_users.db');

    // Fetch role name
    $roleQuery = $db->prepare("SELECT role_name FROM volunteer_roles WHERE id = :role_id");
    $roleQuery->bindValue(':role_id', $role_id, SQLITE3_INTEGER);
    $roleResult = $roleQuery->execute();
    $role = $roleResult->fetchArray(SQLITE3_ASSOC);

    if (!$role) {
        echo "Error: Role not found.";
        exit;
    }

    $role_name = $role['role_name'];

    // Fetch rides and volunteer slots for the selected role
    $query = "
        SELECT r.day, r.time, vs.id AS slot_id, vs.max_volunteers,
               COALESCE((SELECT SUM(num_people) FROM volunteer_signups WHERE slot_id = vs.id), 0) AS filled_slots,
               vs.max_volunteers - COALESCE((SELECT SUM(num_people) FROM volunteer_signups WHERE slot_id = vs.id), 0) AS remaining_slots
        FROM rides r
        LEFT JOIN volunteer_slots vs ON r.id = vs.ride_id AND vs.role_id = :role_id
        ORDER BY r.day ASC, r.time ASC
    ";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':role_id', $role_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    $rides = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        // Format the day as "Day of Week, MM/DD/YYYY"
        $date = DateTime::createFromFormat('Y-m-d', $row['day']);
        $row['day'] = $date ? $date->format('l, m/d/Y') : $row['day'];

        // Ensure default values for slots
        $row['max_volunteers'] = $row['max_volunteers'] ?? 0;
        $row['filled_slots'] = $row['filled_slots'] ?? 0;
        $row['remaining_slots'] = $row['max_volunteers'] - $row['filled_slots'];

        $rides[] = $row;
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
    <title>View Role</title>
    <style>
        .full { background-color: lightgray; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Role: <?= htmlspecialchars($role_name) ?></h1>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Max Slots</th>
                    <th>Filled Slots</th>
                    <th>Remaining Slots</th>
                    <th>Volunteers</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rides as $ride): ?>
                    <?php 
                    $remaining_slots = $ride['remaining_slots'];
                    $is_full = ($remaining_slots <= 0); 
                    ?>
                    <tr class="<?= $is_full ? 'full' : '' ?>">
                        <td><?= htmlspecialchars($ride['day']) ?></td>
                        <td><?= htmlspecialchars($ride['time']) ?></td>
                        <td><?= htmlspecialchars($ride['max_volunteers']) ?></td>
                        <td><?= htmlspecialchars($ride['filled_slots']) ?></td>
                        <td>
                            <?= ($remaining_slots <= 0) ? 'FULL' : htmlspecialchars($remaining_slots) ?>
                        </td>
                        <td>
                            <?php if ($ride['filled_slots'] > 0): ?>
                                <a href="view_volunteers.php?slot_id=<?= $ride['slot_id'] ?>" class="btn btn-primary btn-sm">
                                    View Volunteers
                                </a>
                            <?php else: ?>
                                No Volunteers
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <a href="manage_volunteers.php" class="btn btn-secondary mt-3">Back to Roles</a>
    </div>
</body>
</html>
