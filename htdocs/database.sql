-- TalentProve Database Schema
CREATE DATABASE IF NOT EXISTS talentprove CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE talentprove;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS submissions;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS company_profiles;
DROP TABLE IF EXISTS student_profiles;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'company', 'admin') NOT NULL,
    status ENUM('active', 'blocked', 'pending') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    skills TEXT,
    bio TEXT,
    portfolio_link VARCHAR(255),
    profile_image VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE company_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    company_name VARCHAR(150) NOT NULL,
    industry VARCHAR(100),
    website VARCHAR(255),
    description TEXT,
    logo VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    required_skills TEXT,
    deadline DATE,
    status ENUM('active', 'closed', 'draft') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    task_id INT NOT NULL,
    student_id INT NOT NULL,
    submission_link VARCHAR(500) NOT NULL,
    message TEXT,
    status ENUM('pending', 'reviewed', 'shortlisted', 'rejected') DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Demo accounts use password: password
INSERT INTO users (id, name, email, password, role, status) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$GTSBXJAORkKP5RoAfjkNB.kepcNKZhtba.HLKh5PDpZfEXVNj8Sy.', 'admin', 'active'),
(2, 'Maya Sharma', 'student@demo.com', '$2y$10$GTSBXJAORkKP5RoAfjkNB.kepcNKZhtba.HLKh5PDpZfEXVNj8Sy.', 'student', 'active'),
(3, 'NovaWorks Labs', 'company@demo.com', '$2y$10$GTSBXJAORkKP5RoAfjkNB.kepcNKZhtba.HLKh5PDpZfEXVNj8Sy.', 'company', 'active');

INSERT INTO student_profiles (user_id, skills, bio, portfolio_link) VALUES
(2, 'HTML, CSS, JavaScript, PHP, UI Design', 'Frontend-focused student who enjoys building polished, responsive product interfaces.', 'https://github.com/demo-student');

INSERT INTO company_profiles (user_id, company_name, industry, website, description) VALUES
(3, 'NovaWorks Labs', 'SaaS and AI Tools', 'https://example.com', 'NovaWorks Labs hires practical builders through short, realistic proof-of-work challenges.');

INSERT INTO tasks (company_id, title, description, required_skills, deadline, status) VALUES
(3, 'Build a responsive pricing section', 'Create a modern pricing section with monthly and yearly plan states, clean mobile layout, and accessible buttons.', 'HTML, CSS, JavaScript', DATE_ADD(CURDATE(), INTERVAL 7 DAY), 'active'),
(3, 'Design a PHP contact form flow', 'Build a secure PHP contact form with validation, PDO insert logic, and clear success or error states.', 'PHP, MySQL, JavaScript', DATE_ADD(CURDATE(), INTERVAL 10 DAY), 'active');

INSERT INTO notifications (user_id, message) VALUES
(2, 'Welcome to TalentProve. Browse available tasks and submit your best proof of work.'),
(3, 'Your company dashboard is ready. Post tasks and review submissions from candidates.');
