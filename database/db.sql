-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: sdb-71.hosting.stackcp.net
-- Generation Time: Jun 11, 2026 at 02:22 PM
-- Server version: 10.11.18-MariaDB-log
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gadgethub-35303539733d`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin','staff') NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `username`, `email`, `password`, `role`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'Admin', 'gadgetadmin', 'admin@genex.lk', '$2y$10$ikGhs42kqJs2VArCPY4ss.fpNcLIaeh.WnV3su.d8Acgofr7C0/KG', 'superadmin', 1, '2026-06-11 12:15:09', '2026-05-20 07:14:45');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `description` text DEFAULT NULL,
  `filter_tags` varchar(255) NOT NULL DEFAULT 'other',
  `logo_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `slug`, `description`, `filter_tags`, `logo_path`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'AMD', 'amd', 'Genuine AMD products available in store.', 'Processor', 'images/brands/amd.png', 0, 1, '2026-05-27 13:36:38', '2026-05-28 10:41:50'),
(2, 'Corsair', 'corsair', 'Genuine Corsair products available in store.', 'other', 'images/brands/corsair.png', 1, 1, '2026-05-27 13:36:38', '2026-05-27 13:36:38'),
(3, 'GIGABYTE', 'gigabyte', 'Genuine GIGABYTE products available in store.', 'other', NULL, 2, 1, '2026-05-27 13:36:38', '2026-05-27 13:36:38'),
(4, 'LG', 'lg', 'Genuine LG products available in store.', 'other', 'images/brands/lg.png', 3, 1, '2026-05-27 13:36:38', '2026-05-27 13:36:38'),
(5, 'Logitech', 'logitech', 'Genuine Logitech products available in store.', 'other', 'images/brands/logitech.png', 4, 1, '2026-05-27 13:36:38', '2026-05-27 13:36:38'),
(6, 'MSI', 'msi', 'Genuine MSI products available in store.', 'other', 'images/brands/msi.png', 5, 1, '2026-05-27 13:36:38', '2026-05-27 13:36:38'),
(8, 'Samsung', 'samsung', 'Genuine Samsung products available in store.', 'other', 'images/brands/samsung.png', 7, 1, '2026-05-27 13:36:38', '2026-05-27 13:36:38'),
(10, 'Dahua Technology', 'dahua-technology', NULL, 'Camera', 'uploads/brands/img_6a218cfb342093.36910877.png', 0, 1, '2026-06-04 14:34:35', '2026-06-04 14:34:35'),
(11, 'HIKVISION', 'hikvision', NULL, 'other', 'uploads/brands/img_6a269fe94d4104.15610565.png', 10, 1, '2026-06-08 10:55:19', '2026-06-08 10:56:41');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(100) NOT NULL DEFAULT 'fas fa-box',
  `description` varchar(255) DEFAULT NULL,
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `parent_id`, `sort_order`, `is_active`, `created_at`) VALUES
(16, 'Mobile Accessories', 'mobile-accessories', 'fas fa-mobile-alt', 'Covers, chargers and protectors', NULL, 1, 1, '2026-06-04 14:29:02'),
(17, 'Computer Accessories', 'computer-accessories', 'fas fa-laptop', 'Essential computer add-ons', NULL, 2, 1, '2026-06-04 14:29:15'),
(18, 'Audio Accessories', 'audio-accessories', 'fas fa-headphones', 'Earphones, speakers and earbuds', NULL, 3, 1, '2026-06-04 14:29:34'),
(19, 'Smart Gadgets', 'smart-gadgets', 'fas fa-clock', 'Modern useful tech items', NULL, 4, 1, '2026-06-04 14:29:50'),
(20, 'CCTV & Security', 'cctv-security', 'fas fa-video', 'Cameras and security solutions', NULL, 5, 1, '2026-06-04 14:30:02'),
(21, 'Home Appliances', 'home-appliances', 'fas fa-blender', 'Useful home electronic items', NULL, 6, 1, '2026-06-04 14:30:13');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `created_at`) VALUES
(1, 'gjg', '567567', 'yghj@sdf.sdf', '2026-05-28 09:32:53'),
(2, 'Tharindu Janaka', '0718456999', 'asseminate@gmail.com', '2026-06-04 11:31:44'),
(3, 'dfgdf43', '34534', NULL, '2026-06-04 11:32:15'),
(42, 'sdf', 'sdf', NULL, '2026-06-11 11:59:43'),
(44, 'dfg', 'dfg', NULL, '2026-06-11 12:14:07');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_rates`
--

CREATE TABLE `delivery_rates` (
  `id` int(11) NOT NULL,
  `district` varchar(120) NOT NULL,
  `first_kg_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `additional_kg_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_rates`
--

INSERT INTO `delivery_rates` (`id`, `district`, `first_kg_fee`, `additional_kg_fee`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Ampara', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(2, 'Anuradhapura', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(3, 'Badulla', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(4, 'Batticaloa', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(5, 'Colombo', 350.00, 100.00, 1, '2026-05-28 12:58:15', '2026-05-28 13:00:16'),
(6, 'Galle', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(7, 'Gampaha', 400.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 13:00:16'),
(8, 'Hambantota', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(9, 'Jaffna', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(10, 'Kalutara', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(11, 'Kandy', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(12, 'Kegalle', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(13, 'Kilinochchi', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(14, 'Kurunegala', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(15, 'Mannar', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(16, 'Matale', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(17, 'Matara', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(18, 'Monaragala', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(19, 'Mullaitivu', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(20, 'Nuwara Eliya', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(21, 'Polonnaruwa', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(22, 'Puttalam', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(23, 'Ratnapura', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(24, 'Trincomalee', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41'),
(25, 'Vavuniya', 450.00, 150.00, 1, '2026-05-28 12:58:15', '2026-05-28 12:59:41');

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `desktop_image` varchar(255) NOT NULL,
  `mobile_image` varchar(255) NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `desktop_image`, `mobile_image`, `link_url`, `open_in_new_tab`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(5, 'uploads/hero/img_6a22b2c55f1f86.85825776.jpg', 'uploads/hero/img_6a22b31ca731e6.67771640.jpg', '/shop.php', 0, 0, 1, '2026-06-05 08:45:02', '2026-06-05 11:29:32');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(150) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_number` varchar(30) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_phone_alt` varchar(50) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_city` varchar(120) DEFAULT NULL,
  `customer_district` varchar(120) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`items_json`)),
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `delivery_charge` decimal(12,2) NOT NULL DEFAULT 0.00,
  `handling_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','confirmed','processing','dispatched','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `source` enum('whatsapp','website','instore') NOT NULL DEFAULT 'whatsapp',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'pending',
  `payment_meta` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_phone`, `customer_phone_alt`, `customer_email`, `customer_city`, `customer_district`, `customer_address`, `items_json`, `subtotal`, `delivery_charge`, `handling_fee`, `total`, `status`, `source`, `payment_method`, `payment_status`, `payment_meta`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'GNX-2026-00001', 'gjg', '567567', NULL, 'yghj@sdf.sdf', NULL, NULL, 'sdfd sf s', '[{\"name\":\"Corsair Vengeance LPX 32GB DDR4\",\"category\":\"ram\",\"price\":22000,\"qty\":1}]', 22000.00, 0.00, 0.00, 22000.00, 'cancelled', 'website', NULL, 'pending', NULL, 'sdf sdf', '2026-05-28 09:32:53', '2026-06-04 14:27:08'),
(2, 'GNX-2026-00002', 'Tharindu', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '135/1, Etikehelgalla, Weliveriya\nCity: Kaduwela\nDistrict: Matara', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 450.00, 0.00, 2950.00, 'cancelled', 'website', 'cod', 'pending', NULL, 'Alternate Phone: 0718456999', '2026-06-04 11:31:44', '2026-06-04 14:27:07'),
(3, 'GNX-2026-00003', 'dfgdf43', '34534', NULL, NULL, NULL, NULL, 'sdfsd\nCity: dfgg\nDistrict: Badulla', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 450.00, 0.00, 2950.00, 'cancelled', 'website', 'cod', 'pending', NULL, 'sdfsdf\nAlternate Phone: 435345', '2026-06-04 11:32:15', '2026-06-04 14:27:06'),
(4, 'GNX-2026-00004', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 350.00, 0.00, 2850.00, 'cancelled', 'website', 'cod', 'pending', NULL, 'Test Order Notes\nAlternate Phone: 0762601419', '2026-06-04 11:33:28', '2026-06-04 14:27:02'),
(5, 'GNX-2026-00005', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 350.00, 0.00, 2850.00, 'cancelled', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1}', 'Alternate Phone: 0762601419', '2026-06-04 11:33:42', '2026-06-04 14:27:01'),
(6, 'GNX-2026-00006', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":2}]', 5000.00, 0.00, 0.00, 5000.00, 'cancelled', 'website', 'payhere', 'paid', '{\"merchant_id\":\"1234636\",\"sandbox\":1}', 'Alternate Phone: 0762601419', '2026-06-04 11:36:42', '2026-06-04 14:27:00'),
(7, 'GNX-2026-00007', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":2}]', 5000.00, 0.00, 0.00, 5000.00, 'cancelled', 'website', 'bank_transfer', 'awaiting_payment', '{\"bank_name\":\"Commercial Bank\",\"account_name\":\"KTJ Kumara\",\"account_number\":\"1228009159\",\"branch\":\"Weliveriya\"}', 'Alternate Phone: 0762601419', '2026-06-04 11:39:27', '2026-06-04 14:26:58'),
(8, 'GNX-2026-00008', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 350.00, 0.00, 2850.00, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:07:44', '2026-06-04 14:26:57'),
(9, 'GNX-2026-00009', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1},{\"product_id\":1,\"name\":\"AMD Ryzen 5 5600X\",\"category\":\"ghjh\",\"price\":1000,\"weight_kg\":0.1,\"qty\":1}]', 3500.00, 350.00, 0.00, 3850.00, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:12:51', '2026-06-04 14:26:56'),
(10, 'GNX-2026-00010', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1},{\"product_id\":1,\"name\":\"AMD Ryzen 5 5600X\",\"category\":\"ghjh\",\"price\":1000,\"weight_kg\":0.1,\"qty\":1}]', 3500.00, 350.00, 0.00, 3850.00, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:17:43', '2026-06-04 14:26:55'),
(11, 'GNX-2026-00011', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 350.00, 0.00, 2850.00, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:18:06', '2026-06-04 14:26:52'),
(12, 'GNX-2026-00012', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 350.00, 0.00, 2850.00, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:18:30', '2026-06-04 14:26:51'),
(13, 'GNX-2026-00013', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":2}]', 5000.00, 0.00, 0.00, 5000.00, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"b615804a711bde1fe9bc635e6cabc37a\",\"sandbox\":0,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"koko_order_view\":{\"status\":\"OK\",\"transaction_id\":\"\",\"description\":\"\",\"signature\":\"\",\"signature_verified\":0,\"checked_at\":\"2026-06-04T17:51:22+05:30\",\"http_status_code\":200}}', 'Alternate Phone: 0762601419', '2026-06-04 12:20:19', '2026-06-04 14:26:50'),
(14, 'GNX-2026-00014', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":2}]', 5000.00, 0.00, 0.00, 5000.00, 'cancelled', 'website', 'bank_transfer', 'awaiting_payment', '{\"bank_name\":\"Commercial Bank\",\"account_name\":\"KTJ Kumara\",\"account_number\":\"1228009159\",\"branch\":\"Weliveriya\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:40:59', '2026-06-04 14:26:49'),
(15, 'GNX-2026-00015', 'Test Tharindu Janaka', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, '187/2, Ghanawimala Mawatha,\nHewagama, Kaduwela.\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":11,\"name\":\"TEsting Product\",\"category\":\"processors\",\"price\":2500,\"weight_kg\":0.75,\"qty\":1}]', 2500.00, 350.00, 0.00, 2850.00, 'cancelled', 'website', 'bank_transfer', 'awaiting_payment', '{\"bank_name\":\"Commercial Bank\",\"account_name\":\"KTJ Kumara\",\"account_number\":\"1228009159\",\"branch\":\"Weliveriya\"}', 'Alternate Phone: 0762601419', '2026-06-04 12:45:37', '2026-06-04 14:26:48'),
(16, 'GNX-2026-00016', 'LENOVO IP1-15AMN7 RYZEN 3 7320U|4GB|128SSD|W11', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":14,\"name\":\"LENOVO IP1-15AMN7 RYZEN 3 7320U|4GB|128SSD|W11\",\"category\":\"computer-accessories\",\"price\":276000,\"weight_kg\":2.6000000000000001,\"free_delivery\":0,\"qty\":1}]', 276000.00, 0.00, 0.00, 276000.00, 'cancelled', 'website', 'cod', 'pending', NULL, 'No Special Notes\nAlternate Phone: 0762601419', '2026-06-06 04:00:13', '2026-06-11 05:40:59'),
(17, 'GNX-2026-00017', 'CCTV CAMERA - DAHUA DH-HAC-HDW1801TLP-0360 4K HDCVI IR EYEBALL', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":14,\"name\":\"LENOVO IP1-15AMN7 RYZEN 3 7320U|4GB|128SSD|W11\",\"category\":\"computer-accessories\",\"price\":276000,\"weight_kg\":2.6000000000000001,\"free_delivery\":0,\"qty\":1},{\"product_id\":12,\"name\":\"CCTV CAMERA - DAHUA DH-HAC-HDW1801TLP-0360 4K HDCVI IR EYEBALL\",\"category\":\"mobile-accessories\",\"price\":9400,\"weight_kg\":0.25,\"free_delivery\":0,\"qty\":1}]', 285400.00, 0.00, 0.00, 285400.00, 'cancelled', 'website', 'bank_transfer', 'awaiting_payment', '{\"bank_name\":\"Commercial Bank\",\"account_name\":\"KTJ Kumara\",\"account_number\":\"1228009159\",\"branch\":\"Weliveriya\",\"instructions\":\"මුදල් ගෙවීමේදී ඔබගේ Order ID එක හෝ Order දැමීමේදී ඇතුළත් කළ දුරකතන අංකය යොදා මුදල් තැන්පත් කරන්න. තැන්පත් කළ රිසිට්පත අපගේ Whatsapp අංකයට එවන්න.\"}', 'Alternate Phone: 0762601419', '2026-06-06 04:02:16', '2026-06-11 05:40:56'),
(18, 'GNX-2026-00018', 'CCTV CAMERA - DAHUA DH-HAC-HDW1801TLP-0360 4K HDCVI IR EYEBALL', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":14,\"name\":\"LENOVO IP1-15AMN7 RYZEN 3 7320U|4GB|128SSD|W11\",\"category\":\"Computer Accessories\",\"price\":276000,\"weight_kg\":2.6000000000000001,\"free_delivery\":0,\"qty\":1},{\"product_id\":13,\"name\":\"SPEAKERS - CREATIVE INSPIRE T10\",\"category\":\"Audio Accessories\",\"price\":8100,\"weight_kg\":0.59999999999999998,\"free_delivery\":1,\"qty\":1},{\"product_id\":12,\"name\":\"CCTV CAMERA - DAHUA DH-HAC-HDW1801TLP-0360 4K HDCVI IR EYEBALL\",\"category\":\"Mobile Accessories\",\"price\":9400,\"weight_kg\":0.25,\"free_delivery\":0,\"qty\":1}]', 293500.00, 0.00, 8805.00, 302305.00, 'cancelled', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":3}', 'Alternate Phone: 0762601419', '2026-06-06 04:03:36', '2026-06-11 05:40:55'),
(19, 'GNX-2026-00019', 'Product Name', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":15,\"name\":\"Product Name\",\"category\":\"mobile-accessories\",\"price\":2500,\"weight_kg\":0.5,\"free_delivery\":1,\"qty\":1}]', 2500.00, 0.00, 0.00, 2500.00, 'cancelled', 'website', 'whatsapp', 'awaiting_payment', NULL, 'Alternate Phone: 0762601419', '2026-06-06 06:25:01', '2026-06-11 05:40:53'),
(20, 'GNX-2026-00020', '5 Lens 15MP 4G PTZ Outdoor Camera', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":52,\"name\":\"5 Lens 15MP 4G PTZ Outdoor Camera\",\"category\":\"CCTV &amp; Security\",\"price\":19990,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 19990.00, 0.00, 2398.80, 22388.80, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":0,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12}', 'Alternate Phone: 0762601419', '2026-06-11 03:44:52', '2026-06-11 05:40:51'),
(21, 'GNX-2026-00021', '5 Lens 15MP 4G PTZ Outdoor Camera', '0718456999', NULL, 'asseminate@gmail.com', NULL, NULL, 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":52,\"name\":\"5 Lens 15MP 4G PTZ Outdoor Camera\",\"category\":\"CCTV &amp; Security\",\"price\":19990,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 19990.00, 0.00, 2398.80, 22388.80, 'cancelled', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":0,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12}', 'Alternate Phone: 0762601419', '2026-06-11 03:45:57', '2026-06-11 05:40:50'),
(22, 'GNX-2026-00022', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":52,\"name\":\"5 Lens 15MP 4G PTZ Outdoor Camera\",\"category\":\"CCTV &amp; Security\",\"price\":19990,\"weight_kg\":0,\"free_delivery\":0,\"qty\":2}]', 39980.00, 0.00, 4797.60, 44777.60, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":0,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12}', 'Alternate Phone: 0762601419', '2026-06-11 05:42:09', '2026-06-11 05:42:09'),
(23, 'GNX-2026-00023', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":52,\"name\":\"5 Lens 15MP 4G PTZ Outdoor Camera\",\"category\":\"CCTV &amp; Security\",\"price\":19990,\"weight_kg\":0,\"free_delivery\":0,\"qty\":2}]', 39980.00, 0.00, 4797.60, 44777.60, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12,\"koko_return\":{\"transaction_id\":\"fecfe3bee170566f7adb866bdc4da584\",\"status\":\"SUCCESS\",\"returned_at\":\"2026-06-11T11:46:27+05:30\"},\"koko_order_view\":{\"status\":\"OK\",\"transaction_id\":\"\",\"description\":\"\",\"signature\":\"\",\"signature_verified\":0,\"checked_at\":\"2026-06-11T11:47:34+05:30\",\"http_status_code\":200}}', 'Alternate Phone: 0762601419', '2026-06-11 05:56:18', '2026-06-11 06:17:34'),
(24, 'GNX-2026-00024', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":52,\"name\":\"5 Lens 15MP 4G PTZ Outdoor Camera\",\"category\":\"CCTV &amp; Security\",\"price\":19990,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 19990.00, 0.00, 2398.80, 22388.80, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12,\"koko_return\":{\"transaction_id\":\"edc54108768066654ea76fdbf7e0e183\",\"status\":\"SUCCESS\",\"returned_at\":\"2026-06-11T11:49:02+05:30\"},\"koko_order_view\":{\"status\":\"OK\",\"transaction_id\":\"\",\"description\":\"\",\"signature\":\"\",\"signature_verified\":0,\"checked_at\":\"2026-06-11T12:16:04+05:30\",\"http_status_code\":200}}', 'Alternate Phone: 0762601419', '2026-06-11 06:18:16', '2026-06-11 06:46:04'),
(25, 'GNX-2026-00025', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":52,\"name\":\"5 Lens 15MP 4G PTZ Outdoor Camera\",\"category\":\"CCTV &amp; Security\",\"price\":19990,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 19990.00, 0.00, 2398.80, 22388.80, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12,\"koko_order_view\":{\"status\":\"OK\",\"transaction_id\":\"\",\"description\":\"\",\"signature\":\"\",\"signature_verified\":0,\"checked_at\":\"2026-06-11T11:54:37+05:30\",\"http_status_code\":200}}', 'Alternate Phone: 0762601419', '2026-06-11 06:20:42', '2026-06-11 06:24:37'),
(26, 'GNX-2026-00026', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 350.00, 132.00, 1232.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":0,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12}', 'Alternate Phone: 0762601419', '2026-06-11 06:28:35', '2026-06-11 06:28:35'),
(27, 'GNX-2026-00027', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 350.00, 132.00, 1232.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12,\"koko_order_view\":{\"status\":\"OK\",\"transaction_id\":\"\",\"description\":\"\",\"signature\":\"\",\"signature_verified\":0,\"checked_at\":\"2026-06-11T12:16:20+05:30\",\"http_status_code\":200}}', 'Alternate Phone: 0762601419', '2026-06-11 06:29:00', '2026-06-11 06:46:20'),
(28, 'GNX-2026-00028', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":2}]', 1500.00, 350.00, 222.00, 2072.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12}', 'Alternate Phone: 0762601419', '2026-06-11 06:46:46', '2026-06-11 06:46:46'),
(29, 'GNX-2026-00029', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":2},{\"product_id\":47,\"name\":\"4K HDMI Cable 3M\",\"category\":\"computer-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":2}]', 3000.00, 350.00, 402.00, 3752.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12}', 'Alternate Phone: 0762601419', '2026-06-11 09:18:56', '2026-06-11 09:18:56'),
(30, 'GNX-2026-00030', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 350.00, 132.00, 1232.00, 'pending', 'website', 'koko', 'paid', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":12,\"koko_return\":{\"transaction_id\":\"22ce9b96c154f1eb430144bb7cbee75b\",\"status\":\"SUCCESS\",\"returned_at\":\"2026-06-11T14:52:15+05:30\"},\"koko_order_view\":{\"status\":\"OK\",\"transaction_id\":\"\",\"description\":\"\",\"signature\":\"\",\"signature_verified\":0,\"checked_at\":\"2026-06-11T14:59:48+05:30\",\"http_status_code\":200}}', 'Alternate Phone: 0762601419', '2026-06-11 09:21:38', '2026-06-11 09:58:35'),
(31, 'GNX-2026-00031', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 350.00, 0.00, 1100.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":0}', 'Alternate Phone: 0762601419', '2026-06-11 10:30:13', '2026-06-11 10:30:13'),
(32, 'GNX-2026-00032', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 350.00, 0.00, 1100.00, 'pending', 'website', 'koko', 'paid', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":0,\"koko_return\":{\"transaction_id\":\"9332a1d18af30b1a0b7b52b1ad20f8c6\",\"status\":\"SUCCESS\",\"returned_at\":\"2026-06-11T16:02:20+05:30\"}}', 'Alternate Phone: 0762601419', '2026-06-11 10:30:40', '2026-06-11 10:32:20'),
(33, 'GNX-2026-00033', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":0}', 'Alternate Phone: 0762601419', '2026-06-11 10:33:27', '2026-06-11 10:33:27'),
(34, 'GNX-2026-00034', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0}', 'Alternate Phone: 0762601419', '2026-06-11 10:34:24', '2026-06-11 10:34:24'),
(35, 'GNX-2026-00035', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0}', 'Alternate Phone: 0762601419', '2026-06-11 10:37:34', '2026-06-11 10:37:34'),
(36, 'GNX-2026-00036', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'cancelled', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0,\"payhere_cancel\":{\"cancelled_at\":\"2026-06-11T16:39:59+05:30\"}}', 'Alternate Phone: 0762601419', '2026-06-11 10:41:21', '2026-06-11 11:09:59'),
(37, 'GNX-2026-00037', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0,\"payhere_cancel\":{\"cancelled_at\":\"2026-06-11T16:50:37+05:30\"},\"retry_count\":3,\"last_retry_at\":\"2026-06-11T16:52:02+05:30\",\"last_retry_gateway\":\"payhere\"}', 'Alternate Phone: 0762601419', '2026-06-11 11:10:24', '2026-06-11 11:22:02'),
(38, 'GNX-2026-00038', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0}', 'Alternate Phone: 0762601419', '2026-06-11 11:35:30', '2026-06-11 11:35:30'),
(39, 'GNX-2026-00039', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0,\"retry_count\":1,\"last_retry_at\":\"2026-06-11T17:19:43+05:30\",\"last_retry_gateway\":\"payhere\"}', 'Alternate Phone: 0762601419', '2026-06-11 11:39:02', '2026-06-11 11:49:43'),
(40, 'GNX-2026-00040', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'koko', 'paid', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":0,\"koko_return\":{\"transaction_id\":\"f87e14e4bab2094734407a4070746e78\",\"status\":\"SUCCESS\",\"returned_at\":\"2026-06-11T17:21:08+05:30\"}}', 'Alternate Phone: 0762601419', '2026-06-11 11:50:38', '2026-06-11 11:51:08'),
(41, 'GNX-2026-00041', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0,\"retry_count\":1,\"last_retry_at\":\"2026-06-11T17:28:47+05:30\",\"last_retry_gateway\":\"payhere\"}', 'Alternate Phone: 0762601419', '2026-06-11 11:51:50', '2026-06-11 11:58:47'),
(42, 'GNX-2026-00042', 'sdf', 'sdf', 'sdf', NULL, 'sdf', 'Anuradhapura', 'sdf\nCity: sdf\nDistrict: Anuradhapura', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"CCTV &amp; Security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0}', 'Alternate Phone: sdf', '2026-06-11 11:59:43', '2026-06-11 11:59:43'),
(43, 'GNX-2026-00043', 'Tharindu Janaka', '0718456999', '0762601419', 'asseminate@gmail.com', 'Kaduwela', 'Colombo', 'Kaduwela, Gh Mw\nCity: Kaduwela\nDistrict: Colombo', '[{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"cctv-security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 5999.00, 0.00, 0.00, 5999.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0}', 'Alternate Phone: 0762601419', '2026-06-11 12:07:52', '2026-06-11 12:07:52'),
(44, 'GNX-2026-00044', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"mobile-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 450.00, 0.00, 1200.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":0}', 'Alternate Phone: dfg', '2026-06-11 12:14:07', '2026-06-11 12:14:07'),
(45, 'GNX-2026-00045', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"mobile-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1},{\"product_id\":33,\"name\":\"6MP Dual Lens PTZ WiFi IP Camera ICSEE App\",\"category\":\"CCTV &amp; Security\",\"price\":5999,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 6749.00, 0.00, 67.49, 6816.49, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":1}', 'Alternate Phone: dfg', '2026-06-11 12:15:58', '2026-06-11 12:15:58'),
(46, 'GNX-2026-00046', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":31,\"name\":\"1080P Dahua 8ch Camera CCTV System Full Set\",\"category\":\"CCTV &amp; Security\",\"price\":70800,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 70800.00, 0.00, 708.00, 71508.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":1}', 'Alternate Phone: dfg', '2026-06-11 12:33:09', '2026-06-11 12:33:09'),
(47, 'GNX-2026-00047', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"mobile-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 450.00, 12.00, 1212.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":1}', 'Alternate Phone: dfg', '2026-06-11 12:33:40', '2026-06-11 12:33:40'),
(48, 'GNX-2026-00048', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"mobile-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 450.00, 12.00, 1212.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":1}', 'Alternate Phone: dfg', '2026-06-11 12:35:26', '2026-06-11 12:35:26'),
(49, 'GNX-2026-00049', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"Mobile Accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 450.00, 12.00, 1212.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":1}', 'Alternate Phone: dfg', '2026-06-11 12:56:04', '2026-06-11 12:56:04'),
(50, 'GNX-2026-00050', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"mobile-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 750.00, 450.00, 36.00, 1236.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":3}', 'Alternate Phone: dfg', '2026-06-11 13:06:32', '2026-06-11 13:06:32'),
(51, 'GNX-2026-00051', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":29,\"name\":\"Amaya AEP-22 Handfree\",\"category\":\"mobile-accessories\",\"price\":750,\"weight_kg\":0,\"free_delivery\":0,\"qty\":2}]', 1500.00, 450.00, 0.00, 1950.00, 'pending', 'website', 'koko', 'paid', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":0,\"koko_return\":{\"transaction_id\":\"9b507b36d48b471bbd7e71c4507979dd\",\"status\":\"SUCCESS\",\"returned_at\":\"2026-06-11T18:44:09+05:30\"}}', 'Alternate Phone: dfg', '2026-06-11 13:08:06', '2026-06-11 13:14:09'),
(52, 'GNX-2026-00052', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":28,\"name\":\"Amaya S13 Handfree\",\"category\":\"Mobile Accessories\",\"price\":550,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 550.00, 450.00, 30.00, 1030.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":3}', 'Alternate Phone: dfg', '2026-06-11 13:11:53', '2026-06-11 13:11:53'),
(53, 'GNX-2026-00053', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":28,\"name\":\"Amaya S13 Handfree\",\"category\":\"Mobile Accessories\",\"price\":550,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 550.00, 450.00, 0.00, 1000.00, 'pending', 'website', 'koko', 'awaiting_payment', '{\"merchant_id\":\"c8cca514bdfa0582cdc40c9703c71e9d\",\"sandbox\":1,\"plugin_name\":\"customapi\",\"plugin_version\":\"1.0.1\",\"handling_fee_percent\":0}', 'Alternate Phone: dfg', '2026-06-11 13:12:05', '2026-06-11 13:12:05'),
(54, 'GNX-2026-00054', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":28,\"name\":\"Amaya S13 Handfree\",\"category\":\"Mobile Accessories\",\"price\":550,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1},{\"product_id\":31,\"name\":\"1080P Dahua 8ch Camera CCTV System Full Set\",\"category\":\"CCTV &amp; Security\",\"price\":70800,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 71350.00, 0.00, 2140.50, 73490.50, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":3}', 'Alternate Phone: dfg', '2026-06-11 13:17:07', '2026-06-11 13:17:07'),
(55, 'GNX-2026-00055', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":31,\"name\":\"1080P Dahua 8ch Camera CCTV System Full Set\",\"category\":\"CCTV &amp; Security\",\"price\":70800,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 70800.00, 0.00, 2124.00, 72924.00, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":3}', 'Alternate Phone: dfg', '2026-06-11 13:17:19', '2026-06-11 13:17:19'),
(56, 'GNX-2026-00056', 'dfg', 'dfg', 'dfg', NULL, 'df', 'Matale', 'dfgdf\nCity: df\nDistrict: Matale', '[{\"product_id\":50,\"name\":\"RJ45 CAT6 Network Clip\",\"category\":\"Computer Accessories\",\"price\":25,\"weight_kg\":0,\"free_delivery\":0,\"qty\":1}]', 25.00, 450.00, 14.25, 489.25, 'pending', 'website', 'payhere', 'awaiting_payment', '{\"merchant_id\":\"1234636\",\"sandbox\":1,\"handling_fee_percent\":3}', 'Alternate Phone: dfg', '2026-06-11 13:17:42', '2026-06-11 13:17:42');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `old_price` decimal(12,2) DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_qty` int(11) NOT NULL DEFAULT 0,
  `weight_kg` decimal(10,3) NOT NULL DEFAULT 0.000,
  `free_delivery` tinyint(1) NOT NULL DEFAULT 0,
  `in_stock` tinyint(1) NOT NULL DEFAULT 1,
  `badge` varchar(50) DEFAULT NULL,
  `rating` decimal(3,1) NOT NULL DEFAULT 0.0,
  `review_count` int(11) NOT NULL DEFAULT 0,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `thumbnail` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `category_id`, `brand`, `sku`, `price`, `old_price`, `short_description`, `description`, `stock_qty`, `weight_kg`, `free_delivery`, `in_stock`, `badge`, `rating`, `review_count`, `is_featured`, `is_active`, `thumbnail`, `created_at`, `updated_at`) VALUES
(12, 'CCTV CAMERA - DAHUA DH-HAC-HDW1801TLP-0360 4K HDCVI IR EYEBALL', 'cctv-camera-dahua-dh-hac-hdw1801tlp-0360-4k-hdcvi-ir-eyeball', 16, 'Dahua Technology', 'CAMDAH0176', 9400.00, 10500.00, 'Dahua 4K HDCVI IR Eyeball Camera for ultra-high-resolution surveillance  Max. 4K resolution with 120dB True WDR for clear imaging in varying light  3DNR noise reduction for smooth video  CVI/CVBS/AHD/TVI switchable for versatile installation  3.6mm fixed lens for consistent coverage  Smart IR with max. 30 m night vision  IP67 weatherproof for outdoor use  12V DC power support with 2-year warranty', 'Camera\r\nImage Sensor	1/2.7 inch CMOS\r\nEffective Pixels	3840 (H)×2160 (V), 4K\r\nElectronic Shutter Speed	PAL: 1/3s-1/100000s\r\nNTSC: 1/4s-1/100000s\r\nMinimum Illumination	0.03Lux/F2.0, 30IRE, 0Lux IR on\r\nIR Distance	Distance up to 30m (98.43ft)\r\nIR On/Off Control	Auto/Manual\r\nIR LEDs	1\r\nPan/Tilt/Rotation Range	Pan: 0°-360°\r\nTilt: 0°-78°\r\nRotation: 0°-360°\r\nLens\r\nLens Type	Fixed-focal\r\nMount Type	M12\r\nFocal Length	3.6mm\r\nMax. Aperture	F2.0\r\nAngle of View	3.6mm: 104°×87°×47° (diagonal×horizontal×vertical)\r\nIris type	Fixed Iris\r\nClose Focus Distance	1m/1.8m (3.28ft/5.91ft)\r\nDORI Distance	Lens	Detect	Observe	Recognize	Identify\r\n 	 	 	 	 \r\n3.6mm	146m (351ft)	58m (141ft)	29m (69ft)	15m (36ft)\r\nVideo\r\nVideo Frame Rate	CVI: 4K@15fps; 5M@20fps; 4M@25fps/30fps\r\nCVBS: PAL/NTSC\r\nAHD: 4K@15fps; 5M@20fps; 4M@25fps/30fps\r\nTVI: 4K@15fps; 5M@20fps; 4M@25fps/30fps\r\nResolution	4K (3840×2160); 5M (2592×1944); 4M (2560×1440); 960H (960× 576/960×480)\r\nDay/Night	Auto switch by ICR\r\nBLC	BLC/HLC/WDR\r\nWDR	120dB/WDR\r\nWhite Balance	Auto; manual\r\nGain Control	Auto; manual\r\nNoise Reduction	2D&3D NR\r\nSmart IR	Yes\r\nElectronic Defog	Yes\r\nDigital Zoom	4X\r\nMirror	Off/On\r\nPrivacy Masking	Off/On (8 Area, Rectangle)\r\nCertifications\r\nCertifications	CE (EN55032, EN55024, EN50130-4)\r\nFCC (CFR 47 FCC Part 15 subpartB, ANSI C63.4-2014)\r\nUL (UL60950-1+CAN/CSA C22.2 No.60950-1)\r\nPort\r\nVideo Port	Video output choices of CVI/TVI/AHD/CVBS by one BNC port\r\n 	 \r\nPower\r\nPower Supply	12V±30% DC\r\nPower Consumption	Max 5.2W (12V DC, IR on)\r\nEnvironment\r\nOperating Temperature	-40°C to +60°C (-40°F to 140°F); <95%(non-condensation)\r\nStorage Temperature	-40°C to +60°C (-40°F to 140°F); <95%(non-condensation)\r\nProtection Grade	IP67\r\nStructure\r\nCasing	Metal inner core+plastic cover\r\nDimensions	φ97mm×85.8mm (3.82‘‘×3.38’’)\r\nNet Weight	300g (0.66lb)\r\nGross Weight	380g (0.84lb)', 25, 0.250, 0, 1, NULL, 4.5, 0, 1, 0, 'uploads/products/img_6a218f3e608f32.89021577.jpg', '2026-06-04 14:44:14', '2026-06-08 07:02:09'),
(13, 'SPEAKERS - CREATIVE INSPIRE T10', 'speakers-creative-inspire-t10', 18, '', 'SP2CRE0003', 8100.00, 9250.00, 'Clear and Balanced Sound: Two-way drivers deliver natural and detailed audio for all your music and media needs.  Wide Frequency Range: Covers low to high frequencies from 80 Hz to 20 kHz for rich sound.  Compact & Stylish: Sleek black design fits perfectly on any desktop without taking up much space.  Easy Connectivity: AUX-in port allows connection to PCs, laptops, smartphones, and more (cable not included).  User-Friendly: Simple setup with plug-and-play design.  Perfect for Everyday Use: Gre', 'Compact 2.0 desktop speaker system with elegant design.\r\n\r\nTwo-way driver design for clear, balanced audio.\r\n\r\nFrequency response range of 80 Hz to 20 kHz for rich sound detail.\r\n\r\nAUX-in connector for easy compatibility with multiple devices.\r\n\r\nPristine and natural audio tone for music and media.\r\n\r\nStylish black finish complements any desktop setup.\r\n\r\nLightweight and space-saving dimensions (194 x 88 x 132 mm).\r\n\r\nIdeal for personal use, work, and casual listening.', 12, 0.600, 1, 1, NULL, 0.0, 0, 1, 0, 'uploads/products/img_6a2190d88ad5e4.33905260.jpg', '2026-06-04 14:49:53', '2026-06-08 07:02:08'),
(14, 'LENOVO IP1-15AMN7 RYZEN 3 7320U|4GB|128SSD|W11', 'lenovo-ip1-15amn7-ryzen-3-7320u4gb128ssdw11', 17, '', NULL, 276000.00, 280000.00, '', '', 5, 2.600, 0, 1, NULL, 4.0, 0, 1, 0, 'uploads/products/img_6a219179a7dcc9.99638971.jpg', '2026-06-04 14:53:45', '2026-06-08 07:02:17'),
(16, 'Amaya Electric Shaver', 'amaya-electric-shaver', 21, 'Amaya', 'AHD-560', 5200.00, NULL, 'The AHD-560 electric shaver is designed to deliver a close and effortless shave. Equipped with sharp stainless steel blades and a stylish ergonomic design, it ensures precision and comfort. Easy to use and clean, this shaver offers a premium look and reliable performance for everyday grooming', 'The Amaya AHD-560 Precise Trim Smart Trimmer is your ultimate grooming companion, designed for both men and women seeking precision and style. This sleek black trimmer features high-performance stainless steel blades that ensure long-lasting sharpness, delivering clean lines and premium grooming control. The innovative precision dial allows for effortless adjustments, enabling you to achieve your desired trimming length with ease. Equipped with a digital LED display, you can effortlessly monitor battery percentage and length settings, ensuring you’re always ready for your grooming routine. The USB rechargeable design makes it incredibly portable, allowing for convenient cordless use whether at home or on the go. Included in the package are an adjustable comb/guard attachment for versatile trimming lengths, a USB charging cable for quick recharging, a cleaning brush to maintain your trimmer, and a user manual with safety instructions and warranty information. With a 365-day warranty guarantee, the Amaya AHD-560 offers peace of mind alongside its exceptional performance. Elevate your grooming experience with this stylish and reliable trimmer, perfect for achieving everything from stubble to full beard shape-ups.', 5, 0.000, 0, 1, 'NEW', 4.9, 0, 1, 1, 'uploads/products/img_6a244f844aa298.42350464.jpg', '2026-06-06 16:49:08', '2026-06-08 07:02:48'),
(17, 'Amaya ACD-C82', 'amaya-acd-c82', 16, '', 'ACD-C82', 380.00, 450.00, 'The Amaya ACD-C82 is a USB to Type-C fast-charging and data cable. It is primarily designed to provide reliable, efficient charging and data transfer speeds for smartphones and compatible devices.', '', 10, 0.000, 0, 1, 'NEW', 4.9, 0, 0, 1, 'uploads/products/img_6a24523aef9529.00941927.jpg', '2026-06-06 17:00:42', '2026-06-06 17:00:42'),
(18, 'Amaya Type-C to Type-C 60W Fast Charging Data Cable (1m)', 'amaya-type-c-to-type-c-60w-fast-charging-data-cable-1m', 16, '', 'ACD-CC77', 460.00, 520.00, 'The AMAYA ACD-CC77 Type-C to Type-C cable delivers 60W fast charging and high-speed data syncing for smartphones, tablets, and laptops. Built with improved durability, it resists knots and daily wear, ensuring reliable long-term performance. At 1 meter length, it provides convenient charging and seamless data transfer.', '', 10, 0.000, 0, 1, 'NEW', 4.8, 0, 1, 1, 'uploads/products/img_6a245447ad51f6.88196562.jpg', '2026-06-06 17:09:27', '2026-06-08 07:02:48'),
(19, 'Amaya Fastline Micro USB Fast Data Cable', 'amaya-fastline-micro-usb-fast-data-cable', 16, '', 'ACD-M77', 350.00, NULL, '', 'The Amaya ACD-M77 is a 2A Fast Charging micro USB  cable designed for it\'susers who want reliability, speed, and durability in daily use. Its 1m length gives you enough reach for desks, bedsides, cars, and power banks without being excessively long or messy. The cable maintains stable power delivery for faster, safer charging that protects your battery health.\r\n\r\nBuilt with reinforced connectors and a tough outer jacket, the ACD-M77 holds up against bending, pulling, and everyday wear that destroys cheap cables. It also delivers fast, consistent data transfer, making it suitable for backups, syncing, and wired CarPlay. This is the type of cable you buy once and depend on, not one that fails after two weeks.\r\n\r\nIts compatibility covers all micro USB based devices making it a versatile daily accessory for anyone invested in the ecosystem. Whether you are charging from a wall adapter, power bank, car charger, or laptop, the ACD-M77 stays stable and reliable', 10, 0.000, 0, 1, 'NEW', 4.9, 0, 0, 1, 'uploads/products/img_6a2455e9b4e504.75690274.jpg', '2026-06-06 17:16:25', '2026-06-06 17:16:25'),
(20, 'Amaya AEP-10 Wired In-Ear Earphones', 'amaya-aep-10-wired-in-ear-earphones', 16, '', 'AEP-10', 1150.00, NULL, 'The Amaya AEP-10 is an affordable, wired in-ear earphone equipped with a standard 3.5 mm audio jack. Designed for everyday use, these earphones provide clear sound reproduction and a comfortable, ergonomic fit suitable for extended listening sessions.', '', 10, 0.000, 0, 1, 'NEW', 4.9, 0, 0, 1, 'uploads/products/img_6a245829021320.17120294.jpg', '2026-06-06 17:26:01', '2026-06-06 17:26:01'),
(21, 'ACD-M66 Data Cable', 'acd-m66-data-cable', 16, '', 'acd-m66', 350.00, NULL, 'Designed for speed and versatility, the ACD-M66 is your ideal portable storage solution. With dual USB and Type-C ports, it enables smooth data transfer across smartphones, tablets, and computers—making file sharing faster and smarter', '', 10, 0.000, 0, 1, 'NEW', 4.8, 0, 1, 1, 'uploads/products/img_6a2459e142cee4.26764570.png', '2026-06-06 17:33:21', '2026-06-08 07:02:35'),
(22, 'Amaya 2.4A Fast Charging Data Cable High-Speed USB to Lightning Cable for iPhone & iPad (1-Meter)', 'amaya-24a-fast-charging-data-cable-high-speed-usb-to-lightning-cable-for-iphone-ipad-1-meter', 16, '', 'ACD-L66', 450.00, NULL, '', 'Keep your Apple devices powered and synced with the reliable AMAYA ACD-L66 Lightning Cable. Specifically designed for efficiency and durability, this cable provides a steady 2.4A current for a safe and consistent charge.\r\n\r\n2.4A Stable Charging: Delivers a reliable 2.4A output, providing an efficient charging experience for your iPhone, iPad, and iPod.\r\n\r\nRapid Data Sync: Transfer your files, photos, and music quickly and securely with high-speed data transmission.\r\n\r\nAnti-Bend Protection: Features extra-long reinforced SR joints at the connectors to prevent breakage and extend the cable\'s lifespan.\r\n\r\nPerfect 1-Meter Length: The 1000mm length is ideal for daily use, whether at home, in the office, or while traveling.\r\n\r\nPremium Build: Crafted with high-quality materials to ensure a snug fit and a sleek appearance that matches your Apple devices.', 10, 0.000, 0, 1, 'NEW', 4.8, 0, 0, 1, 'uploads/products/img_6a245b115f8622.43032091.jpg', '2026-06-06 17:38:25', '2026-06-06 17:38:25'),
(23, 'Amaya U110C', 'amaya-u110c', 16, '', 'U110C', 930.00, NULL, 'The Amaya U110C is a 10W wall charger kit that includes a high-durability USB Type-C data cable. Manufactured by Amaya, a consumer electronics brand widely popular across global markets like Africa and South Asia, this budget-friendly adapter is designed as a stable and efficient daily power solution.', 'Because of its standard 10W delivery speed, the Amaya U110C is ideal for users looking to prioritize their battery health over aggressive quick charging. It is highly recommended for overnight phone charging, standard smartphones, tablets, and lower-power wireless accessories like Bluetooth earbuds or smartwatches', 5, 0.000, 0, 1, NULL, 4.5, 0, 1, 1, 'uploads/products/img_6a245caf1db386.63506756.jpg', '2026-06-06 17:45:19', '2026-06-08 07:02:34'),
(24, 'Amaya ACC-03  CAR ADAPTER', 'amaya-acc-03-car-adapter', 16, '', 'acc-03', 800.00, NULL, 'The Amaya ACC-03 is a compact dual-USB car charger designed for quick and safe mobile device charging. It plugs directly into your standard vehicle 12V/24V socket to power smartphones, tablets, and other portable gadgets while driving.', '', 5, 0.000, 0, 1, 'NEW', 4.5, 0, 0, 1, 'uploads/products/img_6a245de5d662f9.96863932.jpg', '2026-06-06 17:50:29', '2026-06-06 17:56:22'),
(25, 'Amaya AD036 type c charger', 'amaya-ad036-type-c-charger', 16, '', 'AD036', 980.00, NULL, 'The Amaya AD036 is a widely used, budget-friendly 3.1A USB smart wall charger. It is built for everyday and travel use with features designed for safe and efficient charging', '', 10, 0.000, 0, 1, 'NEW', 4.5, 0, 1, 1, 'uploads/products/img_6a245f0d55cb91.13355188.jpg', '2026-06-06 17:55:25', '2026-06-08 07:02:36'),
(26, 'AMAYA BD66 COMPACT BLUETOOTH SPEAKER – SUPER BASS, MULTI-MODE PLAYBACK & POWERED PORTABILITY', 'amaya-bd66-compact-bluetooth-speaker-super-bass-multi-mode-playback-powered-portability', 18, '', 'bd66', 3800.00, NULL, 'The Amaya BD66 is a compact yet powerful Bluetooth speaker designed to offer rich, high-fidelity audio wherever you go. Equipped with a strong bass diaphragm, it delivers immersive sound and deep tones in a portable design. Multiple playback options—including Bluetooth, USB, AUX input, and micro-SD card—ensure you can enjoy music from any source. With a built-in 1200 mAh battery, it provides up to six hours of continuous playtime on a single charge. Lightweight and easy-to-carry, it’s perfect fo', '', 5, 0.000, 0, 1, 'HOT', 4.9, 0, 1, 1, 'uploads/products/img_6a24607625cf94.83491725.jpg', '2026-06-06 18:01:26', '2026-06-06 18:01:26'),
(27, 'Amaya Amaya ACC-04 Car adapter', 'amaya-amaya-acc-04-car-adapter', 16, '', 'Amaya ACC-04', 850.00, NULL, 'The Amaya ACC-04 is a 22.5W dual-port in-car charger designed to quickly power devices on the go. It features one USB-A and one USB-C port, making it highly versatile for charging smartphones, tablets, and other gadgets simultaneously', '', 5, 0.000, 0, 1, 'NEW', 4.5, 0, 0, 1, 'uploads/products/img_6a2461d5cb50c7.21780317.jpg', '2026-06-06 18:07:17', '2026-06-06 18:07:17'),
(28, 'Amaya S13 Handfree', 'amaya-s13-handfree', 16, '', 'S13', 550.00, NULL, 'The Amaya S13 is a popular budget-friendly, wired in-ear stereo earphone designed for everyday use. It is widely used locally in Sri Lanka and is primarily known for its deep bass, clear built-in microphone, and universal compatibility', '', 5, 0.000, 0, 1, 'HOT', 4.9, 0, 1, 1, 'uploads/products/img_6a24639257d5a3.05300656.png', '2026-06-06 18:14:42', '2026-06-08 07:02:36'),
(29, 'Amaya AEP-22 Handfree', 'amaya-Amaya AEP-22 Handfree-handfree', 16, '', 'Amaya AEP-22 Handfree', 750.00, NULL, 'The Amaya AEP-22 wired earphones are designed for stereo sound with wide compatibility and clear communication. Featuring high-quality audio and an in-line microphone, they deliver crystal-clear calls and reliable performance. Lightweight yet durable, they are built with TPE cable and ABS material for everyday use. With a Type-C plug and a frequency response of 20–20,000Hz, they provide powerful sound across a wide range. The 1.2m cable length ensures flexibility, while the ergonomic design offe', '', 5, 0.000, 0, 1, 'NEW', 4.3, 0, 1, 1, 'uploads/products/img_6a24652444f8b3.98620884.jpg', '2026-06-06 18:21:24', '2026-06-08 07:02:26'),
(30, '1080P Dahua 4ch Camera CCTV System Full Set', '1080p-dahua-4ch-camera-cctv-system-full-set', 20, 'Dahua Technology', '04 channel', 49500.00, 55000.00, '', '', 8, 0.000, 0, 1, 'HOT', 4.9, 0, 1, 1, 'uploads/products/img_6a26a633df26f3.55696569.jpg', '2026-06-08 11:23:31', '2026-06-08 11:23:31'),
(31, '1080P Dahua 8ch Camera CCTV System Full Set', '1080p-dahua-8ch-camera-cctv-system-full-set', 20, 'Dahua Technology', '8 channel', 70800.00, 77000.00, '', '', 10, 0.000, 0, 1, 'HOT', 4.9, 0, 1, 1, 'uploads/products/img_6a26a84fec84a0.74901120.jpg', '2026-06-08 11:32:31', '2026-06-08 11:32:31'),
(32, 'CCTV POWER SUPPLY', 'cctv-power-supply', 20, '', 'power supply', 650.00, NULL, 'A CCTV power supply converts standard AC mains electricity to the stable 12V DC power required by most security cameras. Single camera setups use individual wall adapters, while multi-camera systems rely on centralized metal-box power supplies to distribute power safely and cleanly.', '', 20, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26a94a853164.57542858.jpg', '2026-06-08 11:36:42', '2026-06-08 11:36:42'),
(33, '6MP Dual Lens PTZ WiFi IP Camera ICSEE App', '6mp-dual-lens-ptz-wifi-ip-camera-icsee-app', 20, '', 'icsee', 5999.99, NULL, '', 'Buy 6MP Dual Lens PTZ WiFi IP Camera ICSEE App. Experience top-notch security with a 6MP dual-lens WiFi Camera. Stay connected and in control with the user-friendly ICSEE App.\r\n\r\nThis 6MP Dual Lens PTZ WiFi IP Camera with Dual Screen AI Auto Tracking is the ultimate outdoor security solution you’ve been looking for! With its sleek and modern design, this white security camera is not only stylish but also highly functional.\r\n\r\nWith the ICSEE app, you can easily access the camera’s live feed from your smartphone or tablet, no matter where you are. The WiFi connectivity ensures a seamless connection, allowing you to check in on your property in real time.\r\n\r\nThe AI auto-tracking feature automatically detects and follows any movement, ensuring that no suspicious activity goes unnoticed. Rest assured knowing that this CCTV surveillance camera is always on guard, providing you with peace of mind and enhanced security', 10, 0.000, 0, 1, NULL, 4.9, 0, 1, 1, 'uploads/products/img_6a26acb28cae99.61536131.jpg', '2026-06-08 11:51:14', '2026-06-08 11:51:14'),
(34, 'CCTV Video Cable SCREW Type Copper BNC Connector', 'cctv-video-cable-screw-type-copper-bnc-connector', 20, '', 'Bnc connector', 180.00, 200.00, 'Joint material: zinc alloy (anti-rust);Contact Material: copper;Connector Type: BNC male to RCA femaleWiring type: screw;Connector role: monitoring video / audio signal transfer;Connector characteristics: good shape, no distortion1.high quality but low price2.We have many types connector for your choosing, if you have any query please contact me, I am glad service for you3.We have professional team to service for you, please tell me your detail information then I will offer you the right product', '', 50, 0.000, 0, 1, 'NEW', 4.9, 0, 0, 1, 'uploads/products/img_6a26cb30c81202.31904379.jpg', '2026-06-08 14:01:20', '2026-06-08 14:01:20'),
(35, 'Male DC jack connector with 20 cm wire', 'male-dc-jack-connector-with-20-cm-wire', 20, '', 'dc wire jack', 50.00, NULL, 'DC 5.5mm x 2.5mm male DC jack connector with 20 cm wire.  Connect any of our single colored / white ledstrip with a DC power supply or dimmer controller.', '', 100, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26cbf3af9f34.26941519.jpg', '2026-06-08 14:04:35', '2026-06-08 14:04:35'),
(36, 'DC Female Connector Jack (With Wires)', 'dc-female-connector-jack-with-wires', 20, '', 'dc female jack', 50.00, NULL, 'Standard DC Socket which suites for most of the standard wall adapter. Internal Diameter of 2.1mm with center pole. DC Female Socket Wire is Power Cable 2 Core to Female DC 12V Connector used for CCTV Security Camera Power over Network Cable.', '', 100, 0.000, 0, 1, NULL, 0.0, 0, 0, 1, 'uploads/products/img_6a26ccf38a2939.50211027.jpg', '2026-06-08 14:08:51', '2026-06-08 14:08:51'),
(37, 'Dvr Rack (3u cabinet)', 'dvr-rack-3u-cabinet', 20, '', 'Dvr Rack (3u cabinet)', 3200.00, NULL, 'Our team is always keeping up with the innovative technologies to assure product quality and low cost of ownership. This product size is Size (W) 350mm x (H) 155mm x (D) 310mm. It is perfectly sized and made according to the industry standards with powder coated steels (Zinc coated + Zinc Alu)..Suitable for light weight/Light duty installations.Ideal for Four channel DVR.This high quality product is manufactured in Sri Lanka to deliver the best output to our customers.', '', 10, 0.000, 0, 1, NULL, 4.5, 0, 0, 1, 'uploads/products/img_6a26cdd259ea09.64523133.jpg', '2026-06-08 14:12:34', '2026-06-08 14:12:34'),
(38, 'Cat 6 Copper Mix Network 100m Cable 0.5 Copper', 'cat-6-copper-mix-network-100m-cable-05-copper', 20, '', 'cat 6', 5100.00, NULL, 'cat 6 cable 1 meter price for - RS.80 please send the whatsapp msg for your inquiris.', '', 5, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26cf35332349.66312685.jpg', '2026-06-08 14:18:29', '2026-06-08 14:18:29'),
(39, 'RG-58 3C2V 100m Cable with Jelly Filled', 'rg-58-3c2v-100m-cable-with-jelly-filled', 20, '', 'rg-58', 4500.00, NULL, '100M High Quality CCTV Video Coax Cable Black High quality combined professional cable often called shotgun cable or siamese cable. This is the professionals choice RG58 Cable  , also known as shotgun CCTV cable. This power cable runs along the side of the coax and will allow you to power your CCTV cameras from a central location. At the end of the cable you would terminate the video signal with BNC connectors and the power signal with DC Power Jacks. This is professional graded cable on 100m wo', 'Model: 3C-2V\r\nCategories: Coaxial Cable\r\nImpedance: 75 ohm\r\nCertificates: UL, ETL, CE, RoHS\r\nLength: 100m, 305m,500m,1000m,500ft,1000ft\r\nPacking: roll, wooden spool, carton, pallet\r\nMOQ: 30KM\r\nDelivery time: Normally 15 working days\r\nPort of Loading: NINGBO, SHANGHAI\r\nTerm of payment: T/T, L/C at sight, D/P at sight\r\nInner Conductor\r\n50mm BC\r\nDielectric\r\n1mm SPE\r\nShield\r\n14mm×6×25 BC Braid(L=26)\r\nJacket\r\n4±0.5mmPVC\r\nJacket Thickness\r\n08mm\r\nApplication\r\nFor Use in Longer CCTV Run Lengths\r\nInner Conductor Resistance\r\nThe Max. at 20℃ shall be<145Ω/km\r\nCapacitance\r\n69±4 pF/m\r\nImpedance\r\n75 ± 3 Ω\r\nReturn loss\r\nbetween 5 and 1000MHz: > 20dB\r\nVelocity of Propagation\r\nSparker Test (VAC)\r\n1\r\nMechanical and Envrionmental Properties\r\nCable bend radius\r\n10 times the cable diameter\r\nOperating Temp Range\r\n-20 ℃ to 65℃\r\nCable diamensions', 8, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26d034698778.85713201.jpg', '2026-06-08 14:22:44', '2026-06-08 14:22:44'),
(44, 'RG-58 3C2V CCTV Video Cable with Jelly Filled 100m(full copper)', 'rg-58-3c2v-cctv-video-cable-with-jelly-filled-100mfull-copper', 20, '', 'rg-58 F', 8500.00, NULL, 'High quality combined professional cable often called shotgun cable or siamese cable. This is the professionals choice RG58 Cable  , also known as shotgun CCTV cable. This power cable runs along the side of the coax and will allow you to power your CCTV cameras from a central location. At the end of the cable you would terminate the video signal with BNC connectors and the power signal with DC Power Jacks. This is professional graded cable on 100m wooden drums, please note that we recommend not ', 'Model: 3C-2V\r\nCategories: Coaxial Cable\r\nImpedance: 75 ohm\r\nCertificates: UL, ETL, CE, RoHS\r\nLength: 100m, 305m,500m,1000m,500ft,1000ft\r\nPacking: roll, wooden spool, carton, pallet\r\nMOQ: 30KM\r\nDelivery time: Normally 15 working days\r\nPort of Loading: NINGBO, SHANGHAI\r\nTerm of payment: T/T, L/C at sight, D/P at sight\r\nInner Conductor\r\n50mm BC\r\nDielectric\r\n1mm SPE\r\nShield\r\n14mm×6×25 BC Braid(L=26)\r\nJacket\r\n4±0.5mmPVC\r\nJacket Thickness\r\n08mm\r\nApplication\r\nFor Use in Longer CCTV Run Lengths\r\nInner Conductor Resistance\r\nThe Max. at 20℃ shall be<145Ω/km Capacitance 69±4 pF/m Impedance 75 ± 3 Ω Return loss between 5 and 1000MHz: > 20dB\r\nVelocity of Propagation\r\nSparker Test (VAC)\r\n1\r\nMechanical and Envrionmental Properties\r\nCable bend radius\r\n10 times the cable diameter\r\nOperating Temp Range\r\n-20 ℃ to 65℃\r\nCable diamensions', 5, 0.000, 0, 1, NULL, 4.5, 0, 0, 1, 'uploads/products/img_6a26d14b61ffa7.70257817.jpg', '2026-06-08 14:27:23', '2026-06-08 14:27:23'),
(45, 'Mini Ups Power Supply Outdoor CCTV 12V 2A Built-in Battery Working System', 'mini-ups-power-supply-outdoor-cctv-12v-2a-built-in-battery-working-system', 20, '', 'MINI UPS 12V', 1850.00, 2200.00, 'UPS Monitoring Power Outage Endurance Power Supply Outdoor CCTV 12V 2A Built-in Battery Working System IP Camera Power Cord', '*12V 2A/5V 2A surveillance camera UPS power supply\r\n*It can supply power directly to the camera\r\n*It can store power in the built-in battery\r\n*Charging and powering can be done simultaneously\r\n*Automatically use the built-in battery to power the camera when there is a power failure\r\n*Built-in 1800/3600/5400 mAh battery, the maximum power supply time can reach 24 hours\r\n*Suitable for most IP cameras on the market\r\n*Good water resistance, can be used outdoors', 10, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26d24fbe47b6.22249681.jpg', '2026-06-08 14:31:43', '2026-06-08 14:31:43'),
(46, '4K HDMI Cable 1.5M', '4k-hdmi-cable', 17, '', '1.5 HDMI', 550.00, NULL, '', '', 10, 0.000, 0, 1, NULL, 5.0, 0, 0, 1, 'uploads/products/img_6a26d3928ac496.29697259.jpg', '2026-06-08 14:37:06', '2026-06-08 14:37:06'),
(47, '4K HDMI Cable 3M', '4k-hdmi-cable-3m', 17, '', '3M HDMI', 750.00, NULL, '', '', 9, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26d403e81ef3.56299389.jpg', '2026-06-08 14:38:59', '2026-06-08 14:38:59'),
(48, '4K HDMI Cable 5M', '4k-hdmi-cable-5m', 17, '', '5M HDMI', 1150.00, NULL, '', '', 10, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26d46fe62130.46333320.jpg', '2026-06-08 14:40:20', '2026-06-08 14:40:47'),
(49, 'HDMI Extender 30M', 'hdmi-extender-30m', 20, '', 'EXTENDER HDMI', 1450.00, NULL, '', '', 10, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26d4f662b674.71069552.jpg', '2026-06-08 14:43:02', '2026-06-08 14:43:02'),
(50, 'RJ45 CAT6 Network Clip', 'rj45-cat6-network-clip', 17, '', 'RJ-45', 25.00, NULL, '', '', 100, 0.000, 0, 1, NULL, 4.9, 0, 0, 1, 'uploads/products/img_6a26d569c50f16.72518862.jpg', '2026-06-08 14:44:57', '2026-06-08 14:44:57'),
(51, '500GB WD PURPLE HARD DISK (REFURBISHED)', '500gb-wd-purple-hard-disk-refurbished', 20, '', '500GB', 5700.00, NULL, 'Surveillance drives are meant for recording video on 24/7 security systems. They typically use something called a network video recorder (NVR) or a digital video recorder (DVR). Unlike a PC, these drives have to be writing data—specifically, video data all the time.', 'Hard Disk – 500 GB\r\nBrand – WD PURPLE', 5, 0.000, 0, 1, NULL, 4.3, 0, 0, 1, 'uploads/products/img_6a26d62c5b87a3.38615952.jpg', '2026-06-08 14:48:12', '2026-06-08 14:48:12'),
(52, '5 Lens 15MP 4G PTZ Outdoor Camera', '5-lens-15mp-4g-ptz-outdoor-camera', 20, '', '5 LENS 4G', 19990.00, NULL, '6 MONTH WARRANTY', '', 5, 0.000, 0, 1, 'HOT', 4.5, 0, 1, 1, 'uploads/products/img_6a26d7326499c5.53707001.jpg', '2026-06-08 14:52:34', '2026-06-08 14:52:34');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `created_at`) VALUES
(12, 16, '2026-06-04 14:47:01'),
(12, 19, '2026-06-04 14:47:01'),
(13, 18, '2026-06-04 14:51:04'),
(13, 21, '2026-06-04 14:51:04'),
(14, 17, '2026-06-04 14:53:45'),
(14, 19, '2026-06-04 14:53:45'),
(15, 16, '2026-06-06 06:25:48'),
(15, 19, '2026-06-06 06:25:48'),
(16, 21, '2026-06-06 16:49:08'),
(17, 16, '2026-06-06 17:00:42'),
(18, 16, '2026-06-06 17:09:27'),
(19, 16, '2026-06-06 17:16:25'),
(20, 16, '2026-06-06 17:26:01'),
(21, 16, '2026-06-06 17:33:21'),
(22, 16, '2026-06-06 17:38:25'),
(23, 16, '2026-06-06 17:45:19'),
(24, 16, '2026-06-06 17:56:22'),
(25, 16, '2026-06-06 17:55:25'),
(26, 18, '2026-06-06 18:01:26'),
(27, 16, '2026-06-06 18:07:17'),
(28, 16, '2026-06-06 18:14:42'),
(29, 16, '2026-06-06 18:21:24'),
(30, 20, '2026-06-08 11:23:31'),
(31, 20, '2026-06-08 11:32:31'),
(32, 20, '2026-06-08 11:36:42'),
(33, 20, '2026-06-08 11:51:14'),
(34, 20, '2026-06-08 14:01:20'),
(35, 20, '2026-06-08 14:04:35'),
(36, 20, '2026-06-08 14:08:51'),
(37, 20, '2026-06-08 14:12:34'),
(38, 20, '2026-06-08 14:18:29'),
(39, 20, '2026-06-08 14:22:44'),
(44, 20, '2026-06-08 14:27:23'),
(45, 20, '2026-06-08 14:31:43'),
(46, 17, '2026-06-08 14:37:06'),
(46, 20, '2026-06-08 14:37:06'),
(47, 17, '2026-06-08 14:38:59'),
(47, 20, '2026-06-08 14:38:59'),
(48, 17, '2026-06-08 14:40:47'),
(48, 20, '2026-06-08 14:40:47'),
(49, 20, '2026-06-08 14:43:02'),
(50, 17, '2026-06-08 14:44:57'),
(50, 20, '2026-06-08 14:44:57'),
(51, 20, '2026-06-08 14:48:12'),
(52, 20, '2026-06-08 14:52:34');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`) VALUES
(11, 14, 'uploads/products/img_6a219179ab8110.66468145.jpg', 1),
(12, 14, 'uploads/products/img_6a219179ac2208.63092876.jpg', 2),
(16, 26, 'uploads/products/img_6a2460762785b9.68911424.jpg', 1),
(17, 26, 'uploads/products/img_6a24607627cb16.19514011.jpg', 2),
(18, 28, 'uploads/products/img_6a246392597d73.67469487.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_specs`
--

CREATE TABLE `product_specs` (
  `id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `spec_key` varchar(100) NOT NULL,
  `spec_value` varchar(500) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_specs`
--

INSERT INTO `product_specs` (`id`, `product_id`, `spec_key`, `spec_value`, `sort_order`) VALUES
(76, 12, 'Image Sensor', '1/2.7 inch CMOS', 0),
(77, 12, 'Effective Pixels', '3840 (H)×2160 (V), 4K', 1),
(82, 16, 'Product Material: PC + ABS', 'Blade Material: Stainless Steel + DLC coating', 0),
(83, 16, 'Input: DC 5V–1A', 'Battery Capacity: 500mAh', 1),
(84, 16, 'Run Time: Up to 50 minutes', 'Charging Time: 2 hours', 2),
(85, 16, 'Input Type: Type-C', 'Durable design with precise trimming', 3),
(86, 17, 'Cable Type: USB to Type-C', 'Primary Functions: High-speed charging and data synchronization', 0),
(87, 17, 'Brand: Amaya (Amaya Technology)', '3A Fast Charging', 1),
(88, 18, 'Output (Type-C to Type-C): 5V=3A (60W Max)', 'Cable Type: Type-C to Type-C', 0),
(89, 18, 'Cable Length: 1m', 'Features: Fast charging, high-speed syncing, improved durability', 1),
(90, 19, 'Certifications: PEFC Timber Certificate|FSC - Forest Stewardship Council|PEFC - Programme for the En', 'Main Material: ABS, PVC', 0),
(91, 19, 'Weight (kg): 0.1', '1m length for flexible everyday use', 1),
(92, 19, '• Reinforced connectors for longer lifespan', '• Stable data transfer for syncing and backups', 2),
(93, 20, 'Driver Size: 10mm dynamic drivers', 'Frequency Response: 20Hz – 20kHz', 0),
(94, 20, 'Impedance: 16Ω', 'Sensitivity: 98dB', 1),
(95, 20, 'Connector: 3.5mm audio jack', 'Cable Length: 1.2 meters', 2),
(96, 20, 'Built-in Microphone: For hands-free calls', 'In-line Controls: Play/pause and answer/end call functionality', 3),
(97, 21, 'Ultra-Fast Transfer: Read and write speeds up to 100MB/s', 'Plug & Play: No software installation required—just plug in and go', 0),
(98, 21, 'Dimensions: 67 × 19 × 8 mm', 'Case Material: Aluminium Alloy', 1),
(99, 22, 'Connector Type: USB to Lightning', 'Charging Speed: Up to 2.4A output', 0),
(100, 22, 'Length: 1 meter (1000mm)', 'Compatibility: All Lightning-enabled Apple devices', 1),
(101, 23, 'Power Output: 10W total power supplying a steady 5V / 2.0A current.', 'Charging Interface: Features dual USB-A output ports, allowing you to charge up to two devices concurrently.', 0),
(102, 23, 'Included Accessory: Ships with a premium, reinforced Type-C cable optimized for both power delivery ', 'Built-in Protections: Outfitted with a Multi-Protect Safety System consisting of a smart chip that automatically manages power flow to guard against short circuits, overheating, and over-voltage', 1),
(105, 25, 'Dual USB Ports: Allows you to charge two devices simultaneously.', 'Fast Charging: Delivers a combined 3.1A output (typically 10W-15W depending on your device).', 0),
(106, 25, 'Smart Chip Technology: Automatically detects your device\'s power requirements to prevent overchargin', 'Included Cable: Frequently sold as a kit that includes either a Micro USB or Type-C fast charging cable.', 1),
(107, 24, 'Dual Outputs: Equipped with two USB-A ports, allowing you to charge two devices at the same time.', 'Power Output: Typically delivers a total output of 2.4A across its ports for standard, efficient charging.', 0),
(108, 24, 'Smart Design: Features a lightweight and compact build to save space on your vehicle’s central conso', 'Device Safety: Standard built-in safety features to help protect your electronics from overcharging and overheating', 1),
(109, 26, 'Powerful bass audio: High-quality diaphragm delivers rich, deep sound', 'Versatile playback options: Bluetooth, USB, micro-SD card, and AUX input', 0),
(110, 26, 'Reliable battery life: Compact internal battery supports around 6 hours of playtime', 'Built-in microphone: Enables hands-free calling', 1),
(111, 26, 'Portable design: Lightweight and easy to carry for both indoor and outdoor use', 'Universal compatibility: Pairs with smartphones, tablets, and most Bluetooth devices', 2),
(112, 27, 'Max Output: 22.5W', 'Charging Ports: 1x USB-A and 1x USB-C', 0),
(113, 27, 'Supported Protocols: Power Delivery (PD) and Quick Charge (QC) 3.0', 'Safety Features: Smart IC technology with built-in protection against overcharging, over-discharging, and overheating', 1),
(114, 28, 'Sound Quality: Features HD stereo sound with a focus on deep, pure bass and three-frequency balance.', 'Design: Ergonomic semi-in-ear design with noise-canceling rubber ear tips for a comfortable and immersive fit.', 0),
(115, 28, 'Connectivity: Standard 3.5mm audio jack compatible with mobile phones, tablets, and computers.', 'Controls & Mic: In-line high-quality microphone with a smart button for seamless call management and music control.', 1),
(116, 29, 'Cable Length: 1.2m', 'Weight: — (lightweight design)', 0),
(117, 29, 'Frequency Response: 20–20,000Hz', 'Impedance: 16Ω', 1),
(118, 29, 'Microphone Sensitivity: 42±3dB', 'Plug Type: Type-C', 2),
(119, 29, 'Earphone Type: Stereo Semi-in-Ear', 'Product Material: TPE Cable, ABS Material', 3),
(120, 30, 'Dahua two way talk 04ch dvr -01', 'Dahua full colour with audio outdoor camera -04', 0),
(121, 30, 'Bnc connector (full copper)-08', '12v 2A Power supply -04', 1),
(122, 30, '3c2v cable -100y', '500GB Hdd (republished)-01', 2),
(123, 31, 'Dahua two way talk 08ch dvr -01:', 'Dahua full colour with audio outdoor camera -08', 0),
(124, 31, 'Bnc connector (full copper)-16', '12v 2A Power supply -08', 1),
(125, 31, '3c2v cable -100y', '500GB Hdd (republished)-01', 2),
(126, 33, 'Resolution: Main resolution 2304×2592, sub-resolution 800×896', 'APP operating system: iOS/Android', 0),
(127, 33, 'Frame rate: 12 frames', 'Focal length: F1.4×3.6mm+F1.4×3.6mm', 1),
(128, 33, 'Viewing angle: 70 degrees/92 degrees', 'Video format: H.265AI', 2),
(129, 33, 'Power supply specification: DC12V', 'Power consumption: 10W', 3),
(130, 33, 'Network: 2.4835GHZ10. Wireless standards: IEEE802.11b, 802.11g, 802.11n', 'Support protocols: support NETIP protocol, support onvif protocol, support Bluetooth distribution network', 4),
(131, 33, 'Pan 300 degree/ Tilt 90 degree', 'Max 50 meters Day Vision', 5),
(132, 33, 'AI human detection Alarm', 'Waterproof IP66 Use for Outdoor', 6),
(133, 33, 'TF Card Slot Support Max 128 GB.', 'Dual-Lens with Dual Screen', 7),
(134, 34, 'Type: BNC', 'Application: Audio & Video', 0),
(135, 34, 'Gender: Male', 'Pins: 24P, 1p', 1),
(136, 34, 'Impedance: 50Ω/75Ω', 'Place of Origin: China', 2),
(137, 36, 'DC Socket Connector with Wire Moulded.', 'Solder Less Design,', 0),
(138, 36, 'Pure Copper Connector For Reliable Quality,', 'Wire Length:  ≈15cms', 1),
(139, 37, 'This product size is Size (W) 350mm x (H) 155mm x (D)  310mm', 'It is perfectly sized and made according to the industry  standards with powder coated steels. ■ Suitable for light weight/Light duty installations  Ideal for Four channel DVR.  A high quality product', 0);

-- --------------------------------------------------------

--
-- Table structure for table `promo_banners`
--

CREATE TABLE `promo_banners` (
  `id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `link_url` varchar(500) DEFAULT NULL,
  `open_in_new_tab` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_banners`
--

INSERT INTO `promo_banners` (`id`, `image_path`, `link_url`, `open_in_new_tab`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'uploads/promo-banners/img_6a226de6b35ea5.90831047.webp', 'asseminate.com', 1, 0, 1, '2026-06-05 06:30:40', '2026-06-05 06:34:14'),
(2, 'uploads/promo-banners/img_6a266999d3d5b2.35293567.webp', NULL, 0, 2, 1, '2026-06-05 06:40:57', '2026-06-08 07:04:57'),
(3, 'uploads/promo-banners/img_6a226f82c3e9c7.99517013.webp', NULL, 0, 3, 1, '2026-06-05 06:41:06', '2026-06-05 06:41:06');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'store_name', 'Gadget Hub', '2026-05-27 09:22:32'),
(2, 'store_tagline', 'Wholesale and retail dealer in all kind of used and brand new mobile accessories & home appliances', '2026-05-27 09:22:33'),
(3, 'store_phone', '+94 76 389 6551', '2026-05-27 09:22:33'),
(4, 'store_whatsapp', '94763896551', '2026-05-27 09:22:33'),
(5, 'store_email', 'info@gadgethub.lk', '2026-05-27 09:22:33'),
(6, 'store_address', 'No 109,wariyagoda ,alawwa , Alawwa, Sri Lanka, 60280', '2026-05-27 09:22:33'),
(7, 'store_hours', 'Mon-Sat: 8:00 AM - 7:00 PM | Sunday: Closed', '2026-05-20 07:14:45'),
(8, 'store_map_embed', '', '2026-05-20 07:14:45'),
(9, 'store_map_link', '', '2026-05-20 07:14:45'),
(10, 'facebook_url', 'https://www.facebook.com/profile.php?id=61577667933423', '2026-05-27 09:22:57'),
(11, 'instagram_url', '', '2026-05-20 07:14:45'),
(12, 'youtube_url', '', '2026-05-20 07:14:45'),
(13, 'tiktok_url', '', '2026-05-20 07:14:45'),
(14, 'currency_symbol', 'Rs.', '2026-05-20 07:14:45'),
(15, 'currency_code', 'LKR', '2026-05-20 07:14:45'),
(16, 'free_delivery_min', '5000', '2026-05-28 14:09:47'),
(17, 'meta_description', 'Genex - Sri Lanka\'s trusted source for genuine computer parts, electronics and accessories. Wholesale & Retail from Kamburupitiya.', '2026-05-20 07:14:45'),
(31, 'theme_primary', '#e51f1f', '2026-05-27 16:08:14'),
(32, 'theme_primary_lt', '#f7d84a', '2026-05-27 16:09:16'),
(33, 'theme_accent', '#ff5900', '2026-05-27 16:09:49'),
(34, 'theme_green', '#16a34a', '2026-05-27 16:08:14'),
(35, 'theme_wa', '#25d366', '2026-05-27 16:08:14'),
(36, 'theme_bg', '#141414', '2026-05-27 16:08:14'),
(37, 'theme_bg2', '#191919', '2026-05-27 16:08:14'),
(38, 'theme_bg3', '#1e1e1e', '2026-05-27 16:08:14'),
(39, 'theme_bg4', '#252525', '2026-05-27 16:08:14'),
(40, 'theme_card', '#1d1d1d', '2026-05-27 16:08:14'),
(41, 'theme_card_hover', '#242424', '2026-05-27 16:08:14'),
(42, 'theme_border', '#303030', '2026-05-27 16:08:14'),
(43, 'theme_border_lt', '#3d3d3d', '2026-05-27 16:08:14'),
(44, 'theme_text', '#ffffff', '2026-05-27 16:08:14'),
(45, 'theme_text2', '#e8e8e8', '2026-05-27 16:08:14'),
(46, 'theme_text_muted', '#999999', '2026-05-27 16:08:14'),
(47, 'theme_text_dim', '#505050', '2026-05-27 16:08:14'),
(119, 'ann_icon_1', 'fas fa-tag', '2026-05-28 09:48:34'),
(120, 'ann_text_1', 'Free delivery on orders over Rs. 10,000', '2026-05-28 09:49:36'),
(121, 'ann_link_1', 'shop.php', '2026-05-28 09:49:36'),
(122, 'ann_icon_2', 'fas fa-boxes', '2026-05-28 09:48:34'),
(123, 'ann_text_2', 'Wholesale prices available - Contact us today', '2026-05-28 09:48:34'),
(124, 'ann_link_2', 'wholesale.php', '2026-05-28 09:48:34'),
(125, 'ann_icon_3', 'fas fa-shield-alt', '2026-05-28 09:48:34'),
(126, 'ann_text_3', '100% Genuine products with manufacturer warranty', '2026-05-28 09:48:34'),
(127, 'ann_link_3', 'shop.php', '2026-05-28 09:49:36'),
(128, 'ann_icon_4', 'fas fa-headset', '2026-05-28 09:48:34'),
(129, 'ann_text_4', '24/7 Customer support', '2026-05-28 09:48:34'),
(130, 'ann_link_4', 'contact.php', '2026-05-28 09:48:34'),
(131, 'ann_icon_5', 'fas fa-truck', '2026-05-28 09:48:34'),
(132, 'ann_text_5', 'Fast island-wide delivery', '2026-05-28 09:48:34'),
(133, 'ann_link_5', 'shipping.php', '2026-05-28 09:48:34'),
(134, 'ann_icon_6', 'fas fa-star', '2026-05-28 09:48:34'),
(135, 'ann_text_6', 'Best prices guaranteed - Retail & Wholesale', '2026-05-28 09:48:34'),
(136, 'ann_link_6', 'shop.php', '2026-05-28 09:48:34'),
(174, 'enable_free_delivery_min', '1', '2026-05-28 14:06:39'),
(189, 'pm_cod_enabled', '1', '2026-06-04 12:40:05'),
(190, 'pm_cod_desc', 'Pay in cash when your order arrives.', '2026-05-28 14:59:55'),
(191, 'pm_bank_enabled', '0', '2026-06-11 10:30:03'),
(192, 'pm_bank_desc', 'Transfer to our bank account and share payment reference.', '2026-05-28 14:59:55'),
(193, 'pm_bank_name', 'Commercial Bank', '2026-06-04 11:39:22'),
(194, 'pm_bank_account_name', 'KTJ Kumara', '2026-06-04 11:39:22'),
(195, 'pm_bank_account_number', '1228009159', '2026-06-04 11:39:22'),
(196, 'pm_bank_branch', 'Weliveriya', '2026-06-04 11:39:22'),
(197, 'pm_bank_instructions', 'මුදල් ගෙවීමේදී ඔබගේ Order ID එක හෝ Order දැමීමේදී ඇතුළත් කළ දුරකතන අංකය යොදා මුදල් තැන්පත් කරන්න. තැන්පත් කළ රිසිට්පත අපගේ Whatsapp අංකයට එවන්න.', '2026-06-04 11:39:22'),
(198, 'pm_whatsapp_enabled', '0', '2026-06-06 06:29:58'),
(199, 'pm_whatsapp_desc', 'Finalize your order details with our team via WhatsApp.', '2026-05-28 14:59:55'),
(200, 'pm_payhere_enabled', '1', '2026-06-04 12:40:05'),
(201, 'pm_payhere_desc', 'Pay securely online using card payments.', '2026-05-28 14:59:55'),
(202, 'pm_payhere_merchant_id', '1234636', '2026-06-04 10:39:47'),
(203, 'pm_payhere_merchant_secret', 'MzM0ODA4MzcyMzYwNDgwMzA5MTM0MDAzOTk1MTEzMjUzNzc2NjA3', '2026-06-11 10:37:29'),
(204, 'pm_payhere_sandbox', '1', '2026-05-28 14:59:55'),
(205, 'pm_payhere_notes', '', '2026-05-28 14:59:55'),
(291, 'pm_koko_enabled', '1', '2026-06-04 14:20:24'),
(292, 'pm_koko_desc', 'Pay in 3 interest free instalments with KOKO.', '2026-06-04 12:07:36'),
(293, 'pm_koko_merchant_id', 'c8cca514bdfa0582cdc40c9703c71e9d', '2026-06-11 03:44:33'),
(294, 'pm_koko_api_key', '83fA5n1xUaj8OKnX23YY5vlni5q39gBi', '2026-06-11 03:44:33'),
(295, 'pm_koko_plugin_name', 'customapi', '2026-06-04 12:07:36'),
(296, 'pm_koko_plugin_version', '1.0.1', '2026-06-04 12:07:36'),
(297, 'pm_koko_public_key', '-----BEGIN PUBLIC KEY-----\r\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDwDt4Q9B+MEAcxP8pPeTYGh22\r\nlvCOxxKEwDuJPAvTtYpfiqU1Ip//njnMgWIpFcpIcqabALPrkHW8eD37SBzQ6R5l\r\nfr01xf7lBG3bGqNXZkdXb0txnoXSmPya+B4oGqZc+KWNrKTntY3sNKD6k4tdOeoX\r\n83rxb/gnZR5v7WP7WQIDAQAB\r\n-----END PUBLIC KEY-----', '2026-06-04 12:07:36'),
(298, 'pm_koko_private_key', '-----BEGIN RSA PRIVATE KEY-----\r\nMIICXAIBAAKBgQCfxX3U4Im2TeoUPJUTAyL4IfPQMTd14AaSg8Z7zhDNpbxwhnii\r\nKBIZbOX5cpjmFyuOXDcwzToTBZxue3jGLtH34ttCn6PurO0f8bYDAejCdAQvaCkT\r\nwmaT776u+pOaRLv0SBgCTrktpan4r7YLxp26drPj5qEm8X2vu6tpjdqrQwIDAQAB\r\nAoGAHSC4LBMziBT0U/QniWvM+EfYV2BhqA/LovJ7QR70WUmZ7TnWzFlZ02DTHIMT\r\n9P7MZgvR1AfWSpl/R3UjM55dkPV7tDEMoq/8HnEnWkiXij7eusGdpkgIvwRXD9U6\r\n91ACqEcRD28avAhkaY8msyCYu5kBsIy40KzsvU9nnUrWUAECQQDjvF4107KV2xfs\r\n8otUwmxLVKCDXwCwmvRRKNNULEikN6dWBwGdJ6MAiNkeCcV9u89/xFGsB1uZaRel\r\nwWgbZTFDAkEAs5nBxcHBmtL8gcSt7btm58lSCTSFnuLerMSQOT3TfdmSEoUUOxQA\r\nrdyaepzidCqXXX8t0SebDhZCookk9kf+AQJAE9IpQPYT7QcMpgrWJaJmWogbEFQM\r\nc1KJQScUfZb9G43cephRg6QXg8xlWT/weGkIPk6P7TEWv9ttu3eB4CCGkQJBAIKZ\r\n98+/ovLcJGSVSklK8nzw5+frupMctQJ7eck2TVoB4ff3sAt58zh66BbriL0iz6lc\r\nt0uV+moXA+O/yRISrgECQAygv5ffE9sVqaugxH0PbwDzsO2kwZWcyJpVxGRUww2q\r\nRvK45Dh7Gx7sOR+7vlb1rx0Eplt6xVPJo8Gq4gfvTAM=\r\n-----END RSA PRIVATE KEY-----', '2026-06-11 03:44:33'),
(299, 'pm_koko_sandbox', '1', '2026-06-11 06:28:52'),
(300, 'pm_koko_notes', '', '2026-06-04 12:07:36'),
(559, 'pm_payhere_handling_fee_percent', '3', '2026-06-11 13:05:19'),
(570, 'pm_koko_handling_fee_percent', '0', '2026-06-11 10:30:09'),
(689, 'smtp_host', 'smtp.zoho.com', '2026-06-05 07:26:15'),
(690, 'smtp_port', '465', '2026-06-05 07:26:15'),
(691, 'smtp_encryption', 'ssl', '2026-06-05 07:26:15'),
(692, 'smtp_username', 'admin@gadgethub.lk', '2026-06-05 07:26:15'),
(693, 'smtp_from_name', 'Gadget Hub', '2026-06-05 07:26:15'),
(694, 'smtp_from_email', 'admin@gadgethub.lk', '2026-06-05 07:26:15'),
(695, 'admin_notify_email', 'jeditinghousesl@gmail.com', '2026-06-05 07:26:15'),
(696, 'smtp_password', 'Ghb@953715', '2026-06-05 07:26:15'),
(697, 'seo_site_title', 'Gadget Hub', '2026-06-05 08:13:01'),
(698, 'seo_meta_description', 'Premium computer parts, electronics and accessories in Sri Lanka.', '2026-06-05 08:13:01'),
(699, 'seo_google_verification', '', '2026-06-05 08:13:01'),
(700, 'seo_twitter_handle', '', '2026-06-05 08:13:01'),
(701, 'seo_robots_custom', '', '2026-06-05 08:13:01'),
(702, 'seo_default_image', 'uploads/site/img_6a228ab22d6885.15606651.jpg', '2026-06-05 08:37:06'),
(703, 'site_favicon', 'uploads/site/img_6a228ab22e6041.44901960.jpg', '2026-06-05 08:37:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_phone` (`phone`);

--
-- Indexes for table `delivery_rates`
--
ALTER TABLE `delivery_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `district` (`district`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_order_number` (`order_number`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_slug` (`slug`),
  ADD UNIQUE KEY `uq_sku` (`sku`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `idx_product_categories_category` (`category_id`),
  ADD KEY `idx_product_categories_product` (`product_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_specs`
--
ALTER TABLE `product_specs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `promo_banners`
--
ALTER TABLE `promo_banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_key` (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `delivery_rates`
--
ALTER TABLE `delivery_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=526;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_specs`
--
ALTER TABLE `product_specs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=140;

--
-- AUTO_INCREMENT for table `promo_banners`
--
ALTER TABLE `promo_banners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1088;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_specs`
--
ALTER TABLE `product_specs`
  ADD CONSTRAINT `product_specs_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
