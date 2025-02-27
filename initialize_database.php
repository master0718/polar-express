<?php
try {
    // Database file name
    $dbFileName = 'new_admin_users.db';

    // SQL file path
    $filePath = 'create_new_database.sql'; // Ensure this matches your actual .sql file name and location

    // Check if the SQL file exists
    if (!file_exists($filePath)) {
        throw new Exception("SQL file not found at: " . realpath($filePath));
    }

    // Read the SQL file
    $sql = file_get_contents($filePath);
    if (!$sql) {
        throw new Exception("Failed to read SQL file at: " . $filePath);
    }

    // Connect to SQLite and create the database
    $db = new SQLite3($dbFileName);

    // Execute each command in the SQL file
    foreach (explode(';', $sql) as $command) {
        $command = trim($command); // Remove extra whitespace
        if (!empty($command)) {
            if (!$db->exec($command)) {
                throw new Exception("SQL Error: " . $db->lastErrorMsg());
            }
        }
    }

    echo "Database initialized successfully!";

    // Close the database connection
    $db->close();
} catch (Exception $e) {
    // Display error messages for debugging
    echo "Error: " . $e->getMessage();
}
?>
