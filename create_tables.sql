-- 1. USER TABLE
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Hash passwords before inserting
    role VARCHAR(10) NOT NULL CHECK (role IN ('ADMIN', 'AGENT', 'CLIENT')),
    phone VARCHAR(20) NOT NULL UNIQUE,
    address VARCHAR(255)
);

-- 2. AGENT TABLE
CREATE TABLE agent (
    agent_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    middlename VARCHAR(50),
    license_number VARCHAR(50) NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

-- 3. CLIENT TABLE
CREATE TABLE client (
    client_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    agent_id INT,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    middlename VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (agent_id) REFERENCES agent(agent_id) ON DELETE SET NULL
);

-- 4. MAP TABLE (renamed from lot_batch)
CREATE TABLE map (
    map_id INT AUTO_INCREMENT PRIMARY KEY,
    map_number VARCHAR(50) NOT NULL UNIQUE, 
    location VARCHAR(255) NOT NULL,
    map_layout VARCHAR(255), -- Path to the map layout image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. LOT TABLE (updated reference from batch_id → map_id)
CREATE TABLE lot (
    lot_id INT AUTO_INCREMENT PRIMARY KEY,
    map_id INT NOT NULL, -- Links to map
    segment_number INT NOT NULL, -- Example: Lot 1, Lot 2, Lot 3
    size_meter_square DECIMAL(10,2) NOT NULL, -- Size in sqm
    price DECIMAL(12,2) NOT NULL,
    status ENUM('Available', 'Reserved', 'Sold') NOT NULL DEFAULT 'Available',
    lot_image VARCHAR(255),
    FOREIGN KEY (map_id) REFERENCES map(map_id) ON DELETE CASCADE
);

-- 6. PAYMENT TABLE
CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_method ENUM('Cash', 'Credit') NOT NULL -- Payment types
);

-- 7. RESERVATION TABLE
CREATE TABLE reservation (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    lot_id INT NOT NULL,
    payment_id INT NOT NULL,
    reservation_fee DECIMAL(12,2) NOT NULL,
    status ENUM('Approved', 'Expired') NOT NULL,
    reservation_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_approved DATETIME,
    expiry_date DATETIME,
    request_form LONGBLOB, 
    signed_form LONGBLOB, 
    FOREIGN KEY (client_id) REFERENCES client(client_id) ON DELETE CASCADE,
    FOREIGN KEY (lot_id) REFERENCES lot(lot_id) ON DELETE CASCADE,
    FOREIGN KEY (payment_id) REFERENCES payment(payment_id) ON DELETE CASCADE
);


CREATE TABLE agent_commission (
    agent_id INT NOT NULL,
    reservation_id INT NOT NULL,
    commission_fee DECIMAL(12,2) NOT NULL,
    PRIMARY KEY (agent_id, reservation_id),
    FOREIGN KEY (agent_id) REFERENCES agent(agent_id) ON DELETE CASCADE,
    FOREIGN KEY (reservation_id) REFERENCES reservation(reservation_id) ON DELETE CASCADE
);

INSERT INTO user (email, password, role, phone, address) VALUES 
('admin@example.com', 'admin123(must be hashed first)', 'ADMIN', '09171234567', 'Admin Address');
