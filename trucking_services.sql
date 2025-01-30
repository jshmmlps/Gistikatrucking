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

-- Users accounts
CREATE DATABASE IF NOT EXISTS trucking_services;

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




-- Create Database if not exists
CREATE DATABASE IF NOT EXISTS trucking_services;

IF EXISTS USE trucking_services;

-- Use the database
USE trucking_services;

-- Create the drivers table with additional fields
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

