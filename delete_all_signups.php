<?php
require 'db_connection.php';
session_start();

$volunteerId = $_SESSION['volunteer_id'];

$stmt = $db->prepare("DELETE FROM volunteer_signups WHERE volunteer_id = :volunteerId");
$stmt->bindValue(':volunteerId', $volunteerId, SQLITE3_INTEGER);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete all signups.']);
}
?>
