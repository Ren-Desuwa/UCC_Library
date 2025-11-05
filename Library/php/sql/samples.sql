-- This script populates the 'imlibrary' database with sample data.
-- It assumes the tables from your schema script already exist.

-- -----------------------------------------------------
-- Setup
-- -----------------------------------------------------
USE `imlibrary`;
SET foreign_key_checks = 0; -- Disable key checks to truncate tables

-- -----------------------------------------------------
-- Clear existing data (makes this script re-runnable)
-- -----------------------------------------------------
TRUNCATE TABLE `accounts`;
TRUNCATE TABLE `authors`;
TRUNCATE TABLE `genres`;
TRUNCATE TABLE `books`;
TRUNCATE TABLE `book_authors`;
TRUNCATE TABLE `book_genres`;
TRUNCATE TABLE `book_copies`;
TRUNCATE TABLE `transactions`;
TRUNCATE TABLE `favorites`;
TRUNCATE TABLE `announcements`;
TRUNCATE TABLE `notifications`;
TRUNCATE TABLE `logs`;
TRUNCATE TABLE `sessions`;
TRUNCATE TABLE `user_otp`;
-- We do not truncate `settings`, as it's configuration data.

-- -----------------------------------------------------
-- 1. Populate Independent Tables
-- -----------------------------------------------------

-- Populate `accounts`
-- Passwords are SHA-256 hashes.
-- 'admin123' -> 240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9
-- 'student123' -> 703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b
-- 'password123' -> ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f
INSERT INTO `accounts` (`account_id`, `username`, `password_hash`, `role`, `name`,`physical_id`, `email`, `birthday`, `contact_number`, `is_active`) VALUES
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Admin', 'Admin User', '2006001-A', 'admin@imlibrary.com', '1990-01-01', '09170000001', 1),
(2, 'alex', '703b0a3d6ad75b649a28adde7d83c6251da457549263bc7ff45ec709b0a8448b', 'Student', 'Alex Reyes', '20240001-C', 'alex@imlibrary.com', '2002-05-15', '09170000002', 1),
(3, 'mary', 'ef92b778bafe771e89245b89ecbc08a44a4e166c06659911881f383d4473e94f', 'Librarian', 'Mary Jane', '20240001-L', 'mary@imlibrary.com', '2002-05-15', '09170000003', 1);

-- Populate `authors`
INSERT INTO `authors` (`author_id`, `name`) VALUES
(1, 'George Orwell'),
(2, 'J.R.R. Tolkien'),
(3, 'F. Scott Fitzgerald'),
(4, 'Harper Lee'),
(5, 'Liane Moriarty'),
(6, 'Lucy Foley'),
(7, 'Gillian Flynn'),
(8, 'Jean Hanff Korelitz'),
(9, 'A. J. Finn'),
(10, 'Jane Austen'),
(11, 'Author Name'); -- For 'The Adventure'

-- Populate `genres`
INSERT INTO `genres` (`genre_id`, `name`) VALUES
(1, 'Dystopian'),
(2, 'Classic'),
(3, 'Fantasy'),
(4, 'Thriller'),
(5, 'Mystery'),
(6, 'Psychological'),
(7, 'Adventure'),
(8, 'Romance'),
(9, 'Contemporary Fiction');

-- -----------------------------------------------------
-- 2. Populate `books` (using your uploaded cover filenames)
-- -----------------------------------------------------
INSERT INTO `books` (`book_id`, `title`, `isbn`, `publisher`, `year_published`, `description`, `cover_url`) VALUES
(1, '1984', '9780451524935', 'Signet Classic', 1950, 'A dystopian social science fiction novel and cautionary tale.', '1984.jpg'),
(2, 'The Hobbit', '9780618260300', 'Houghton Mifflin', 1937, 'A fantasy novel and children''s book.', 'hobbit.jpg'),
(3, 'The Lord of the Rings: The Fellowship of the Ring', '9780618640157', 'Allen & Unwin', 1954, 'The first of three volumes of the epic novel The Lord of the Rings.', 'lord_of_the_rings_fellowship.jpg'),
(4, 'The Great Gatsby', '9780743273565', 'Scribner', 1925, 'A novel about the American dream.', 'the_great_gatsby.jpg'),
(5, 'To Kill a Mockingbird', '9780061120084', 'HarperPerennial', 1960, 'A classic of modern American literature.', 'to_kill_a_mockingbird.jpg'),
(6, 'Big Little Lies', '9780399167065', 'G.P. Putnam''s Sons', 2014, 'A tale of murder and mischief in a tranquil seaside town.', 'big_little_lies.jpg'),
(7, 'The Guest List', '9780062868930', 'William Morrow', 2020, 'A wedding celebration turns dark and deadly in this chilling mystery.', 'the_guest_list.jpg'),
(8, 'Sharp Objects', '9780307341556', 'Crown', 2006, 'A reporter confronts the psychological demons from her past.', 'sharp_objects.jpg'),
(9, 'The Plot', '9781250266938', 'Celadon Books', 2021, 'A once-promising writer steals a story from a deceased former student.', 'the_plot.jpg'),
(10, 'The Woman in the Window', '9780062678416', 'William Morrow', 2018, 'An agoraphobic woman living alone in New York City witnesses something she shouldn''t have.', 'the_woman_in_the_window.jpg'),
(11, 'Pride and Prejudice', '9780141439518', 'Penguin Classics', 1813, 'A classic novel of manners and romance.', 'pride_and_prejudice.jpg'),
(12, 'The Adventure: A Fantasy Time Travel Journey', '9780000000001', 'Stock Images', 2024, 'A fantasy time travel journey by Author Name.', 'the_adventure.jpg');

-- -----------------------------------------------------
-- 3. Populate Junction Tables
-- -----------------------------------------------------

-- Populate `book_authors`
INSERT INTO `book_authors` (`book_id`, `author_id`) VALUES
(1, 1),  -- 1984, George Orwell
(2, 2),  -- The Hobbit, J.R.R. Tolkien
(3, 2),  -- LOTR, J.R.R. Tolkien
(4, 3),  -- The Great Gatsby, F. Scott Fitzgerald
(5, 4),  -- To Kill a Mockingbird, Harper Lee
(6, 5),  -- Big Little Lies, Liane Moriarty
(7, 6),  -- The Guest List, Lucy Foley
(8, 7),  -- Sharp Objects, Gillian Flynn
(9, 8),  -- The Plot, Jean Hanff Korelitz
(10, 9), -- The Woman in the Window, A. J. Finn
(11, 10),-- Pride and Prejudice, Jane Austen
(12, 11);-- The Adventure, Author Name

-- Populate `book_genres`
INSERT INTO `book_genres` (`book_id`, `genre_id`) VALUES
(1, 1),  -- 1984, Dystopian
(1, 2),  -- 1984, Classic
(2, 3),  -- The Hobbit, Fantasy
(2, 7),  -- The Hobbit, Adventure
(3, 3),  -- LOTR, Fantasy
(3, 7),  -- LOTR, Adventure
(4, 2),  -- The Great Gatsby, Classic
(5, 2),  -- To Kill a Mockingbird, Classic
(6, 4),  -- Big Little Lies, Thriller
(6, 5),  -- Big Little Lies, Mystery
(7, 4),  -- The Guest List, Thriller
(7, 5),  -- The Guest List, Mystery
(8, 4),  -- Sharp Objects, Thriller
(8, 6),  -- Sharp Objects, Psychological
(9, 4),  -- The Plot, Thriller
(9, 9),  -- The Plot, Contemporary Fiction
(10, 4), -- The Woman in the Window, Thriller
(10, 6), -- The Woman in the Window, Psychological
(11, 2), -- Pride and Prejudice, Classic
(11, 8), -- Pride and Prejudice, Romance
(12, 3), -- The Adventure, Fantasy
(12, 7); -- The Adventure, Adventure

-- -----------------------------------------------------
-- 4. Populate `book_copies`
-- -----------------------------------------------------
INSERT INTO `book_copies` (`copy_id`, `book_id`, `status`, `shelf_location`) VALUES
(1, 1, 'Available', 'DYS-01-A'),
(2, 1, 'Available', 'DYS-01-B'),
(3, 2, 'Available', 'FAN-01-A'),
(4, 2, 'Overdue', 'FAN-01-B'),   -- For an overdue transaction
(5, 3, 'Available', 'FAN-01-C'),
(6, 3, 'Borrowed', 'FAN-01-D'),  -- For an active transaction
(7, 4, 'Available', 'CLA-01-A'),
(8, 5, 'Available', 'CLA-02-A'),
(9, 6, 'Available', 'THR-01-A'),
(10, 7, 'Available', 'THR-02-A'),
(11, 8, 'Maintenance', 'THR-01-B'),
(12, 9, 'Available', 'THR-03-C'),
(13, 10, 'Available', 'THR-04-A'),
(14, 11, 'Available', 'CLA-03-A'),
(15, 12, 'Available', 'FAN-05-A');

-- -----------------------------------------------------
-- 5. Populate `transactions`
-- -----------------------------------------------------
INSERT INTO `transactions` (`transaction_id`, `account_id`, `copy_id`, `transaction_type`, `date_borrowed`, `date_due`, `date_returned`, `fine`, `status`) VALUES
(1, 2, 6, 'Borrow', NOW() - INTERVAL 5 DAY, NOW() + INTERVAL 9 DAY, NULL, 0.00, 'Active'), -- Borrowed 5 days ago, due in 9 days
(2, 2, 4, 'Borrow', NOW() - INTERVAL 20 DAY, NOW() - INTERVAL 6 DAY, NULL, 6.00, 'Overdue'), -- Borrowed 20 days ago, due 6 days ago (6.00 fine based on settings)
(3, 2, 7, 'Borrow', NOW() - INTERVAL 30 DAY, NOW() - INTERVAL 16 DAY, NOW() - INTERVAL 10 DAY, 0.00, 'Completed'); -- Returned 10 days ago

-- -----------------------------------------------------
-- 6. Populate Supporting Tables
-- -----------------------------------------------------

-- Populate `announcements`
INSERT INTO `announcements` (`admin_id`, `title`, `message`, `priority`) VALUES
(1, 'Welcome to the new imLibrary!', 'The new web-based library system is now live. Please report any issues to the admin desk. Enjoy!', 'High');

-- Populate `favorites`
INSERT INTO `favorites` (`account_id`, `book_id`) VALUES
(2, 1),  -- Alex favorites 1984
(2, 2),  -- Alex favorites The Hobbit
(2, 6);  -- Alex favorites Big Little Lies

-- Populate `logs`
INSERT INTO `logs` (`account_id`, `action`, `details`, `severity`) VALUES
(1, 'Login Success', 'User successfully logged in.', 'Info'),
(2, 'Login Success', 'User successfully logged in.', 'Info'),
(2, 'Book Borrow', 'TransactionID: 1, CopyID: 6', 'Info'),
(2, 'Book Borrow', 'TransactionID: 2, CopyID: 4', 'Info'),
(2, 'Book Return', 'TransactionID: 3, CopyID: 7', 'Info');

-- Populate `notifications`
INSERT INTO `notifications` (`account_id`, `transaction_id`, `message`, `is_read`, `notification_type`) VALUES
(2, 1, 'Reminder: Your book "The Lord of the Rings: The Fellowship of the Ring" is due in 9 days.', 0, 0), -- 0 = BookReminder
(2, 2, 'OVERDUE: Your book "The Hobbit" is 6 days overdue. Please return it as soon as possible.', 0, 1); -- 1 = OverdueReminder

-- -----------------------------------------------------
-- Finalize
-- -----------------------------------------------------
SET foreign_key_checks = 1; -- Re-enable foreign key checks
COMMIT;