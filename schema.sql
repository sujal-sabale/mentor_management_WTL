-- ===================================================
-- Database: mentor_management
-- Table: mentors
-- ===================================================

CREATE DATABASE IF NOT EXISTS `mentor_management` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `mentor_management`;

-- Drop table if it already exists
DROP TABLE IF EXISTS `mentors`;

-- Create mentors table
CREATE TABLE `mentors` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `employee_id` VARCHAR(50) NOT NULL UNIQUE,
    `department` VARCHAR(100) NOT NULL,
    `designation` VARCHAR(100) NOT NULL,
    `max_mentees` INT NOT NULL,
    `photo_path` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional Initial Seed Data
INSERT INTO `mentors` (`name`, `employee_id`, `department`, `designation`, `max_mentees`, `photo_path`) VALUES
('Dr. Alan Turing', 'EMP101', 'Computer Science', 'Professor', 5, NULL),
('Grace Hopper', 'EMP102', 'Information Technology', 'Associate Professor', 4, NULL);
