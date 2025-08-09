-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jul 30, 2025 at 01:29 PM
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
('modernlife_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:20:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"view_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"manage-roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"create_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"edit_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"delete_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"view_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"create_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"edit_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:18:\"delete_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:10:\"view_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"create_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"edit_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"delete_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"edit_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"manage_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:1:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}', 1753966762, '2025-07-30 12:59:22', '2025-07-30 12:59:22'),
('modernlife_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1752066194;', 1752066194, '2025-07-09 13:02:14', '2025-07-09 13:02:14'),
('modernlife_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1752066194, '2025-07-09 13:02:14', '2025-07-09 13:02:14'),
('test_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1752499677;', 1752499677, '2025-07-14 13:26:57', '2025-07-14 13:26:57'),
('test_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1752499677, '2025-07-14 13:26:57', '2025-07-14 13:26:57'),
('test_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:20:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"view_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"manage-roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"create_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"edit_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"delete_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"view_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"create_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"edit_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:18:\"delete_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:10:\"view_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"create_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"edit_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"delete_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"edit_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"manage_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:1:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}}}', 1753626479, '2025-07-26 14:27:59', '2025-07-26 14:27:59'),
('test_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1753548957;', 1753548957, '2025-07-26 16:54:57', '2025-07-26 16:54:57'),
('test_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:2;', 1753548957, '2025-07-26 16:54:57', '2025-07-26 16:55:02');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `factory_id`, `dept_name`, `dept_code`, `parent_dept_id`, `dept_type`, `location`, `phone_extension`, `email`, `is_active`, `color_code`, `created_at`, `updated_at`) VALUES
(7, 1, 'الإدارة', 'CODE-MG', NULL, 4, NULL, NULL, 'mg@mg.com', 1, '#3498db', '2025-07-06 06:25:03', '2025-07-06 06:25:03'),
(8, 1, 'المبيعات', 'CODE-SALES', NULL, 4, NULL, NULL, 'sales@sales.com', 1, '#ad0766', '2025-07-06 06:25:36', '2025-07-06 06:25:36'),
(9, 1, 'قسم الألومنيوم', 'DEPT-ALUM', NULL, 5, NULL, NULL, NULL, 1, '#3498db', '2025-07-14 11:39:55', '2025-07-14 11:39:55');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_07_09_063841_create_countries_table', 1),
(2, '2025_07_09_063901_create_cities_table', 1),
(3, '2025_07_09_063327_create_production_requests_table', 2),
(4, '2025_07_09_081616_create_showrooms_table', 2),
(5, '2025_07_14_134642_create_production_requests_table', 3);

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(20, 'view_any_role', 'web', '2025-07-06 08:30:25', '2025-07-06 08:30:25');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_requests`
--

INSERT INTO `production_requests` (`id`, `project_name`, `project_description`, `client_id`, `showroom_id`, `agreement_file`, `status`, `created_by`, `submitted_at`, `created_at`, `updated_at`) VALUES
(1, 'مشروع تجريبي', 'وصف تجريبي', 1, 1, 'agreements/01K09X53YA9VW2K91YHSZC0FY2.pdf', 'approved', 1, '2025-07-16 15:20:23', '2025-07-16 12:20:23', '2025-07-30 10:00:34'),
(4, 'مشروع تجريبي جديد', NULL, 1, 1, 'agreements/01K13THR8P1RYS53N520EJ00FB.png', 'submitted', 1, '2025-07-26 16:55:06', '2025-07-26 13:55:06', '2025-07-26 14:01:30');

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_request_files`
--

INSERT INTO `production_request_files` (`id`, `production_request_id`, `department_id`, `file_path`, `created_at`, `updated_at`) VALUES
(2, 1, 9, 'production_files/01K09XR3JYYE7Z7HHA0JGRRS9G.pdf', '2025-07-16 12:20:23', '2025-07-16 12:30:45'),
(3, 3, 9, 'production_files/01K13T4NNHRFZ4301YVR1HXZAS.png', '2025-07-26 13:47:58', '2025-07-26 13:47:58'),
(4, 4, 9, 'production_files/01K13THR8V45SXVBZ0EYBKD6VW.png', '2025-07-26 13:55:06', '2025-07-26 13:55:06');

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_request_logs`
--

INSERT INTO `production_request_logs` (`id`, `production_request_id`, `user_id`, `action`, `note`, `action_at`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'draft', 'تم تغيير حالة الطلب إلى: مسودة', '2025-07-26 12:38:08', '2025-07-26 12:38:08', '2025-07-26 12:38:08'),
(2, 1, 1, 'draft', 'تم تغيير حالة الطلب إلى: مسودة', '2025-07-26 12:38:22', '2025-07-26 12:38:22', '2025-07-26 12:38:22'),
(3, 1, 1, 'submitted', 'تم تغيير حالة الطلب إلى submitted', '2025-07-26 12:49:01', '2025-07-26 12:49:01', '2025-07-26 12:49:01'),
(4, 1, 1, 'submitted', 'تم تغيير الحالة إلى: تم الإرسال', '2025-07-26 12:49:01', '2025-07-26 12:49:01', '2025-07-26 12:49:01'),
(5, 1, 1, 'draft', 'تم تغيير حالة الطلب إلى draft', '2025-07-26 12:49:08', '2025-07-26 12:49:08', '2025-07-26 12:49:08'),
(6, 1, 1, 'draft', 'تم تغيير الحالة إلى: مسودة', '2025-07-26 12:49:08', '2025-07-26 12:49:08', '2025-07-26 12:49:08'),
(7, 1, 1, 'under_review', 'تم تغيير حالة الطلب إلى قيد المراجعة', '2025-07-26 12:53:26', '2025-07-26 12:53:26', '2025-07-26 12:53:26'),
(8, 1, 1, 'submitted', 'تم تغيير حالة الطلب إلى تم الإرسال', '2025-07-26 12:53:52', '2025-07-26 12:53:52', '2025-07-26 12:53:52'),
(9, 1, 1, 'under_review', 'تم تغيير حالة الطلب إلى قيد المراجعة', '2025-07-26 13:00:02', '2025-07-26 13:00:02', '2025-07-26 13:00:02'),
(10, 1, 1, 'rejected', 'تم تغيير حالة الطلب إلى مرفوض', '2025-07-26 13:08:26', '2025-07-26 13:08:26', '2025-07-26 13:08:26'),
(11, 1, 1, 'rejected', 'تجربة', '2025-07-26 13:08:26', '2025-07-26 13:08:26', '2025-07-26 13:08:26'),
(12, 3, 1, 'deleted', 'تم حذف الطلب', '2025-07-26 13:54:24', '2025-07-26 13:54:24', '2025-07-26 13:54:24'),
(13, 4, 1, 'created', 'تم إنشاء الطلب', '2025-07-26 13:55:06', '2025-07-26 13:55:06', '2025-07-26 13:55:06'),
(14, 4, 1, 'submitted', 'تم تغيير حالة الطلب إلى تم الإرسال', '2025-07-26 14:01:30', '2025-07-26 14:01:30', '2025-07-26 14:01:30'),
(15, 1, 1, 'approved', 'تم تغيير حالة الطلب إلى مقبول', '2025-07-30 10:00:34', '2025-07-30 10:00:34', '2025-07-30 10:00:34');

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
  `phase_id` int DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int NOT NULL,
  `category` enum('design','contract','report','approval','other') NOT NULL,
  `uploaded_by` int NOT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text,
  `version` varchar(20) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_files_project` (`project_id`),
  KEY `idx_files_phase` (`phase_id`),
  KEY `idx_files_category` (`category`),
  KEY `idx_files_type` (`file_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'web', '2025-07-06 07:18:11', '2025-07-06 07:18:11');

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
(20, 1);

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
('xtw3fTwd7jp2NsXOeGt38Cy1a4o6A5WL3MkE9bzU', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiQm1tcWlFV0tld2JpVWdhN1hPS1NmMmY5WUlMYTRqcEhteWNMcTY4ViI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDE3bEVQOXloTWRqZmp6N3JnNmVkRy5uRG5LRU9vWGpsYXZWeWlFaWNCbWZUTnp3Q0NjRHZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1ODoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3Byb2R1Y3Rpb24tcmVxdWVzdHMvMS90aW1lbGluZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6ODoiZmlsYW1lbnQiO2E6MDp7fX0=', 1753880573, '2025-07-30 12:59:23', '2025-07-30 13:02:53'),
('BwlP2NSXaSLw5MaSBRNad8r8GOtdKy6gGrRP0UCH', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUUE2cnJna0JSU2o0ZHpoakxGSTZLRzdTVk9YTVVDT2hCdWtxVjhQcCI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDE3bEVQOXloTWRqZmp6N3JnNmVkRy5uRG5LRU9vWGpsYXZWeWlFaWNCbWZUTnp3Q0NjRHZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3Byb2R1Y3Rpb24tcmVxdWVzdHMvNC9lZGl0Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1753623807, '2025-07-27 13:42:44', '2025-07-27 13:43:27'),
('OaibTJL1bur5Sle0deANDIdDjFcz2SrToBt1YGfp', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiWlhNUVhZYkE0MGRJVkRZY0VtcXBFWjZVVkdEM1l5anE1UDVPTDduQyI7czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDE3bEVQOXloTWRqZmp6N3JnNmVkRy5uRG5LRU9vWGpsYXZWeWlFaWNCbWZUTnp3Q0NjRHZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo1NDoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL3Byb2R1Y3Rpb24tcmVxdWVzdHMvY3JlYXRlIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJmaWxhbWVudCI7YTowOnt9czo2OiJ0YWJsZXMiO2E6MTp7czoxOToiTGlzdENpdGllc19wZXJfcGFnZSI7czoyOiIxMCI7fX0=', 1753624525, '2025-07-27 13:45:19', '2025-07-27 13:55:25'),
('G6D0KG3BFckTzK9AtGBrGeJlonYbtFMr8oHipSZR', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNGNFdDVTdEJlR2JKaWNiVjhjNU9FbEVvS0VPbERrdEJuRkppczlxSiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1753876158, '2025-07-30 11:49:18', '2025-07-30 11:49:18');

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
(1, 'Admin', 'info@a-elsayed.com', NULL, '$2y$12$17lEP9yhMdjfjz7rg6edG.nDnKEOoXjlavVyiEicBmfTNzwCCcDvW', 'Rcfle7PdN7KIdsj0QzXyZ0JWj8QzLAY4QeYNVoOhlWgOQXkuB4aqpGrZv8DZ', '2025-07-03 14:55:58', '2025-07-03 14:55:58'),
(9, 'Ahmed', 'ahmed@ahmed.com', NULL, '$2y$12$aiSMdB4n8WXQC5nfLjwBceoi35opjjY1.9U86/7dQPo7B6SNlEjz6', NULL, '2025-07-06 06:51:31', '2025-07-06 06:51:31');

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
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
