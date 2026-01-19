-- Car Service Portal Database Schema
-- Run this script to create the database and all tables

-- Create database
CREATE DATABASE IF NOT EXISTS railway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE railway;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'garage_owner', 'admin') NOT NULL DEFAULT 'customer',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Garages table
CREATE TABLE IF NOT EXISTS garages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    description TEXT,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services table
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    garage_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    duration_minutes INT NOT NULL DEFAULT 60,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (garage_id) REFERENCES garages(id) ON DELETE CASCADE,
    INDEX idx_garage (garage_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    make VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    license_plate VARCHAR(20) NOT NULL,
    color VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    UNIQUE KEY unique_license (user_id, license_plate)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    garage_id INT NOT NULL,
    service_id INT NOT NULL,
    vehicle_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (garage_id) REFERENCES garages(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_garage (garage_id),
    INDEX idx_status (status),
    INDEX idx_date (booking_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Data
-- Note: Passwords are hashed using password_hash() - default password for all sample users is "password123"
-- Password hash generated with: password_hash('password123', PASSWORD_DEFAULT)

-- Admin user (password: password123)
INSERT INTO users (name, email, password_hash, role, status) VALUES
('System Admin', 'admin@carservice.com', '$2y$12$FhNw8qW73.6CRUYtwjctgeEvgMvVX.vPMcZkghFQCNpHFgGABusCy', 'admin', 'active');

-- Garage Owner users (password: password123)
INSERT INTO users (name, email, password_hash, role, status) VALUES
('John Garage Owner', 'garage1@example.com', '$2y$12$FhNw8qW73.6CRUYtwjctgeEvgMvVX.vPMcZkghFQCNpHFgGABusCy', 'garage_owner', 'active'),
('Sarah Auto Shop', 'garage2@example.com', '$2y$12$FhNw8qW73.6CRUYtwjctgeEvgMvVX.vPMcZkghFQCNpHFgGABusCy', 'garage_owner', 'active');

-- Customer users (password: password123)
INSERT INTO users (name, email, password_hash, role, status) VALUES
('Alice Customer', 'customer1@example.com', '$2y$12$FhNw8qW73.6CRUYtwjctgeEvgMvVX.vPMcZkghFQCNpHFgGABusCy', 'customer', 'active'),
('Bob Driver', 'customer2@example.com', '$2y$12$FhNw8qW73.6CRUYtwjctgeEvgMvVX.vPMcZkghFQCNpHFgGABusCy', 'customer', 'active');

-- Garages
INSERT INTO garages (owner_user_id, name, address, phone, email, description, status) VALUES
(2, 'John\'s Auto Service', '123 Main Street, City Center, State 12345', '555-0101', 'john@autoservice.com', 'Full-service automotive repair shop specializing in engine diagnostics, oil changes, and brake services. Open Monday-Saturday.', 'active'),
(3, 'Sarah\'s Quick Lube & Tire', '456 Oak Avenue, Downtown, State 12346', '555-0102', 'sarah@quicklube.com', 'Fast and reliable oil changes, tire replacement, and basic maintenance. We get you back on the road quickly!', 'active');

-- Services
INSERT INTO services (garage_id, name, description, price, duration_minutes, status) VALUES
-- John's Auto Service
(1, 'Oil Change', 'Standard oil change with filter replacement', 29.99, 30, 'active'),
(1, 'Full Service', 'Complete vehicle inspection, oil change, filter replacement, fluid top-up', 89.99, 90, 'active'),
(1, 'Brake Service', 'Brake pad replacement and brake fluid check', 149.99, 120, 'active'),
(1, 'Engine Diagnostic', 'Computer diagnostic scan to identify engine issues', 79.99, 60, 'active'),
-- Sarah's Quick Lube & Tire
(2, 'Quick Oil Change', 'Express oil change service', 24.99, 20, 'active'),
(2, 'Tire Replacement', 'Replace all four tires with new ones', 399.99, 60, 'active'),
(2, 'Tire Rotation', 'Rotate tires for even wear', 29.99, 30, 'active'),
(2, 'Battery Replacement', 'Replace car battery with new one', 129.99, 45, 'active');

-- Vehicles
INSERT INTO vehicles (user_id, make, model, year, license_plate, color) VALUES
(4, 'Toyota', 'Camry', 2020, 'ABC-1234', 'Silver'),
(4, 'Honda', 'Civic', 2018, 'XYZ-5678', 'Blue'),
(5, 'Ford', 'F-150', 2021, 'DEF-9012', 'Black');

-- Bookings
INSERT INTO bookings (user_id, garage_id, service_id, vehicle_id, booking_date, booking_time, status, notes) VALUES
(4, 1, 1, 1, DATE_ADD(CURDATE(), INTERVAL 3 DAY), '10:00:00', 'pending', 'First time customer'),
(4, 2, 5, 2, DATE_ADD(CURDATE(), INTERVAL 5 DAY), '14:30:00', 'approved', 'Regular maintenance'),
(5, 1, 3, 3, DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'pending', 'Brakes making noise');
