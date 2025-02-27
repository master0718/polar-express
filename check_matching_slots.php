<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "
        SELECT vs.id AS slot_id, vs.ride_id, vs.category, vs.max_volunteers
        FROM volunteer_slots vs
        JOIN ride_visibility rv ON vs.ride_id = rv.ride_id
        WHERE rv.role_id = 4 AND rv.visible = 1
    ";
    $result = $db->query($query);

    echo "<h1>Volunteer Slots for Role ID 4</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Slot ID</th><th>Ride ID</th><th>Category</th><th>Max Volunteers</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['slot_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ride_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>" . htmlspecialchars($row['max_volunteers']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
