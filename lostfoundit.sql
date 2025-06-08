-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 04:33 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.0.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lostfoundit`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_claimItem` (IN `p_user_id` INT, IN `p_item_id` INT)   BEGIN
    DECLARE item_status VARCHAR(20);

    SELECT status INTO item_status FROM items WHERE item_id = p_item_id;

    IF item_status = 'available' THEN
        INSERT INTO claims (item_id, claimant_id, status)
        VALUES (p_item_id, p_user_id, 'approved');

        UPDATE items SET status = 'claimed' WHERE item_id = p_item_id;
    ELSE
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Item not available for claim.';
    END IF;
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_isItemClaimable` (`p_item_id` INT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    DECLARE claimable BOOLEAN;

    SELECT status = 'available' INTO claimable
    FROM items
    WHERE item_id = p_item_id;

    RETURN claimable;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `date_claimed` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`claim_id`, `item_id`, `claimant_id`, `date_claimed`, `status`) VALUES
(1, 2, 1, '2025-06-08 21:05:36', 'approved'),
(2, 1, 1, '2025-06-08 21:17:03', 'approved');

--
-- Triggers `claims`
--
DELIMITER $$
CREATE TRIGGER `trg_after_claim` AFTER INSERT ON `claims` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE items SET status = 'claimed'
        WHERE item_id = NEW.item_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('lost','found') NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `date_reported` date DEFAULT curdate(),
  `status` enum('available','claimed') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `user_id`, `title`, `description`, `type`, `location`, `date_reported`, `status`) VALUES
(1, 1, 'Dompet Hitam', 'Hilang di parkiran dekat Gedung A', 'lost', 'Gedung A', '2025-06-08', 'claimed'),
(2, 2, 'Payung Merah', 'Ditemukan di depan perpustakaan', 'found', 'Perpustakaan', '2025-06-08', 'claimed');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`) VALUES
(1, 'Rani', 'rani@example.com', 'hashed_pw1'),
(2, 'Budi', 'budi@example.com', 'hashed_pw2'),
(3, 'Dea Delvinata Riyan', 'dea@gmail.com', '$2y$10$O.FnvElxEk1NaaJZVeQ.wup6ThaObbimFxqf7yAI7Ps37A7qzpfAG');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `claimant_id` (`claimant_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
