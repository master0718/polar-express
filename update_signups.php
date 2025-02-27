<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require 'db_connection.php';

// Set headers
header('Content-Type: application/json');

// Capture raw POST data
$rawData = file_get_contents('php://input');
file_put_contents('debug_log.txt', "Start of update_signups.php\nRaw Data Received: $rawData\n", FILE_APPEND);

// Decode JSON data
$data = json_decode($rawData, true);

// Check if JSON is valid
if (json_last_error() !== JSON_ERROR_NONE) {
    file_put_contents('debug_log.txt', "JSON Decode Error: " . json_last_error_msg() . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON received.']);
    exit;
}

// Check if 'updates' key exists
if (!isset($data['updates']) || empty($data['updates'])) {
    file_put_contents('debug_log.txt', "No updates received.\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'No updates received.']);
    exit;
}

try {
    // Start processing updates
$updatedCount = 0;

foreach ($data['updates'] as $update) {
    file_put_contents('debug_log.txt', "Processing Update: " . json_encode($update) . PHP_EOL, FILE_APPEND);

    $signupId = intval($update['signup_id']);
    $slotId = intval($update['slot_id']);
    $roleId = intval($update['role_id']);
    $numPeople = intval($update['num_people']);
    $notes = trim($update['notes']);

    // Fetch current values from the database
    $currentStmt = $db->prepare("SELECT slot_id, num_people, notes FROM volunteer_signups WHERE id = :signup_id");
    $currentStmt->bindValue(':signup_id', $signupId, SQLITE3_INTEGER);
    $currentResult = $currentStmt->execute()->fetchArray(SQLITE3_ASSOC);

    // Skip update if no changes are detected
    if (
        $currentResult['slot_id'] == $slotId &&
        $currentResult['num_people'] == $numPeople &&
        $currentResult['notes'] == $notes
    ) {
        file_put_contents('debug_log.txt', "No changes detected for signup_id: $signupId" . PHP_EOL, FILE_APPEND);
        continue;
    }

    // Execute update
    $stmt = $db->prepare("
        UPDATE volunteer_signups 
        SET slot_id = :slot_id, num_people = :num_people, notes = :notes 
        WHERE id = :signup_id
    ");

    $stmt->bindValue(':slot_id', $slotId, SQLITE3_INTEGER);
    $stmt->bindValue(':num_people', $numPeople, SQLITE3_INTEGER);
    $stmt->bindValue(':notes', $notes, SQLITE3_TEXT);
    $stmt->bindValue(':signup_id', $signupId, SQLITE3_INTEGER);

    if ($stmt->execute()) {
        $updatedCount++;
        file_put_contents('debug_log.txt', "Update successful for signup_id: $signupId" . PHP_EOL, FILE_APPEND);
    } else {
        $error = $db->lastErrorMsg();
        file_put_contents('debug_log.txt', "SQL Error for signup_id: $signupId - $error" . PHP_EOL, FILE_APPEND);
        throw new Exception("SQL Error: $error");
    }
}

// Send a generic success message
$responseMessage = $updatedCount > 0 ? "All changes successfully recorded." : "No changes were made.";
$response = ['success' => true, 'message' => $responseMessage];

file_put_contents('debug_log.txt', "Response Sent: " . json_encode($response) . PHP_EOL, FILE_APPEND);
echo json_encode($response);

} catch (Exception $e) {
    // Handle exceptions
    $errorResponse = ['success' => false, 'message' => $e->getMessage()];
    file_put_contents('debug_log.txt', "Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
    echo json_encode($errorResponse);
}
?>
