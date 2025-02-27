<?php
header("Content-Type: application/json");
require 'db_connection.php';
session_start(); // Ensure session is started to access user data

// Decode incoming data
$data = json_decode(file_get_contents("php://input"), true);

// Validate incoming data structure
if (!$data || empty($data) || !isset($data[0]['slotId']) || !isset($data[0]['partySize'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data structure or missing required fields']);
    exit;
}

// Ensure the volunteer is logged in
$volunteerId = $_SESSION['volunteer_id'] ?? null;
if (!$volunteerId) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

try {
    $db->exec('BEGIN TRANSACTION');

    foreach ($data as $ride) {
        $slotId = (int) $ride['slotId'];
        $partySize = (int) $ride['partySize'];
        $notes = $ride['notes'] ?? '';

        // Check if the ride has enough available slots
        $availabilityStmt = $db->prepare("
            SELECT max_volunteers - COALESCE((SELECT SUM(num_people) FROM volunteer_signups WHERE slot_id = :slot_id), 0) AS available_slots
            FROM volunteer_slots
            WHERE id = :slot_id
        ");
        $availabilityStmt->bindValue(':slot_id', $slotId, SQLITE3_INTEGER);
        $availabilityResult = $availabilityStmt->execute();
        $availability = $availabilityResult->fetchArray(SQLITE3_ASSOC);

        if (!$availability || $partySize > $availability['available_slots']) {
            echo json_encode(['success' => false, 'message' => "Not enough available slots for Slot ID: $slotId"]);
            $db->exec('ROLLBACK');
            exit;
        }

        // Insert the volunteer signup
        $stmt = $db->prepare("
            INSERT INTO volunteer_signups (slot_id, volunteer_id, num_people, notes)
            VALUES (:slot_id, :volunteer_id, :num_people, :notes)
        ");
        $stmt->bindValue(':slot_id', $slotId, SQLITE3_INTEGER);
        $stmt->bindValue(':volunteer_id', $volunteerId, SQLITE3_INTEGER);
        $stmt->bindValue(':num_people', $partySize, SQLITE3_INTEGER);
        $stmt->bindValue(':notes', $notes, SQLITE3_TEXT);
        $stmt->execute();
    }

    $db->exec('COMMIT');
    echo json_encode(['success' => true, 'message' => 'Selections successfully submitted']);
} catch (Exception $e) {
    $db->exec('ROLLBACK');
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
