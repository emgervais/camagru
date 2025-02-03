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
    verification_token VARCHAR(255) DEFAULT NULL,
    password_reset_token VARCHAR(255) DEFAULT NULL,
    notification BOOLEAN NOT NULL DEFAULT 1
);
INSERT INTO users (username, email, password, is_verified) VALUES ('a', 'emilegervais@hotmail.fr', '123', 1);
CREATE TABLE if not exists posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    likes INT DEFAULT 0
);
CREATE table if not exists comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    comment TEXT NOT NULL,
    creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
INSERT INTO posts (user_id, image_path, likes) VALUES (1, 'img/cat.jpg', 10);
INSERT INTO posts (user_id, image_path) VALUES (1, 'img/egervais.jpg');