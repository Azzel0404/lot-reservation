INSERT INTO user (email, password, role, phone, address) VALUES 
( 'admin@example.com', 'admin123', 'ADMIN', '09171234567', 'Admin Address' );

INSERT INTO payment (payment_method) VALUES ('Cash'), ('Credit');

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
    aerial_image VARCHAR(255) AFTER status,
    numbered_image VARCHAR(255) AFTER aerial_image,
    pdf_file VARCHAR(255) AFTER numbered_image;
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

DELIMITER //

CREATE PROCEDURE sp_calculate_agent_commission_dynamic (
    IN p_agent_id INT,
    IN p_reservation_id INT,
    IN p_commission_percent DECIMAL(5,2) -- e.g., 3.00 for 3%
)
BEGIN
    DECLARE v_commission_fee DECIMAL(12,2);
    DECLARE v_lot_price DECIMAL(12,2);
    DECLARE existing_count INT;

    -- Get the lot price linked to the reservation
    SELECT l.price INTO v_lot_price
    FROM reservation r
    JOIN lot l ON r.lot_id = l.lot_id
    WHERE r.reservation_id = p_reservation_id;

    -- Calculate commission fee
    SET v_commission_fee = v_lot_price * (p_commission_percent / 100);

    -- Check if a commission record already exists
    SELECT COUNT(*) INTO existing_count
    FROM agent_commission
    WHERE agent_id = p_agent_id AND reservation_id = p_reservation_id;

    IF existing_count > 0 THEN
        -- Update existing commission
        UPDATE agent_commission
        SET commission_fee = v_commission_fee
        WHERE agent_id = p_agent_id AND reservation_id = p_reservation_id;
    ELSE
        -- Insert new commission
        INSERT INTO agent_commission (agent_id, reservation_id, commission_fee)
        VALUES (p_agent_id, p_reservation_id, v_commission_fee);
    END IF;
END //

DELIMITER ;


DELIMITER //

CREATE PROCEDURE sp_calculate_reservation_fee (
    IN p_lot_id INT,
    OUT p_reservation_fee DECIMAL(12,2)
)
BEGIN
    DECLARE v_lot_price DECIMAL(12,2);

    -- Get the lot price linked to the given lot ID
    SELECT price INTO v_lot_price
    FROM lot
    WHERE lot_id = p_lot_id;

    -- Calculate the reservation fee as 10% of the lot price
    SET p_reservation_fee = v_lot_price * 0.10;
END //

DELIMITER ;
