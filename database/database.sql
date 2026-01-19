-- Removed CREATE DATABASE to compatible with Shared Hosting
-- Please select your database in phpMyAdmin before importing

CREATE TABLE IF NOT EXISTS queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_code VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(100),
    status VARCHAR(50) DEFAULT 'ลงทะเบียนแล้ว',
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default Admin User
-- Username: admin
-- Password: @msjbooth2026
INSERT INTO admins (username, password_hash) 
SELECT 'admin', '$2y$10$Gc2O7zIdAhAkppsl1w8B8OIVG2JNNkLMj8vXqvPRgikR2MRKY4ua2'
WHERE NOT EXISTS (SELECT 1 FROM admins WHERE username = 'admin');
