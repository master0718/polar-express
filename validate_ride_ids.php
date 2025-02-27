<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "
        SELECT r.id AS ride_id_in_rides, vs.ride_id AS ride_id_in_slots, rv.ride_id AS ride_id_in_visibility
        FROM rides r
        LEFT JOIN volunteer_slots vs ON r.id = vs.ride_id
        LEFT JOIN ride_visibility rv ON r.id = rv.ride_id
        ORDER BY r.id
    ";
    $result = $db->query($query);

    echo "<h1>Ride ID Validation</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Ride ID (Rides)</th><th>Ride ID (Slots)</th><th>Ride ID (Visibility)</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ride_id_in_rides']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ride_id_in_slots']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ride_id_in_visibility']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";

    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
