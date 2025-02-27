-- Create rides table
CREATE TABLE rides (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    day TEXT NOT NULL,
    time TEXT NOT NULL
);

-- Create volunteer roles table
CREATE TABLE volunteer_roles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    role_name TEXT NOT NULL
);

-- Create volunteer slots table
CREATE TABLE volunteer_slots (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ride_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    max_volunteers INTEGER NOT NULL,
    FOREIGN KEY (ride_id) REFERENCES rides (id),
    FOREIGN KEY (role_id) REFERENCES volunteer_roles (id)
);

-- Create ride visibility table
CREATE TABLE ride_visibility (
    ride_id INTEGER NOT NULL,
    role_id INTEGER NOT NULL,
    visible INTEGER NOT NULL,
    PRIMARY KEY (ride_id, role_id),
    FOREIGN KEY (ride_id) REFERENCES rides (id),
    FOREIGN KEY (role_id) REFERENCES volunteer_roles (id)
);

-- Create volunteer signups table
CREATE TABLE volunteer_signups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    slot_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    email TEXT NOT NULL,
    phone TEXT,
    num_people INTEGER NOT NULL,
    notes TEXT,
    FOREIGN KEY (slot_id) REFERENCES volunteer_slots (id)
);
