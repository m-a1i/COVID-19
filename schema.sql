-- =========================================================================
-- DATABASE: ors_covid_db
-- PURPOSE: Relational data mapping for Patients, Hospitals, and Vaccine inventories.
-- ARCHITECTURE: InnoDB Engine optimized for low-footprint relational tracking.
-- =========================================================================

CREATE DATABASE IF NOT EXISTS ors_covid_db;
USE ors_covid_db;

-- 1. Patient Master Records (The Dark & Twisty User Base)
CREATE TABLE IF NOT EXISTS patient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mobile_number VARCHAR(15) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    physical_address TEXT NOT NULL,
    location_details VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(mobile_number)
) ENGINE=InnoDB;

-- 2. Hospital Master Nodes (The Bright & Shiny Clinical Interfaces)
CREATE TABLE IF NOT EXISTS hospital (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_name VARCHAR(150) NOT NULL,
    address TEXT NOT NULL,
    location_details VARCHAR(100) NOT NULL,
    functional_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. Central Vaccine Stock Inventory Matrix
CREATE TABLE IF NOT EXISTS vaccine_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vaccine_name VARCHAR(100) NOT NULL UNIQUE,
    current_availability TINYINT(1) NOT NULL DEFAULT 1 -- 1 = Available, 0 = Unavailable
) ENGINE=InnoDB;

-- 4. Central Appointment and Lifecycle Registry
CREATE TABLE IF NOT EXISTS appointment_registry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    hospital_id INT NOT NULL,
    appointment_type ENUM('TEST', 'VACCINATION') NOT NULL,
    scheduled_date DATE NOT NULL,
    request_status ENUM('PENDING', 'APPROVED', 'REJECTED') DEFAULT 'PENDING',
    execution_result VARCHAR(100) DEFAULT 'PENDING LAB UPDATE',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient(id) ON DELETE CASCADE,
    FOREIGN KEY (hospital_id) REFERENCES hospital(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Seed Default Essential Administrative Stocks
INSERT INTO vaccine_inventory (vaccine_name, current_availability) VALUES 
('Covaxin B-11', 1),
('Sputnik-V Alpha', 1),
('AstraZeneca Shield', 0)
ON DUPLICATE KEY UPDATE current_availability=current_availability;
