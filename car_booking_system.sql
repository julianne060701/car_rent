-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 04:43 AM
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
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `car_id` int(11) NOT NULL,
  `car_name` varchar(100) NOT NULL,
  `brand` varchar(100) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `rate_per_day` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = Available, 0 = Unavailable',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`car_id`, `car_name`, `brand`, `plate_number`, `rate_per_day`, `status`, `created_at`) VALUES
(1, 'Toyota Vios', 'Toyota', 'ABC-1234', 1500.00, 1, '2025-08-15 02:35:21'),
(2, 'Honda Civic', 'Honda', 'XYZ-5678', 2000.00, 0, '2025-08-15 02:35:21'),
(3, 'Mitsubishi Mirage', 'Mitsubishi', 'DEF-3456', 1300.00, 1, '2025-08-15 02:35:21'),
(4, 'Ferrari1', 'Ferrari ', '9877', 900.00, 1, '2025-08-15 02:41:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `employee_id` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` enum('staff','admin','super_admin') DEFAULT 'staff',
  `status` enum('active','inactive','suspended') DEFAULT 'active',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `password_reset_token` varchar(255) DEFAULT NULL,
  `password_reset_expires` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `employee_id`, `department`, `role`, `status`, `profile_image`, `created_at`, `updated_at`, `last_login`, `password_reset_token`, `password_reset_expires`) VALUES
(1, 'admin@carbook.com', '$2y$10$example_hashed_password_admin123', 'System', 'Administrator', NULL, 'EMP001', 'IT', 'admin', 'active', NULL, '2025-08-13 08:24:35', '2025-08-13 08:24:35', NULL, NULL, NULL),
(2, 'manager@carbook.com', '$2y$10$example_hashed_password_manager123', 'Fleet', 'Manager', NULL, 'EMP002', 'Operations', 'admin', 'active', NULL, '2025-08-13 08:24:35', '2025-08-13 08:24:35', NULL, NULL, NULL),
(3, 'staff@carbook.com', '$2y$10$example_hashed_password_staff123', 'John', 'Staff', NULL, 'EMP003', 'Sales', 'staff', 'active', NULL, '2025-08-13 08:24:35', '2025-08-13 08:24:35', NULL, NULL, NULL),
(4, 'supervisor@carbook.com', '$2y$10$example_hashed_password_super123', 'Jane', 'Supervisor', NULL, 'EMP004', 'Marketing', 'staff', 'active', NULL, '2025-08-13 08:24:35', '2025-08-13 08:24:35', NULL, NULL, NULL);

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
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_booking_reference` (`booking_reference`),
  ADD KEY `idx_user_bookings` (`user_id`),
  ADD KEY `idx_vehicle_bookings` (`vehicle_id`),
  ADD KEY `idx_booking_dates` (`start_date`,`end_date`),
  ADD KEY `idx_booking_status` (`status`),
  ADD KEY `idx_pending_approvals` (`status`,`created_at`);

--
-- Indexes for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `idx_booking_history` (`booking_id`),
  ADD KEY `idx_history_timestamp` (`timestamp`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`car_id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `employee_id` (`employee_id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

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
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `booking_history`
--
ALTER TABLE `booking_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `car_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `booking_history`
--
ALTER TABLE `booking_history`
  ADD CONSTRAINT `booking_history_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `vehicle_categories` (`category_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
