-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 18, 2026 at 07:08 PM
-- Server version: 10.4.25-MariaDB
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sample_php_pdo`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `status` enum('success','failed') DEFAULT 'success',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','user') DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(64) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `is_verified`, `verification_token`, `email_verification_expires`, `created_at`, `updated_at`) VALUES
(1, 'admin@example.com', '$2y$10$HNfhClczEWBxcFuJwP53iu2Y75Tba7IEtmX8vX.1tp0dZ5EVt9CbO', 'admin', 1, NULL, NULL, '2026-03-11 15:01:09', '2026-03-11 15:01:09'),
(2, 'manager@example.com', '$2y$10$HNfhClczEWBxcFuJwP53iu2Y75Tba7IEtmX8vX.1tp0dZ5EVt9CbO', 'manager', 1, NULL, NULL, '2026-03-11 15:01:09', '2026-03-11 15:01:09'),
(3, 'user@example.com', '$2y$10$HNfhClczEWBxcFuJwP53iu2Y75Tba7IEtmX8vX.1tp0dZ5EVt9CbO', 'user', 1, NULL, NULL, '2026-03-11 15:01:09', '2026-03-11 15:01:09');

-- --------------------------------------------------------

--
-- Table structure for table `user_weather_history`
--

CREATE TABLE `user_weather_history` (
  `search_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `location` varchar(255) NOT NULL,
  `temperature` float NOT NULL,
  `cond` varchar(255) NOT NULL,
  `humidity` int(11) NOT NULL,
  `wind_speed` float NOT NULL,
  `cloth_rec` varchar(255) NOT NULL,
  `searched_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `icon` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_weather_history`
--
ALTER TABLE `user_weather_history`
  ADD PRIMARY KEY (`search_id`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_weather_history`
--
ALTER TABLE `user_weather_history`
  MODIFY `search_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_weather_history`
--
ALTER TABLE `user_weather_history`
  ADD CONSTRAINT `user_weather_history_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;