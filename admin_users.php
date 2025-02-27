<?php
try {
    $db = new SQLite3('admin_users.db');
    $filePath = 'seed_1.sql';

    if (!file_exists($filePath)) {
        throw new Exception("SQL file not found at: " . realpath($filePath));
    }

    $sql = file_get_contents($filePath);
    $commands = explode(';', $sql);

    foreach ($commands as $command) {
        $command = trim($command);
        if (!empty($command)) {
            $db->exec($command);
        }
    }

    echo "Database schema updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
