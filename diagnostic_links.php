<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "
        SELECT vs.id AS slot_id, vs.category AS role_name, vs.ride_id, rv.role_id, rv.visible, r.day, r.time
        FROM volunteer_slots vs
        JOIN ride_visibility rv ON vs.ride_id = rv.ride_id
        JOIN rides r ON vs.ride_id = r.id
        WHERE rv.visible = 1
    ";
    $result = $db->query($query);

    echo "<h1>Diagnostic: Slot, Role, and Ride Links</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Slot ID</th><th>Role Name</th><th>Ride ID</th><th>Role ID</th><th>Visible</th><th>Day</th><th>Time</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['slot_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role_name']) . "</td>";
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
