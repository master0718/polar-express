<?php
try {
    $db = new SQLite3('admin_users.db');

    $db->exec("
        INSERT OR IGNORE INTO ride_visibility (ride_id, role_id, visible) VALUES
        (1, 1, 1), -- Jolly People, Ride 1
        (1, 2, 1), -- Elves, Ride 1
        (1, 3, 1), -- Chefs, Ride 1
        (1, 4, 1), -- Conductors, Ride 1
        (2, 1, 1), -- Jolly People, Ride 2
        (2, 2, 1), -- Elves, Ride 2
        (2, 3, 1), -- Chefs, Ride 2
        (2, 4, 1), -- Conductors, Ride 2
        ...
    ");

    echo "Ride visibility repopulated successfully!";
    $db->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
