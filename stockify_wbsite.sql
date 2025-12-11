-- Create the database
CREATE DATABASE IF NOT EXISTS stockify_website;

-- Use the database
USE stockify_website;

-- Create the users table
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create the admins table
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert the default admin user
-- The password 'admin123' is hashed
INSERT INTO `admins` (`username`, `password`) VALUES
('admin', '$2y$10$Ie.yM/eE1ZfL5wX.KxO/ge2Y9g2bXV8gH0xV4w.m8E5un.3vKOg0i');

-- Create the orders table
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `requested_version` enum('Standard Plan','Premium Plan') NOT NULL,
  `note` text,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `confirmed_version` enum('Standard Plan','Premium Plan') DEFAULT NULL,
  `order_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;