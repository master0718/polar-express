<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "
        SELECT vs.id AS slot_id, vs.category, rv.role_id, r.day, r.time
        FROM volunteer_slots vs
        JOIN ride_visibility rv ON vs.ride_id = rv.ride_id
        JOIN rides r ON vs.ride_id = r.id
        WHERE rv.visible = 1
    ";
    $result = $db->query($query);

    echo "<h1>Diagnostic: Volunteer Slots and Ride Visibility</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Slot ID</th><th>Category</th><th>Role ID</th><th>Day</th><th>Time</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['slot_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role_id']) . "</td>";
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
