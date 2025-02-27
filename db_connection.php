<?php
try {
    $db = new SQLite3('admin_users.db'); // Adjust the database filename if necessary
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
