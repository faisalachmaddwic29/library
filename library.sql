-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 30, 2024 at 06:48 AM
-- Server version: 8.0.30
-- PHP Version: 8.2.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `description` text,
  `cover_image` varchar(255) DEFAULT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `total_copies` int DEFAULT '1',
  `available_copies` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `description`, `cover_image`, `isbn`, `category`, `total_copies`, `available_copies`, `created_at`, `updated_at`) VALUES
(12, 'Cantik Itu Luka', 'Eka Kurniawan', 'cantik itu luka', '676fbbbc91998.jpg', '978602031258', 'Fiksi', 15, 14, '2024-12-28 08:50:04', '2024-12-28 09:02:20'),
(13, 'Laut Bercerita', 'Leila S. Chudori', 'Laut Bercerita', '676fbbfc18ce6.jpg', '9786024246945', 'Fiksi', 43, 43, '2024-12-28 08:51:08', '2024-12-28 08:51:08'),
(14, 'Ronggeng Dukuh Paruk', 'Ahmad Tohari', 'ronggeng dukuh paruk', '676fbc579d369.jpg', '9789792201963', 'Non-Fiksi', 100, 100, '2024-12-28 08:52:39', '2024-12-28 08:52:39'),
(15, 'Menjadi Guru Hebat Zaman Now', 'Robert Bala', 'Menjadi Guru Hebat Zaman Now', '676fbd752957a.jpg', '9786020504094', 'Pendidikan', 150, 150, '2024-12-28 08:57:25', '2024-12-28 08:57:25');

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `borrow_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` timestamp NOT NULL,
  `return_date` timestamp NULL DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`id`, `user_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`, `approved_at`) VALUES
(5, 2, 12, '2024-12-28 08:58:54', '2025-01-11 01:58:54', NULL, 'borrowed', '2024-12-28 15:59:54'),
(6, 6, 12, '2024-12-28 09:01:10', '2025-01-11 02:01:10', '2024-12-28 09:02:20', 'returned', '2024-12-28 16:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@library.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2024-12-26 12:03:29'),
(2, 'user1', 'user1@gmail.com', '$2y$10$HjWyCX88ryFKnxAxXnO/T.aG8kO38LPi80dsrxmfMr2XwJX6kQ0/.', 'user', '2024-12-26 14:19:28'),
(3, 'user2', 'user2@gmail.com', '$2y$10$Q1b8BEpwKUhHdkWhmtvfoezMiwfWAdULRSAFMWc1hbbQorAE64RiO', 'user', '2024-12-28 08:40:31'),
(4, 'user3', 'user3@gmail.com', '$2y$10$8mVXFO7eg5u2xMvx3MW96OEfF/7e.W0znXWhsKa9GfNqwCDE/9A6e', 'user', '2024-12-28 08:41:26'),
(5, 'user4', 'user4@gmail.com', '$2y$10$Ay9GlyFY.gZmDX8ieWyb5ubZXKy55jVSmJVsHBk6T5yjY038hkFr.', 'user', '2024-12-28 08:42:35'),
(6, 'user10', 'user10@gmail.com', '$2y$10$oR51lgh5dhF7WcvyqYLPp.60dpxwNY/eQ/KtbtOJ3xiOlPFLfJhQq', 'user', '2024-12-28 09:00:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_isbn` (`isbn`),
  ADD KEY `idx_title` (`title`);

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_due_date` (`due_date`);

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
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `borrowings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `borrowings_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;