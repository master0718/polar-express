<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection
require 'db_connection.php'; // Ensure this file establishes a valid SQLite3 connection

// Function to fetch valid shifts for a specific ride, role, and group size
function getValidShifts($rideId, $roleId, $groupSize) {
    global $db;

    // Updated SQL query to calculate and format shift times
    $stmt = $db->prepare("
        SELECT sac.shift_id, r.day, r.time AS ride_time,
               STRFTIME('%I:%M %p', r.time) AS shift_start,
               STRFTIME('%I:%M %p', DATETIME(r.time, '+90 minutes')) AS shift_end,
               sac.remaining_spots,
               CASE
                   WHEN sac.remaining_spots < :groupSize THEN 1
                   ELSE 0
               END AS is_conditional
        FROM shift_availability_cache sac
        JOIN volunteer_slots vs ON sac.shift_id = vs.id
        JOIN rides r ON vs.ride_id = r.id
        WHERE vs.role_id = :roleId
        ORDER BY r.day, r.time
    ");

    // Bind parameters
    $stmt->bindValue(':rideId', $rideId, SQLITE3_INTEGER);
    $stmt->bindValue(':roleId', $roleId, SQLITE3_INTEGER);
    $stmt->bindValue(':groupSize', $groupSize, SQLITE3_INTEGER);

    $result = $stmt->execute();
    $validShifts = [];

    // Process the results
    while ($shift = $result->fetchArray(SQLITE3_ASSOC)) {
        // Format the day abbreviation
        $dayAbbr = date('D', strtotime($shift['day']));

        // Create display text
        $shift['display_text'] = sprintf(
            "%s - %s - %s",
            $dayAbbr,
            $shift['shift_start'],
            $shift['shift_end']
        );

        // Add valid shifts to the array
        if ($shift['remaining_spots'] > 0 || $shift['is_conditional']) {
            $validShifts[] = $shift;
        }
    }

    return $validShifts;
}
?>