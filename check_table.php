<?php
try {
    $db = new SQLite3('admin_users.db'); // Ensure the database name matches
    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='admin_users'");
    
    if ($result->fetchArray(SQLITE3_ASSOC)) {
        echo "The 'admin_users' table exists.";
    } else {
        echo "The 'admin_users' table does not exist.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
