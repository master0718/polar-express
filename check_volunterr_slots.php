<?php
try {
    $db = new SQLite3('admin_users.db');
    $query = "SELECT * FROM volunteer_slots;";
    $result = $db->query($query);

    echo "<h1>Volunteer Slots Table</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Ride ID</th><th>Category</th><th>Max Volunteers</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
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
