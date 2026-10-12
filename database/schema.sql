-- =======================================================
-- Software Testing & Quality Assurance (STQA) Mini Project
-- Project: COVID-19 Dynamic Information & Registration Portal
-- Database Schema: covid_stqa_db
-- =======================================================

CREATE DATABASE IF NOT EXISTS `covid_stqa_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `covid_stqa_db`;

-- 1. Users Table (Account management)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(120) NOT NULL UNIQUE,
    `phone` VARCHAR(15) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Registrations Table (Vaccine & RT-PCR Booking)
CREATE TABLE IF NOT EXISTS `registrations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `patient_name` VARCHAR(100) NOT NULL,
    `age` INT NOT NULL,
    `gender` ENUM('Male', 'Female', 'Other') NOT NULL,
    `id_type` ENUM('Aadhaar', 'Passport', 'Voter ID', 'Driving License') NOT NULL,
    `id_number` VARCHAR(50) NOT NULL,
    `mobile` VARCHAR(15) NOT NULL,
    `email` VARCHAR(120) NOT NULL,
    `pincode` VARCHAR(10) NOT NULL,
    `dose_type` ENUM('Dose 1 (Covishield/Covaxin)', 'Dose 2 (Covishield/Covaxin)', 'Precaution / Booster Dose', 'RT-PCR Test (Diagnostic)') NOT NULL,
    `preferred_date` DATE NOT NULL,
    `time_slot` VARCHAR(30) NOT NULL,
    `status` ENUM('Confirmed', 'Pending', 'Completed', 'Cancelled') DEFAULT 'Confirmed',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Comments & Community Forum Table
CREATE TABLE IF NOT EXISTS `comments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `author_name` VARCHAR(100) NOT NULL,
    `author_email` VARCHAR(120) NOT NULL,
    `message` TEXT NOT NULL,
    `rating` TINYINT DEFAULT 5,
    `status` ENUM('approved', 'pending') DEFAULT 'approved',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Initial Seed Data for Demo & Evaluation

-- Insert an Admin and a standard Demo User
-- Passwords below are hashed for 'Admin@123' and 'User@1234' respectively using password_hash(PASSWORD_BCRYPT)
INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password_hash`, `role`) VALUES
(1, 'System Administrator', 'admin@covidportal.gov.in', '9876543210', '$2y$10$y6qU5YF/WfAek2v8yVpWteYIffpfxoE4s67.pE4zZgQ6W.9QhF74e', 'admin'),
(2, 'Aarav Sharma', 'aarav.sharma@example.com', '9820123456', '$2y$10$E5z4gL7rGk0oN7nF/yB.E.fM0y6eH3fE8pG4nB9rK2wL5mQ8uT6rS', 'user')
ON DUPLICATE KEY UPDATE `email`=`email`;

-- Insert initial sample registrations
INSERT INTO `registrations` (`id`, `user_id`, `patient_name`, `age`, `gender`, `id_type`, `id_number`, `mobile`, `email`, `pincode`, `dose_type`, `preferred_date`, `time_slot`, `status`) VALUES
(1, 2, 'Aarav Sharma', 28, 'Male', 'Aadhaar', '2345 6789 0123', '9820123456', 'aarav.sharma@example.com', '400001', 'Dose 1 (Covishield/Covaxin)', '2026-10-20', '10:00 AM - 11:30 AM', 'Confirmed'),
(2, NULL, 'Priya Nair', 34, 'Female', 'Passport', 'M1234567', '9845112233', 'priya.nair@sample.org', '560001', 'Precaution / Booster Dose', '2026-10-22', '02:00 PM - 03:30 PM', 'Confirmed'),
(3, NULL, 'Rahul Verma', 45, 'Male', 'Aadhaar', '9876 5432 1098', '9711002288', 'rahul.v@testmail.com', '110001', 'RT-PCR Test (Diagnostic)', '2026-10-18', '09:00 AM - 10:00 AM', 'Confirmed')
ON DUPLICATE KEY UPDATE `id`=`id`;

-- Insert initial sample comments
INSERT INTO `comments` (`id`, `user_id`, `author_name`, `author_email`, `message`, `rating`, `status`) VALUES
(1, 2, 'Aarav Sharma', 'aarav.sharma@example.com', 'Booking process was very smooth and verification took under 5 minutes at the center. Highly recommended!', 5, 'approved'),
(2, NULL, 'Dr. Sunita Deshmukh', 'sunita.doc@health.org', 'Please remember to maintain social distancing and wear an N-95 mask even after vaccination.', 5, 'approved'),
(3, NULL, 'Rohan Mehta', 'rohan.mehta@webmail.in', 'Got my booster dose certificate immediately linked to my portal registration. Thanks!', 4, 'approved')
ON DUPLICATE KEY UPDATE `id`=`id`;
