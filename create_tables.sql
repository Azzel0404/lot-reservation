INSERT INTO user (email, password, role, phone, address) VALUES 
( 'admin@example.com', 'admin123', 'ADMIN', '09171234567', 'Admin Address' );

CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL CHECK (role IN ('ADMIN', 'AGENT', 'CLIENT')),
    phone VARCHAR(20) NOT NULL UNIQUE,
    address VARCHAR(255)
);

CREATE TABLE agent (
    agent_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    middlename VARCHAR(50),
    license_number VARCHAR(50) NOT NULL UNIQUE,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE
);

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

CREATE TABLE lot (
    lot_id INT AUTO_INCREMENT PRIMARY KEY,
    lot_number VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    size_meter_square DECIMAL(10,2) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    status VARCHAR(10) NOT NULL CHECK (status IN ('Available', 'Reserved'))
);

CREATE TABLE payment (
    payment_id INT AUTO_INCREMENT PRIMARY KEY,
    payment_method VARCHAR(10) NOT NULL CHECK (payment_method IN ('Cash', 'Credit'))
);

CREATE TABLE reservation (
    reservation_id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    lot_id INT NOT NULL,
    payment_id INT NOT NULL,
    reservation_fee DECIMAL(12,2) NOT NULL,
    status VARCHAR(10) NOT NULL CHECK (status IN ('Approved', 'Expired')),
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