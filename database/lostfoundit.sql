-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 10:43 AM
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
    DECLARE item_type VARCHAR(20);
    DECLARE existing_claim INT;
    
    -- Deklarasi handler untuk menangkap error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;  -- Rollback jika terjadi error
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Terjadi kesalahan saat mengklaim barang.';
    END;

    START TRANSACTION;  -- Mulai transaction

    -- Cek dan lock data barang
    SELECT status, type INTO item_status, item_type
    FROM items 
    WHERE item_id = p_item_id 
    FOR UPDATE;  -- Lock row untuk mencegah race condition

    -- Validasi dengan rollback eksplisit
    IF item_status IS NULL THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang tidak ditemukan.';
    END IF;

    IF item_status != 'available' THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Barang tidak tersedia untuk diklaim.';
    END IF;

    -- Cek apakah user sudah pernah mengklaim barang ini
    SELECT COUNT(*) INTO existing_claim
    FROM claims 
    WHERE item_id = p_item_id 
    AND claimant_id = p_user_id;

    IF existing_claim > 0 THEN
        ROLLBACK;
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Anda sudah mengklaim barang ini sebelumnya.';
    END IF;

    -- Proses klaim
    INSERT INTO claims (item_id, claimant_id, status, claim_date) 
    VALUES (p_item_id, p_user_id, 'pending', NOW());

    UPDATE items 
    SET status = 'claimed' 
    WHERE item_id = p_item_id;

    -- Log aktivitas
    INSERT INTO activity_logs (user_id, item_id, activity_type, activity_date)
    VALUES (p_user_id, p_item_id, 'claim_item', NOW());

    COMMIT;  -- Commit jika semua berhasil
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `fn_isItemClaimable` (`p_item_id` INT) RETURNS TINYINT(1) DETERMINISTIC BEGIN
    DECLARE claimable BOOLEAN;
    SELECT status = 'available' INTO claimable
    FROM items
    WHERE item_id = p_item_id;
    RETURN IFNULL(claimable, FALSE);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `item_id`, `activity_type`, `activity_date`) VALUES
(5, 3, 17, 'report_lost', '2025-06-11 14:01:14'),
(6, 3, 18, 'report_found', '2025-06-11 14:01:39'),
(7, 3, 17, 'claim_item', '2025-06-11 14:03:10'),
(8, 3, 18, 'claim_item', '2025-06-11 14:03:26'),
(9, 3, 19, 'report_lost', '2025-06-11 14:13:18'),
(10, 3, 19, 'claim_item', '2025-06-11 14:13:35'),
(11, 3, 19, 'update_claim_status', '2025-06-11 14:18:11'),
(12, 3, 18, 'update_claim_status', '2025-06-11 14:28:34'),
(13, 3, 17, 'update_claim_status', '2025-06-11 14:28:45');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `date_claimed` datetime DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `claim_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`claim_id`, `item_id`, `claimant_id`, `date_claimed`, `status`, `claim_date`) VALUES
(13, 17, 3, '2025-06-11 14:03:10', 'rejected', '2025-06-11 14:03:10'),
(14, 18, 3, '2025-06-11 14:03:26', 'approved', '2025-06-11 14:03:26'),
(15, 19, 3, '2025-06-11 14:13:35', 'approved', '2025-06-11 14:13:35');

--
-- Triggers `claims`
--
DELIMITER $$
CREATE TRIGGER `trg_after_claim` AFTER INSERT ON `claims` FOR EACH ROW BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE items 
        SET status = 'claimed' 
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
  `photo` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `date_reported` date DEFAULT curdate(),
  `status` enum('available','claimed') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `user_id`, `title`, `description`, `type`, `photo`, `location`, `date_reported`, `status`) VALUES
(17, 3, 'zvsvfv', 'agdsgds', 'lost', 'uploads/items/684929baeacec.png', 'aasdfg', '2025-06-11', 'claimed'),
(18, 3, 'afEergeage', 'wgrsge', 'found', 'uploads/items/684929d3eeb28.png', 'agreege', '2025-06-11', 'claimed'),
(19, 3, 'adafwf', 'efwewefwf', 'lost', 'uploads/items/68492c8ea4318.png', 'afeafer', '2025-06-11', 'claimed');

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
(3, 'Dea Delvinata Riyan', 'dea@gmail.com', '$2y$10$O.FnvElxEk1NaaJZVeQ.wup6ThaObbimFxqf7yAI7Ps37A7qzpfAG'),
(4, 'Delia Eshal', 'delia@gmail.com', '$2y$10$CYVU42JycytuFW9VbO78TOSbznlvbn3eV3YEVQdtm8ur7RprDacji'),
(5, 'Dea Delvinata Riyan', 'eshal@gmail.com', '$2y$10$9TEeGhF3HyofGuh92bnIW.0kRupAYngJ1lHcKGhY0ZwQqiiVvNd7a');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

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
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

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
