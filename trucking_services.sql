-- Users accounts
CREATE DATABASE IF NOT EXISTS trucking_services;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    address VARCHAR(255) NOT NULL,
    position VARCHAR(50) NOT NULL,
    user_id VARCHAR(20) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    birthday DATE NOT NULL,
    gender ENUM('Male', 'Female') NOT NULL
);

-- Insert sample data
INSERT INTO users (first_name, last_name, email, contact_number, address, position, user_id, username, birthday, gender)
VALUES
('Jamaeca', 'Quizon', 'jamaecaquizon@gmail.com', '09771002413', 'Malolos City, Bulacan', 'Admin', '202110719', 'QUIZON@123', '2003-04-24', 'Female');

USE trucking_services;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    position VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    address VARCHAR(255) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    role ENUM('Driver', 'Conductor', 'Admin', 'Resource Manager', 'Billing and Collection Officer', 'Operations Coordinator', 'Payroll Officer') NOT NULL
);

-- Sample Data
INSERT INTO users (first_name, last_name, position, email, contact_number, address, username, role)
VALUES
('Yannah', 'Villareal', 'Resource Manager', 'yannah@example.com', '09123456789', 'Makati, Manila', 'yannah123', 'Resource Manager'),
('Sunny', 'Manlapas', 'Billing and Collection Officer', 'sunny@example.com', '09234567890', 'Quezon City, Manila', 'sunny123', 'Billing and Collection Officer'),
('Mark', 'Logmao', 'Operations Coordinator', 'mark@example.com', '09345678901', 'Pasig, Manila', 'mark123', 'Operations Coordinator'),
('Franiel', 'Sarto', 'Payroll Officer', 'franiel@example.com', '09456789012', 'Caloocan, Manila', 'franiel123', 'Payroll Officer'),
('Juan', 'Dela Cruz', 'Driver', 'juan@example.com', '09567890123', 'Manila City', 'juan123', 'Driver'),
('Pedro', 'Dela Cruz', 'Driver', 'pedro@example.com', '09678901234', 'Taguig, Manila', 'pedro123', 'Driver'),
('Lilo', 'Snitch', 'Conductor', 'lilo@example.com', '09789012345', 'Paranaque, Manila', 'lilo123', 'Conductor'),
('Arman', 'Salon', 'Conductor', 'arman@example.com', '09890123456', 'Las Pinas, Manila', 'arman123', 'Conductor');


--Drivers
CREATE TABLE drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    contact_number VARCHAR(15) NOT NULL,
    position ENUM('Driver', 'Conductor') NOT NULL,
    home_address VARCHAR(255) NOT NULL,
    employee_id VARCHAR(20) UNIQUE NOT NULL,
    date_of_employment DATE NOT NULL,
    last_truck_assigned VARCHAR(50) NULL,
    license_number VARCHAR(50) UNIQUE NOT NULL,
    license_expiry_date DATE NOT NULL,
    birthday DATE NOT NULL,
    medical_record TEXT NULL,
    trips_completed INT DEFAULT 0,
    notes TEXT NULL
);

-- Insert sample data
INSERT INTO drivers (first_name, last_name, contact_number, position, home_address, employee_id, date_of_employment, last_truck_assigned, license_number, license_expiry_date, birthday, medical_record, trips_completed, notes)
VALUES 
('Juan', 'Dela Cruz', '09563540192', 'Driver', 'Sampaloc, Manila City', '202212345', '2022-01-15', 'Truck-101', 'DL-987654', '2025-06-30', '1985-04-20', 'No major illnesses', 120, 'Excellent driver with clean record'),
('Pedro', 'Santos', '09123456789', 'Conductor', 'Makati City, NCR', '202345678', '2021-09-10', 'Truck-202', 'DL-123456', '2024-12-15', '1990-08-15', 'Asthma (managed)', 90, 'Punctual and responsible');

--Bookings SQL

-- Create Bookings Table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id VARCHAR(20) UNIQUE NOT NULL,
    client_name VARCHAR(100) NOT NULL,
    booking_date DATE NOT NULL,
    dispatch_date DATE NOT NULL,
    cargo_type VARCHAR(100) NOT NULL,
    cargo_weight VARCHAR(50) NOT NULL,
    drop_off_location VARCHAR(255) NOT NULL,
    contact_number VARCHAR(20) NOT NULL,
    pick_up_location VARCHAR(255) NOT NULL,
    truck_model VARCHAR(100) NOT NULL,
    conductor_name VARCHAR(100) NOT NULL,
    license_plate VARCHAR(50) NOT NULL,
    driver_name VARCHAR(100) NOT NULL,
    distance VARCHAR(50) NOT NULL,
    type_of_truck VARCHAR(100) NOT NULL,
    person_of_contact VARCHAR(100) NOT NULL,
    status ENUM('Pending', 'Approved', 'Completed') NOT NULL DEFAULT 'Pending'
);

-- Insert Sample Data
INSERT INTO bookings (booking_id, client_name, booking_date, dispatch_date, cargo_type, cargo_weight, drop_off_location, contact_number, pick_up_location, truck_model, conductor_name, license_plate, driver_name, distance, type_of_truck, person_of_contact, status)
VALUES 
('000001', 'Fresh Farms Corporation', '2024-11-08', '2024-11-23', 'Fresh Produce', '5035kg', 'Pasay City', '09876554431', 'Marikina City', 'Isuzu F-Series FSR34', 'Pedro Dela Cruz', 'NBD 1234', 'Juan Dela Cruz', '20.4 km', '6-Wheeler', 'Ken Dolores', 'Approved'),
('000002', 'Karen Villanueva', '2024-11-09', '2024-11-18', 'Frozen Goods', '3200kg', 'Pasig City', '09765432123', 'Quezon City', 'Hino 300', 'Jose Dela Rosa', 'ABC 5678', 'Mark Reyes', '15.8 km', '4-Wheeler', 'Ana Santos', 'Completed');

-- Verify Data
SELECT * FROM bookings;
<<<<<<< Updated upstream

=======
>>>>>>> Stashed changes
-- Truck SQL (Improved)
CREATE TABLE trucks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    plate_number VARCHAR(20) NOT NULL UNIQUE,
    engine_number VARCHAR(50) NOT NULL,
    chassis_number VARCHAR(50) NOT NULL,
    color VARCHAR(50),
    certificate_registration VARCHAR(100),
    insurance_details VARCHAR(255),
    license_plate_expiry DATE,
    registration_expiry DATE,
    type VARCHAR(50),
    wheels INT NOT NULL,
    fuel_type VARCHAR(50),
    truck_length VARCHAR(50),
    load_capacity VARCHAR(50),
    maintenance_technician VARCHAR(255),
    status ENUM('Active', 'Maintenance', 'Inactive') DEFAULT 'Active'
);

INSERT INTO trucks (name, plate_number, engine_number, chassis_number, color, certificate_registration, insurance_details, license_plate_expiry, registration_expiry, type, wheels, fuel_type, truck_length, load_capacity, maintenance_technician, status) VALUES
('Isuzu F-Series FSR34', 'NBD 1234', '4HK1-1234567', 'JALF6D1N3S0009876', 'White', 'CR-458320-2023', 'Comprehensive, XYZ Insurance, Policy # 901234567', '2025-12-01', '2025-12-01', '6-Wheeler', 6, 'Diesel', '7.33 meters / 24 feet', '8,000 kg', 'JBC Truck Service Center, Quezon City', 'Active'),
('Isuzu Giga 10-Wheeler', 'ABM 9876', '6WG1-9876543', 'JALF6D1N3S0001234', 'Blue', 'CR-123450-2023', 'Basic, ABC Insurance, Policy # 123456789', '2026-08-15', '2026-08-15', '10-Wheeler', 10, 'Diesel', '10.5 meters / 34 feet', '15,000 kg', 'ABC Truck Maintenance, Manila', 'Active');


-- Maintenance Sql
CREATE TABLE maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    report_number VARCHAR(20) NOT NULL UNIQUE,
    report_type VARCHAR(50) NOT NULL,
    date DATE NOT NULL
);

INSERT INTO maintenance (report_number, report_type, date) VALUES
('R000001', 'Delivery Report', '2024-11-19'),
('R000002', 'Discrepancy Report', '2024-11-17'),
('R000003', 'Discrepancy Report', '2024-11-17'),
('R000004', 'Delivery Report', '2024-11-16'),
('R000005', 'Delivery Report', '2024-11-15'),
('R000006', 'Delivery Report', '2024-11-14'),
('R000007', 'Delivery Report', '2024-11-13'),
('R000008', 'Delivery Report', '2024-11-13'),
('R000009', 'Discrepancy Report', '2024-11-13');

