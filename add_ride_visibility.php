<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    // Connect to SQLite database
    $db = new SQLite3('admin_users.db');
    echo "Database connected successfully.<br>";

    // Fetch all rides
    $rides = $db->query("SELECT id FROM rides");
    if (!$rides) {
        throw new Exception("Error: Unable to fetch rides.<br>");
    }

    // Fetch all roles
    $roles = $db->query("SELECT id FROM volunteer_roles");
    if (!$roles) {
        throw new Exception("Error: Unable to fetch volunteer_roles.<br>");
    }

    // Populate ride_visibility table
    $rideCount = 0;
    $visibilityCount = 0;
    while ($ride = $rides->fetchArray(SQLITE3_ASSOC)) {
        $roles->reset(); // Reset roles pointer for each ride
        $rideCount++;

        while ($role = $roles->fetchArray(SQLITE3_ASSOC)) {
            $stmt = $db->prepare("INSERT OR IGNORE INTO ride_visibility (ride_id, role_id, visible) VALUES (:ride_id, :role_id, :visible)");
            $stmt->bindValue(':ride_id', $ride['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':role_id', $role['id'], SQLITE3_INTEGER);
            $stmt->bindValue(':visible', 1, SQLITE3_INTEGER); // Default to visible

            if ($stmt->execute()) {
                $visibilityCount++;
            } else {
                echo "Error: Failed to insert visibility for Ride ID {$ride['id']}, Role ID {$role['id']}.<br>";
            }
        }
    }

    echo "Processed $rideCount rides and created/updated $visibilityCount visibility entries.<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
