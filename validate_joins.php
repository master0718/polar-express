<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "
        SELECT vs.id AS slot_id, vs.category, r.day, r.time, rv.role_id, rv.visible
        FROM volunteer_slots vs
        JOIN rides r ON vs.ride_id = r.id
        JOIN ride_visibility rv ON vs.ride_id = rv.ride_id
        ORDER BY rv.role_id, r.day, r.time
    ";
    $result = $db->query($query);

    echo "<h1>Join Validation Results</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Slot ID</th><th>Category</th><th>Day</th><th>Time</th><th>Role ID</th><th>Visible</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['slot_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category']) . "</td>";
        echo "<td>" . htmlspecialchars($row['day']) . "</td>";
        echo "<td>" . htmlspecialchars($row['time']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['visible']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
