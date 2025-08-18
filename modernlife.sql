-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Aug 18, 2025 at 12:59 AM
-- Server version: 8.3.0
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `modernlife`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE IF NOT EXISTS `activity_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int DEFAULT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`, `created_at`, `updated_at`) VALUES
('modernlife_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:68:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"view_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"manage-roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"create_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"edit_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"delete_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"view_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"create_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"edit_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:18:\"delete_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:10:\"view_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"create_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"edit_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"delete_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"edit_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"manage_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:18:\"view_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:20:\"create_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:18:\"edit_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:20:\"delete_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:20:\"view_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:22:\"create_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:20:\"edit_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:22:\"delete_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:21:\"view_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:23:\"create_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:21:\"edit_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:23:\"delete_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:35:\"view_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:37:\"create_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:35:\"edit_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:37:\"delete_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:24:\"view_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:26:\"create_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:24:\"edit_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:26:\"delete_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:22:\"view_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:24:\"create_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:22:\"edit_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:24:\"delete_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:24:\"view_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:26:\"create_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:24:\"edit_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:26:\"delete_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:32:\"view_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:34:\"create_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:32:\"edit_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:34:\"delete_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:21:\"view_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:23:\"create_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:21:\"edit_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:23:\"delete_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:18:\"view_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:20:\"create_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:18:\"edit_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:20:\"delete_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:22:\"view_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:24:\"create_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:22:\"edit_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:24:\"delete_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:27:\"access_manage_project_tasks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:19:\"access_view_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:32:\"access_review_production_request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:31:\"access_view_production_timeline\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:27:\"الإدارة العليا\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:16:\"المبيعات\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:23:\"إدارة المصنع\";s:1:\"c\";s:3:\"web\";}}}', 1755556166, '2025-08-17 22:29:26', '2025-08-17 22:29:26'),
('modernlife_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1755473243;', 1755473243, '2025-08-17 23:26:23', '2025-08-17 23:26:23'),
('modernlife_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1755473243, '2025-08-17 23:26:23', '2025-08-17 23:26:23'),
('modernlife_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1755477243;', 1755477243, '2025-08-18 00:33:03', '2025-08-18 00:33:03'),
('modernlife_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1755477243, '2025-08-18 00:33:03', '2025-08-18 00:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
CREATE TABLE IF NOT EXISTS `cities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_country_id_foreign` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `country_id`, `created_at`, `updated_at`) VALUES
(2, 'الرياض', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(3, 'جدة', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(4, 'مكة المكرمة', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(5, 'المدينة المنورة', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(6, 'الدمام', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(7, 'الخبر', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(8, 'الطائف', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(9, 'أبها', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(10, 'بريدة', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(11, 'تبوك', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(12, 'حائل', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(13, 'الجبيل', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(14, 'ينبع', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(15, 'نجران', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(16, 'جازان', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(17, 'القطيف', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(18, 'عرعر', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `client_id` int NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) NOT NULL,
  `client_type` enum('individual','company') NOT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `commercial_registration` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `secondary_phone` varchar(20) DEFAULT NULL,
  `address` text,
  `city_id` smallint DEFAULT NULL,
  `country_id` smallint DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `credit_limit` decimal(15,2) DEFAULT '0.00',
  `payment_terms` int DEFAULT '30',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `client_type`, `tax_number`, `commercial_registration`, `email`, `phone`, `secondary_phone`, `address`, `city_id`, `country_id`, `is_active`, `credit_limit`, `payment_terms`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'عميل تجريبي', 'individual', NULL, '251244222', 'new@new.com', '0501234567', NULL, NULL, 6, 1, 1, 0.00, 30, NULL, '2025-07-07 08:30:18', '2025-07-09 04:55:34');

-- --------------------------------------------------------

--
-- Table structure for table `client_contacts`
--

DROP TABLE IF EXISTS `client_contacts`;
CREATE TABLE IF NOT EXISTS `client_contacts` (
  `contact_id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `client_contacts`
--

INSERT INTO `client_contacts` (`contact_id`, `client_id`, `contact_name`, `position`, `email`, `phone`, `is_primary`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'جهة اتصال تجريبية', 'تجربة', 'new@new.new', '0121212121', 0, NULL, '2025-07-07 08:38:53', '2025-07-07 08:38:53');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'السعودية', 'SA', '2025-07-09 08:02:41', '2025-07-09 08:02:41'),
(3, 'الإمارات', 'AE', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(4, 'قطر', 'QA', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(5, 'البحرين', 'BH', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(6, 'الكويت', 'KW', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(7, 'عمان', 'OM', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(8, 'مصر', 'EG', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(9, 'الأردن', 'JO', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(10, 'لبنان', 'LB', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(11, 'فلسطين', 'PS', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(12, 'سوريا', 'SY', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(13, 'العراق', 'IQ', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(14, 'اليمن', 'YE', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(15, 'ليبيا', 'LY', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(16, 'تونس', 'TN', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(17, 'الجزائر', 'DZ', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(18, 'المغرب', 'MA', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(19, 'السودان', 'SD', '2025-07-09 07:31:53', '2025-07-09 07:31:53');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `dept_id` int NOT NULL AUTO_INCREMENT,
  `factory_id` int NOT NULL DEFAULT '1',
  `dept_name` varchar(100) NOT NULL,
  `dept_code` varchar(20) NOT NULL,
  `parent_dept_id` int DEFAULT '0',
  `dept_type` tinyint NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone_extension` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `color_code` varchar(7) DEFAULT '#3498db',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dept_id`),
  KEY `idx_dept_parent` (`parent_dept_id`),
  KEY `idx_dept_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `factory_id`, `dept_name`, `dept_code`, `parent_dept_id`, `dept_type`, `location`, `phone_extension`, `email`, `is_active`, `color_code`, `created_at`, `updated_at`) VALUES
(7, 1, 'الإدارة', 'CODE-MG', NULL, 4, NULL, NULL, 'mg@mg.com', 1, '#3498db', '2025-07-06 06:25:03', '2025-07-06 06:25:03'),
(8, 1, 'المبيعات', 'CODE-SALES', NULL, 4, NULL, NULL, 'sales@sales.com', 1, '#ad0766', '2025-07-06 06:25:36', '2025-07-06 06:25:36'),
(9, 1, 'قسم الألومنيوم', 'DEPT-ALUM', 11, 5, NULL, NULL, NULL, 1, '#3498db', '2025-07-14 11:39:55', '2025-08-04 10:35:58'),
(10, 1, 'قسم الزجاج', 'CODE-GLASS', 11, 5, 'الخبر', NULL, NULL, 1, '#3498db', '2025-08-04 10:34:57', '2025-08-04 10:35:47'),
(11, 1, 'التصنيع', 'CODE-MANUF', NULL, 5, 'الخبر', NULL, NULL, 1, '#3498db', '2025-08-04 10:35:36', '2025-08-04 10:35:36'),
(12, 1, 'قسم الخشب', 'CODE-WOOD', 11, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:37:36', '2025-08-04 10:37:36'),
(13, 1, 'قسم الرخام', 'CODE-MARBLE', 11, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:38:25', '2025-08-04 10:38:25'),
(14, 1, 'قسم الحديد', 'CODE-IRON', 11, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:41:59', '2025-08-04 10:41:59');

-- --------------------------------------------------------

--
-- Table structure for table `department_categories`
--

DROP TABLE IF EXISTS `department_categories`;
CREATE TABLE IF NOT EXISTS `department_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) NOT NULL,
  `description` text,
  `color_code` varchar(7) DEFAULT '#95a5a6',
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `department_categories`
--

INSERT INTO `department_categories` (`category_id`, `category_name`, `description`, `color_code`, `icon`, `created_at`, `updated_at`) VALUES
(4, 'إداري', NULL, '#de001b', NULL, '2025-07-03 15:39:55', '2025-07-03 15:42:00'),
(5, 'إنتاجي', NULL, '#089600', NULL, '2025-07-03 15:40:20', '2025-07-03 15:42:11'),
(6, 'خدمي', NULL, '#00438a', NULL, '2025-07-03 15:40:30', '2025-07-03 15:42:23');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `employee_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `employee_name` varchar(100) NOT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `address` text,
  `department_id` int DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contractor') DEFAULT 'full_time',
  `is_active` tinyint(1) DEFAULT '1',
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`employee_id`),
  KEY `idx_employees_department` (`department_id`),
  KEY `idx_employees_status` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `national_id`, `employee_name`, `gender`, `birth_date`, `email`, `phone`, `address`, `department_id`, `position`, `hire_date`, `salary`, `employment_type`, `is_active`, `emergency_contact_name`, `emergency_contact_phone`, `notes`, `created_at`, `updated_at`) VALUES
(6, 9, '2264078805', 'Ahmed Mohamed', 'male', '2025-07-10', 'designer_4ever@hotmail.com', '0560645034', 'Dammam', 7, 'Developer', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-07-06 06:51:31', '2025-07-06 09:46:32');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_permissions`
--

DROP TABLE IF EXISTS `group_permissions`;
CREATE TABLE IF NOT EXISTS `group_permissions` (
  `group_id` int NOT NULL,
  `permission_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group_permissions`
--

INSERT INTO `group_permissions` (`group_id`, `permission_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2025-07-06 05:38:48', '2025-07-06 05:38:48'),
(1, 2, '2025-07-06 05:38:48', '2025-07-06 05:38:48');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"a30dfb1b-b322-4e00-b0c1-e7dc36b35783\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"29cb5ac2-fb2a-4702-aa5e-72fd936d00ed\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755473318,\"delay\":null}', 0, NULL, 1755473318, 1755473318),
(2, 'default', '{\"uuid\":\"175ef7ce-01fc-4e4e-8134-d2f21bd3a52d\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:10;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"1a1a7033-ea30-43ca-b3cd-329f9fdfafc6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755475316,\"delay\":null}', 0, NULL, 1755475316, 1755475316),
(3, 'default', '{\"uuid\":\"759ce9c5-77e8-4104-ab73-dde93d657e55\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:11;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"4e7a2b22-9709-4974-aad5-f3c8f50b7380\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755476984,\"delay\":null}', 0, NULL, 1755476984, 1755476984),
(4, 'default', '{\"uuid\":\"41f356ea-de34-4d7b-8211-02c9919aea1c\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"d71bfce9-5b0e-42de-82ca-6b5ec8369379\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755477191,\"delay\":null}', 0, NULL, 1755477191, 1755477191),
(5, 'default', '{\"uuid\":\"1f67d129-2226-482e-8eac-49fe7c3bce93\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:13;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"9e05c03f-1eff-40d7-84e6-215cbb93580b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755477306,\"delay\":null}', 0, NULL, 1755477306, 1755477306),
(6, 'default', '{\"uuid\":\"d29ca60e-6a23-43fb-9889-b9c52a7214c3\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:14;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"9e487379-4857-4cb3-8ba7-8ff856cea145\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755477468,\"delay\":null}', 0, NULL, 1755477468, 1755477468),
(7, 'default', '{\"uuid\":\"2123461d-05b1-49a1-b611-318e4326382b\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:6;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:15;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"e71bacd9-5369-44c1-82b5-77cc261628a0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1755478345,\"delay\":null}', 0, NULL, 1755478345, 1755478345),
(8, 'default', '{\"uuid\":\"2e06a5b0-91b5-4569-9605-873ebab8c8d5\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:4:\\\"open\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:19:\\\"فتح المهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:0;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:52:\\\"http:\\/\\/localhost:8000\\/admin\\/projects\\/14\\/manage-tasks\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:128:\\\"المشروع: مشروع مصنع الرخام — القسم: قسم الألومنيوم — التسليم: غير محدد\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:23:\\\"heroicon-o-check-circle\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:52:\\\"مهمة تصنيع جديدة أُسندت إليك\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"932fee01-4b76-45ce-8475-c8718fae46f1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1755478345,\"delay\":null}', 0, NULL, 1755478345, 1755478345);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons_learned`
--

DROP TABLE IF EXISTS `lessons_learned`;
CREATE TABLE IF NOT EXISTS `lessons_learned` (
  `lesson_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `category` enum('process','quality','schedule','cost','safety') NOT NULL,
  `lesson_description` text NOT NULL,
  `recommendations` text NOT NULL,
  `recorded_by` int NOT NULL,
  `record_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `implemented` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lesson_id`),
  KEY `project_id` (`project_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturing_projects`
--

DROP TABLE IF EXISTS `manufacturing_projects`;
CREATE TABLE IF NOT EXISTS `manufacturing_projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) NOT NULL,
  `client_id` int NOT NULL,
  `production_manager_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `status` enum('pending','design','procurement','production','quality_check','installation','completed','delayed') DEFAULT 'pending',
  `priority` tinyint DEFAULT '2',
  `current_phase` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  KEY `production_manager_id` (`production_manager_id`),
  KEY `idx_projects_status` (`status`),
  KEY `idx_projects_deadline` (`deadline_date`),
  KEY `idx_projects_client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement_units`
--

DROP TABLE IF EXISTS `measurement_units`;
CREATE TABLE IF NOT EXISTS `measurement_units` (
  `unit_id` int NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(20) NOT NULL,
  `unit_symbol` varchar(10) NOT NULL,
  `unit_type` enum('length','weight','volume','area','count') NOT NULL,
  `base_unit` tinyint(1) DEFAULT '0',
  `conversion_factor` decimal(15,6) DEFAULT '1.000000',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_07_09_063841_create_countries_table', 1),
(2, '2025_07_09_063901_create_cities_table', 1),
(3, '2025_07_09_063327_create_production_requests_table', 2),
(4, '2025_07_09_081616_create_showrooms_table', 2),
(5, '2025_07_14_134642_create_production_requests_table', 3),
(6, '2025_07_30_132843_create_projects_table', 4),
(7, '2025_07_30_161309_create_production_tasks_table', 5),
(8, '2025_08_16_161951_create_notifications_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\Employee', 6);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('afeb90fa-2bd5-40b1-9d4b-aa378474a5cb', 'App\\Notifications\\TaskAssignedInAppNotification', 'App\\Models\\User', 9, '{\"title\":\"\\u0645\\u0647\\u0645\\u0629 \\u062a\\u0635\\u0646\\u064a\\u0639 \\u062c\\u062f\\u064a\\u062f\\u0629 \\u0623\\u064f\\u0633\\u0646\\u062f\\u062a \\u0625\\u0644\\u064a\\u0643\",\"body\":\"\\u0627\\u0644\\u0645\\u0634\\u0631\\u0648\\u0639: \\u0645\\u0634\\u0631\\u0648\\u0639 \\u0645\\u0635\\u0646\\u0639 \\u0627\\u0644\\u0631\\u062e\\u0627\\u0645 \\u2014 \\u0627\\u0644\\u0642\\u0633\\u0645: \\u0642\\u0633\\u0645 \\u0627\\u0644\\u0632\\u062c\\u0627\\u062c \\u2014 \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645: \\u063a\\u064a\\u0631 \\u0645\\u062d\\u062f\\u062f\",\"project_id\":14,\"task_id\":14,\"url\":\"http:\\/\\/localhost:8000\\/admin\\/projects\\/14\\/manage-tasks\",\"action_text\":\"\\u0641\\u062a\\u062d \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629\"}', NULL, '2025-08-17 21:37:48', '2025-08-17 21:37:48');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_roles', 'web', '2025-07-06 03:54:15', '2025-07-06 03:54:15'),
(2, 'manage-roles', 'web', '2025-07-06 04:58:21', '2025-07-06 04:58:21'),
(3, 'create_roles', 'web', '2025-07-06 07:18:11', '2025-07-06 07:18:11'),
(4, 'edit_roles', 'web', '2025-07-06 07:18:11', '2025-07-06 07:18:11'),
(5, 'delete_roles', 'web', '2025-07-06 07:18:11', '2025-07-06 07:18:11'),
(6, 'view_permissions', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(7, 'create_permissions', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(8, 'edit_permissions', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(9, 'delete_permissions', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(10, 'view_users', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(11, 'create_users', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(12, 'edit_users', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(13, 'delete_users', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(14, 'manage_users', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(15, 'view_employees', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(16, 'create_employees', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(17, 'edit_employees', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(18, 'delete_employees', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(19, 'manage_employees', 'web', '2025-07-06 07:20:03', '2025-07-06 07:20:03'),
(20, 'view_any_role', 'web', '2025-07-06 08:30:25', '2025-07-06 08:30:25'),
(21, 'view_city_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(22, 'create_city_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(23, 'edit_city_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(24, 'delete_city_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(25, 'view_client_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(26, 'create_client_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(27, 'edit_client_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(28, 'delete_client_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(29, 'view_country_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(30, 'create_country_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(31, 'edit_country_resource', 'web', '2025-08-04 11:27:34', '2025-08-04 11:27:34'),
(32, 'delete_country_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(33, 'view_department_categories_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(34, 'create_department_categories_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(35, 'edit_department_categories_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(36, 'delete_department_categories_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(37, 'view_department_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(38, 'create_department_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(39, 'edit_department_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(40, 'delete_department_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(41, 'view_employee_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(42, 'create_employee_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(43, 'edit_employee_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(44, 'delete_employee_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(45, 'view_permission_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(46, 'create_permission_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(47, 'edit_permission_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(48, 'delete_permission_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(49, 'view_production_request_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(50, 'create_production_request_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(51, 'edit_production_request_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(52, 'delete_production_request_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(53, 'view_project_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(54, 'create_project_resource', 'web', '2025-08-04 11:27:35', '2025-08-04 11:27:35'),
(55, 'edit_project_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(56, 'delete_project_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(57, 'view_role_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(58, 'create_role_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(59, 'edit_role_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(60, 'delete_role_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(61, 'view_showroom_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(62, 'create_showroom_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(63, 'edit_showroom_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(64, 'delete_showroom_resource', 'web', '2025-08-04 11:27:36', '2025-08-04 11:27:36'),
(65, 'access_manage_project_tasks', 'web', '2025-08-04 11:52:45', '2025-08-04 11:52:45'),
(66, 'access_view_project', 'web', '2025-08-04 11:52:45', '2025-08-04 11:52:45'),
(67, 'access_review_production_request', 'web', '2025-08-04 11:52:45', '2025-08-04 11:52:45'),
(68, 'access_view_production_timeline', 'web', '2025-08-04 11:52:45', '2025-08-04 11:52:45');

-- --------------------------------------------------------

--
-- Table structure for table `production_requests`
--

DROP TABLE IF EXISTS `production_requests`;
CREATE TABLE IF NOT EXISTS `production_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_description` text COLLATE utf8mb4_unicode_ci,
  `client_id` bigint UNSIGNED NOT NULL,
  `showroom_id` bigint UNSIGNED NOT NULL,
  `agreement_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','submitted','under_review','approved','rejected','created','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_by` bigint UNSIGNED NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `showroom_id` (`showroom_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_requests`
--

INSERT INTO `production_requests` (`id`, `project_name`, `project_description`, `client_id`, `showroom_id`, `agreement_file`, `status`, `created_by`, `submitted_at`, `created_at`, `updated_at`) VALUES
(6, 'مشروع مصنع الرخام', NULL, 1, 1, 'agreements/01K2CQ47547VKVJ4XKX7DY61BS.png', 'approved', 1, '2025-08-11 14:04:09', '2025-08-11 11:04:09', '2025-08-12 17:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `production_request_files`
--

DROP TABLE IF EXISTS `production_request_files`;
CREATE TABLE IF NOT EXISTS `production_request_files` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_request_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_request_id` (`production_request_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_request_files`
--

INSERT INTO `production_request_files` (`id`, `production_request_id`, `department_id`, `file_path`, `created_at`, `updated_at`) VALUES
(2, 1, 9, 'production_files/01K09XR3JYYE7Z7HHA0JGRRS9G.pdf', '2025-07-16 12:20:23', '2025-07-16 12:30:45'),
(3, 3, 9, 'production_files/01K13T4NNHRFZ4301YVR1HXZAS.png', '2025-07-26 13:47:58', '2025-07-26 13:47:58'),
(4, 4, 9, 'production_files/01K13THR8V45SXVBZ0EYBKD6VW.png', '2025-07-26 13:55:06', '2025-07-26 13:55:06'),
(5, 5, 13, 'production_files/01K1X11ZQQMMWXWVF2S0GH4DF6.png', '2025-08-05 08:49:51', '2025-08-05 08:49:51'),
(6, 5, 12, 'production_files/01K1X11ZR6XYPC6AC8MEPF4GAB.png', '2025-08-05 08:49:51', '2025-08-05 08:49:51'),
(7, 6, 13, 'production_files/01K2CQ475R3ZDRV3K8MFSV2PN1.png', '2025-08-11 11:04:09', '2025-08-11 11:04:09'),
(8, 6, 14, 'production_files/01K2CQ47650CC16TX9TGVRC9R1.png', '2025-08-11 11:04:09', '2025-08-11 11:04:09');

-- --------------------------------------------------------

--
-- Table structure for table `production_request_logs`
--

DROP TABLE IF EXISTS `production_request_logs`;
CREATE TABLE IF NOT EXISTS `production_request_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_request_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `action` enum('draft','submitted','under_review','approved','rejected','created','deleted') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `action_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_request_id` (`production_request_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_request_logs`
--

INSERT INTO `production_request_logs` (`id`, `production_request_id`, `user_id`, `action`, `note`, `action_at`, `created_at`, `updated_at`) VALUES
(29, 6, 1, 'submitted', 'تم تغيير حالة الطلب إلى تم الإرسال', '2025-08-11 11:19:37', '2025-08-11 11:19:37', '2025-08-11 11:19:37'),
(30, 6, 1, 'approved', 'تم تغيير حالة الطلب إلى مقبول', '2025-08-11 11:20:05', '2025-08-11 11:20:05', '2025-08-11 11:20:05'),
(33, 6, 1, 'approved', 'تم تغيير حالة الطلب إلى مقبول', '2025-08-12 17:44:22', '2025-08-12 17:44:22', '2025-08-12 17:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks`
--

DROP TABLE IF EXISTS `production_tasks`;
CREATE TABLE IF NOT EXISTS `production_tasks` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `department_id` int NOT NULL,
  `assigned_to_employee_id` int DEFAULT NULL,
  `assigned_budget` float(9,2) DEFAULT '0.00',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `status` enum('pending','assigned','acknowledged','in_progress','blocked','under_review','rework','completed','closed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_tasks_project_id_foreign` (`project_id`),
  KEY `production_tasks_department_id_foreign` (`department_id`),
  KEY `production_tasks_assigned_to_employee_id_foreign` (`assigned_to_employee_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_tasks`
--

INSERT INTO `production_tasks` (`id`, `project_id`, `department_id`, `assigned_to_employee_id`, `assigned_budget`, `file_path`, `due_date`, `assigned_at`, `received_at`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(15, 14, 9, 6, 400.00, NULL, '2025-08-27', '2025-08-18 00:52:25', NULL, 'completed', '', '2025-08-17 21:52:25', '2025-08-17 21:57:15');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_request_id` bigint UNSIGNED NOT NULL,
  `client_id` int NOT NULL,
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('in_progress','completed','on_hold') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'in_progress',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `production_request_id` (`production_request_id`),
  KEY `projects_client_id_foreign` (`client_id`),
  KEY `projects_created_by_foreign` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `production_request_id`, `client_id`, `project_name`, `description`, `start_date`, `end_date`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(14, 6, 1, 'مشروع مصنع الرخام', NULL, '2025-08-12', NULL, 'in_progress', 1, '2025-08-12 17:44:22', '2025-08-12 17:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `project_delays`
--

DROP TABLE IF EXISTS `project_delays`;
CREATE TABLE IF NOT EXISTS `project_delays` (
  `delay_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `phase_id` int DEFAULT NULL,
  `delay_days` int NOT NULL,
  `delay_reason` text NOT NULL,
  `responsible_party` enum('internal','external','client') NOT NULL,
  `action_taken` text,
  `recorded_by` int NOT NULL,
  `record_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`delay_id`),
  KEY `project_id` (`project_id`),
  KEY `phase_id` (`phase_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

DROP TABLE IF EXISTS `project_files`;
CREATE TABLE IF NOT EXISTS `project_files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `version` varchar(20) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_files_project` (`project_id`),
  KEY `idx_files_phase` (`department_id`),
  KEY `idx_files_type` (`file_type`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `project_files`
--

INSERT INTO `project_files` (`file_id`, `project_id`, `department_id`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `upload_date`, `description`, `version`, `is_current`, `created_at`, `updated_at`) VALUES
(2, 6, 9, '01K09XR3JYYE7Z7HHA0JGRRS9G.pdf', 'production_files/01K09XR3JYYE7Z7HHA0JGRRS9G.pdf', 'pdf', 108027, 1, '2025-08-02 10:39:28', NULL, '1', 1, '2025-08-02 10:39:28', '2025-08-02 10:39:28'),
(3, 8, 13, '01K1X11ZQQMMWXWVF2S0GH4DF6.png', 'production_files/01K1X11ZQQMMWXWVF2S0GH4DF6.png', 'png', 40559, 1, '2025-08-05 09:32:56', NULL, '1', 1, '2025-08-05 09:32:56', '2025-08-05 09:32:56'),
(4, 8, 12, '01K1X11ZR6XYPC6AC8MEPF4GAB.png', 'production_files/01K1X11ZR6XYPC6AC8MEPF4GAB.png', 'png', 40559, 1, '2025-08-05 09:32:56', NULL, '1', 1, '2025-08-05 09:32:56', '2025-08-05 09:32:56'),
(5, 10, 13, '01K2CQ475R3ZDRV3K8MFSV2PN1.png', 'production_files/01K2CQ475R3ZDRV3K8MFSV2PN1.png', 'png', 55616, 1, '2025-08-11 11:05:15', NULL, '1', 1, '2025-08-11 11:05:15', '2025-08-11 11:05:15'),
(6, 10, 14, '01K2CQ47650CC16TX9TGVRC9R1.png', 'production_files/01K2CQ47650CC16TX9TGVRC9R1.png', 'png', 71744, 1, '2025-08-11 11:05:15', NULL, '1', 1, '2025-08-11 11:05:15', '2025-08-11 11:05:15'),
(7, 11, 13, '01K2CQ475R3ZDRV3K8MFSV2PN1.png', 'production_files/01K2CQ475R3ZDRV3K8MFSV2PN1.png', 'png', 55616, 1, '2025-08-11 11:20:05', NULL, '1', 1, '2025-08-11 11:20:05', '2025-08-11 11:20:05'),
(8, 11, 14, '01K2CQ47650CC16TX9TGVRC9R1.png', 'production_files/01K2CQ47650CC16TX9TGVRC9R1.png', 'png', 71744, 1, '2025-08-11 11:20:05', NULL, '1', 1, '2025-08-11 11:20:05', '2025-08-11 11:20:05'),
(13, 14, 13, '01K2CQ475R3ZDRV3K8MFSV2PN1.png', 'production_files/01K2CQ475R3ZDRV3K8MFSV2PN1.png', 'png', 55616, 1, '2025-08-12 17:44:22', NULL, '1', 1, '2025-08-12 17:44:22', '2025-08-12 17:44:22'),
(14, 14, 14, '01K2CQ47650CC16TX9TGVRC9R1.png', 'production_files/01K2CQ47650CC16TX9TGVRC9R1.png', 'png', 71744, 1, '2025-08-12 17:44:22', NULL, '1', 1, '2025-08-12 17:44:22', '2025-08-12 17:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

DROP TABLE IF EXISTS `project_images`;
CREATE TABLE IF NOT EXISTS `project_images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `phase_id` int DEFAULT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_path` varchar(512) NOT NULL,
  `thumbnail_path` varchar(512) DEFAULT NULL,
  `image_type` enum('progress','issue','final','design','other') NOT NULL,
  `taken_by` int DEFAULT NULL,
  `taken_date` datetime DEFAULT NULL,
  `uploaded_by` int NOT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `location` varchar(100) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`image_id`),
  KEY `taken_by` (`taken_by`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_images_project` (`project_id`),
  KEY `idx_images_phase` (`phase_id`),
  KEY `idx_images_type` (`image_type`),
  KEY `idx_images_date` (`taken_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quality_checks`
--

DROP TABLE IF EXISTS `quality_checks`;
CREATE TABLE IF NOT EXISTS `quality_checks` (
  `check_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `phase_id` int NOT NULL,
  `check_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `inspector_id` int NOT NULL,
  `check_type` enum('incoming','in_process','final') NOT NULL,
  `result` enum('passed','failed','conditional') NOT NULL,
  `defects_found` int DEFAULT '0',
  `notes` text,
  `corrective_actions` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`check_id`),
  KEY `project_id` (`project_id`),
  KEY `phase_id` (`phase_id`),
  KEY `inspector_id` (`inspector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'الإدارة العليا', 'web', '2025-07-06 07:18:11', '2025-08-04 11:19:09'),
(2, 'إدارة المصنع', 'web', '2025-08-04 11:57:05', '2025-08-04 11:57:05'),
(3, 'المبيعات', 'web', '2025-08-04 12:03:25', '2025-08-04 12:03:25');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(51, 2),
(54, 2),
(55, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(25, 3),
(26, 3),
(27, 3),
(49, 3),
(50, 3),
(51, 3);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`, `created_at`, `updated_at`) VALUES
('ae5gjilYzWnNi20FRHs2cX5gFAW9XCBtLZaZb4Hq', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiYlRXTmU2SFBWYzE2OG94NVdBSHZ3bHh1YVhTNDBrWmc4emFSMTlYWiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9teS10YXNrcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjk7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiRhaVNNZEI0bjhXWFFDNW5mTGp3QmNlb2kzNW9wampZMS45VTg2LzdkUVBvN0I2U05sRWp6NiI7czo4OiJmaWxhbWVudCI7YTowOnt9fQ==', 1755478647, '2025-08-17 23:26:24', '2025-08-18 00:57:27'),
('XtWE1xy2202HEjypcu4HasJiQP4aiiiGYwzmqEuX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU28zWGhEanZmUHdkbFpTM1RnUWNCb3NWN3VBSFgwRGxCd1V0Tm5LUiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1755472993, '2025-08-17 23:23:03', '2025-08-17 23:23:13'),
('LPHdpf9FCUvcMXgjG8fCgWqlrgrIvbC6V6MUvoIg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36 Edg/139.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibmJBOXB0U3lWZUMzSHVBbVVoRGlYT1NxNnA4VnBZWXpGMU42VTNVYyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9sb2dpbiI7fX0=', 1755473069, '2025-08-17 23:24:26', '2025-08-17 23:24:29'),
('OaAx9YXybONr4sA987YfJxMDAu6Zv4GWEUyAN3Cg', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiamVxZVVPYTVyZHcxcnhLNkFNaWZiMWdreU1OeWRUS0p3TjBaeGp3ZiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYWRtaW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkMTdsRVA5eWhNZGpmano3cmc2ZWRHLm5EbktFT29YamxhdlZ5aUVpY0JtZlROendDQ2NEdlciO30=', 1755477009, '2025-08-17 23:03:17', '2025-08-18 00:30:09'),
('JyuQMApPZdDkyoHyfeMO7BlGJQWY4E2u1kDINRkR', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiamN4NlhINHEzSFVKcE1PbVZyVjVxSEoweE96T3JWWVpFclZSOXVSaCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NTI6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9wcm9qZWN0cy8xNC9tYW5hZ2UtdGFza3MiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkMTdsRVA5eWhNZGpmano3cmc2ZWRHLm5EbktFT29YamxhdlZ5aUVpY0JtZlROendDQ2NEdlciO3M6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1755478742, '2025-08-17 23:23:40', '2025-08-18 00:59:02');

-- --------------------------------------------------------

--
-- Table structure for table `showrooms`
--

DROP TABLE IF EXISTS `showrooms`;
CREATE TABLE IF NOT EXISTS `showrooms` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `city_id` bigint UNSIGNED DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `showrooms_city_id_foreign` (`city_id`),
  KEY `showrooms_manager_id_foreign` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `showrooms`
--

INSERT INTO `showrooms` (`id`, `name`, `address`, `city_id`, `phone`, `email`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'معرض القطيف', 'القطيف - شارع القدس', 17, '013122222', 'qatif@modernlife.com', 6, '2025-07-09 05:28:32', '2025-07-09 05:28:32');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE IF NOT EXISTS `system_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `log_level` enum('info','warning','error','critical') NOT NULL,
  `message` text NOT NULL,
  `context` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_group` varchar(50) DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_settings_key` (`setting_key`),
  KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_group`, `setting_type`, `is_public`, `description`, `created_at`, `updated_at`) VALUES
(2, 'site_name', 'نظام إدارة التصنيع', 'general', 'text', 1, 'اسم النظام المعروض في العنوان', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(3, 'system_email', 'info@factory.com', 'general', 'email', 0, 'البريد الإلكتروني العام للنظام', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(4, 'default_language', 'ar', 'general', 'select', 0, 'اللغة الافتراضية للنظام', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(5, 'notify_on_late_department_response', '1', 'notifications', 'boolean', 0, 'إرسال إشعار عند تأخر القسم عن الرد', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(6, 'notify_manager_on_request', '1', 'notifications', 'boolean', 0, 'إشعار مدير المصنع عند وصول طلب جديد', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(7, 'notification_email', 'notifications@factory.com', 'notifications', 'email', 0, 'البريد المستلم للتنبيهات', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(8, 'factory_name', 'مصنع الحياة الحديثة للأثاث', 'factory', 'text', 1, 'الاسم التجاري للمصنع', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(9, 'factory_logo', 'settings/bullet.png', 'factory', 'file', 1, 'شعار المصنع الرسمي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(10, 'factory_address', 'جدة - المدينة الصناعية', 'factory', 'text', 1, 'عنوان المصنع الرئيسي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(11, 'factory_phone', '+966500000000', 'factory', 'text', 1, 'رقم الهاتف الرئيسي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(12, 'factory_email', 'support@factory.com', 'factory', 'email', 1, 'البريد الإلكتروني الرسمي', '2025-07-09 11:41:12', '2025-07-09 10:02:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'info@a-elsayed.com', NULL, '$2y$12$17lEP9yhMdjfjz7rg6edG.nDnKEOoXjlavVyiEicBmfTNzwCCcDvW', 'S8rfUBD7m3O1pRzwHgRr2t1ge5OgHSSmfLe15Zrj3JQMwocFn7OBJyNxVqs3', '2025-07-03 14:55:58', '2025-07-03 14:55:58'),
(9, 'Ahmed', 'designer_4ever@hotmail.com', NULL, '$2y$12$aiSMdB4n8WXQC5nfLjwBceoi35opjjY1.9U86/7dQPo7B6SNlEjz6', NULL, '2025-07-06 06:51:31', '2025-08-16 12:53:42');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_tasks`
--
ALTER TABLE `production_tasks`
  ADD CONSTRAINT `production_tasks_assigned_to_employee_id_foreign` FOREIGN KEY (`assigned_to_employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_production_request_id_foreign` FOREIGN KEY (`production_request_id`) REFERENCES `production_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
