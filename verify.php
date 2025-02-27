<?php
$db = new SQLite3('admin_users.db');
$result = $db->query("SELECT * FROM rides");

echo "<h1>Rides Table</h1>";
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    echo "<p>Day: " . $row['day'] . " | Time: " . $row['time'] . "</p>";
}
?>
