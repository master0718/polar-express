<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $db = new SQLite3('admin_users.db');

    echo "Clearing all volunteer data...\n";

    // Drop and recreate the volunteers table
    $db->exec("DROP TABLE IF EXISTS volunteer_signups");
    $db->exec("DROP TABLE IF EXISTS volunteers");
    $db->exec("DROP TABLE IF EXISTS volunteer_slots");

    $db->exec("
        CREATE TABLE IF NOT EXISTS volunteers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            phone TEXT,
            password TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            slot_id INTEGER,
            num_people INTEGER DEFAULT 1,
            notes TEXT
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS volunteer_signups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            volunteer_id INTEGER NOT NULL,
            slot_id INTEGER NOT NULL,
            num_people INTEGER NOT NULL DEFAULT 1,
            notes TEXT,
            created_at TEXT DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (volunteer_id) REFERENCES volunteers (id),
            FOREIGN KEY (slot_id) REFERENCES volunteer_slots (id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS volunteer_slots (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ride_id INTEGER NOT NULL,
            role_id INTEGER NOT NULL,
            max_volunteers INTEGER NOT NULL,
            FOREIGN KEY (ride_id) REFERENCES rides (id),
            FOREIGN KEY (role_id) REFERENCES volunteer_roles (id)
        )
    ");

    echo "All volunteer data and tables have been cleared and recreated successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
