<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'get_valid_shifts.php';
require 'db_connection.php';
session_start();

// Custom error handler for runtime issues
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "Error [$errno]: $errstr in $errfile on line $errline<br>";
    return true;
});

set_exception_handler(function ($exception) {
    echo "Uncaught Exception: " . $exception->getMessage() . "<br>";
});


// Redirect if the user is not logged in
if (!isset($_SESSION['volunteer_id']) || empty($_SESSION['volunteer_id'])) {
    echo "<script>alert('You have not yet signed up for any rides - Nothing to Edit'); window.location.href = 'role_selection.php';</script>";
    exit;
}

$volunteerId = intval($_SESSION['volunteer_id']);
// echo "Session volunteer_id: " . htmlspecialchars($volunteerId) . "<br>"; // Debug output

try {
    // Confirm the database connection
    if (!$db) {
        throw new Exception("Failed to connect to the database.");
    }

    // Fetch all rides
    // echo "Preparing to fetch rides...<br>";
    $rideStmt = $db->prepare("SELECT id, day, time FROM rides ORDER BY day ASC, STRFTIME('%H:%M', time) ASC");
    if (!$rideStmt) {
        throw new Exception("Failed to prepare ride statement: " . $db->lastErrorMsg());
    }

    $rideResult = $rideStmt->execute();
    if (!$rideResult) {
        throw new Exception("Failed to execute ride statement: " . $db->lastErrorMsg());
    }

    // echo "Ride query executed successfully.<br>";

    $rideShifts = [];
    $lastDay = null;

    while ($ride = $rideResult->fetchArray(SQLITE3_ASSOC)) {
        if (!$ride) {
            echo "No rides found.<br>";
            continue;
        }

        $rideStart = strtotime($ride['time']);
        $isFirstRide = $lastDay !== $ride['day'];

        // echo "Processing ride on {$ride['day']} at {$ride['time']}...<br>";
        // echo $isFirstRide ? "Applying FIRST ride formula.<br>" : "Applying SUBSEQUENT ride formula.<br>";

        // First ride formula
        if ($isFirstRide) {
            $shiftStart = $rideStart - 1800;  // 30 minutes before departure
            $shiftEnd = $shiftStart + 6300;   // 105 minutes shift
        } else {
            // Subsequent rides formula
            $shiftStart = $rideStart - 900;   // 15 minutes before departure
            $shiftEnd = $shiftStart + 5400;   // 90 minutes shift
        }

        // echo "Shift Start: " . date('h:i A', $shiftStart) . "<br>";
        // echo "Shift End: " . date('h:i A', $shiftEnd) . "<br><br>";

        $rideShifts[$ride['id']] = [
            'day_abbr' => DateTime::createFromFormat('Y-m-d', $ride['day'])->format('D'),
            'shift_start' => date('h:i A', $shiftStart),
            'shift_end' => date('h:i A', $shiftEnd),
        ];

        $lastDay = $ride['day'];
    }

    // echo "Finished processing all rides.<br>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    exit;
}

// Fetch available roles for the dropdown
$roleStmt = $db->prepare("SELECT id, role_name FROM volunteer_roles");
$roleResult = $roleStmt->execute();

$roles = [];
while ($roleRow = $roleResult->fetchArray(SQLITE3_ASSOC)) {
    $roles[] = $roleRow;
}
$stmt = $db->prepare("
    SELECT r.id AS ride_id, r.day, r.time, vr.role_name, vs.num_people, vs.notes, vs.id AS signup_id, vslot.id AS slot_id, vslot.role_id
    FROM volunteer_signups vs
    JOIN volunteer_slots vslot ON vs.slot_id = vslot.id
    JOIN rides r ON vslot.ride_id = r.id
    JOIN volunteer_roles vr ON vslot.role_id = vr.id
    WHERE vs.volunteer_id = :volunteerId
    ORDER BY r.day ASC, STRFTIME('%H:%M', r.time) ASC
");

$days = $db->prepare("SELECT DISTINCT day FROM rides ORDER BY day ASC");
$daysResult = $days->execute();

$availableDays = [];
while ($dayRow = $daysResult->fetchArray(SQLITE3_ASSOC)) {
    $date = DateTime::createFromFormat('Y-m-d', $dayRow['day']);
    $availableDays[] = [
        'day' => $dayRow['day'],
        'day_abbr' => $date->format('D'),
        'formatted_date' => $date->format('m-d-Y')
    ];
}

$shifts = $db->prepare("SELECT DISTINCT time FROM rides ORDER BY time ASC");
$shiftsResult = $shifts->execute();
$availableShifts = [];
while ($shiftRow = $shiftsResult->fetchArray(SQLITE3_ASSOC)) {
    $availableShifts[] = [
        'time' => $shiftRow['time'],
        'formatted_time' => date('h:i A', strtotime($shiftRow['time'])),
        'formatted_time_end' => date('h:i A', strtotime($shiftRow['time']) + 105 * 60)
    ];
}

$shifts = $db->prepare("
    SELECT DISTINCT time 
    FROM rides 
    WHERE day = :day 
    ORDER BY time ASC
");
$availableShifts = [];
// Function to get shifts for a specific day
function getShiftsForDay($db, $stmt, $day)
{
    $stmt->bindValue(':day', $day, SQLITE3_TEXT);
    $shiftsResult = $stmt->execute();
    $shifts = [];
    while ($shiftRow = $shiftsResult->fetchArray(SQLITE3_ASSOC)) {
        $shifts[] = [
            'time' => $shiftRow['time'],
            'formatted_time' => date('h:i A', strtotime($shiftRow['time'])),
            'formatted_time_end' => date('h:i A', strtotime($shiftRow['time']) + 105 * 60)
        ];
    }
    return $shifts;
}

if (!$stmt) {
    throw new Exception("Failed to prepare volunteer query: " . $db->lastErrorMsg());
}

$stmt->bindValue(':volunteerId', $volunteerId, SQLITE3_INTEGER);
$result = $stmt->execute();

if (!$result) {
    throw new Exception("Failed to execute volunteer query: " . $db->lastErrorMsg());
}

// echo "Volunteer query executed successfully.<br>";


while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    if (!$row) {
        echo "No rows fetched.<br>";
        continue;
    }



    $rideId = $row['ride_id'] ?? null;
    $rideDay = $row['day'] ?? null;

    if (isset($rideShifts[$rideId])) {
        $row['day_abbr'] = $rideShifts[$rideId]['day_abbr'];
        $row['shift_start'] = $rideShifts[$rideId]['shift_start'];
        $row['shift_end'] = $rideShifts[$rideId]['shift_end'];
        $row['shift'] = $row['shift_start'] . ' to ' . $rideShifts[$rideId]['shift_end'];
    } else {
        $row['day_abbr'] = 'N/A';
        $row['shift_start'] = 'N/A';
        $row['shift_end'] = 'N/A';
        $row['shift'] = 'N/A';
    }

    // Validate the shift options
    $groupSize = $row['num_people'] ?? 0;
    $roleId = $row['role_id'] ?? 0;
    $row['shift_options'] = getValidShifts($rideId, $roleId, $groupSize) ?? [];
    // Debug the shift options
    $rides[] = $row;

    // Debug the current row
}
// Debug the final rides array
// echo '<pre>';
// echo "Final rides array:<br>";
// echo '</pre>';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit My Selections</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #d4edda;
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .header-row .left {
            flex: 1;
            text-align: left;
        }

        .header-row .right {
            flex: 1;
            text-align: right;
        }

        button {
            background-color: blue;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:disabled {
            background-color: gray;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header-row">
            <div class="left mt-3">
                <h1 class="">Edit My Selections</h1>
            </div>
            <div class="right">
                <button onclick="window.location.href='role_selection.php'">Return to Role Selection</button>
                <button id="save-button" onclick="saveChanges()" disabled>Save These Changes</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Shift</th>
                    <th>Role</th>
                    <th>Group Size</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rides as $ride): ?>

                    <!-- Debugging Block -->
                    <tr>
                        <td>
                            <select onchange="enableSaveButton()">
                                <?php foreach ($availableDays as $day): ?>
                                    <option value="<?= htmlspecialchars($day['day']) ?>"
                                        <?= ($day['day'] === $ride['day']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars("{$day['day_abbr']} {$day['formatted_date']}")
                                        ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select class="shift-select" data-day="<?= htmlspecialchars($ride['day']) ?>" onchange="enableSaveButton()">
                                <?php
                                $dayShifts = getShiftsForDay($db, $shifts, $ride['day']);
                                foreach ($dayShifts as $shift):
                                ?>
                                    <option value="<?= htmlspecialchars($shift['time']) ?>"
                                        <?= ($shift['time'] === $ride['time']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($shift['formatted_time']) . " to " . htmlspecialchars($shift['formatted_time_end']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <select onchange="handleRoleChange(this); enableSaveButton()">
                                <option value="<?= htmlspecialchars($ride['role_id']) ?>" selected>
                                    <?= htmlspecialchars($ride['role_name']) ?>
                                </option>
                                <?php foreach ($roles as $role): ?>
                                    <?php if ($role['id'] != $ride['role_id']): ?>
                                        <option value="<?= htmlspecialchars($role['id']) ?>">
                                            <?= htmlspecialchars($role['role_name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <input type="number" value="<?= htmlspecialchars($ride['num_people']) ?>" onchange="enableSaveButton()">
                        </td>
                        <td>
                            <textarea><?= htmlspecialchars($ride['notes']) ?></textarea>
                        </td>
                        <td>
                            <button onclick="deleteSignup(<?= $ride['signup_id'] ?>)">Delete This Ride</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button onclick="removeAll()">Remove Me From ALL Rides</button>
        <button onclick="printSchedule()">Print My Schedule</button>
    </div>
    <script>
        function enableSaveButton() {
            document.getElementById('save-button').disabled = false;
        }

        function updateShiftOptions(daySelect) {
            const row = daySelect.closest('tr');
            const shiftSelect = row.querySelector('.shift-select');
            const selectedDay = daySelect.value;

            fetch(`get_shifts.php?day=${selectedDay}`)
                .then(response => response.json())
                .then(shifts => {
                    shiftSelect.innerHTML = shifts.map(shift =>
                        `<option value="${shift.time}" ${shift.time === shiftSelect.value ? 'selected' : ''}>
                    ${shift.formatted_time} to ${shift.formatted_time_end}
                </option>`
                    ).join('');
                })
                .catch(error => console.error('Error:', error));
        }

        document.addEventListener('DOMContentLoaded', () => {
            const dropdowns = document.querySelectorAll('select');
            const textareas = document.querySelectorAll('textarea');
            const numberInputs = document.querySelectorAll('input[type="number"]');

            dropdowns.forEach(dropdown => dropdown.addEventListener('change', enableSaveButton));
            textareas.forEach(textarea => textarea.addEventListener('input', enableSaveButton));
            numberInputs.forEach(input => input.addEventListener('input', enableSaveButton));
        });

        function handleRoleChange(selectElement) {
            console.log(selectElement.value);
        }

        function saveChanges() {
            const rows = document.querySelectorAll("tbody tr");
            console.log(rows)
            const updates = [];

            rows.forEach(row => {
                const shiftSelect = row.querySelector("td:nth-of-type(2) select");
                const roleSelect = row.querySelector("td:nth-of-type(3) select");
                const groupSizeInput = row.querySelector("input[type='number']");
                const notesTextarea = row.querySelector("textarea");
                const signupId = row.querySelector("button[onclick]").getAttribute("onclick").match(/\d+/)[0];

                updates.push({
                    signup_id: signupId,
                    slot_id: shiftSelect.value,
                    role_id: roleSelect.value,
                    num_people: groupSizeInput.value,
                    notes: notesTextarea.value
                });
            });

            fetch('update_signups.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        updates: updates
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    alert("An unexpected error occurred.");
                });
        }
    </script>
</body>

</html>