CREATE DATABASE IF NOT EXISTS camagru;
CREATE USER IF NOT EXISTS 'admin'@'%' IDENTIFIED BY 'pass123';
GRANT ALL PRIVILEGES ON camagru.* TO 'admin'@'%';
FLUSH PRIVILEGES;
USE camagru;
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_verified BOOLEAN NOT NULL DEFAULT 0,
    verification_token VARCHAR(255),
    password_reset_token VARCHAR(255),
    token VARCHAR(255),
    notification BOOLEAN NOT NULL DEFAULT 1
);