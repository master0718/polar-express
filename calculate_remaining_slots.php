<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';

try {
    // Prepare the cache table
    $db->exec("
        CREATE TABLE IF NOT EXISTS shift_availability_cache (
            shift_id INTEGER,
            role_id INTEGER,
            remaining_spots INTEGER,
            is_full BOOLEAN,
            PRIMARY KEY (shift_id, role_id)
        )
    ");

    // Clear the cache table before updating
    $db->exec("DELETE FROM shift_availability_cache");

    // Calculate remaining spots for each shift and role
    $stmt = $db->prepare("
        SELECT vslot.id AS shift_id, vslot.role_id, 
               vslot.max_volunteers - IFNULL(SUM(vs.num_people), 0) AS remaining_spots
        FROM volunteer_slots vslot
        LEFT JOIN volunteer_signups vs ON vslot.id = vs.slot_id
        GROUP BY vslot.id, vslot.role_id
    ");
    
    $result = $stmt->execute();

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $shiftId = $row['shift_id'];
        $roleId = $row['role_id'];
        $remainingSpots = $row['remaining_spots'];
        $isFull = $remainingSpots <= 0 ? 1 : 0;

        // Insert calculated data into the cache table
        $insertStmt = $db->prepare("
            INSERT INTO shift_availability_cache (shift_id, role_id, remaining_spots, is_full)
            VALUES (:shift_id, :role_id, :remaining_spots, :is_full)
        ");
        $insertStmt->bindValue(':shift_id', $shiftId, SQLITE3_INTEGER);
        $insertStmt->bindValue(':role_id', $roleId, SQLITE3_INTEGER);
        $insertStmt->bindValue(':remaining_spots', $remainingSpots, SQLITE3_INTEGER);
        $insertStmt->bindValue(':is_full', $isFull, SQLITE3_INTEGER);
        $insertStmt->execute();
    }

    echo "Shift availability cache updated successfully.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>