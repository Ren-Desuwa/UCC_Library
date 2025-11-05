-- This script creates the 'imlibrary' database from scratch.
-- It is idempotent, meaning it can be run multiple times without errors.

-- -----------------------------------------------------
-- Setup
-- -----------------------------------------------------
SET NAMES utf8mb4;
SET time_zone = '+00:00';
SET foreign_key_checks = 0; -- Disable foreign key checks to allow dropping tables in any order.

-- -----------------------------------------------------
-- Database
-- -----------------------------------------------------
CREATE DATABASE IF NOT EXISTS `imlibrary` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `imlibrary`;

-- -----------------------------------------------------
-- Drop existing tables (if they exist)
-- -----------------------------------------------------
DROP TABLE IF EXISTS `accounts`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `book_copies`;
DROP TABLE IF EXISTS `announcements`;
DROP TABLE IF EXISTS `favorites`;
DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `user_otp`;
DROP TABLE IF EXISTS `genres`;
DROP TABLE IF EXISTS `book_genres`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `authors`;
DROP TABLE IF EXISTS `book_authors`;
DROP TABLE IF EXISTS `settings`;

-- -----------------------------------------------------
-- Table structure for `accounts`
-- -----------------------------------------------------
CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(64) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Student',
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `fav_book_design` tinyint(1) DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`account_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `books`
-- (NOTE: `author` and `genre` columns are removed)
-- -----------------------------------------------------
CREATE TABLE `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `year_published` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`book_id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `title` (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `book_copies`
-- -----------------------------------------------------
CREATE TABLE `book_copies` (
  `copy_id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `condition` varchar(50) NOT NULL DEFAULT 'Good',
  `status` varchar(50) NOT NULL DEFAULT 'Available',
  `shelf_location` varchar(100) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`copy_id`),
  KEY `book_id` (`book_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `announcements`
-- -----------------------------------------------------
CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_posted` datetime NOT NULL DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `priority` varchar(50) NOT NULL DEFAULT 'Normal',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`announcement_id`),
  KEY `admin_id` (`admin_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `favorites`
-- -----------------------------------------------------
CREATE TABLE `favorites` (
  `fav_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `date_added` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`fav_id`),
  UNIQUE KEY `account_book_uniq` (`account_id`,`book_id`),
  KEY `account_id` (`account_id`),
  KEY `book_id` (`book_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `logs`
-- -----------------------------------------------------
CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `severity` varchar(50) NOT NULL DEFAULT 'Info',
  PRIMARY KEY (`log_id`),
  KEY `account_id` (`account_id`),
  KEY `timestamp` (`timestamp`),
  KEY `severity` (`severity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `transactions`
-- -----------------------------------------------------
CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `copy_id` int(11) DEFAULT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `date_borrowed` datetime DEFAULT NULL,
  `date_due` datetime DEFAULT NULL,
  `date_returned` datetime DEFAULT NULL,
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Active',
  PRIMARY KEY (`transaction_id`),
  KEY `account_id` (`account_id`),
  KEY `copy_id` (`copy_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `notifications`
-- -----------------------------------------------------
CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `notification_type` int(11) NOT NULL,
  PRIMARY KEY (`notification_id`),
  KEY `account_id` (`account_id`),
  KEY `transaction_id` (`transaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `user_otp`
-- -----------------------------------------------------
CREATE TABLE `user_otp` (
  `otp_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `verification_target` varchar(255) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`otp_id`),
  KEY `verification_target` (`verification_target`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `genres`
-- -----------------------------------------------------
CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`genre_id`),
  UNIQUE KEY `UK_genre_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `book_genres`
-- -----------------------------------------------------
CREATE TABLE `book_genres` (
  `book_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`, `genre_id`),
  KEY `IX_book_id` (`book_id`),
  KEY `IX_genre_id` (`genre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `sessions`
-- -----------------------------------------------------
CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `UK_token_hash` (`token_hash`),
  KEY `IX_account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `authors`
-- -----------------------------------------------------
CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`author_id`),
  UNIQUE KEY `UK_author_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `book_authors`
-- -----------------------------------------------------
CREATE TABLE `book_authors` (
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  PRIMARY KEY (`book_id`, `author_id`),
  KEY `IX_book_id` (`book_id`),
  KEY `IX_author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Table structure for `settings`
-- -----------------------------------------------------
CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------
-- Insert default settings
-- -----------------------------------------------------
INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('borrow_duration_days', '14', 'Number of days a book can be borrowed.'),
('max_books_per_user', '5', 'Maximum number of books a user can borrow at one time.'),
('overdue_fine_per_day', '1.00', 'Fine amount (in local currency) per day a book is overdue.'),
('reservation_expiry_hours', '48', 'Hours a user has to pick up a reserved book once available.');

-- -----------------------------------------------------
-- Add Foreign Key Constraints
-- -----------------------------------------------------

-- Constraints for table `announcements`
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_admin` FOREIGN KEY (`admin_id`) REFERENCES `accounts` (`account_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

-- Constraints for table `book_copies`
ALTER TABLE `book_copies`
  ADD CONSTRAINT `fk_bookcopies_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `favorites`
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favorites_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `logs`
ALTER TABLE `logs`
  ADD CONSTRAINT `fk_logs_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Constraints for table `notifications`
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- Constraints for table `transactions`
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_copy` FOREIGN KEY (`copy_id`) REFERENCES `book_copies` (`copy_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

-- Constraints for table `user_otp`
ALTER TABLE `user_otp`
  ADD CONSTRAINT `fk_userotp_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- Constraints for table `book_genres`
ALTER TABLE `book_genres`
  ADD CONSTRAINT `FK_book_genres_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_book_genres_genres` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE;

-- Constraints for table `sessions`
ALTER TABLE `sessions`
  ADD CONSTRAINT `FK_sessions_accounts` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

-- Constraints for table `book_authors`
ALTER TABLE `book_authors`
  ADD CONSTRAINT `FK_book_authors_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_book_authors_authors` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE CASCADE;

-- -----------------------------------------------------
-- Finalize
-- -----------------------------------------------------
SET foreign_key_checks = 1; -- Re-enable foreign key checks.
COMMIT; -- Commit the transaction.