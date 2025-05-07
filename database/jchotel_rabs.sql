-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 05, 2025 at 09:57 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jchotel_rabs`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `RedeemLoyaltyPoints`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `RedeemLoyaltyPoints` (IN `customerID` INT, IN `pointsToRedeem` INT)   BEGIN
    DECLARE currentPoints INT;

    -- Get current loyalty points of the customer
    SELECT loyalty_points INTO currentPoints FROM customers WHERE customer_id = customerID;

    -- Check if the customer has enough points to redeem
    IF currentPoints >= pointsToRedeem THEN
        -- Deduct points from the customer's total
        UPDATE customers SET loyalty_points = loyalty_points - pointsToRedeem WHERE customer_id = customerID;

        -- Log the transaction in loyalty_transactions
        INSERT INTO loyalty_transactions (customer_id, points, transaction_type) 
        VALUES (customerID, pointsToRedeem, 'redeem');
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Not enough loyalty points to redeem.';
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `audit_trails`
--

DROP TABLE IF EXISTS `audit_trails`;
CREATE TABLE IF NOT EXISTS `audit_trails` (
  `audit_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action_taken` text NOT NULL,
  `action_timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`audit_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing`
--

DROP TABLE IF EXISTS `billing`;
CREATE TABLE IF NOT EXISTS `billing` (
  `billing_id` int NOT NULL AUTO_INCREMENT,
  `group_number` int NOT NULL,
  `reservation_id` int NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) DEFAULT '0.00',
  `loyalty_points_used` int DEFAULT '0',
  `final_amount` decimal(10,2) GENERATED ALWAYS AS ((`total_amount` - `discount`)) STORED,
  `status` enum('unpaid','paid','cancelled') DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`billing_id`),
  KEY `reservation_id` (`reservation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `billing_statements`
--

DROP TABLE IF EXISTS `billing_statements`;
CREATE TABLE IF NOT EXISTS `billing_statements` (
  `statement_id` int NOT NULL AUTO_INCREMENT,
  `billing_id` int NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `generated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`statement_id`),
  KEY `billing_id` (`billing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `booking_notifications`
--

DROP TABLE IF EXISTS `booking_notifications`;
CREATE TABLE IF NOT EXISTS `booking_notifications` (
  `notification_id` int NOT NULL AUTO_INCREMENT,
  `reservation_id` int NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `reservation_id` (`reservation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `customer_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `password` text NOT NULL,
  `loyalty_points` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `full_name`, `email`, `phone`, `address`, `password`, `loyalty_points`, `created_at`) VALUES
(2, 'Harvey Pajayao', 'pajayao11@gmail.com', '09757693012', 'Sulit, Polomolok, South Cotabato', '$2y$10$1YBnV7bevtSp3DrKiOlL.utZnidmEnBi4UxwaKAVf4yS6yTI7/uFq', 0, '2025-04-02 09:40:02'),
(3, 'Marktitus Montales', 'montales@gmail.com', '09123455678', 'tantangan', '$2y$10$yL83BDqPLtNIytlfwoEfWeRyXhKu6XEG.PffgVC26V1LgLFkdB7cq', 0, '2025-04-02 19:15:51');

-- --------------------------------------------------------

--
-- Table structure for table `frontdesk_transactions`
--

DROP TABLE IF EXISTS `frontdesk_transactions`;
CREATE TABLE IF NOT EXISTS `frontdesk_transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `transaction_type` enum('check-in','check-out','payment','refund') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_transactions`
--

DROP TABLE IF EXISTS `loyalty_transactions`;
CREATE TABLE IF NOT EXISTS `loyalty_transactions` (
  `transaction_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int DEFAULT NULL,
  `points` int NOT NULL,
  `status` enum('earned','redeemed') NOT NULL,
  `transaction_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`transaction_id`),
  KEY `fk_customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `loyalty_transactions`
--

INSERT INTO `loyalty_transactions` (`transaction_id`, `customer_id`, `points`, `status`, `transaction_date`) VALUES
(1, 2, 200, 'earned', '2025-04-02 18:50:28');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `billing_id` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','credit_card','debit_card','digital_wallet') NOT NULL,
  `payment_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `billing_id` (`billing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Triggers `payments`
--
DROP TRIGGER IF EXISTS `after_payment_insert`;
DELIMITER $$
CREATE TRIGGER `after_payment_insert` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    -- Earn 1 loyalty point per 100 currency units spent
    DECLARE points_earned INT;
    SET points_earned = FLOOR(NEW.amount_paid / 100);

    IF points_earned > 0 THEN
        -- Insert the earned points into loyalty_transactions
        INSERT INTO loyalty_transactions (customer_id, points, transaction_type)
        SELECT r.customer_id, points_earned, 'earn'
        FROM billing b
        JOIN reservations r ON b.reservation_id = r.reservation_id
        WHERE b.billing_id = NEW.billing_id;

        -- Update the customer's total loyalty points
        UPDATE customers
        SET loyalty_points = loyalty_points + points_earned
        WHERE customer_id = (SELECT r.customer_id FROM billing b
                             JOIN reservations r ON b.reservation_id = r.reservation_id
                             WHERE b.billing_id = NEW.billing_id);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

DROP TABLE IF EXISTS `payment_history`;
CREATE TABLE IF NOT EXISTS `payment_history` (
  `history_id` int NOT NULL AUTO_INCREMENT,
  `payment_id` int NOT NULL,
  `details` text,
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`history_id`),
  KEY `payment_id` (`payment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

DROP TABLE IF EXISTS `reservations`;
CREATE TABLE IF NOT EXISTS `reservations` (
  `reservation_id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `room_id` int NOT NULL,
  `check_in` datetime NOT NULL,
  `check_out` datetime NOT NULL,
  `status` enum('pending','confirmed','checked-in','checked-out') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'pending',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reservation_id`),
  KEY `customer_id` (`customer_id`),
  KEY `room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
CREATE TABLE IF NOT EXISTS `rooms` (
  `room_id` int NOT NULL AUTO_INCREMENT,
  `room_number` varchar(10) NOT NULL,
  `type` enum('standard','superior','suite','deluxe') NOT NULL,
  `image` text,
  `price` decimal(10,2) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_number` (`room_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_number`, `type`, `image`, `price`, `description`, `created_at`) VALUES
(1, '100', 'standard', 'standard1.jpg', 1890.00, '', '2025-04-02 09:46:27'),
(2, '101', 'standard', 'standard3.jpg', 1890.00, '', '2025-04-02 09:46:50'),
(3, '102', 'standard', 'standard2.jpg', 1890.00, '', '2025-04-02 11:30:53'),
(4, '103', 'superior', 'superior.jpg', 3000.00, '', '2025-04-02 11:31:13'),
(5, '104', 'superior', 'superior1.jpg', 3000.00, '', '2025-04-02 11:31:28');

-- --------------------------------------------------------

--
-- Table structure for table `room_occupancy`
--

DROP TABLE IF EXISTS `room_occupancy`;
CREATE TABLE IF NOT EXISTS `room_occupancy` (
  `occupancy_id` int NOT NULL AUTO_INCREMENT,
  `room_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `check_in_time` datetime NOT NULL,
  `check_out_time` datetime DEFAULT NULL,
  `status` enum('checked-in','checked-out') DEFAULT 'checked-in',
  PRIMARY KEY (`occupancy_id`),
  KEY `room_id` (`room_id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tipping_suggestions`
--

DROP TABLE IF EXISTS `tipping_suggestions`;
CREATE TABLE IF NOT EXISTS `tipping_suggestions` (
  `tip_id` int NOT NULL AUTO_INCREMENT,
  `billing_id` int NOT NULL,
  `suggested_tip` decimal(10,2) NOT NULL,
  `status` enum('accepted','declined') DEFAULT 'declined',
  `recorded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`tip_id`),
  KEY `billing_id` (`billing_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','frontdesk','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(7, 'admin', '$2y$10$V9WIjRtm9glIst94Dhn9beAbg9TyFF.GRgbIUYZhjXJBqCRCu/BFu', 'administrator', 'admin', '2025-04-03 05:07:12'),
(2, 'staff', '$2y$10$yF/gbQ9Q4F6At0jfS8XVL.nVpEXCoDpYCcBFRv.8ZVn7EO.jJza/u', 'Marktitus', 'staff', '2025-04-01 03:50:37'),
(3, 'frontdesk', '$2y$10$pFJQWSx3NOED..GzCJR6V.SoVl8JObVuVOA6AytHQFPWpoAO3z5Ru', 'Izagani', 'frontdesk', '2025-04-01 04:23:38'),
(4, 'admin01', '$2y$10$abcdefghij1234567890klmnopqrstuvwxYZABCDE1234567890', 'System Admin', 'admin', '2025-04-02 09:34:35');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
