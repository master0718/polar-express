<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_connection.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['volunteer_logged_in']) || !$_SESSION['volunteer_logged_in']) {
    header('Location: volunteer_login.php');
    exit;
}

// Define the role ID for "Elves" (update this ID based on your database)
$role_id = 2;

// Check if the user is logged in
if (!isset($_SESSION['volunteer_logged_in']) || !$_SESSION['volunteer_logged_in']) {
    header('Location: volunteer_login.php');
    exit;
}

// Define the role ID for "Elves"
$role_id = 4;

// Fetch rides and slots
try {
    $stmt = $db->prepare("
        SELECT r.day, r.time, vs.id AS slot_id, vs.max_volunteers,
               COALESCE((SELECT SUM(num_people) FROM volunteer_signups WHERE slot_id = vs.id), 0) AS filled_slots,
               vs.max_volunteers - COALESCE((SELECT SUM(num_people) FROM volunteer_signups WHERE slot_id = vs.id), 0) AS remaining_slots
        FROM rides r
        LEFT JOIN volunteer_slots vs ON r.id = vs.ride_id AND vs.role_id = :role_id
        ORDER BY r.day ASC, STRFTIME('%H:%M', r.time) ASC
    ");
    $stmt->bindValue(':role_id', $role_id, SQLITE3_INTEGER);
    $result = $stmt->execute();

    $rides = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rideStart = strtotime($row['time']);
        $isFirstRide = empty($rides) || end($rides)['day'] !== $row['day'];

        $row['day_formatted'] = DateTime::createFromFormat('Y-m-d', $row['day'])->format('l, m/d/Y');
        $row['shift_start'] = $isFirstRide
            ? date('h:i A', $rideStart - 1800)
            : date('h:i A', $rideStart - 900);
        $row['shift_end'] = $isFirstRide
            ? date('h:i A', $rideStart - 1800 + 6300)
            : date('h:i A', $rideStart - 900 + 5400);
        $row['shift_minutes'] = $isFirstRide ? 105 : 90;

        // Check if the volunteer is signed up for another role on this ride
        $conflictStmt = $db->prepare("
            SELECT vr.role_name AS role_name
            FROM volunteer_signups vs
            JOIN volunteer_slots vslot ON vs.slot_id = vslot.id
            JOIN volunteer_roles vr ON vslot.role_id = vr.id
            WHERE vs.volunteer_id = :volunteer_id 
              AND vslot.ride_id = :ride_id 
              AND vslot.role_id != :current_role_id
        ");
        $conflictStmt->bindValue(':volunteer_id', $_SESSION['volunteer_id'], SQLITE3_INTEGER);
        $conflictStmt->bindValue(':ride_id', $row['slot_id'], SQLITE3_INTEGER);
        $conflictStmt->bindValue(':current_role_id', $role_id, SQLITE3_INTEGER);
        $conflictResult = $conflictStmt->execute();
        $conflict = $conflictResult->fetchArray(SQLITE3_ASSOC);

        if ($conflict) {
            $row['conflict_message'] = "You are already signed up as one of our " . htmlspecialchars($conflict['role_name']) . " on this ride. Please select a different ride.";
        }

        // Get existing signups for this slot
        $slotStmt = $db->prepare("
            SELECT v.name, vs.num_people, vs.notes
            FROM volunteer_signups vs
            JOIN volunteers v ON vs.volunteer_id = v.id
            WHERE vs.slot_id = :slot_id
        ");
        $slotStmt->bindValue(':slot_id', $row['slot_id'], SQLITE3_INTEGER);
        $slotResult = $slotStmt->execute();

        $row['signups'] = [];
        while ($signup = $slotResult->fetchArray(SQLITE3_ASSOC)) {
            $row['signups'][] = $signup;
        }

        $rides[] = $row;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}

// Check if the volunteer has existing signups
$volunteerId = $_SESSION['volunteer_id'];
$stmt = $db->prepare("SELECT * FROM volunteer_signups WHERE volunteer_id = :volunteerId");
$stmt->bindValue(':volunteerId', $volunteerId, SQLITE3_INTEGER);
$existingSelections = [];
$result = $stmt->execute();
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $existingSelections[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Elves Signup</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; margin: 0; padding: 0; }
        .container { width: 90%; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        .day-saturday { background-color: #d4edda; text-align: center; }
        .day-sunday { background-color: #ffe5b4; text-align: center; }
        .full { background-color: lightgray; pointer-events: none; }
        .comments { font-style: italic; font-size: 0.85em; color: gray; }
        .header-row { display: flex; justify-content: space-between; align-items: center; height: 80px; }
        .header-row .left { flex: 1; text-align: left; }
        .header-row .right { flex: 1; text-align: right; }
        #total-commitment { font-weight: bold; display: none; margin-bottom: 10px; }
        #submit-button, #edit-button { background-color: blue; color: white; border: none; padding: 10px; cursor: pointer; display: none; margin-top: 10px; }
        #submit-button:disabled, #edit-button:disabled { background-color: gray; }
    </style>
    <script>
        let totalMinutes = 0;
        const selectedRides = new Set();
        let hasShownPopup = false;

        function toggleSelection(slotId, minutes) {
            const checkbox = document.getElementById(`checkbox-${slotId}`);
            const label = document.getElementById(`label-${slotId}`);
            const dropdown = document.getElementById(`party-size-${slotId}`);
            const dropdownText = document.getElementById(`dropdown-text-${slotId}`);
            const notes = document.getElementById(`notes-${slotId}`);

            if (checkbox.checked) {
                totalMinutes += parseInt(minutes, 10);
                selectedRides.add(slotId);

                label.style.display = 'none';
                dropdown.style.display = 'inline-block';
                dropdownText.style.display = 'inline-block';
                notes.style.display = 'block';
            } else {
                totalMinutes -= parseInt(minutes, 10);
                selectedRides.delete(slotId);

                label.style.display = 'inline-block';
                dropdown.style.display = 'none';
                dropdownText.style.display = 'none';
                notes.style.display = 'none';
            }

            const totalHours = (totalMinutes / 60).toFixed(2);
            const commitment = document.getElementById('total-commitment');
            const submitButton = document.getElementById('submit-button');

            if (totalMinutes > 0) {
                commitment.textContent = `Total Time Commitment: ${totalHours} hours`;
                commitment.style.display = 'block';
                submitButton.style.display = 'inline-block';

                if (totalMinutes > 360 && !hasShownPopup) {
                    alert(`You have committed to ${totalHours} hours. Please confirm that you meant to volunteer so much of your time.`);
                    hasShownPopup = true;
                }
            } else {
                commitment.style.display = 'none';
                submitButton.style.display = 'none';
                hasShownPopup = false;
            }
        }

        function handleSubmitSelections() {
            if (selectedRides.size === 0) {
                alert('Please select at least one ride.');
                return;
            }

            const volunteerId = <?= json_encode($_SESSION['volunteer_id']) ?>;
            const data = Array.from(selectedRides).map(slotId => ({
                volunteerId,
                slotId,
                partySize: document.getElementById(`party-size-${slotId}`).value,
                notes: document.getElementById(`notes-${slotId}`).value,
            }));

            fetch('submit_selections.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Selections submitted successfully!');
                        location.reload();
                    } else {
                        alert(`Error: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Submission failed:', error);
                    alert('An error occurred. Please try again.');
                });
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header-row">
            <div class="left"><h1>Elves Signup</h1></div>
            <div class="right">
                <div id="total-commitment"></div>
                <?php if (!empty($existingSelections)): ?>
                    <button id="edit-button" onclick="loadExistingSelections()">Edit My Selections</button>
                <?php endif; ?>
                <button id="submit-button" onclick="handleSubmitSelections()">Submit Selections</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Shift Start</th>
                    <th>Shift End</th>
                    <th>Max Slots</th>
                    <th>Filled Slots</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $lastDay = '';
                foreach ($rides as $ride):
                    $isFull = $ride['remaining_slots'] <= 0;
                    $dayClass = strpos($ride['day_formatted'], 'Saturday') !== false ? 'day-saturday' : 'day-sunday';

                    if ($lastDay !== $ride['day_formatted']): ?>
                        <tr class="table-header <?= $dayClass ?>">
                            <td colspan="6"><?= htmlspecialchars($ride['day_formatted']) ?></td>
                        </tr>
                    <?php
                        $lastDay = $ride['day_formatted'];
                    endif;
                ?>
                <tr class="<?= $isFull ? 'full' : $dayClass ?>">
                    <td><?= htmlspecialchars($ride['day_formatted']) ?></td>
                    <td><?= htmlspecialchars($ride['shift_start']) ?></td>
                    <td><?= htmlspecialchars($ride['shift_end']) ?></td>
                    <td><?= htmlspecialchars($ride['max_volunteers']) ?></td>
                    <td><?= htmlspecialchars($ride['filled_slots']) ?></td>
                    <td>
                        <?php if (!$isFull && empty($ride['conflict_message'])): ?>
                            <input type="checkbox" id="checkbox-<?= $ride['slot_id'] ?>" 
                                   onchange="toggleSelection(<?= $ride['slot_id'] ?>, <?= $ride['shift_minutes'] ?>)">
                            <label id="label-<?= $ride['slot_id'] ?>">Check Box to Select</label>
                            <select id="party-size-<?= $ride['slot_id'] ?>" style="display: none; margin-right: 5px;">
                                <?php for ($i = 1; $i <= $ride['remaining_slots']; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?></option>
                                <?php endfor; ?>
                            </select>
                            <span id="dropdown-text-<?= $ride['slot_id'] ?>" style="display: none;">How many in your group</span>
                            <textarea id="notes-<?= $ride['slot_id'] ?>" placeholder="Add notes" style="display: none;"></textarea>
                        <?php elseif (!empty($ride['conflict_message'])): ?>
                            <div style="color: red;"><?= htmlspecialchars($ride['conflict_message']) ?></div>
                        <?php else: ?>
                            <div>FULL</div>
                        <?php endif; ?>
                        <div id="volunteer-display-<?= $ride['slot_id'] ?>">
                            <?php foreach ($ride['signups'] as $signup): ?>
                                <div>
                                    <?= htmlspecialchars($signup['name']) ?> (<?= htmlspecialchars($signup['num_people']) ?>)
                                    <div class="comments"><?= htmlspecialchars($signup['notes']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>