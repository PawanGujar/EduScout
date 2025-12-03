-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 12:55 PM
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
-- Database: `eduscout_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE `bookmarks` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookmarks`
--

INSERT INTO `bookmarks` (`id`, `user_id`, `course_id`, `created_at`) VALUES
(10, 4, 3, '2025-08-24 07:26:50'),
(11, 7, 7, '2025-12-02 20:00:39'),
(12, 7, 9, '2025-12-03 08:57:31');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT '?',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `icon`, `created_at`) VALUES
(1, 'IT', 'it', 'Information Technology & Programming', '💻', '2025-12-03 08:54:47'),
(2, 'Medical', 'medical', 'Medical & Healthcare Sciences', '🏥', '2025-12-03 08:54:47'),
(3, 'Engineering', 'engineering', 'Engineering & Technology', '⚙️', '2025-12-03 08:54:47'),
(4, 'Arts', 'arts', 'Arts, History & Humanities', '🎨', '2025-12-03 08:54:47');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `url` varchar(500) DEFAULT NULL,
  `duration` varchar(50) DEFAULT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `field` varchar(50) DEFAULT NULL,
  `thumbnail_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `submitted_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `title`, `url`, `duration`, `provider`, `field`, `thumbnail_url`, `created_at`, `status`, `submitted_by`) VALUES
(1, 'Full Stack Web Development for Begginers', 'https://youtu.be/nu_pCVPKzTk?si=Wb2fQjnGO7dI2G9Q', '7:29 hours', 'freeCodeCamp', 'IT', 'https://via.placeholder.com/300x180?text=Web+Dev', '2025-07-18 02:23:30', 'approved', 5),
(2, 'Python for Beginners', NULL, '2 hours', 'Programming with Mosh', 'IT', 'https://via.placeholder.com/300x180?text=Python', '2025-07-18 02:23:30', 'pending', NULL),
(3, 'JavaScript Essentials', NULL, '4 hours', 'Traversy Media', 'IT', 'https://via.placeholder.com/300x180?text=JS', '2025-07-18 02:23:30', 'pending', NULL),
(4, 'SQL Crash Course', NULL, '1.5 hours', 'The Net Ninja', 'IT', 'https://via.placeholder.com/300x180?text=SQL', '2025-07-18 02:23:30', 'pending', NULL),
(6, 'Python for Begginers 100 Days Coding Tutorial', 'https://www.youtube.com/watch?v=7wnove7K-ZQ&list=PLu0W_9lII9agwh1XjRt242xIpHhPT2llg', '25 hours', 'Code with Harry', 'IT', NULL, '2025-12-02 19:47:00', 'approved', 6),
(7, 'Complete Java Tutorial For Begginers', 'https://www.youtube.com/watch?v=ntLJmHOJ0ME&list=PLu0W_9lII9agS67Uits0UnJyrYiXhDS6q', '3 hours', 'code with harry', 'IT', NULL, '2025-12-02 19:48:09', 'approved', 6),
(8, 'SolidWorks Two Cylinder Engine', 'https://youtu.be/0y56DD_6KkQ?si=9lWFLgPjFFXbthW5', '12 videos', 'CAD CAM TUTORIAL BY MAHTABALAM', 'Engineering', NULL, '2025-12-03 08:37:44', 'approved', 8),
(9, 'Electrical Engineering Basics', 'https://youtu.be/mc979OhitAg?si=DGo03_st1OaQk1gc', '41 videso', 'The Engineering Mindset', 'Engineering', NULL, '2025-12-03 08:41:51', 'approved', 6),
(10, 'Introduction to Civil Engineering Profession', 'https://youtu.be/CsKddkqgwVk?si=ga7FYT5NpI2i_ncM', '20 videos', 'NPTEL-NOC IITM', 'Engineering', NULL, '2025-12-03 09:08:04', 'pending', 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('learner','editor','admin') DEFAULT 'learner',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(4, 'testuser', 'test@example.com', '$2y$10$yEHtlF6DyIk87vfanJmDLej9rQjJrUqPqb6zIWr33CQjNxyyEV.8C', 'learner', '2025-08-24 07:24:59'),
(5, 'adminuser', 'admin@example.com', '$2y$10$Xjv0Mxf..pkzxZjvF7Ouiul04dJl.vF5ya6.lDGPV/1kdkvLFparu', 'admin', '2025-08-24 07:27:58'),
(6, 'adminbro', 'adminbro@example.com', 'adminbro', 'admin', '2025-12-02 19:04:19'),
(7, 'testiguy', 'testiguy@example.com', '$2y$10$jlEexZOn6QBsUu10rZApReeUIl0EbV2Isxk/.XnT72WsYWfvx5GV.', 'learner', '2025-12-02 19:57:26'),
(8, 'editorial', 'editorial@example.com', '$2y$10$5xaBn1n3TW3J71A/5eYE2OreyHSmXgI.bjiVpD2jKcub7vpwjeaNO', 'editor', '2025-12-02 20:03:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_bookmark` (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookmarks`
--
ALTER TABLE `bookmarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookmarks`
--
ALTER TABLE `bookmarks`
  ADD CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
