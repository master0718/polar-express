<?php
try {
    // Connect to the SQLite database
    $db = new SQLite3('admin_users.db');

    // Fetch a list of all tables in the database
    $tables = [];
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;");
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $tables[] = $row['name'];
    }

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head><meta charset='UTF-8'><title>Database Debugger</title>";
    echo "<link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>";
    echo "</head><body class='bg-light'>";
    echo "<div class='container py-5'>";
    echo "<h1 class='text-center mb-4'>Database Debugger</h1>";

    foreach ($tables as $table) {
        echo "<h2>Table: $table</h2>";
        echo "<table class='table table-bordered'>";
        
        // Fetch all data from the table
        $data = $db->query("SELECT * FROM $table");
        $columnsResult = $db->query("PRAGMA table_info($table);");

        // Display table columns
        echo "<thead class='table-dark'><tr>";
        while ($column = $columnsResult->fetchArray(SQLITE3_ASSOC)) {
            echo "<th>" . htmlspecialchars($column['name']) . "</th>";
        }
        echo "</tr></thead>";

        // Display table rows
        echo "<tbody>";
        while ($row = $data->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    echo "</div></body></html>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
