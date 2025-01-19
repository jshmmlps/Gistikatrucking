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
