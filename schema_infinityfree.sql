-- ===================================================
-- InfinityFree Database Schema with Mentors & Mentees
-- ===================================================

-- Drop tables if they already exist
DROP TABLE IF EXISTS `mentees`;
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

-- Create mentees table
CREATE TABLE `mentees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `student_id` VARCHAR(50) NOT NULL UNIQUE,
    `department` VARCHAR(100) NOT NULL,
    `academic_year` VARCHAR(50) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `mentor_id` INT DEFAULT NULL,
    `photo_path` VARCHAR(255) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_mentor` FOREIGN KEY (`mentor_id`) REFERENCES `mentors`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample Initial Mentors
INSERT INTO `mentors` (`id`, `name`, `employee_id`, `department`, `designation`, `max_mentees`, `photo_path`) VALUES
(1, 'Dr. Alan Turing', 'EMP101', 'Computer Science', 'Professor', 5, NULL),
(2, 'Grace Hopper', 'EMP102', 'Information Technology', 'Associate Professor', 4, NULL),
(3, 'Dr. Claude Shannon', 'EMP103', 'Electronics', 'Professor', 4, NULL);

-- Sample Initial Mentees
INSERT INTO `mentees` (`name`, `student_id`, `department`, `academic_year`, `email`, `phone`, `mentor_id`, `photo_path`) VALUES
('Rohan Patil', 'CS-2026-014', 'Computer Science', 'Third Year (TE)', 'rohan@college.edu', '+91 98234 11223', 1, NULL),
('Sneha Kulkarni', 'CS-2026-029', 'Computer Science', 'Third Year (TE)', 'sneha@college.edu', '+91 97654 33445', 1, NULL),
('Amit Verma', 'IT-2026-008', 'Information Technology', 'Second Year (SE)', 'amit@college.edu', '+91 99112 44556', 2, NULL),
('Priya Nair', 'IT-2026-015', 'Information Technology', 'Second Year (SE)', 'priya@college.edu', '+91 98334 55667', 2, NULL),
('Tanmay Deshmukh', 'CS-2026-045', 'Computer Science', 'First Year (FE)', 'tanmay@college.edu', '+91 94567 88990', NULL, NULL);
