<?php
try {
    $db = new SQLite3('admin_users.db');
    $query = "SELECT * FROM rides;";
    $result = $db->query($query);

    echo "<h1>Rides Table</h1>";
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>Day</th><th>Time</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
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
