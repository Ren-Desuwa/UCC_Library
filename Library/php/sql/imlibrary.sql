-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 01, 2025 at 05:00 AM
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
-- Database: `imlibrary`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(64) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'Student',
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `birthday` date DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `fav_book_design` tinyint(1) DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `username`, `password_hash`, `role`, `name`, `email`, `birthday`, `contact_number`, `fav_book_design`, `date_created`, `is_active`) VALUES
(1, 'admin', 'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3', 'Admin', 'Admin User', 'admin@imlibrary.com', '1990-01-01', '09170000001', 1, '2025-10-31 11:50:07', 1),
(2, 'alex', '93f18139c11d4b32f9d985a11631f406691a89c89c8558448a08d23a6c4b9167', 'Student', 'Alex Reyes', 'alex@imlibrary.com', '2002-05-15', '09170000002', 1, '2025-10-31 11:50:07', 1),
(3, 'mary', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Librarian', 'Mary Jane', 'mary@imlibrary.com', '2002-05-15', '09170000003', 1);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `date_posted` datetime NOT NULL DEFAULT current_timestamp(),
  `expiry_date` datetime DEFAULT NULL,
  `priority` varchar(50) NOT NULL DEFAULT 'Normal',
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`announcement_id`, `admin_id`, `title`, `message`, `date_posted`, `expiry_date`, `priority`, `is_active`) VALUES
(1, 1, 'Welcome to the new imLibrary!', 'The new web-based library system is now live. Please report any issues to the admin desk. Enjoy!', '2025-10-31 11:50:07', NULL, 'High', 1);

-- --------------------------------------------------------

--
-- Table structure for table `authors`
--

CREATE TABLE `authors` (
  `author_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authors`
--

INSERT INTO `authors` (`author_id`, `name`) VALUES
(9, 'A. J. Finn'),
(11, 'Author Name'),
(3, 'F. Scott Fitzgerald'),
(1, 'George Orwell'),
(7, 'Gillian Flynn'),
(4, 'Harper Lee'),
(2, 'J.R.R. Tolkien'),
(10, 'Jane Austen'),
(8, 'Jean Hanff Korelitz'),
(5, 'Liane Moriarty'),
(6, 'Lucy Foley');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `isbn` varchar(13) NOT NULL,
  `publisher` varchar(255) DEFAULT NULL,
  `year_published` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `isbn`, `publisher`, `year_published`, `description`, `cover_url`) VALUES
(1, '1984', '9780451524935', 'Signet Classic', 1950, 'A dystopian social science fiction novel and cautionary tale.', '1984.jpg'),
(2, 'The Hobbit', '9780618260300', 'Houghton Mifflin', 1937, 'A fantasy novel and children\'s book.', 'hobbit.jpg'),
(3, 'The Lord of the Rings: The Fellowship of the Ring', '9780618640157', 'Allen & Unwin', 1954, 'The first of three volumes of the epic novel The Lord of the Rings.', 'lord_of_the_rings_fellowship.jpg'),
(4, 'The Great Gatsby', '9780743273565', 'Scribner', 1925, 'A novel about the American dream.', 'the_great_gatsby.jpg'),
(5, 'To Kill a Mockingbird', '9780061120084', 'HarperPerennial', 1960, 'A classic of modern American literature.', 'to_kill_a_mockingbird.jpg'),
(6, 'Big Little Lies', '9780399167065', 'G.P. Putnam\'s Sons', 2014, 'A tale of murder and mischief in a tranquil seaside town.', 'big_little_lies.jpg'),
(7, 'The Guest List', '9780062868930', 'William Morrow', 2020, 'A wedding celebration turns dark and deadly in this chilling mystery.', 'the_guest_list.jpg'),
(8, 'Sharp Objects', '9780307341556', 'Crown', 2006, 'A reporter confronts the psychological demons from her past.', 'sharp_objects.jpg'),
(9, 'The Plot', '9781250266938', 'Celadon Books', 2021, 'A once-promising writer steals a story from a deceased former student.', 'the_plot.jpg'),
(10, 'The Woman in the Window', '9780062678416', 'William Morrow', 2018, 'An agoraphobic woman living alone in New York City witnesses something she shouldn\'t have.', 'the_woman_in_the_window.jpg'),
(11, 'Pride and Prejudice', '9780141439518', 'Penguin Classics', 1813, 'A classic novel of manners and romance.', 'pride_and_prejudice.jpg'),
(12, 'The Adventure: A Fantasy Time Travel Journey', '9780000000001', 'Stock Images', 2024, 'A fantasy time travel journey by Author Name.', 'the_adventure.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `book_authors`
--

CREATE TABLE `book_authors` (
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_authors`
--

INSERT INTO `book_authors` (`book_id`, `author_id`) VALUES
(1, 1),
(2, 2),
(3, 2),
(4, 3),
(5, 4),
(6, 5),
(7, 6),
(8, 7),
(9, 8),
(10, 9),
(11, 10),
(12, 11);

-- --------------------------------------------------------

--
-- Table structure for table `book_copies`
--

CREATE TABLE `book_copies` (
  `copy_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `condition` varchar(50) NOT NULL DEFAULT 'Good',
  `status` varchar(50) NOT NULL DEFAULT 'Available',
  `shelf_location` varchar(100) DEFAULT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `last_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_copies`
--

INSERT INTO `book_copies` (`copy_id`, `book_id`, `condition`, `status`, `shelf_location`, `date_added`, `last_updated`) VALUES
(1, 1, 'Good', 'Available', 'DYS-01-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(2, 1, 'Good', 'Available', 'DYS-01-B', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(3, 2, 'Good', 'Available', 'FAN-01-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(4, 2, 'Good', 'Overdue', 'FAN-01-B', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(5, 3, 'Good', 'Available', 'FAN-01-C', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(6, 3, 'Good', 'Borrowed', 'FAN-01-D', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(7, 4, 'Good', 'Available', 'CLA-01-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(8, 5, 'Good', 'Available', 'CLA-02-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(9, 6, 'Good', 'Available', 'THR-01-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(10, 7, 'Good', 'Available', 'THR-02-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(11, 8, 'Good', 'Maintenance', 'THR-01-B', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(12, 9, 'Good', 'Available', 'THR-03-C', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(13, 10, 'Good', 'Available', 'THR-04-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(14, 11, 'Good', 'Available', 'CLA-03-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07'),
(15, 12, 'Good', 'Available', 'FAN-05-A', '2025-10-31 11:50:07', '2025-10-31 11:50:07');

-- --------------------------------------------------------

--
-- Table structure for table `book_genres`
--

CREATE TABLE `book_genres` (
  `book_id` int(11) NOT NULL,
  `genre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_genres`
--

INSERT INTO `book_genres` (`book_id`, `genre_id`) VALUES
(1, 1),
(1, 2),
(2, 3),
(2, 7),
(3, 3),
(3, 7),
(4, 2),
(5, 2),
(6, 4),
(6, 5),
(7, 4),
(7, 5),
(8, 4),
(8, 6),
(9, 4),
(9, 9),
(10, 4),
(10, 6),
(11, 2),
(11, 8),
(12, 3),
(12, 7);

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `fav_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`fav_id`, `account_id`, `book_id`, `date_added`) VALUES
(1, 2, 1, '2025-10-31 11:50:07'),
(2, 2, 2, '2025-10-31 11:50:07'),
(3, 2, 6, '2025-10-31 11:50:07');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `genre_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`genre_id`, `name`) VALUES
(7, 'Adventure'),
(2, 'Classic'),
(9, 'Contemporary Fiction'),
(1, 'Dystopian'),
(3, 'Fantasy'),
(5, 'Mystery'),
(6, 'Psychological'),
(8, 'Romance'),
(4, 'Thriller');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `log_id` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp(),
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `severity` varchar(50) NOT NULL DEFAULT 'Info'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`log_id`, `account_id`, `action`, `timestamp`, `details`, `ip_address`, `severity`) VALUES
(1, 1, 'Login Success', '2025-10-31 11:50:07', 'User successfully logged in.', NULL, 'Info'),
(2, 2, 'Login Success', '2025-10-31 11:50:07', 'User successfully logged in.', NULL, 'Info'),
(3, 2, 'Book Borrow', '2025-10-31 11:50:07', 'TransactionID: 1, CopyID: 6', NULL, 'Info'),
(4, 2, 'Book Borrow', '2025-10-31 11:50:07', 'TransactionID: 2, CopyID: 4', NULL, 'Info'),
(5, 2, 'Book Return', '2025-10-31 11:50:07', 'TransactionID: 3, CopyID: 7', NULL, 'Info');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `date_sent` datetime NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `notification_type` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `account_id`, `transaction_id`, `message`, `date_sent`, `is_read`, `notification_type`) VALUES
(1, 2, 1, 'Reminder: Your book \"The Lord of the Rings: The Fellowship of the Ring\" is due in 9 days.', '2025-10-31 11:50:07', 0, 0),
(2, 2, 2, 'OVERDUE: Your book \"The Hobbit\" is 6 days overdue. Please return it as soon as possible.', '2025-10-31 11:50:07', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `description`) VALUES
('borrow_duration_days', '14', 'Number of days a book can be borrowed.'),
('max_books_per_user', '5', 'Maximum number of books a user can borrow at one time.'),
('overdue_fine_per_day', '1.00', 'Fine amount (in local currency) per day a book is overdue.'),
('reservation_expiry_hours', '48', 'Hours a user has to pick up a reserved book once available.');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `copy_id` int(11) DEFAULT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `date_borrowed` datetime DEFAULT NULL,
  `date_due` datetime DEFAULT NULL,
  `date_returned` datetime DEFAULT NULL,
  `fine` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `account_id`, `copy_id`, `transaction_type`, `date_borrowed`, `date_due`, `date_returned`, `fine`, `status`) VALUES
(1, 2, 6, 'Borrow', '2025-10-26 11:50:07', '2025-11-09 11:50:07', NULL, 0.00, 'Active'),
(2, 2, 4, 'Borrow', '2025-10-11 11:50:07', '2025-10-25 11:50:07', NULL, 6.00, 'Overdue'),
(3, 2, 7, 'Borrow', '2025-10-01 11:50:07', '2025-10-15 11:50:07', '2025-10-21 11:50:07', 0.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `user_otp`
--

CREATE TABLE `user_otp` (
  `otp_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `verification_target` varchar(255) DEFAULT NULL,
  `otp_code` varchar(6) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `is_used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `is_active` (`is_active`);

--
-- Indexes for table `authors`
--
ALTER TABLE `authors`
  ADD PRIMARY KEY (`author_id`),
  ADD UNIQUE KEY `UK_author_name` (`name`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `title` (`title`);

--
-- Indexes for table `book_authors`
--
ALTER TABLE `book_authors`
  ADD PRIMARY KEY (`book_id`,`author_id`),
  ADD KEY `IX_book_id` (`book_id`),
  ADD KEY `IX_author_id` (`author_id`);

--
-- Indexes for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD PRIMARY KEY (`copy_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `book_genres`
--
ALTER TABLE `book_genres`
  ADD PRIMARY KEY (`book_id`,`genre_id`),
  ADD KEY `IX_book_id` (`book_id`),
  ADD KEY `IX_genre_id` (`genre_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`fav_id`),
  ADD UNIQUE KEY `account_book_uniq` (`account_id`,`book_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`genre_id`),
  ADD UNIQUE KEY `UK_genre_name` (`name`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `timestamp` (`timestamp`),
  ADD KEY `severity` (`severity`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD UNIQUE KEY `UK_token_hash` (`token_hash`),
  ADD KEY `IX_account_id` (`account_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `copy_id` (`copy_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `user_otp`
--
ALTER TABLE `user_otp`
  ADD PRIMARY KEY (`otp_id`),
  ADD KEY `verification_target` (`verification_target`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `authors`
--
ALTER TABLE `authors`
  MODIFY `author_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `book_copies`
--
ALTER TABLE `book_copies`
  MODIFY `copy_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `fav_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `genre_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_otp`
--
ALTER TABLE `user_otp`
  MODIFY `otp_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcements_admin` FOREIGN KEY (`admin_id`) REFERENCES `accounts` (`account_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `book_authors`
--
ALTER TABLE `book_authors`
  ADD CONSTRAINT `FK_book_authors_authors` FOREIGN KEY (`author_id`) REFERENCES `authors` (`author_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_book_authors_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD CONSTRAINT `fk_bookcopies_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `book_genres`
--
ALTER TABLE `book_genres`
  ADD CONSTRAINT `FK_book_genres_books` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_book_genres_genres` FOREIGN KEY (`genre_id`) REFERENCES `genres` (`genre_id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_favorites_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_favorites_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `fk_logs_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notifications_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `FK_sessions_accounts` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`account_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_copy` FOREIGN KEY (`copy_id`) REFERENCES `book_copies` (`copy_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `user_otp`
--
ALTER TABLE `user_otp`
  ADD CONSTRAINT `fk_userotp_user` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
