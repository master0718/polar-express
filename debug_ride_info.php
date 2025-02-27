<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['volunteer_id']) || empty($_SESSION['volunteer_id'])) {
    echo "Error: Not logged in.";
    exit;
}

$volunteerId = intval($_SESSION['volunteer_id']);
echo "Session volunteer_id: " . htmlspecialchars($volunteerId) . "<br><br>";

try {
    // Query 1: Fetch all signups for the volunteer
    echo "<strong>Step 1: volunteer_signups for volunteer_id = $volunteerId</strong><br>";
    $signupStmt = $db->prepare("SELECT * FROM volunteer_signups WHERE volunteer_id = :volunteerId");
    $signupStmt->bindValue(':volunteerId', $volunteerId, SQLITE3_INTEGER);
    $signupResult = $signupStmt->execute();

    $signups = [];
    while ($row = $signupResult->fetchArray(SQLITE3_ASSOC)) {
        $signups[] = $row;
    }

    if (empty($signups)) {
        echo "No signups found for the logged-in volunteer.<br><br>";
    } else {
        echo "<pre>";
        print_r($signups);
        echo "</pre>";
    }

    // Query 2: Check connections between volunteer_signups and volunteer_slots
    echo "<strong>Step 2: Checking connections between volunteer_signups and volunteer_slots</strong><br>";
    $slotsStmt = $db->prepare("
        SELECT vs.id AS signup_id, vs.volunteer_id, vslot.id AS slot_id, vslot.ride_id
        FROM volunteer_signups vs
        JOIN volunteer_slots vslot ON vs.slot_id = vslot.id
        WHERE vs.volunteer_id = :volunteerId
    ");
    $slotsStmt->bindValue(':volunteerId', $volunteerId, SQLITE3_INTEGER);
    $slotsResult = $slotsStmt->execute();

    $slots = [];
    while ($row = $slotsResult->fetchArray(SQLITE3_ASSOC)) {
        $slots[] = $row;
    }

    if (empty($slots)) {
        echo "No connections found between volunteer_signups and volunteer_slots.<br><br>";
    } else {
        echo "<pre>";
        print_r($slots);
        echo "</pre>";
    }

    // Query 3: Check connections between volunteer_slots and rides
    echo "<strong>Step 3: Checking connections between volunteer_slots and rides</strong><br>";
    $ridesStmt = $db->prepare("
        SELECT vslot.id AS slot_id, r.id AS ride_id, r.day, r.time
        FROM volunteer_slots vslot
        JOIN rides r ON vslot.ride_id = r.id
        WHERE vslot.id IN (SELECT slot_id FROM volunteer_signups WHERE volunteer_id = :volunteerId)
    ");
    $ridesStmt->bindValue(':volunteerId', $volunteerId, SQLITE3_INTEGER);
    $ridesResult = $ridesStmt->execute();

    $rides = [];
    while ($row = $ridesResult->fetchArray(SQLITE3_ASSOC)) {
        $rides[] = $row;
    }

    if (empty($rides)) {
        echo "No connections found between volunteer_slots and rides.<br><br>";
    } else {
        echo "<pre>";
        print_r($rides);
        echo "</pre>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
