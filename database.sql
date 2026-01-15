CREATE DATABASE IF NOT EXISTS photobooth_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE photobooth_db;

CREATE TABLE IF NOT EXISTS queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(100),
    status ENUM('Registered', 'Shooting', 'Print', 'Completed') DEFAULT 'Registered',
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
