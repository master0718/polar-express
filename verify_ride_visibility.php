<?php
try {
    $db = new SQLite3('admin_users.db');

    $query = "SELECT * FROM ride_visibility;";
    $result = $db->query($query);

    echo "<h1>Ride Visibility Table</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>Ride ID</th><th>Role ID</th><th>Visible</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['ride_id']) . "</td>";
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
