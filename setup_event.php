<?php
require 'db_connection.php';
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin']['logged_in']) || $_SESSION['admin']['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $eventYear = intval($_POST['event_year']);
        $day1 = $_POST['day1'];
        $day2 = $_POST['day2'];
        $firstRideTime = $_POST['first_ride_time'];
        $rideDuration = intval($_POST['ride_duration']);
        $ridesDay1 = intval($_POST['rides_day1']);
        $ridesDay2 = intval($_POST['rides_day2']);

        // Delete old rides for the selected year
        $db->exec("DELETE FROM rides WHERE strftime('%Y', day) = '$eventYear'");

        // Insert new rides for Day 1
        $time = strtotime($firstRideTime);
        for ($i = 0; $i < $ridesDay1; $i++) {
            $rideTime = date('h:i A', $time + ($rideDuration * $i * 60));
            $db->exec("INSERT INTO rides (day, time) VALUES ('$day1', '$rideTime')");
        }

        // Insert new rides for Day 2
        for ($i = 0; $i < $ridesDay2; $i++) {
            $rideTime = date('h:i A', $time + ($rideDuration * $i * 60));
            $db->exec("INSERT INTO rides (day, time) VALUES ('$day2', '$rideTime')");
        }

        echo "<div class='alert alert-success'>Event setup completed successfully!</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Setup Event</title>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">Setup Event</h1>
        <form method="POST" class="w-50 mx-auto">
            <div class="mb-3">
                <label for="event_year" class="form-label">Event Year</label>
                <input type="number" id="event_year" name="event_year" class="form-control" value="<?= date('Y') ?>" required>
            </div>
            <div class="mb-3">
                <label for="day1" class="form-label">Day 1 (YYYY-MM-DD)</label>
                <input type="date" id="day1" name="day1" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="day2" class="form-label">Day 2 (YYYY-MM-DD)</label>
                <input type="date" id="day2" name="day2" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="first_ride_time" class="form-label">First Ride Time</label>
                <input type="time" id="first_ride_time" name="first_ride_time" class="form-control" value="10:00" required>
            </div>
            <div class="mb-3">
                <label for="ride_duration" class="form-label">Ride Duration (Minutes)</label>
                <input type="number" id="ride_duration" name="ride_duration" class="form-control" value="90" required>
            </div>
            <div class="mb-3">
                <label for="rides_day1" class="form-label">Number of Rides (Day 1)</label>
                <input type="number" id="rides_day1" name="rides_day1" class="form-control" value="7" required>
            </div>
            <div class="mb-3">
                <label for="rides_day2" class="form-label">Number of Rides (Day 2)</label>
                <input type="number" id="rides_day2" name="rides_day2" class="form-control" value="6" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Setup Event</button>
        </form>
    </div>
</body>
</html>