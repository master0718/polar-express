<?php
try {
    $db = new SQLite3('admin_users.db');

    $db->exec("
        DELETE FROM ride_visibility
        WHERE rowid NOT IN (
            SELECT MIN(rowid)
            FROM ride_visibility
            GROUP BY ride_id, role_id
        )
    ");

    echo "Redundant entries removed from ride_visibility.";
    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
