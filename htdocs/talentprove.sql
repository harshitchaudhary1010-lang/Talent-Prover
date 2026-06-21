-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 08, 2026 at 08:35 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `talentprove`
--

-- --------------------------------------------------------

--
-- Table structure for table `company_profiles`
--

CREATE TABLE `company_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_name` varchar(150) NOT NULL,
  `industry` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company_profiles`
--

INSERT INTO `company_profiles` (`id`, `user_id`, `company_name`, `industry`, `website`, `description`, `logo`) VALUES
(1, 3, 'NovaWorks Labs', 'SaaS and AI Tools', 'https://example.com', 'NovaWorks Labs hires practical builders through short, realistic proof-of-work challenges.', '/assets/uploads/profiles/company-3-14ae46528552.png');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `body`, `is_read`, `created_at`) VALUES
(1, 3, 2, 'hi', 1, '2026-05-05 04:15:55'),
(5, 3, 2, 'HI Brother How are you?', 1, '2026-05-05 04:28:12'),
(6, 2, 3, 'hi', 1, '2026-05-05 04:28:46'),
(7, 2, 3, 'Hi ,', 1, '2026-05-05 04:29:21'),
(9, 2, 3, 'oky sir 🙏🙏', 1, '2026-05-05 04:56:09'),
(10, 3, 2, 'hunxa vai la vetum', 1, '2026-05-05 04:56:39'),
(11, 2, 3, 'hi', 1, '2026-05-05 05:01:43'),
(12, 3, 2, '🎉htgrfevrgtbhyujijhgnv', 1, '2026-05-05 07:24:24'),
(13, 2, 3, 'hi', 1, '2026-05-05 07:24:40'),
(14, 3, 2, 'hwy  you  are shotlisted', 1, '2026-05-05 07:24:42'),
(15, 2, 3, 'hiii', 1, '2026-05-05 07:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 2, 'Welcome to TalentProve. Browse available tasks and submit your best proof of work.', 1, '2026-05-04 12:43:00'),
(2, 3, 'Your company dashboard is ready. Post tasks and review submissions from candidates.', 1, '2026-05-04 12:43:00'),
(3, 3, 'Maya Sharma submitted work for \"Build a responsive pricing section\".', 1, '2026-05-04 14:24:49'),
(4, 2, 'Your submission for \"Build a responsive pricing section\" is now reviewed.', 1, '2026-05-04 14:36:07'),
(5, 2, 'Your submission for \"Build a responsive pricing section\" is now reviewed.', 1, '2026-05-04 14:36:08'),
(6, 2, 'Your submission for \"Build a responsive pricing section\" is now shortlisted.', 1, '2026-05-04 14:36:09'),
(7, 2, 'Your submission for \"Build a responsive pricing section\" is now rejected.', 1, '2026-05-04 14:36:13'),
(8, 2, 'Your submission for \"Build a responsive pricing section\" is now rejected.', 1, '2026-05-04 14:51:45'),
(9, 2, 'New task posted: \"Best\". Check it out!', 0, '2026-05-04 17:28:07'),
(10, 3, 'Maya Sharma submitted work for \"Best\".', 0, '2026-05-04 17:28:44'),
(15, 2, 'You have a new message from NovaWorks Labs.', 0, '2026-05-05 04:28:12'),
(16, 3, 'You have a new message from Maya Sharma.', 0, '2026-05-05 04:28:46'),
(17, 3, 'You have a new message from Maya Sharma.', 0, '2026-05-05 04:29:21'),
(19, 3, 'You have a new message from Maya Sharma.', 0, '2026-05-05 04:56:09'),
(20, 2, 'You have a new message from NovaWorks Labs.', 0, '2026-05-05 04:56:39'),
(21, 3, 'You have a new message from Maya Sharma.', 0, '2026-05-05 05:01:43'),
(22, 2, 'New task posted: \"Video Editing\". Check it out!', 0, '2026-05-05 07:14:45'),
(23, 3, 'Maya Sharma submitted work for \"Video Editing\".', 0, '2026-05-05 07:16:38'),
(24, 2, 'Your submission for \"Video Editing\" is now shortlisted.', 0, '2026-05-05 07:17:14'),
(25, 2, 'Your submission for \"Video Editing\" is now reviewed.', 0, '2026-05-05 07:17:14'),
(26, 2, 'New task posted: \"figma  design  of       website\". Check it out!', 0, '2026-05-05 07:18:51'),
(27, 3, 'Maya Sharma submitted work for \"figma  design  of       website\".', 0, '2026-05-05 07:19:17'),
(28, 2, 'Your submission for \"figma  design  of       website\" is now shortlisted.', 0, '2026-05-05 07:20:04'),
(29, 2, 'Your submission for \"figma  design  of       website\" is now rejected.', 0, '2026-05-05 07:20:32'),
(30, 2, 'Your submission for \"figma  design  of       website\" is now shortlisted.', 0, '2026-05-05 07:20:43'),
(31, 2, 'Your submission for \"figma  design  of       website\" is now reviewed.', 0, '2026-05-05 07:20:59'),
(32, 2, 'Your submission for \"figma  design  of       website\" is now reviewed.', 0, '2026-05-05 07:21:30'),
(33, 2, 'Your submission for \"Video Editing\" is now reviewed.', 0, '2026-05-05 07:21:31'),
(34, 2, 'Your submission for \"figma  design  of       website\" is now shortlisted.', 0, '2026-05-05 07:21:39'),
(35, 2, 'Your submission for \"Video Editing\" is now reviewed.', 0, '2026-05-05 07:23:08'),
(36, 2, 'Your submission for \"Video Editing\" is now shortlisted.', 0, '2026-05-05 07:23:55'),
(37, 2, 'You have a new message from NovaWorks Labs.', 0, '2026-05-05 07:24:24'),
(38, 3, 'You have a new message from Maya Sharma.', 0, '2026-05-05 07:24:40'),
(39, 2, 'You have a new message from NovaWorks Labs.', 0, '2026-05-05 07:24:42'),
(40, 3, 'You have a new message from Maya Sharma.', 0, '2026-05-05 07:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `student_profiles`
--

CREATE TABLE `student_profiles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `skills` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `portfolio_link` varchar(255) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_profiles`
--

INSERT INTO `student_profiles` (`id`, `user_id`, `skills`, `bio`, `portfolio_link`, `profile_image`) VALUES
(1, 2, 'HTML, CSS, JavaScript, PHP, UI Design', 'Frontend-focused student who enjoys building polished, responsive product interfaces.', 'https://github.com/demo-student', '/assets/uploads/profiles/student-2-78b0ec8f6a81.png');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `submission_link` varchar(500) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','reviewed','shortlisted','rejected') DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`id`, `task_id`, `student_id`, `submission_link`, `message`, `status`, `submitted_at`) VALUES
(2, 3, 2, 'https://github.com.np/sachinsunway', 'h999', 'pending', '2026-05-04 17:28:44'),
(3, 4, 2, 'http://10.59.51.105:8090/', 'sir 50', 'shortlisted', '2026-05-05 07:16:38');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `required_skills` text DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `status` enum('active','closed','draft') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `company_id`, `title`, `description`, `required_skills`, `deadline`, `status`, `created_at`) VALUES
(3, 3, 'Best', 'Code', 'Htmll,', NULL, 'closed', '2026-05-04 17:28:07'),
(4, 3, 'Video Editing', 'Edit A College Video', 'video editing', '2026-05-06', 'active', '2026-05-05 07:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('student','company','admin') NOT NULL,
  `status` enum('active','blocked','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'Admin', 'admin@talentprove.com', '$2y$10$GTSBXJAORkKP5RoAfjkNB.kepcNKZhtba.HLKh5PDpZfEXVNj8Sy.', 'admin', 'active', '2026-05-04 12:43:00'),
(2, 'Maya Sharma', 'student@demo.com', '$2y$10$GTSBXJAORkKP5RoAfjkNB.kepcNKZhtba.HLKh5PDpZfEXVNj8Sy.', 'student', 'active', '2026-05-04 12:43:00'),
(3, 'NovaWorks Labs', 'company@demo.com', '$2y$10$GTSBXJAORkKP5RoAfjkNB.kepcNKZhtba.HLKh5PDpZfEXVNj8Sy.', 'company', 'active', '2026-05-04 12:43:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `company_profiles`
--
ALTER TABLE `company_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `student_profiles`
--
ALTER TABLE `student_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD CONSTRAINT `company_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_profiles`
--
ALTER TABLE `student_profiles`
  ADD CONSTRAINT `student_profiles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
