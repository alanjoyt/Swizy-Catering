-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 02, 2026 at 02:06 AM
-- Server version: 8.4.7
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swizy`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `event_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guest_count` int NOT NULL,
  `event_date` date NOT NULL,
  `event_days` int DEFAULT '1',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_id` int DEFAULT NULL,
  `is_custom` tinyint(1) DEFAULT '0',
  `custom_items` json DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `advance_amount` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','advance_paid','fully_paid','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `booking_status` enum('pending','approved','rejected','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `special_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `package_id` (`package_id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `customer_id`, `event_type`, `guest_count`, `event_date`, `event_days`, `location`, `package_id`, `is_custom`, `custom_items`, `total_amount`, `advance_amount`, `payment_status`, `booking_status`, `special_notes`, `created_at`) VALUES
(1, 32, 'Corporate', 500, '2026-05-21', 2, 'Grand Banquet Hall', 5, 0, NULL, 900000.00, 104550.00, 'pending', 'completed', 'Standard event setup', '2026-05-15 10:38:34'),
(2, 32, 'Wedding', 100, '2026-05-21', 1, 'Grand Banquet Hall', 1, 0, NULL, 45000.00, 22500.00, 'refunded', 'cancelled', 'Cancelled upon request', '2026-05-15 11:04:38'),
(3, 32, 'Wedding', 500, '2026-05-21', 1, 'Grand Banquet Hall', 2, 0, NULL, 425000.00, 10625.00, 'refunded', 'cancelled', 'Cancelled upon request', '2026-05-15 11:04:38'),
(4, 35, 'Wedding Reception', 150, '2026-05-26', 1, 'Grand Palace Hall', 1, 0, NULL, 75000.00, 20000.00, 'advance_paid', 'approved', NULL, '2026-05-19 13:19:37'),
(5, 35, 'Birthday Party', 50, '2026-06-02', 1, 'Sunny Garden', 2, 0, NULL, 50000.00, 0.00, 'pending', 'pending', NULL, '2026-05-19 13:19:37'),
(6, 39, 'Anniversary Celebration', 80, '2026-06-08', 1, 'Sea View Hotel', 2, 0, NULL, 80000.00, 20000.00, 'advance_paid', 'approved', NULL, '2026-05-19 14:23:18'),
(7, 40, 'Corporate Event', 300, '2026-07-03', 2, 'Tech Park Hall', 1, 0, NULL, 150000.00, 50000.00, 'advance_paid', 'approved', NULL, '2026-05-19 14:23:18'),
(8, 40, 'Private Dinner', 20, '2026-05-29', 1, 'Villa Rose', 2, 0, NULL, 20000.00, 0.00, 'pending', 'completed', NULL, '2026-05-19 14:23:18'),
(9, 41, 'Baby Shower', 40, '2026-06-18', 1, 'Community Hall', 1, 0, NULL, 20000.00, 5000.00, 'advance_paid', 'completed', NULL, '2026-05-19 14:23:18'),
(10, 40, 'Birthday', 100, '2026-06-11', 1, 'Grand Palace Hall', NULL, 1, '[\"1\", \"2\", \"5\", \"6\"]', 12998.00, 6499.00, 'advance_paid', 'approved', '', '2026-05-29 18:48:13'),
(11, 40, 'Birthday', 100, '2026-06-04', 1, 'Grand Palace Hall', 1, 0, NULL, 45000.00, 22500.00, 'advance_paid', 'approved', '', '2026-05-29 18:48:13'),
(12, 32, 'Wedding', 100, '2026-06-05', 1, 'Grand Banquet Hall', NULL, 1, '[\"1\", \"2\", \"6\"]', 11998.00, 5999.00, 'advance_paid', 'approved', 'Special dietary requirements', '2026-05-30 12:40:40'),
(13, 32, 'Wedding', 10, '2026-06-25', 1, 'Grand Banquet Hall', NULL, 1, '[\"2\", \"3\", \"6\"]', 999.90, 499.95, 'advance_paid', 'approved', 'Special dietary requirements', '2026-05-30 12:40:40');

-- --------------------------------------------------------

--
-- Table structure for table `booking_staff`
--

DROP TABLE IF EXISTS `booking_staff`;
CREATE TABLE IF NOT EXISTS `booking_staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `user_id` int NOT NULL,
  `assigned_role` enum('chef','server','site_manager') COLLATE utf8mb4_unicode_ci NOT NULL,
  `assignment_status` enum('assigned','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'assigned',
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_staff`
--

INSERT INTO `booking_staff` (`id`, `booking_id`, `user_id`, `assigned_role`, `assignment_status`) VALUES
(20, 7, 4, 'chef', 'assigned'),
(19, 6, 12, 'server', 'assigned'),
(18, 6, 3, 'chef', 'assigned'),
(17, 1, 5, 'site_manager', 'completed'),
(16, 1, 4, 'server', 'completed'),
(15, 1, 3, 'chef', 'completed'),
(8, 2, 2, 'chef', 'completed'),
(9, 2, 3, 'chef', 'assigned'),
(10, 2, 7, 'chef', 'assigned'),
(11, 2, 27, 'site_manager', 'assigned'),
(12, 2, 12, 'server', 'assigned'),
(13, 2, 21, 'server', 'assigned'),
(21, 7, 13, 'server', 'assigned'),
(22, 7, 28, 'site_manager', 'assigned'),
(23, 9, 5, 'chef', 'completed'),
(24, 9, 14, 'server', 'completed'),
(25, 8, 2, 'chef', 'completed'),
(26, 8, 12, 'server', 'completed'),
(27, 8, 27, 'site_manager', 'completed'),
(28, 10, 2, 'chef', 'assigned'),
(29, 10, 12, 'server', 'assigned'),
(30, 10, 13, 'server', 'assigned'),
(31, 10, 27, 'site_manager', 'assigned'),
(32, 11, 2, 'chef', 'assigned'),
(33, 11, 12, 'server', 'assigned'),
(34, 11, 13, 'server', 'assigned'),
(35, 11, 27, 'site_manager', 'assigned'),
(36, 12, 2, 'chef', 'assigned'),
(37, 12, 12, 'server', 'assigned'),
(38, 12, 13, 'server', 'assigned'),
(39, 12, 27, 'site_manager', 'assigned'),
(40, 13, 2, 'chef', 'assigned'),
(41, 13, 12, 'server', 'assigned'),
(42, 13, 27, 'site_manager', 'assigned');

-- --------------------------------------------------------



--
-- Table structure for table `items`
--

DROP TABLE IF EXISTS `items`;
CREATE TABLE IF NOT EXISTS `items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('veg','non-veg','both') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_per_head` decimal(10,2) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `category`, `price_per_head`, `description`, `image`, `status`) VALUES
(1, 'Paneer Butter Masala', 'veg', 69.99, 'Rich and creamy curry made with paneer, spices, onions, tomatoes, cashews, and butter.', 'paneer_butter_masala.png', 1),
(2, 'Vegetable Spring Rolls', 'veg', 29.99, 'Crispy deep-fried pastry wrapper filled with a savory mixture of stir-fried vegetables.', 'veg_spring_rolls.png', 1),
(3, 'Chicken Tikka Masala', 'non-veg', 50.00, 'Roasted marinated chicken chunks in a spiced sauce, creamy and orange-coloured.', 'chicken_tikka.png', 1),
(4, 'Butter Chicken', 'non-veg', 30.00, 'Tender chicken pieces cooked in a smooth, rich, and creamy butter-tomato sauce.', 'butter_chicken.png', 1),
(5, 'Assorted Indian Breads', 'both', 10.00, 'A combination of freshly baked hot Butter Naan, Garlic Naan, and Tandoori Roti.', 'naan_roti.png', 1),
(6, 'Gulab Jamun with Ice Cream', 'both', 20.00, 'Warm golden milk-solid dumplings dipped in cardamom sugar syrup, served with a scoop of vanilla ice cream.', 'gulab_jamun_ice_cream.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
CREATE TABLE IF NOT EXISTS `leave_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `leave_date` date NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_requests`
--

INSERT INTO `leave_requests` (`id`, `staff_id`, `leave_date`, `reason`, `status`, `created_at`) VALUES
(1, 33, '2027-03-10', 'hosptial', 'approved', '2026-05-16 14:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
CREATE TABLE IF NOT EXISTS `media` (
  `id` int NOT NULL AUTO_INCREMENT,
  `media_type` enum('image','video') COLLATE utf8mb4_unicode_ci NOT NULL,
  `media_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `section` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `media`
--

INSERT INTO `media` (`id`, `media_type`, `media_path`, `title`, `section`, `status`) VALUES
(1, 'image', 'gallery_1.png', 'Delicious Event Setup', 'gallery', 1),
(2, 'image', 'gallery_2.png', 'Luxurious Banquet Hall', 'gallery', 1),
(3, 'image', 'gallery_3.png', 'Professional Kitchen Prep', 'gallery', 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu_packages`
--

DROP TABLE IF EXISTS `menu_packages`;
CREATE TABLE IF NOT EXISTS `menu_packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_type` enum('normal','premium','luxury') COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('veg','non-veg','both') COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `guest_limit` int DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_items` json NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_packages`
--

INSERT INTO `menu_packages` (`id`, `package_name`, `package_type`, `category`, `price`, `guest_limit`, `image`, `menu_items`, `status`) VALUES
(1, 'Traditional Kerala Sadya', 'normal', 'veg', 450.00, 0, '1778835781_7641.jpg', '[\"Welcome Drink (Sambharam)\", \"Pappadam\", \"Banana Chips\", \"Sarkara Varatti\", \"Inji Curry\", \"Mango Pickle\", \"Olan\", \"Kalan\", \"Erissery\", \"Avial\", \"Thoran\", \"Pachadi\", \"Rice\", \"Parippu\", \"Sambar\", \"Rasam\", \"Pulissery\", \"Payasam (Palada & Parippu)\"]', 1),
(2, 'Malabar Non-Veg Feast', 'premium', 'non-veg', 850.00, 0, '1778835783_5788.jpg', '[\"Kulukki Sarbath\", \"Chicken Kondattam\", \"Kallummakkaya Roast\", \"Thalassery Chicken Biriyani\", \"Malabar Parotta\", \"Beef Roast\", \"Mutton Curry\", \"Fish Molly\", \"Sulaimani\", \"Mutta Mala\"]', 1),
(3, 'Kerala Christian Wedding Menu', 'luxury', 'both', 1200.00, 0, '1778835783_9707.jpg', '[\"Grape Wine & Grape Juice\", \"Cutlet with Onion Salad\", \"Appam\", \"Mutton Stew\", \"Pork Vindaloo\", \"Fish Moilee\", \"Beef Fry\", \"Chicken Roast\", \"Ghee Rice\", \"Vattayappam\", \"Fruit Salad with Ice Cream\"]', 1),
(4, 'Travancore Royal Banquet', 'luxury', 'both', 1500.00, 0, '1778835784_4443.jpg', '[\"Tender Coconut Water\", \"Karimeen Pollichathu\", \"Duck Roast\", \"Prawns Mango Curry\", \"Kallappam\", \"Idiyappam\", \"Mutton Biriyani\", \"Chicken Ularthiyathu\", \"Palada Pradhaman\", \"Ada Pradhaman\"]', 1),
(5, 'Kuttanadan Special', 'premium', 'non-veg', 900.00, 0, '1778835785_4697.jpg', '[\"Lime Juice\", \"Tapioca (Kappa)\", \"Karimeen Fry\", \"Meen Curry (Kudampuli)\", \"Crab Roast\", \"Duck Curry\", \"Boiled Rice\", \"Pulissery\", \"Semiya Payasam\"]', 1),
(6, 'Simple Veg Buffet', 'normal', 'veg', 350.00, 0, '1778835786_4658.jpg', '[\"Fresh Juice\", \"Veg Cutlet\", \"Gobi Manchurian\", \"Veg Fried Rice\", \"Paneer Butter Masala\", \"Naan / Roti\", \"Dal Fry\", \"Salad\", \"Gulab Jamun\"]', 1),
(7, 'Classic Delight', 'normal', 'both', 500.00, 0, 'tasty.mp4', '[\"Chicken Curry\", \"Paneer Butter Masala\", \"Naan\", \"Rice\", \"Gulab Jamun\"]', 1),
(8, 'Royal Feast', 'premium', 'non-veg', 1000.00, 0, 'tasty.mp4', '[\"Mutton Biryani\", \"Fish Fry\", \"Chicken Tikka\", \"Ice Cream\"]', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `review` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `booking_id` (`booking_id`)
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `booking_id`, `user_id`, `rating`, `review`, `image`, `status`) VALUES
(1, NULL, 35, 5, 'Absolutely fantastic service! The food was delicious and the staff were very professional.', NULL, 1),
(2, NULL, 35, 4, 'Great experience overall. The booking process was smooth and easy.', NULL, 1),
(3, NULL, 39, 5, 'The Anniversary Celebration was perfect! The food was extraordinary and the presentation was top-notch.', NULL, 1),
(4, NULL, 40, 5, 'Swizy handled our corporate event flawlessly. All 300 guests were impressed by the Royal Feast menu.', NULL, 1),
(5, NULL, 41, 4, 'Really good service and tasty food for my event. The staff was very attentive.', NULL, 1),
(6, 1, 32, 4, 'Good work.', NULL, 1),
(7, 8, 40, 5, 'Amazing service and great food!', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role` enum('admin','customer','chef','server','site_manager') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default.png',
  `availability_status` tinyint(1) DEFAULT '1',
  `active_status` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role`, `name`, `email`, `phone`, `password`, `profile_image`, `availability_status`, `active_status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'Admin Swizy', 'admin@swizy.com', '1234567890', '$2y$10$peMGsdQlzWdYej8PTepP0uo4ywPgQN6L6vN.uJ9ha2kzf/C0/zCYm', 'default.png', 1, 1, '2026-05-15 08:17:01', '2026-05-15 08:57:45'),
(2, 'chef', 'Chef Kerala 1', 'chef1@swizy.com', '9876543211', '$2y$10$hVzvaepkgP0/jaQ7LlEfKOGotZrwDLavnUmFLFBRsrE4Vtktvyht2', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-25 04:51:47'),
(3, 'chef', 'Chef Kerala 2', 'chef2@swizy.com', '9876543212', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(4, 'chef', 'Chef Kerala 3', 'chef3@swizy.com', '9876543213', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(5, 'chef', 'Chef Kerala 4', 'chef4@swizy.com', '9876543214', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(6, 'chef', 'Chef Kerala 5', 'chef5@swizy.com', '9876543215', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(7, 'chef', 'Chef Kerala 6', 'chef6@swizy.com', '9876543216', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(8, 'chef', 'Chef Kerala 7', 'chef7@swizy.com', '9876543217', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(9, 'chef', 'Chef Kerala 8', 'chef8@swizy.com', '9876543218', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(10, 'chef', 'Chef Kerala 9', 'chef9@swizy.com', '9876543219', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(11, 'chef', 'Chef Kerala 10', 'chef10@swizy.com', '98765432110', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(12, 'server', 'Server 1', 'server1@swizy.com', '8765432101', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(13, 'server', 'Server 2', 'server2@swizy.com', '8765432102', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(14, 'server', 'Server 3', 'server3@swizy.com', '8765432103', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(15, 'server', 'Server 4', 'server4@swizy.com', '8765432104', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(16, 'server', 'Server 5', 'server5@swizy.com', '8765432105', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(17, 'server', 'Server 6', 'server6@swizy.com', '8765432106', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(18, 'server', 'Server 7', 'server7@swizy.com', '8765432107', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(19, 'server', 'Server 8', 'server8@swizy.com', '8765432108', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(20, 'server', 'Server 9', 'server9@swizy.com', '8765432109', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(21, 'server', 'Server 10', 'server10@swizy.com', '87654321010', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(22, 'server', 'Server 11', 'server11@swizy.com', '87654321011', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(23, 'server', 'Server 12', 'server12@swizy.com', '87654321012', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(24, 'server', 'Server 13', 'server13@swizy.com', '87654321013', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(25, 'server', 'Server 14', 'server14@swizy.com', '87654321014', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(26, 'server', 'Server 15', 'server15@swizy.com', '87654321015', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(27, 'site_manager', 'Manager 1', 'manager1@swizy.com', '7654321091', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(28, 'site_manager', 'Manager 2', 'manager2@swizy.com', '7654321092', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(29, 'site_manager', 'Manager 3', 'manager3@swizy.com', '7654321093', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(30, 'site_manager', 'Manager 4', 'manager4@swizy.com', '7654321094', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(31, 'site_manager', 'Manager 5', 'manager5@swizy.com', '7654321095', '$2y$10$X5f2/fSG340DCsOaPjctzuLDLY.sAh0QiQH7im616y82fUZckPVTS', 'default.png', 1, 1, '2026-05-15 09:03:01', '2026-05-15 09:03:01'),
(32, 'customer', 'Demo Customer', 'customer@example.com', '9876543230', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-15 10:10:16', '2026-05-15 12:12:50'),
(33, 'chef', 'Demo Chef', 'chef@example.com', '9876543231', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-15 12:04:13', '2026-05-15 12:04:13'),
(34, 'server', 'Demo Server', 'server@example.com', '9876543232', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-18 05:15:35', '2026-05-18 05:15:35'),
(35, 'customer', 'John Doe', 'john@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-19 13:19:37', '2026-05-19 13:19:37'),
(36, 'chef', 'Gordon Ramsay', 'gordon@swizy.com', '9876543211', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-19 13:19:37', '2026-05-19 13:19:37'),
(37, 'server', 'Alice Smith', 'alice@swizy.com', '9876543212', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-19 13:19:37', '2026-05-19 13:19:37'),
(38, 'site_manager', 'Bob Builder', 'bob@swizy.com', '9876543213', '$2y$10$PuKZPaIsmg0dumxFry2IQup7jhpr/KXbzzkC797cjOvVb8Ioy84fK', 'default.png', 1, 1, '2026-05-19 13:19:37', '2026-05-22 14:46:06'),
(39, 'customer', 'Emma Watson', 'emma@example.com', '9876543220', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-19 14:23:18', '2026-05-19 14:23:18'),
(40, 'customer', 'Robert Downey', 'robert@example.com', '9876543221', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-19 14:23:18', '2026-05-19 14:23:18'),
(41, 'customer', 'Scarlett Johansson', 'scarlett@example.com', '9876543222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'default.png', 1, 1, '2026-05-19 14:23:18', '2026-05-19 14:23:18');

-- --------------------------------------------------------

--
-- Table structure for table `blocked_dates`
--

DROP TABLE IF EXISTS `blocked_dates`;
CREATE TABLE IF NOT EXISTS `blocked_dates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `blocked_date` date NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blocked_date` (`blocked_date`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
