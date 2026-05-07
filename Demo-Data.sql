-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: May 07, 2026 at 04:57 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `srim`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `email` varchar(180) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(32) NOT NULL DEFAULT 'CANDIDATE',
  `status` varchar(32) NOT NULL DEFAULT 'ACTIVE',
  `is_department_head` tinyint(1) NOT NULL DEFAULT 0,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `department_id`, `name`, `email`, `password_hash`, `role`, `status`, `is_department_head`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'SRIM HR Admin', 'hr.admin@example.com', '$2y$10$z0Zqz5PxpWrw07zSH5gpCedDoUjITCX7MxdgUf1rw7jFTYeXZxBrS', 'HR_ADMIN', 'ACTIVE', 0, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(2, 2, 'Dana Farouk', 'dana.head@example.com', '$2y$10$6Km728xkASCs5HtTSQ4mnOR9jNHYDejZpxgf7rTikhsphxlq2Mwee', 'HR_ADMIN', 'ACTIVE', 1, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(3, 2, 'Omar Nabil', 'omar.interviewer@example.com', '$2y$10$EYCNoVztww0D5htxYIqrROW37hOeMMEuEWstfUzAdvrLIKipR077K', 'INTERVIEWER', 'ACTIVE', 0, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(4, 2, 'Mona Salem', 'mona.shadow@example.com', '$2y$10$um5O6m5y/Wkqo8x9.klY1.0l9pG3wn4xSBBbcm/YWHGWKcCCMX5LW', 'INTERVIEWER', 'ACTIVE', 0, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(5, NULL, 'Lina Hassan', 'lina.candidate@example.com', '$2y$10$g6lcpde.AsoHPBle27beourvx3EIBjsmY5ihNNy0J8snKfDgqci9a', 'CANDIDATE', 'ACTIVE', 0, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(6, NULL, 'Karim Atef', 'karim.candidate@example.com', '$2y$10$2yrVwrgwFk.5xJo5J2YxyOD/JULyMHxh1YUbHLRTmOQZReBA7m8l6', 'CANDIDATE', 'ACTIVE', 0, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(7, NULL, 'Sara Mansour', 'sara.candidate@example.com', '$2y$10$WdEinUckVzg8nHjE.Uv6lOPNNeOIFgZrdMxeQ.AtjEJxRwI9ihxYq', 'CANDIDATE', 'ACTIVE', 0, NULL, '2026-05-06 19:09:44', '2026-05-06 19:09:44'),
(8, NULL, 'Nour Adel 2431', 'nour.demo.20260507012431@example.com', '$2y$10$DDzxyAm/xG3bfb7F0Ig7nuqN184DVsHhf8OelO2aN0.rDTKK631OW', 'CANDIDATE', 'ACTIVE', 0, NULL, '2026-05-07 00:27:12', '2026-05-07 00:27:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_department` (`department_id`),
  ADD KEY `idx_users_role_status` (`role`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
