<?php
require 'db_connection.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$signupId = $data['signupId'] ?? null;

if ($signupId) {
    $stmt = $db->prepare("DELETE FROM volunteer_signups WHERE id = :signupId");
    $stmt->bindValue(':signupId', $signupId, SQLITE3_INTEGER);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete signup.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid signup ID.']);
}
?>
