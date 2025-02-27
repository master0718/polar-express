-- Step 1: Drop volunteer_signups table
DROP TABLE IF EXISTS volunteer_signups;

-- Step 2: Add new columns to volunteers table
ALTER TABLE volunteers ADD COLUMN slot_id INTEGER;
ALTER TABLE volunteers ADD COLUMN num_people INTEGER NOT NULL DEFAULT 1;
ALTER TABLE volunteers ADD COLUMN notes TEXT;

-- Step 3: Seed volunteers table with test data
INSERT INTO volunteers (name, email, phone, password, slot_id, num_people, notes, created_at)
VALUES
('Alice', 'alice@example.com', '555-1234', '$2y$10$hash', 1, 2, 'Excited to help!', '2024-12-22 12:00:00'),
('Bob', 'bob@example.com', '555-5678', '$2y$10$hash', 2, 1, 'Looking forward to it.', '2024-12-22 12:05:00'),
('Charlie', 'charlie@example.com', '555-9999', '$2y$10$hash', 3, 3, 'First-time volunteer.', '2024-12-22 12:10:00'),
('Eve', 'eve@example.com', '555-8888', '$2y$10$hash', 4, 4, 'Can’t wait!', '2024-12-22 12:15:00');
