-- Student Performance & Study Planner
-- MySQL schema (beginner-friendly)

CREATE DATABASE IF NOT EXISTS student_planner;
USE student_planner;

-- 1) Assignments table
CREATE TABLE IF NOT EXISTS assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    description TEXT NULL,
    due_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    estimated_hours DECIMAL(4,2) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2) Basic GPA records table (simple version)
CREATE TABLE IF NOT EXISTS gpa_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    term_name VARCHAR(50) NOT NULL,
    credits_attempted DECIMAL(5,2) NOT NULL,
    credits_earned DECIMAL(5,2) NOT NULL,
    gpa DECIMAL(3,2) NOT NULL,
    notes VARCHAR(255) NULL,
    recorded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (gpa >= 0.00 AND gpa <= 4.00),
    CHECK (credits_attempted >= 0),
    CHECK (credits_earned >= 0)
);
