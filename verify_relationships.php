<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "
        SELECT rv.ride_id, rv.role_id, rv.visible, r.day, r.time
        FROM ride_visibility rv
        JOIN rides r ON rv.ride_id = r.id
        WHERE rv.role_id = 4 AND rv.visible = 1
        ORDER BY r.day, r.time
    ";
    $result = $db->query($query);

    echo "<h1>Ride Visibility and Rides</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Ride ID</th><th>Role ID</th><th>Visible</th><th>Day</th><th>Time</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ride_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['visible']) . "</td>";
        echo "<td>" . htmlspecialchars($row['day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['time']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
