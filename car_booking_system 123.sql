-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 22, 2025 at 10:55 AM
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
-- Database: `car_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `car_id` int(11) UNSIGNED DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(11) NOT NULL,
  `customer_email` varchar(50) NOT NULL,
  `license_number` varchar(20) NOT NULL,
  `booking_reference` varchar(20) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `pickup_location` varchar(255) DEFAULT NULL,
  `return_location` varchar(255) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `passengers` int(11) DEFAULT 1,
  `status` enum('pending','approved','rejected','active','completed','cancelled') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT 0.00,
  `mileage_start` int(11) DEFAULT NULL,
  `mileage_end` int(11) DEFAULT NULL,
  `fuel_level_start` decimal(3,1) DEFAULT NULL,
  `fuel_level_end` decimal(3,1) DEFAULT NULL,
  `damage_reported` tinyint(1) DEFAULT 0,
  `damage_notes` text DEFAULT NULL,
  `late_return` tinyint(1) DEFAULT 0,
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `rental_type` enum('hourly','daily') DEFAULT 'daily',
  `rental_duration_hours` int(11) DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT NULL,
  `uploaded_document` varchar(255) DEFAULT NULL,
  `pickup_time` time DEFAULT NULL,
  `return_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `car_id`, `customer_name`, `customer_phone`, `customer_email`, `license_number`, `booking_reference`, `user_id`, `start_date`, `end_date`, `start_time`, `end_time`, `pickup_location`, `return_location`, `purpose`, `passengers`, `status`, `approved_by`, `approved_at`, `rejection_reason`, `total_cost`, `mileage_start`, `mileage_end`, `fuel_level_start`, `fuel_level_end`, `damage_reported`, `damage_notes`, `late_return`, `late_fee`, `created_at`, `updated_at`, `rental_type`, `rental_duration_hours`, `total_hours`, `uploaded_document`, `pickup_time`, `return_time`) VALUES
(7, 8, 'Cleo Long', '+1 (408) 80', 'fusyw@mailinator.com', '844', 'BK20250007', 0, '2025-08-20', '2025-08-20', '08:00:00', '20:00:00', 'robinsons-place', 'sm-city-gensan', 'Eaque quidem quasi e', 1, 'pending', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, 0, NULL, 0, 0.00, '2025-08-19 06:54:07', '2025-08-22 00:35:22', '', 12, 12.00, NULL, '08:00:00', '20:00:00'),
(8, 6, 'Iona Bennett', '+1 (427) 54', 'vezahavog@mailinator.com', '737', 'BK20250007', 0, '2025-08-21', '2025-08-21', '10:00:00', '22:00:00', 'sm-city-gensan', 'kcc-mall', 'Suscipit dolor cum r', 8, 'pending', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, 0, NULL, 0, 0.00, '2025-08-19 06:57:04', '2025-08-22 00:35:40', '', 24, 24.00, NULL, '10:00:00', '22:00:00'),
(13, 16, 'Hermione Lara', '+1 (592) 31', 'ropawo@mailinator.com', '448', 'BK20250008', 0, '2025-08-20', '2025-08-20', '05:00:00', '17:00:00', 'sm-city-gensan', 'gensan-airport', 'Est ipsum quaerat q', 1, 'pending', NULL, NULL, NULL, 0.00, NULL, NULL, NULL, NULL, 0, NULL, 0, 0.00, '2025-08-19 07:54:55', '2025-08-22 00:35:56', '', 24, 24.00, NULL, '05:00:00', '17:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `booking_history`
--

CREATE TABLE `booking_history` (
  `history_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `action` enum('created','approved','rejected','started','completed','cancelled','modified') NOT NULL,
  `performed_by` int(11) NOT NULL,
  `previous_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `car_id` int(11) UNSIGNED NOT NULL,
  `car_name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `rate_per_day` decimal(10,2) NOT NULL,
  `hourly_rate` decimal(10,2) NOT NULL,
  `rate_6h` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rate_8h` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rate_12h` decimal(10,2) NOT NULL DEFAULT 0.00,
  `rate_24h` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '3 = Booked,2= Pending,1 = Available, 0 = unavailable ',
  `car_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `passenger_seater` int(11) DEFAULT NULL,
  `transmission` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `car_name`, `brand`, `plate_number`, `rate_per_day`, `hourly_rate`, `rate_6h`, `rate_8h`, `rate_12h`, `rate_24h`, `status`, `car_image`, `created_at`, `description`, `passenger_seater`, `transmission`) VALUES
(6, 'Toyota Vios 2', 'Toyota', 'ABC-34422', 0.00, 0.00, 300.00, 500.00, 800.00, 1200.00, 1, 'car_1755566496_8916.jpg', '2025-08-18 17:21:36', 'red', 4, 'Automatic'),
(7, 'Ford Ranger', 'Ford', 'ABC-1234', 0.00, 0.00, 500.00, 800.00, 1000.00, 1500.00, 1, 'car_1755566613_1820.jpg', '2025-08-18 17:23:19', NULL, NULL, NULL),
(8, 'Artista Van', 'Toyota', '334', 0.00, 0.00, 96.00, 84.00, 72.00, 42.00, 1, 'car_1755585779_8399.jpg', '2025-08-18 17:28:49', 'red', 4, 'Manual'),
(9, 'Chester Barton', 'In harum accusamus q', '841', 0.00, 0.00, 35.00, 88.00, 64.00, 2000.00, 1, 'car_1755567351_3215.jpg', '2025-08-18 17:35:51', NULL, NULL, NULL),
(10, 'Priscilla Shannon', 'Illum architecto qu', '351', 0.00, 0.00, 48.00, 6.00, 50.00, 21.00, 1, 'car_1755592646_2020.jpg', '2025-08-19 08:37:26', 'Culpa elit maxime v', 2, 'CVT'),
(15, 'Tucker Eaton', 'Repudiandae tempor n', '908', 0.00, 0.00, 60.00, 96.00, 91.00, 59.00, 1, 'car_1755592724_4587.jpg', '2025-08-19 08:38:44', 'Quia fuga Et ex ali', 2, 'CVT'),
(16, 'Trevor Wright', 'Nisi exercitation fu', '784', 0.00, 0.00, 52.00, 27.00, 5.00, 25.00, 1, 'car_1755670207_7623.png', '2025-08-20 06:10:07', 'Qui possimus dicta ', 4, 'CVT'),
(17, 'Bigasan Car', 'Explicabo Fugiat a', '444', 0.00, 0.00, 5.00, 25.00, 60.00, 25.00, 1, 'car_1755671349_8439.png', '2025-08-20 06:29:09', 'Praesentium nostrud ', 5, 'Manual');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','staff') NOT NULL DEFAULT 'staff',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$qCnlLOcpsDT1JgZUh8ZUGuy7Y1eUwbAHjcnOuBdsDn8L9A1kZk88W', 'Nath Kat', 'admin', 'active', '2025-08-15 07:23:26', '2025-08-15 07:23:26'),
(2, 'staff', 'sra@dfds', '$2y$10$nMyqmQP1NUbUDIkrzjvcBeC2zv/pNWkUOIQAzkcCpHO06QDnVegHG', 'Staff', 'staff', 'active', '2025-08-15 07:27:49', '2025-08-15 07:27:49');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `license_plate` varchar(20) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `vin` varchar(17) DEFAULT NULL,
  `fuel_type` enum('gasoline','diesel','electric','hybrid') DEFAULT 'gasoline',
  `transmission` enum('manual','automatic') DEFAULT 'automatic',
  `seats` int(11) DEFAULT 5,
  `mileage` int(11) DEFAULT 0,
  `status` enum('available','booked','maintenance','out_of_service') DEFAULT 'available',
  `location` varchar(255) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `insurance_policy` varchar(100) DEFAULT NULL,
  `insurance_expires` date DEFAULT NULL,
  `registration_expires` date DEFAULT NULL,
  `last_service_date` date DEFAULT NULL,
  `next_service_due` date DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `license_plate`, `make`, `model`, `year`, `color`, `category_id`, `vin`, `fuel_type`, `transmission`, `seats`, `mileage`, `status`, `location`, `features`, `insurance_policy`, `insurance_expires`, `registration_expires`, `last_service_date`, `next_service_due`, `daily_rate`, `image_url`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ABC-1234', 'Toyota', 'Corolla', 2022, 'White', 1, NULL, 'gasoline', 'automatic', 5, 0, 'available', 'Main Office', NULL, NULL, NULL, NULL, NULL, NULL, 45.00, NULL, NULL, '2025-08-13 08:24:36', '2025-08-13 08:24:36'),
(2, 'XYZ-5678', 'Honda', 'Accord', 2023, 'Silver', 2, NULL, 'gasoline', 'automatic', 5, 0, 'available', 'Main Office', NULL, NULL, NULL, NULL, NULL, NULL, 65.00, NULL, NULL, '2025-08-13 08:24:36', '2025-08-13 08:24:36'),
(3, 'SUV-9012', 'Ford', 'Explorer', 2022, 'Black', 3, NULL, 'gasoline', 'automatic', 7, 0, 'available', 'Branch Office', NULL, NULL, NULL, NULL, NULL, NULL, 85.00, NULL, NULL, '2025-08-13 08:24:36', '2025-08-13 08:24:36'),
(4, 'LUX-3456', 'BMW', '3 Series', 2023, 'Blue', 4, NULL, 'gasoline', 'automatic', 5, 0, 'maintenance', 'Service Center', NULL, NULL, NULL, NULL, NULL, NULL, 120.00, NULL, NULL, '2025-08-13 08:24:36', '2025-08-13 08:24:36');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_categories`
--

CREATE TABLE `vehicle_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_categories`
--

INSERT INTO `vehicle_categories` (`category_id`, `category_name`, `description`, `daily_rate`, `created_at`) VALUES
(1, 'Economy', 'Fuel-efficient compact cars', 45.00, '2025-08-13 08:24:35'),
(2, 'Mid-size', 'Comfortable sedan vehicles', 65.00, '2025-08-13 08:24:35'),
(3, 'SUV', 'Sport utility vehicles for larger groups', 85.00, '2025-08-13 08:24:35'),
(4, 'Luxury', 'Premium vehicles with enhanced features', 120.00, '2025-08-13 08:24:35'),
(5, 'Van', 'Large capacity vehicles for groups', 95.00, '2025-08-13 08:24:35'),
(6, 'Economy', 'Fuel-efficient compact cars', 45.00, '2025-08-13 08:24:45'),
(7, 'Mid-size', 'Comfortable sedan vehicles', 65.00, '2025-08-13 08:24:45'),
(8, 'SUV', 'Sport utility vehicles for larger groups', 85.00, '2025-08-13 08:24:45'),
(9, 'Luxury', 'Premium vehicles with enhanced features', 120.00, '2025-08-13 08:24:45'),
(10, 'Van', 'Large capacity vehicles for groups', 95.00, '2025-08-13 08:24:45'),
(11, 'Economy', 'Fuel-efficient compact cars', 45.00, '2025-08-13 08:24:58'),
(12, 'Mid-size', 'Comfortable sedan vehicles', 65.00, '2025-08-13 08:24:58'),
(13, 'SUV', 'Sport utility vehicles for larger groups', 85.00, '2025-08-13 08:24:58'),
(14, 'Luxury', 'Premium vehicles with enhanced features', 120.00, '2025-08-13 08:24:58'),
(15, 'Van', 'Large capacity vehicles for groups', 95.00, '2025-08-13 08:24:58'),
(16, 'Economy', 'Fuel-efficient compact cars', 45.00, '2025-08-13 08:25:22'),
(17, 'Mid-size', 'Comfortable sedan vehicles', 65.00, '2025-08-13 08:25:22'),
(18, 'SUV', 'Sport utility vehicles for larger groups', 85.00, '2025-08-13 08:25:22'),
(19, 'Luxury', 'Premium vehicles with enhanced features', 120.00, '2025-08-13 08:25:22'),
(20, 'Van', 'Large capacity vehicles for groups', 95.00, '2025-08-13 08:25:22'),
(21, 'Economy', 'Fuel-efficient compact cars', 45.00, '2025-08-13 08:25:27'),
(22, 'Mid-size', 'Comfortable sedan vehicles', 65.00, '2025-08-13 08:25:27'),
(23, 'SUV', 'Sport utility vehicles for larger groups', 85.00, '2025-08-13 08:25:27'),
(24, 'Luxury', 'Premium vehicles with enhanced features', 120.00, '2025-08-13 08:25:27'),
(25, 'Van', 'Large capacity vehicles for groups', 95.00, '2025-08-13 08:25:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `fk_bookings_car` (`car_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `license_plate` (`license_plate`),
  ADD UNIQUE KEY `vin` (`vin`),
  ADD KEY `idx_license_plate` (`license_plate`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_availability` (`status`,`location`);

--
-- Indexes for table `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `vehicle_categories`
--
ALTER TABLE `vehicle_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`car_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
