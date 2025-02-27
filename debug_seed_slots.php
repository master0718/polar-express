<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
echo "Debug script started.<br>";

try {
    echo "<h1>Debugging Volunteer Slots Seeding</h1>";

    // Connect to SQLite database
    $db = new SQLite3('admin_users.db'); // Ensure correct path
echo "Database connected successfully.<br>";

    // Step 1: Verify rides table
    echo "<h2>Step 1: Verifying Rides Table</h2>";
    $rides = $db->query("SELECT * FROM rides");
if (!$rides) {
    throw new Exception("Error: Unable to fetch rides.<br>");
} else {
    echo "Fetched rides successfully.<br>";
}


    // Step 2: Verify roles table
    echo "<h2>Step 2: Verifying Volunteer Roles Table</h2>";
    $roles = $db->query("SELECT * FROM volunteer_roles");
    if (!$roles) {
        echo "Error: Unable to fetch volunteer_roles table.<br>";
    } else {
        echo "<table border='1' cellpadding='5'><tr><th>Role ID</th><th>Role Name</th></tr>";
        while ($row = $roles->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['role_name']}</td></tr>";
        }
        echo "</table>";
    }

    // Step 3: Verify slots table
    echo "<h2>Step 3: Verifying Volunteer Slots Table</h2>";
    $slots = $db->query("SELECT * FROM volunteer_slots");
    if (!$slots) {
        echo "Error: Unable to fetch volunteer_slots table.<br>";
    } else {
        echo "<table border='1' cellpadding='5'><tr><th>Slot ID</th><th>Ride ID</th><th>Role ID</th><th>Max Volunteers</th></tr>";
        while ($row = $slots->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr><td>{$row['id']}</td><td>{$row['ride_id']}</td><td>{$row['role_id']}</td><td>{$row['max_volunteers']}</td></tr>";
        }
        echo "</table>";
    }

    // Step 4: Attempt to seed slots (for debugging purposes)
    echo "<h2>Step 4: Attempting to Seed Volunteer Slots</h2>";
    $rides = $db->query("SELECT id FROM rides");
    $roles = $db->query("SELECT id, role_name FROM volunteer_roles");

    if (!$rides || !$roles) {
        echo "Error: Unable to fetch rides or roles for seeding.<br>";
    } else {
        $db->exec("DELETE FROM volunteer_slots");
        echo "Cleared volunteer_slots table.<br>";

        $rideCount = 0;
        $roleCount = 0;

        while ($ride = $rides->fetchArray(SQLITE3_ASSOC)) {
            $roles->reset(); // Reset roles pointer
            $rideCount++;

            while ($role = $roles->fetchArray(SQLITE3_ASSOC)) {
                $roleCount++;
                $max_volunteers = match ($role['role_name']) {
                    'Jolly People' => 8,
                    'Elves' => 12,
                    'Chefs' => 6,
                    'Conductors' => 3,
                    default => 0,
                };

                $stmt = $db->prepare("INSERT INTO volunteer_slots (ride_id, role_id, max_volunteers) VALUES (:ride_id, :role_id, :max_volunteers)");
                if (!$stmt) {
                    echo "Error: Failed to prepare statement for Ride ID {$ride['id']}, Role ID {$role['id']}.<br>";
                    continue;
                }

                $stmt->bindValue(':ride_id', $ride['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':role_id', $role['id'], SQLITE3_INTEGER);
                $stmt->bindValue(':max_volunteers', $max_volunteers, SQLITE3_INTEGER);

                if ($stmt->execute()) {
                    echo "Inserted slot for Ride ID {$ride['id']}, Role ID {$role['id']} with Max Volunteers: $max_volunteers.<br>";
                } else {
                    echo "Error: Failed to insert slot for Ride ID {$ride['id']}, Role ID {$role['id']}.<br>";
                }
            }
        }

        echo "Processed $rideCount rides and $roleCount roles for seeding.<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
