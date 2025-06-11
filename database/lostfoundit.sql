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

CREATE DATABASE IF NOT EXISTS `lostfoundit` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `lostfoundit`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(10) DEFAULT 'user',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`) VALUES
(1, 'Rani', 'rani@example.com', 'hashed_pw1', 'user'),
(2, 'Budi', 'budi@example.com', 'hashed_pw2', 'user'),
(3, 'Dea Delvinata Riyan', 'dea@gmail.com', '$2y$10$O.FnvElxEk1NaaJZVeQ.wup6ThaObbimFxqf7yAI7Ps37A7qzpfAG', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('lost','found') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `date_reported` date DEFAULT (CURRENT_DATE),
  `status` enum('available','claimed') DEFAULT 'available',
  PRIMARY KEY (`item_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `user_id`, `title`, `description`, `type`, `photo`, `location`, `date_reported`, `status`) VALUES
(1, 1, 'Dompet Hitam', 'Hilang di parkiran dekat Gedung A', 'lost', NULL, 'Gedung A', CURRENT_DATE, 'claimed'),
(2, 2, 'Payung Merah', 'Ditemukan di depan perpustakaan', 'found', NULL, 'Perpustakaan', CURRENT_DATE, 'claimed');

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `claimant_id` int(11) NOT NULL,
  `date_claimed` datetime DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `claim_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`claim_id`),
  KEY `item_id` (`item_id`),
  KEY `claimant_id` (`claimant_id`),
  CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`),
  CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`claimant_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `activity_type` varchar(50) NOT NULL,
  `activity_date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Procedures
--

DELIMITER $$
CREATE PROCEDURE `sp_claimItem` (IN `p_user_id` INT, IN `p_item_id` INT)  
BEGIN
    DECLARE item_status VARCHAR(20);
    DECLARE item_type VARCHAR(20);
    DECLARE existing_claim INT;
    DECLARE user_role VARCHAR(10);
    
    -- Deklarasi handler untuk menangkap error
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;  -- Rollback jika terjadi error
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Terjadi kesalahan saat mengklaim barang.';
    END;

    START TRANSACTION;  -- Mulai transaction

    -- Cek role user
    SELECT role INTO user_role FROM users WHERE user_id = p_user_id;
    IF user_role = 'admin' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Admin tidak dapat mengklaim barang.';
    END IF;

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

    -- Update status item menjadi claimed
    UPDATE items 
    SET status = 'claimed' 
    WHERE item_id = p_item_id;

    -- Log aktivitas
    INSERT INTO activity_logs (user_id, item_id, activity_type, activity_date)
    VALUES (p_user_id, p_item_id, 'claim_item', NOW());

    COMMIT;  -- Commit jika semua berhasil
END$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Functions
--

DELIMITER $$
CREATE FUNCTION `fn_isItemClaimable` (`p_item_id` INT) 
RETURNS TINYINT(1) DETERMINISTIC
BEGIN
    DECLARE claimable BOOLEAN;
    SELECT status = 'available' INTO claimable
    FROM items
    WHERE item_id = p_item_id;
    RETURN IFNULL(claimable, FALSE);
END$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Triggers
--

DELIMITER $$
CREATE TRIGGER `trg_after_claim` 
AFTER INSERT ON `claims` 
FOR EACH ROW 
BEGIN
    -- Update status item menjadi claimed saat ada klaim baru
    UPDATE items 
    SET status = 'claimed' 
    WHERE item_id = NEW.item_id;
END$$
DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
