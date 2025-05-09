-- phpMyAdmin SQL Dump
-- version 4.9.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 09, 2025 at 04:59 PM
-- Server version: 10.5.22-MariaDB
-- PHP Version: 7.0.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `demo4`
--

-- --------------------------------------------------------

--
-- Table structure for table `cases`
--

CREATE TABLE `cases` (
  `id` int(11) NOT NULL,
  `case_name` varchar(100) NOT NULL,
  `case_description` text DEFAULT NULL,
  `case_url` varchar(255) NOT NULL,
  `max_score` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `cases`
--

INSERT INTO `cases` (`id`, `case_name`, `case_description`, `case_url`, `max_score`) VALUES
(1, 'Case 1: Website mất kết nối', 'Website không thể truy cập được từ người dùng bên ngoài Internet. Hãy kiểm tra và xử lý sự cố.', '/case1/index.php', 100),
(2, 'Case 2: Xử lý sự cố kết nối cơ sở dữ liệu', 'Website không truy cập được do lỗi kết nối cơ sở dữ liệu. Hãy khắc phục sự cố và bảo mật hệ thống.', '/case2/index.php', 100);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `score`) VALUES
(2, 'user1', '482c811da5d5b4bc6d497ffa98491e38', 'Nguyễn Văn A', 100),
(3, 'thuong', '619170c4d559a0786a141d489d019e22', 'Hoài Thương', 125),
(4, 'khang', 'c39e2024ef5db5d740027aee5250440b', 'Dương Khang', 0),
(5, 'nhan', 'bb4e31f2d20f8e7f88e2b8459263657f', 'Thiện Nhân', 100);

-- --------------------------------------------------------

--
-- Table structure for table `user_case_scores`
--

CREATE TABLE `user_case_scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `case_id` int(11) NOT NULL,
  `score` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `user_case_scores`
--

INSERT INTO `user_case_scores` (`id`, `user_id`, `case_id`, `score`) VALUES
(1, 3, 1, 100),
(10, 4, 2, 0),
(11, 5, 2, 0),
(12, 3, 2, 25),
(13, 2, 2, 0),
(18, 5, 1, 100);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cases`
--
ALTER TABLE `cases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_case_scores`
--
ALTER TABLE `user_case_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_case` (`user_id`,`case_id`),
  ADD KEY `case_id` (`case_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cases`
--
ALTER TABLE `cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_case_scores`
--
ALTER TABLE `user_case_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_case_scores`
--
ALTER TABLE `user_case_scores`
  ADD CONSTRAINT `user_case_scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_case_scores_ibfk_2` FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
