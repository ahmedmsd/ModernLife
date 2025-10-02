-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 01, 2025 at 09:45 PM
-- Server version: 11.4.8-MariaDB
-- PHP Version: 8.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aelsayed_modernlife`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(190) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`, `created_at`, `updated_at`) VALUES
('modern_life_cache_356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1759237970;', 1759237970, '2025-09-30 13:11:50', '2025-09-30 13:11:50'),
('modern_life_cache_356a192b7913b04c54574d18c28d46e6395428ab', 'i:1;', 1759237970, '2025-09-30 13:11:50', '2025-09-30 13:11:50'),
('modern_life_cache_livewire-rate-limiter:da46ff1dba54d75ccc371827e47ef79083f08011:timer', 'i:1759244630;', 1759244630, '2025-09-30 15:02:50', '2025-09-30 15:02:50'),
('modern_life_cache_livewire-rate-limiter:da46ff1dba54d75ccc371827e47ef79083f08011', 'i:1;', 1759244630, '2025-09-30 15:02:50', '2025-09-30 15:02:50'),
('modern_life_cache_fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b:timer', 'i:1759251217;', 1759251217, '2025-09-30 16:52:37', '2025-09-30 16:52:37'),
('modern_life_cache_fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b', 'i:1;', 1759251217, '2025-09-30 16:52:37', '2025-09-30 16:52:37'),
('modern_life_cache_system_settings_all', 'a:50:{s:9:\"site_name\";s:34:\"نظام إدارة التصنيع\";s:12:\"system_email\";s:16:\"info@factory.com\";s:16:\"default_language\";s:2:\"ar\";s:34:\"notify_on_late_department_response\";s:1:\"1\";s:25:\"notify_manager_on_request\";s:1:\"1\";s:18:\"notification_email\";s:25:\"notifications@factory.com\";s:12:\"factory_name\";s:52:\"مصنع الحياة الحديثة للصناعة \";s:12:\"factory_logo\";s:19:\"settings/bullet.png\";s:15:\"factory_address\";s:46:\"الدمام - المدينة الصناعية\";s:13:\"factory_phone\";s:13:\"+966500000000\";s:13:\"factory_email\";s:19:\"support@factory.com\";s:25:\"purchasing_budget_cap_pct\";s:2:\"50\";s:8:\"app_name\";s:11:\"Modern Life\";s:12:\"company_name\";s:15:\"Modern Life Co.\";s:13:\"support_email\";s:19:\"support@example.com\";s:13:\"support_phone\";N;s:8:\"base_url\";s:21:\"http://localhost:8000\";s:10:\"brand_logo\";s:39:\"settings/01K5BGXA006ZEQBX6B8XDAX6N0.png\";s:13:\"brand_favicon\";N;s:13:\"primary_color\";s:7:\"#0ea5e9\";s:15:\"secondary_color\";s:7:\"#22c55e\";s:6:\"locale\";s:2:\"ar\";s:8:\"timezone\";s:11:\"Asia/Riyadh\";s:11:\"date_format\";s:5:\"Y-m-d\";s:11:\"time_format\";s:5:\"h:i A\";s:14:\"week_starts_on\";s:8:\"saturday\";s:21:\"project_due_soon_days\";s:1:\"3\";s:24:\"task_overdue_grace_hours\";s:2:\"24\";s:13:\"max_upload_mb\";s:2:\"50\";s:18:\"allowed_mime_extra\";N;s:13:\"notify_in_app\";s:1:\"1\";s:12:\"notify_email\";s:1:\"1\";s:12:\"notify_slack\";s:1:\"0\";s:15:\"notify_telegram\";s:1:\"0\";s:17:\"quiet_hours_start\";s:5:\"21:00\";s:15:\"quiet_hours_end\";s:5:\"08:00\";s:17:\"daily_digest_time\";s:5:\"09:00\";s:17:\"weekly_digest_day\";s:6:\"sunday\";s:18:\"weekly_digest_time\";s:5:\"09:00\";s:14:\"mail_from_name\";s:11:\"Modern Life\";s:17:\"mail_from_address\";s:21:\"noreply@a-elsayed.com\";s:9:\"smtp_host\";s:18:\"mail.a-elsayed.com\";s:9:\"smtp_port\";s:3:\"587\";s:13:\"smtp_username\";s:21:\"noreply@a-elsayed.com\";s:13:\"smtp_password\";s:200:\"eyJpdiI6IlFSNXVRRk8vUXNHMEkzTHpSQUFHWWc9PSIsInZhbHVlIjoiTUhxRkVpQWxXY1NCVnN3dy9CNFpXQT09IiwibWFjIjoiMmE2NDMxODczNjBkOGI2NzQ2NGM3Njg2NWM3N2FjYmRjYzk4ODhkYWI4ZDEwYzk3NmZmMDUyODUzODIxNGRiMSIsInRhZyI6IiJ9\";s:15:\"smtp_encryption\";s:3:\"ssl\";s:17:\"slack_webhook_url\";N;s:18:\"telegram_bot_token\";s:200:\"eyJpdiI6IjRBakxxTkFiUkcxeUN6YklldmVyd1E9PSIsInZhbHVlIjoiaE0rZ3NZR3RabjArajRBbWl6VE1Mdz09IiwibWFjIjoiNDAwNzkzNmViMDMxMzYyZmY3NDVjMjZjNTU3M2FiNzJiNDZhNDU5YmYyOGI3ZGI0NWY0OTNjZWJiOWUyMTdmYyIsInRhZyI6IiJ9\";s:16:\"telegram_chat_id\";N;s:11:\"webhook_url\";N;}', 2074604260, '2025-09-30 14:57:40', '2025-09-30 14:57:40'),
('modern_life_cache_1574bddb75c78a6fd2251d61e2993b5146201319:timer', 'i:1759252327;', 1759252327, '2025-09-30 17:11:07', '2025-09-30 17:11:07'),
('modern_life_cache_1574bddb75c78a6fd2251d61e2993b5146201319', 'i:1;', 1759252327, '2025-09-30 17:11:07', '2025-09-30 17:11:07'),
('modern_life_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:78:{i:0;a:4:{s:1:\"a\";s:1:\"1\";s:1:\"b\";s:10:\"view_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";s:1:\"2\";s:1:\"b\";s:12:\"manage-roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";s:1:\"3\";s:1:\"b\";s:12:\"create_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";s:1:\"4\";s:1:\"b\";s:10:\"edit_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";s:1:\"5\";s:1:\"b\";s:12:\"delete_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";s:1:\"6\";s:1:\"b\";s:16:\"view_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";s:1:\"7\";s:1:\"b\";s:18:\"create_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";s:1:\"8\";s:1:\"b\";s:16:\"edit_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";s:1:\"9\";s:1:\"b\";s:18:\"delete_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";s:2:\"10\";s:1:\"b\";s:10:\"view_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";s:2:\"11\";s:1:\"b\";s:12:\"create_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";s:2:\"12\";s:1:\"b\";s:10:\"edit_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";s:2:\"13\";s:1:\"b\";s:12:\"delete_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";s:2:\"14\";s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";s:2:\"15\";s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";s:2:\"16\";s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";s:2:\"17\";s:1:\"b\";s:14:\"edit_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";s:2:\"18\";s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";s:2:\"19\";s:1:\"b\";s:16:\"manage_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";s:2:\"20\";s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";s:2:\"21\";s:1:\"b\";s:18:\"view_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";s:2:\"22\";s:1:\"b\";s:20:\"create_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";s:2:\"23\";s:1:\"b\";s:18:\"edit_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:\"a\";s:2:\"24\";s:1:\"b\";s:20:\"delete_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:\"a\";s:2:\"25\";s:1:\"b\";s:20:\"view_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:25;a:4:{s:1:\"a\";s:2:\"26\";s:1:\"b\";s:22:\"create_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:26;a:4:{s:1:\"a\";s:2:\"27\";s:1:\"b\";s:20:\"edit_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:27;a:4:{s:1:\"a\";s:2:\"28\";s:1:\"b\";s:22:\"delete_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:\"a\";s:2:\"29\";s:1:\"b\";s:21:\"view_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";s:2:\"30\";s:1:\"b\";s:23:\"create_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:30;a:4:{s:1:\"a\";s:2:\"31\";s:1:\"b\";s:21:\"edit_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:4:{s:1:\"a\";s:2:\"32\";s:1:\"b\";s:23:\"delete_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:\"a\";s:2:\"33\";s:1:\"b\";s:35:\"view_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";s:2:\"34\";s:1:\"b\";s:37:\"create_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";s:2:\"35\";s:1:\"b\";s:35:\"edit_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:35;a:4:{s:1:\"a\";s:2:\"36\";s:1:\"b\";s:37:\"delete_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";s:2:\"37\";s:1:\"b\";s:24:\"view_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:4:{s:1:\"a\";s:2:\"38\";s:1:\"b\";s:26:\"create_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:38;a:4:{s:1:\"a\";s:2:\"39\";s:1:\"b\";s:24:\"edit_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:\"a\";s:2:\"40\";s:1:\"b\";s:26:\"delete_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";s:2:\"41\";s:1:\"b\";s:22:\"view_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";s:2:\"42\";s:1:\"b\";s:24:\"create_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:\"a\";s:2:\"43\";s:1:\"b\";s:22:\"edit_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:\"a\";s:2:\"44\";s:1:\"b\";s:24:\"delete_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:44;a:4:{s:1:\"a\";s:2:\"45\";s:1:\"b\";s:24:\"view_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";s:2:\"46\";s:1:\"b\";s:26:\"create_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:46;a:4:{s:1:\"a\";s:2:\"47\";s:1:\"b\";s:24:\"edit_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:47;a:4:{s:1:\"a\";s:2:\"48\";s:1:\"b\";s:26:\"delete_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:48;a:4:{s:1:\"a\";s:2:\"49\";s:1:\"b\";s:32:\"view_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:3;i:2;i:6;i:3;i:7;}}i:49;a:4:{s:1:\"a\";s:2:\"50\";s:1:\"b\";s:34:\"create_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:6;}}i:50;a:4:{s:1:\"a\";s:2:\"51\";s:1:\"b\";s:32:\"edit_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:6;i:4;i:7;}}i:51;a:4:{s:1:\"a\";s:2:\"52\";s:1:\"b\";s:34:\"delete_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:4:{s:1:\"a\";s:2:\"53\";s:1:\"b\";s:21:\"view_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:6;i:2;i:7;}}i:53;a:4:{s:1:\"a\";s:2:\"54\";s:1:\"b\";s:23:\"create_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:7;}}i:54;a:4:{s:1:\"a\";s:2:\"55\";s:1:\"b\";s:21:\"edit_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:7;}}i:55;a:4:{s:1:\"a\";s:2:\"56\";s:1:\"b\";s:23:\"delete_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:56;a:4:{s:1:\"a\";s:2:\"57\";s:1:\"b\";s:18:\"view_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:4:{s:1:\"a\";s:2:\"58\";s:1:\"b\";s:20:\"create_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:58;a:4:{s:1:\"a\";s:2:\"59\";s:1:\"b\";s:18:\"edit_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:59;a:4:{s:1:\"a\";s:2:\"60\";s:1:\"b\";s:20:\"delete_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:60;a:4:{s:1:\"a\";s:2:\"61\";s:1:\"b\";s:22:\"view_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";s:2:\"62\";s:1:\"b\";s:24:\"create_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:62;a:4:{s:1:\"a\";s:2:\"63\";s:1:\"b\";s:22:\"edit_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:63;a:4:{s:1:\"a\";s:2:\"64\";s:1:\"b\";s:24:\"delete_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:64;a:4:{s:1:\"a\";s:2:\"65\";s:1:\"b\";s:27:\"access_manage_project_tasks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:5;i:4;i:6;i:5;i:7;}}i:65;a:4:{s:1:\"a\";s:2:\"66\";s:1:\"b\";s:19:\"access_view_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:6;i:4;i:7;}}i:66;a:4:{s:1:\"a\";s:2:\"67\";s:1:\"b\";s:32:\"access_review_production_request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;i:5;i:7;}}i:67;a:4:{s:1:\"a\";s:2:\"68\";s:1:\"b\";s:31:\"access_view_production_timeline\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;i:5;i:7;}}i:68;a:4:{s:1:\"a\";s:2:\"69\";s:1:\"b\";s:18:\"view_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:4;i:1;i:5;i:2;i:6;i:3;i:7;}}i:69;a:4:{s:1:\"a\";s:2:\"70\";s:1:\"b\";s:20:\"create_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:7;}}i:70;a:4:{s:1:\"a\";s:2:\"71\";s:1:\"b\";s:18:\"edit_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:71;a:4:{s:1:\"a\";s:2:\"72\";s:1:\"b\";s:20:\"delete_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:72;a:4:{s:1:\"a\";s:2:\"73\";s:1:\"b\";s:12:\"manage-tasks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:6;i:1;i:7;}}i:73;a:3:{s:1:\"a\";s:2:\"74\";s:1:\"b\";s:35:\"view_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";}i:74;a:4:{s:1:\"a\";s:2:\"75\";s:1:\"b\";s:37:\"create_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:75;a:4:{s:1:\"a\";s:2:\"76\";s:1:\"b\";s:35:\"edit_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:76;a:3:{s:1:\"a\";s:2:\"77\";s:1:\"b\";s:37:\"delete_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";}i:77;a:3:{s:1:\"a\";s:2:\"78\";s:1:\"b\";s:27:\"view_material_requests_done\";s:1:\"c\";s:3:\"web\";}}s:5:\"roles\";a:7:{i:0;a:3:{s:1:\"a\";s:1:\"1\";s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";s:1:\"2\";s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";s:1:\"3\";s:1:\"b\";s:5:\"sales\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";s:1:\"6\";s:1:\"b\";s:16:\"showroom_manager\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";s:1:\"7\";s:1:\"b\";s:15:\"factory_manager\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";s:1:\"4\";s:1:\"b\";s:18:\"department_manager\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";s:1:\"5\";s:1:\"b\";s:18:\"purchasing_manager\";s:1:\"c\";s:3:\"web\";}}}', 1759435682, '2025-10-01 20:08:02', '2025-10-01 20:08:02'),
('modern_life_cache_12c6fc06c99a462375eeb3f43dfd832b08ca9e17:timer', 'i:1759253835;', 1759253835, '2025-09-30 17:36:15', '2025-09-30 17:36:15'),
('modern_life_cache_12c6fc06c99a462375eeb3f43dfd832b08ca9e17', 'i:1;', 1759253835, '2025-09-30 17:36:15', '2025-09-30 17:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(190) NOT NULL,
  `owner` varchar(190) NOT NULL,
  `expiration` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

CREATE TABLE `cities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `country_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `cities`
--

INSERT INTO `cities` (`id`, `name`, `country_id`, `created_at`, `updated_at`) VALUES
(2, 'الرياض', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(6, 'الدمام', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(7, 'الخبر', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(13, 'الجبيل', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(17, 'القطيف', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(21, 'سيهـات', 1, '2025-09-10 12:14:10', '2025-09-10 12:14:23'),
(22, 'تاروت', 1, '2025-09-17 16:52:23', '2025-09-17 16:52:23'),
(23, 'الاحساء', 1, '2025-09-17 16:52:42', '2025-09-17 16:52:42'),
(24, 'الظهران', 1, '2025-09-17 16:52:57', '2025-09-17 16:52:57'),
(25, 'الهفوف', 1, '2025-09-17 16:53:38', '2025-09-17 16:53:38'),
(26, 'بقيق', 1, '2025-09-17 16:53:51', '2025-09-17 16:53:51'),
(27, 'الراكة', 1, '2025-09-17 16:54:18', '2025-09-17 16:54:18');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_type` enum('individual','company') NOT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `commercial_registration` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `secondary_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city_id` smallint(6) DEFAULT NULL,
  `country_id` smallint(6) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `payment_terms` int(11) DEFAULT 30,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `client_type`, `tax_number`, `commercial_registration`, `email`, `phone`, `secondary_phone`, `address`, `city_id`, `country_id`, `is_active`, `credit_limit`, `payment_terms`, `notes`, `created_at`, `updated_at`) VALUES
(3, 'عبدالله علي ال عبدالوهاب', 'individual', NULL, NULL, NULL, '0553653067', NULL, NULL, 17, 1, 1, 0.00, 30, NULL, '2025-09-17 11:49:59', '2025-09-17 11:49:59'),
(4, 'نازك عامر', 'individual', NULL, NULL, NULL, '0535744493', NULL, NULL, 17, 1, 1, 0.00, 30, NULL, '2025-09-28 17:43:26', '2025-09-28 17:43:26'),
(5, 'احمد محمد تجربة', 'individual', NULL, NULL, NULL, '054444555', NULL, NULL, 13, 1, 1, 0.00, 30, NULL, '2025-09-30 17:55:36', '2025-09-30 17:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `client_contacts`
--

CREATE TABLE `client_contacts` (
  `contact_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'السعودية', 'SA', '2025-07-09 08:02:41', '2025-07-09 08:02:41'),
(3, 'الإمارات', 'AE', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(4, 'قطر', 'QA', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(5, 'البحرين', 'BH', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(6, 'الكويت', 'KW', '2025-07-09 07:31:53', '2025-07-09 07:31:53'),
(7, 'عُمان', 'OM', '2025-07-09 07:31:53', '2025-09-10 12:13:31'),
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

CREATE TABLE `departments` (
  `dept_id` int(11) NOT NULL,
  `factory_id` int(11) NOT NULL DEFAULT 1,
  `dept_name` varchar(100) NOT NULL,
  `dept_code` varchar(20) NOT NULL,
  `parent_dept_id` int(11) DEFAULT 0,
  `manager_id` int(11) DEFAULT NULL,
  `dept_type` tinyint(4) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `phone_extension` varchar(10) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `color_code` varchar(7) DEFAULT '#3498db',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `factory_id`, `dept_name`, `dept_code`, `parent_dept_id`, `manager_id`, `dept_type`, `location`, `phone_extension`, `email`, `is_active`, `color_code`, `created_at`, `updated_at`) VALUES
(7, 1, 'الإدارة', 'CODE-MG', NULL, NULL, 4, NULL, NULL, 'mg@mg.com', 1, '#3498db', '2025-07-06 06:25:03', '2025-07-06 06:25:03'),
(8, 1, 'المبيعات', 'CODE-SALES', NULL, NULL, 4, NULL, NULL, 'sales@sales.com', 1, '#ad0766', '2025-07-06 06:25:36', '2025-07-06 06:25:36'),
(9, 1, 'قسم الألومنيوم', 'DEPT-ALUM', 11, 13, 5, NULL, NULL, NULL, 1, '#3498db', '2025-07-14 11:39:55', '2025-09-30 17:05:55'),
(10, 1, 'قسم الزجاج', 'CODE-GLASS', 11, 6, 5, 'الخبر', NULL, NULL, 1, '#3498db', '2025-08-04 10:34:57', '2025-08-20 05:37:45'),
(11, 1, 'التصنيع', 'CODE-MANUF', NULL, NULL, 5, 'الخبر', NULL, NULL, 1, '#3498db', '2025-08-04 10:35:36', '2025-08-04 10:35:36'),
(12, 1, 'قسم الخشب', 'CODE-WOOD', 11, NULL, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:37:36', '2025-08-04 10:37:36'),
(13, 1, 'قسم الرخام', 'CODE-MARBLE', 11, 6, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:38:25', '2025-09-15 07:29:25'),
(14, 1, 'قسم الحديد', 'CODE-IRON', 11, NULL, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:41:59', '2025-08-04 10:41:59'),
(15, 1, 'المشتريات', 'DEPT-PCH', 7, 7, 6, NULL, NULL, NULL, 1, '#3498db', '2025-08-20 04:41:32', '2025-08-20 05:37:27');

-- --------------------------------------------------------

--
-- Table structure for table `department_categories`
--

CREATE TABLE `department_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color_code` varchar(7) DEFAULT '#95a5a6',
  `icon` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `national_id` varchar(20) DEFAULT NULL,
  `employee_name` varchar(100) NOT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contractor') DEFAULT 'full_time',
  `is_active` tinyint(1) DEFAULT 1,
  `emergency_contact_name` varchar(100) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `national_id`, `employee_name`, `gender`, `birth_date`, `email`, `phone`, `address`, `department_id`, `position`, `hire_date`, `salary`, `employment_type`, `is_active`, `emergency_contact_name`, `emergency_contact_phone`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(10, 13, '1113597320', 'كوثر عبدالعزيز الزهيري', NULL, NULL, 'salesmodernlife@gmail.com', '0533089918', NULL, 8, 'مهندس مبيعات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 09:38:18', '2025-09-17 07:06:17', NULL),
(11, 14, '1075565000', 'حوراء عيسى ال شيبان', NULL, NULL, 'eng.hawra.e@modern-life.net', '0534322553', NULL, 8, 'مهندس مبيعات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 09:48:19', '2025-09-17 07:06:20', NULL),
(12, 15, '1065245688', 'الشيخ علي البرباري', NULL, NULL, 'ali.albarbari@modern-life.net', '0543315404', NULL, 7, 'مدير المصنع', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 09:55:51', '2025-09-17 07:06:22', NULL),
(13, 16, '2606911978', 'محمد رزق شطا', NULL, NULL, 'eng555666777@gmail.com', '0509163262', NULL, 9, 'مسؤول قسم الالمنيوم', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 10:19:18', '2025-09-17 07:42:30', NULL),
(14, 17, '2205788660', 'نايف مقبل سالم الحطامي', NULL, NULL, 'warehouse@modern-life.net', '0549887675', NULL, 15, 'مسؤول قسم المشتريات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 10:29:26', '2025-09-17 07:42:33', NULL),
(15, 18, '2386250498', 'عمرو احمد عمر النوساني', NULL, NULL, 'amroalnawasany9900@gmail.com', '0566805580', NULL, 12, 'مسؤول قسم الخشب ', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 10:36:19', '2025-09-17 07:42:35', NULL),
(16, 19, '2521715017', 'فهمي جمعه فهمي', NULL, NULL, 'fahmyelahly@gmail.com', '0539322462', NULL, 11, 'مشرف الجودة والتركيب ', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 10:44:20', '2025-09-17 10:51:15', NULL),
(17, 20, '2431499769', 'شهاب الدين وحيد محمد', NULL, NULL, 'shehab@modern-life.net', '0534004668', NULL, 8, 'مهندس مبيعات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 10:59:06', '2025-09-17 11:00:18', NULL),
(18, 21, '2016609949', 'نازك عامر', NULL, NULL, 'sales@modern-life.net', '0535744493', NULL, 7, 'مدير معرض', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 11:05:45', '2025-09-17 11:05:45', NULL),
(19, 22, '0000000000', 'هاني ال حاجي', NULL, NULL, 'ceo@modern-life.net', '0506848583', NULL, 7, 'الرئيس التنفيذي', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 11:16:30', '2025-09-17 11:16:30', NULL),
(20, 23, '2264105319', 'عناد ناصر أحمد', NULL, NULL, 'einad3775@gmail.com', '0580161903', NULL, 14, 'مسؤول قسم الحديد', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 15:42:44', '2025-09-17 15:42:44', NULL),
(21, 24, '2490099617', 'محمد أحمد عبد العزيز', NULL, NULL, 'Mohamed.abdulaziz@modern-life.net', '0545513228', NULL, 8, 'مدير قسم المبيعات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 15:47:48', '2025-09-17 15:47:48', NULL),
(22, 25, '2398267373', 'احمد محمد صالح', NULL, NULL, 'Ahmed@modern-life.net', '0564471217', NULL, 8, 'مهندس مبيعات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 15:51:27', '2025-09-17 15:51:27', NULL),
(23, 26, '2405572856', 'شعبان عبدالقادر شعبان', NULL, NULL, 'shaaban@modern-life.net', '0542485208', NULL, 7, 'مدير معرض', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 15:58:22', '2025-09-17 15:58:22', NULL),
(24, 27, '2347696615', 'ابراهيم رضا هويدي', NULL, NULL, 'hewidybebo@gmail.com', '0547533660', NULL, 13, 'مسؤول قسم الرخام', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 16:05:16', '2025-09-17 16:05:16', NULL),
(25, 28, '2317865265', 'مصطفي السيد عبدالله ', NULL, NULL, 'factory@modern-life.net', '0564186144', NULL, 8, 'مهندس مبيعات', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-17 16:47:44', '2025-09-17 16:47:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(190) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_permissions`
--

CREATE TABLE `group_permissions` (
  `group_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(190) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(256, 'default', '{\"uuid\":\"bdb960e8-bc6f-4091-9c52-41a1e55cce3b\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"899cf545-2801-4b62-99da-33072a2e7126\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758117835,\"delay\":null}', 0, NULL, 1758117835, 1758117835),
(257, 'default', '{\"uuid\":\"2469e139-7cef-42be-a40e-8c992ef940f0\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"899cf545-2801-4b62-99da-33072a2e7126\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758117835,\"delay\":null}', 0, NULL, 1758117835, 1758117835),
(258, 'default', '{\"uuid\":\"524e8f24-4a0a-4041-900a-48ab9ab48c10\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"7167e0f5-151d-4c52-8489-d3444a5c6e1b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758117835,\"delay\":null}', 0, NULL, 1758117835, 1758117835),
(259, 'default', '{\"uuid\":\"4dd22e9e-4ce2-417e-8e02-c2e08b23d7b9\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"7167e0f5-151d-4c52-8489-d3444a5c6e1b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758117835,\"delay\":null}', 0, NULL, 1758117835, 1758117835),
(260, 'default', '{\"uuid\":\"833ceb0f-14cd-4797-8fc8-abe635cce962\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"7e499834-5247-4f84-8471-79ea8d01709f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758118958,\"delay\":null}', 0, NULL, 1758118958, 1758118958),
(261, 'default', '{\"uuid\":\"8a21f6e1-adf5-4f13-943b-5c87400d6e69\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"7e499834-5247-4f84-8471-79ea8d01709f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758118958,\"delay\":null}', 0, NULL, 1758118958, 1758118958),
(262, 'default', '{\"uuid\":\"65cd3806-3ca7-4dc3-9c60-dfd87ecdbdce\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:37;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:1;s:2:\\\"id\\\";s:36:\\\"03e1683a-a63e-4f4f-a656-0f154c9cb1a6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758119683,\"delay\":null}', 0, NULL, 1758119683, 1758119683),
(263, 'default', '{\"uuid\":\"5de81081-40eb-4987-ae3b-c67c1352d38a\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:23:\\\"رقم المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"da9f5173-a022-4ac9-9478-164c10b884fc\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758119842,\"delay\":null}', 0, NULL, 1758119842, 1758119842),
(264, 'default', '{\"uuid\":\"1bb5fc28-4322-4007-82dc-9cfcb2ea3024\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"body\\\";s:23:\\\"رقم المهمة #37\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"c23b85c2-e03d-48e9-a656-963d3e3da000\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758119842,\"delay\":null}', 0, NULL, 1758119842, 1758119842),
(265, 'default', '{\"uuid\":\"7ffeb3a0-a27f-4bc0-b249-62f2624118e6\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:17;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:81:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: طلب خامات\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"abac3388-b7d9-455a-8b63-8c799edbd355\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758120083,\"delay\":null}', 0, NULL, 1758120083, 1758120083),
(266, 'default', '{\"uuid\":\"e8be6360-3180-46c0-979c-b77ff9997aad\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:17;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"body\\\";s:81:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: طلب خامات\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"d3afd484-2da5-4861-9be3-9a40f8926807\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758120083,\"delay\":null}', 0, NULL, 1758120083, 1758120083),
(267, 'default', '{\"uuid\":\"dbd3ab99-2590-41a3-a177-6483b59be23a\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:50:\\\"المهمة #37 بانتظار المشتريات\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:37:\\\"تم إرسال طلب الخامات\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"4735ecf6-774a-442f-b9d4-ad32970c0049\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1758120083,\"delay\":null}', 0, NULL, 1758120083, 1758120083),
(268, 'default', '{\"uuid\":\"9c145201-63cd-4cdf-b90d-cfe34aa9e4e8\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:37:\\\"تم إرسال طلب الخامات\\\";s:4:\\\"body\\\";s:50:\\\"المهمة #37 بانتظار المشتريات\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"d6107410-2cf6-4983-b5e7-a3299e4b5dbe\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1758120083,\"delay\":null}', 0, NULL, 1758120083, 1758120083),
(269, 'default', '{\"uuid\":\"760d4db3-9533-4fd0-b0b8-d8a9c58bfa43\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"adf7b16f-e90d-49f5-89a6-76ba21f41295\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071108,\"delay\":null}', 0, NULL, 1759071108, 1759071108),
(270, 'default', '{\"uuid\":\"eef8cd53-732a-40b2-b11a-2b2f7c95ba19\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"adf7b16f-e90d-49f5-89a6-76ba21f41295\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071108,\"delay\":null}', 0, NULL, 1759071108, 1759071108),
(271, 'default', '{\"uuid\":\"f50a0748-e1cf-442e-8ea4-d4b3c680712e\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"90659ee5-88e5-46bf-ac78-909c97e60cac\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071108,\"delay\":null}', 0, NULL, 1759071108, 1759071108),
(272, 'default', '{\"uuid\":\"337de85c-fef3-4d08-980b-e76436ece37a\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"90659ee5-88e5-46bf-ac78-909c97e60cac\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071108,\"delay\":null}', 0, NULL, 1759071108, 1759071108),
(273, 'default', '{\"uuid\":\"db9d4fd3-6461-49b4-a2ea-525289a471ff\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"project\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"034e2544-33b5-49b7-84bf-6878d1b784a2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071200,\"delay\":null}', 0, NULL, 1759071200, 1759071200),
(274, 'default', '{\"uuid\":\"f2ab366f-d442-4d62-a223-78fd5ff316f7\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"project\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"034e2544-33b5-49b7-84bf-6878d1b784a2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071200,\"delay\":null}', 0, NULL, 1759071200, 1759071200),
(275, 'default', '{\"uuid\":\"21b99015-7e62-4145-86ae-639f6876b669\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"d615e0c4-83db-4c12-a99c-b2594df2215a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071240,\"delay\":null}', 0, NULL, 1759071240, 1759071240),
(276, 'default', '{\"uuid\":\"35382b41-318c-4ea9-918a-e309ab7a14b8\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"d615e0c4-83db-4c12-a99c-b2594df2215a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071240,\"delay\":null}', 0, NULL, 1759071240, 1759071240),
(277, 'default', '{\"uuid\":\"db4fa46f-5827-4f9d-bad4-dead71a480e1\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"60b5c5e8-2ce7-4c50-8457-f602f7168d25\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071255,\"delay\":null}', 0, NULL, 1759071255, 1759071255),
(278, 'default', '{\"uuid\":\"b11b8bef-3624-4ebb-8dd7-efc867206d63\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"60b5c5e8-2ce7-4c50-8457-f602f7168d25\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071255,\"delay\":null}', 0, NULL, 1759071255, 1759071255),
(279, 'default', '{\"uuid\":\"3d45b2fa-2b3d-4ec9-9244-6c89f263a38e\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e35890a2-7c4c-491b-a180-2e6f7495e1d6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071281,\"delay\":null}', 0, NULL, 1759071281, 1759071281),
(280, 'default', '{\"uuid\":\"86eddfb0-0f23-4f69-8f43-ec3d2a87c87f\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e35890a2-7c4c-491b-a180-2e6f7495e1d6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071281,\"delay\":null}', 0, NULL, 1759071281, 1759071281),
(281, 'default', '{\"uuid\":\"2198b5e9-700b-4c71-b534-fd0087913b07\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"21639664-964f-486d-8029-0d8d4ba5b4c2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071295,\"delay\":null}', 0, NULL, 1759071295, 1759071295),
(282, 'default', '{\"uuid\":\"4c0cdd4f-a93f-4357-be61-97029798f7c1\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"21639664-964f-486d-8029-0d8d4ba5b4c2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071295,\"delay\":null}', 0, NULL, 1759071295, 1759071295),
(283, 'default', '{\"uuid\":\"e1d80a4a-effa-49de-ae4f-164a6c0baeab\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:8:\\\"approved\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e23d3379-036d-4912-9189-488782a040ec\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071308,\"delay\":null}', 0, NULL, 1759071308, 1759071308),
(284, 'default', '{\"uuid\":\"b5b38337-0c25-49fe-b920-13e981ab4b14\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:8:\\\"approved\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e23d3379-036d-4912-9189-488782a040ec\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071308,\"delay\":null}', 0, NULL, 1759071308, 1759071308),
(285, 'default', '{\"uuid\":\"328eb40c-83f4-427b-a108-5fab7b8f7853\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:1:{i:0;s:5:\\\"files\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"approved\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"d773c759-c0de-4b8d-9bc2-0410dca34f3c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071308,\"delay\":null}', 0, NULL, 1759071308, 1759071308),
(286, 'default', '{\"uuid\":\"d18f873a-043a-4284-af9c-402cef68c25e\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:1:{i:0;s:5:\\\"files\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"approved\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"d773c759-c0de-4b8d-9bc2-0410dca34f3c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071308,\"delay\":null}', 0, NULL, 1759071308, 1759071308),
(287, 'default', '{\"uuid\":\"99ed6eb6-18b1-4223-9166-ae098a912882\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"da78958a-2ee5-4819-8316-fd6852027c0c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759071516,\"delay\":null}', 0, NULL, 1759071516, 1759071516);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(288, 'default', '{\"uuid\":\"6181ef21-c393-419c-ad60-8a75d03a833d\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"da78958a-2ee5-4819-8316-fd6852027c0c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759071516,\"delay\":null}', 0, NULL, 1759071516, 1759071516),
(289, 'default', '{\"uuid\":\"c3da68c3-5b94-4d06-a31b-9b5b1bbd0494\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:39;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"c0e57281-e142-4d6d-9c50-1e7d1b9bf363\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759145069,\"delay\":null}', 0, NULL, 1759145069, 1759145069),
(290, 'default', '{\"uuid\":\"2fa2071f-39af-4a58-90b9-27e570d05788\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/39\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:23:\\\"رقم المهمة #39\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"3809269e-7da3-4087-abd3-78c31da7bd81\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759146059,\"delay\":null}', 0, NULL, 1759146059, 1759146059),
(291, 'default', '{\"uuid\":\"a97e7533-d22c-44d4-91c4-61a75d9e0f46\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"body\\\";s:23:\\\"رقم المهمة #39\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/39\\\";s:2:\\\"id\\\";s:36:\\\"6ea88a7d-437b-47c7-bf3a-36aecc9fa5ea\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759146059,\"delay\":null}', 0, NULL, 1759146059, 1759146059),
(292, 'default', '{\"uuid\":\"0069831e-d43c-4208-937c-f776f91e95b6\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:18;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:40;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:0;s:2:\\\"id\\\";s:36:\\\"8eb63ea9-3444-4285-97fb-5da6f4b2bd33\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759237934,\"delay\":null}', 0, NULL, 1759237934, 1759237934),
(293, 'default', '{\"uuid\":\"c245fd46-2108-4f97-b529-64985f2769b3\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"571ff03c-5212-415a-b66d-2307dc7ad4c2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759244260,\"delay\":null}', 0, NULL, 1759244260, 1759244260),
(294, 'default', '{\"uuid\":\"6d307273-6e20-4c90-b314-22434cc6c94b\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"571ff03c-5212-415a-b66d-2307dc7ad4c2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759244260,\"delay\":null}', 0, NULL, 1759244260, 1759244260),
(295, 'default', '{\"uuid\":\"467f65bb-d55d-44d2-b260-a8cc879fbd89\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"4fa2d8b0-0615-424b-bae3-3876fe468ed6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759244260,\"delay\":null}', 0, NULL, 1759244260, 1759244260),
(296, 'default', '{\"uuid\":\"c0451fb5-4c02-4d7a-bc5c-234cebef0ef5\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"4fa2d8b0-0615-424b-bae3-3876fe468ed6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759244260,\"delay\":null}', 0, NULL, 1759244260, 1759244260),
(297, 'default', '{\"uuid\":\"36727f8e-2d54-4bf3-a1cb-ea4addf67322\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"ee7a1d9b-c5f9-4026-bc22-1303dbbb996f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759250435,\"delay\":null}', 0, NULL, 1759250435, 1759250435),
(298, 'default', '{\"uuid\":\"6487ac34-f98e-42f1-9c87-47005175ae43\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"ee7a1d9b-c5f9-4026-bc22-1303dbbb996f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759250435,\"delay\":null}', 0, NULL, 1759250435, 1759250435),
(299, 'default', '{\"uuid\":\"01375eeb-7c7b-4ba7-be81-ab82a899fed1\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"3affe674-a931-428a-90e5-49af5c0a0b31\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759250488,\"delay\":null}', 0, NULL, 1759250488, 1759250488),
(300, 'default', '{\"uuid\":\"8e20d838-66f8-4536-b2ca-2159a0b39a95\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"3affe674-a931-428a-90e5-49af5c0a0b31\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759250488,\"delay\":null}', 0, NULL, 1759250488, 1759250488),
(301, 'default', '{\"uuid\":\"15f8eaf7-b246-4b82-87fa-4c9422f5dfee\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"42919284-e672-4078-be42-4e44eadd8d61\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759250823,\"delay\":null}', 0, NULL, 1759250823, 1759250823),
(302, 'default', '{\"uuid\":\"3f69ffb8-d8dc-4c9d-8fbd-b8a02ba7c3e3\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestCreated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestCreated\\\":2:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";N;s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"42919284-e672-4078-be42-4e44eadd8d61\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759250823,\"delay\":null}', 0, NULL, 1759250823, 1759250823),
(303, 'default', '{\"uuid\":\"949c28a5-1766-4669-b9af-5d5546cac095\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"6779966e-1aed-4cf6-a9e9-40951ec53bdf\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759250823,\"delay\":null}', 0, NULL, 1759250823, 1759250823),
(304, 'default', '{\"uuid\":\"1628b5be-9bdd-4113-a0d9-ea1ccb2f406b\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:0:\\\"\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"6779966e-1aed-4cf6-a9e9-40951ec53bdf\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759250823,\"delay\":null}', 0, NULL, 1759250823, 1759250823),
(305, 'default', '{\"uuid\":\"842740a4-a84e-43f6-94ea-203d13cd53b9\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"project\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"17f9d473-7b82-4d39-89ef-8d24e33ca8d4\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759250892,\"delay\":null}', 0, NULL, 1759250892, 1759250892),
(306, 'default', '{\"uuid\":\"fcc2b52c-6df1-41ba-8ea5-4b8752e48da5\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"project\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"17f9d473-7b82-4d39-89ef-8d24e33ca8d4\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759250892,\"delay\":null}', 0, NULL, 1759250892, 1759250892),
(307, 'default', '{\"uuid\":\"fc01af24-7c98-400d-9b74-6b57f86e627a\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"bd6504be-06ce-4f0e-be2b-b4125f2f5a51\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759250900,\"delay\":null}', 0, NULL, 1759250900, 1759250900),
(308, 'default', '{\"uuid\":\"15192276-dd87-42f4-a635-f10db61d6113\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"bd6504be-06ce-4f0e-be2b-b4125f2f5a51\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759250900,\"delay\":null}', 0, NULL, 1759250900, 1759250900),
(309, 'default', '{\"uuid\":\"394989cf-1f8e-473c-a2e5-1f9dd29d55bc\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:8:\\\"rejected\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e4ebf77e-60ac-4427-9542-7049bd9a357a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251069,\"delay\":null}', 0, NULL, 1759251069, 1759251069),
(310, 'default', '{\"uuid\":\"5186fef4-b736-4776-8d91-92fe52f954c6\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:8:\\\"rejected\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e4ebf77e-60ac-4427-9542-7049bd9a357a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251069,\"delay\":null}', 0, NULL, 1759251069, 1759251069),
(311, 'default', '{\"uuid\":\"81144e3f-6b2c-4b06-86b5-64a8217c6281\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:4:{i:0;s:5:\\\"files\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"rejected\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"50a62a87-02ea-4b1d-8020-df2b3677c515\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251325,\"delay\":null}', 0, NULL, 1759251325, 1759251325),
(312, 'default', '{\"uuid\":\"e4e593f8-a308-45d9-9451-2e77ad41d50d\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:4:{i:0;s:5:\\\"files\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"rejected\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"50a62a87-02ea-4b1d-8020-df2b3677c515\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251325,\"delay\":null}', 0, NULL, 1759251325, 1759251325),
(313, 'default', '{\"uuid\":\"7aed0e9f-cb26-4592-a263-35080c5bd9bf\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestUpdated\\\":3:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:11:\\\"changedKeys\\\";a:1:{i:0;s:12:\\\"project_name\\\";}s:2:\\\"id\\\";s:36:\\\"1c488ba8-fb51-41e5-946a-c6150ef2f924\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251325,\"delay\":null}', 0, NULL, 1759251325, 1759251325),
(314, 'default', '{\"uuid\":\"05e9869b-f9d6-4281-bc58-89ddbff974c8\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestUpdated\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\ProductionRequestUpdated\\\":3:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:11:\\\"changedKeys\\\";a:1:{i:0;s:12:\\\"project_name\\\";}s:2:\\\"id\\\";s:36:\\\"1c488ba8-fb51-41e5-946a-c6150ef2f924\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251325,\"delay\":null}', 0, NULL, 1759251325, 1759251325),
(315, 'default', '{\"uuid\":\"571a4317-4a2b-4a6b-a7d1-070d59b3b462\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"project\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"b5bef76f-f207-42db-b6fc-f0eb775ed489\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251372,\"delay\":null}', 0, NULL, 1759251372, 1759251372),
(316, 'default', '{\"uuid\":\"9f1f9e99-9446-4c6b-9aa9-4ae9ec4927d2\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:4:{i:0;s:7:\\\"project\\\";i:1;s:8:\\\"showroom\\\";i:2;s:16:\\\"showroom.manager\\\";i:3;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"b5bef76f-f207-42db-b6fc-f0eb775ed489\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251372,\"delay\":null}', 0, NULL, 1759251372, 1759251372),
(317, 'default', '{\"uuid\":\"2e0c5034-68f3-4df4-99bc-8efd2495a087\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"8483f897-f44c-42a7-8673-6ab3dffbefb3\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251378,\"delay\":null}', 0, NULL, 1759251378, 1759251378),
(318, 'default', '{\"uuid\":\"7b6de81d-d085-4ca6-a26c-1266478e2f41\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:21;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:3:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"8483f897-f44c-42a7-8673-6ab3dffbefb3\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251378,\"delay\":null}', 0, NULL, 1759251378, 1759251378),
(319, 'default', '{\"uuid\":\"cc3d61e5-cb32-4ab5-a253-60395977a12e\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"353931c1-553c-443c-a89a-de84232743bb\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251428,\"delay\":null}', 0, NULL, 1759251428, 1759251428);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(320, 'default', '{\"uuid\":\"1a8d7ab4-99a5-4ab8-bcf6-a52a7d7611ad\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"353931c1-553c-443c-a89a-de84232743bb\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251428,\"delay\":null}', 0, NULL, 1759251428, 1759251428),
(321, 'default', '{\"uuid\":\"f013e7b2-9861-4e95-b580-7fdbd8c3cfe5\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"fc381aa1-1637-4a2e-bd60-7e5a6bc3f5d7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251470,\"delay\":null}', 0, NULL, 1759251470, 1759251470),
(322, 'default', '{\"uuid\":\"13fd2b7b-e21d-4257-9d92-457241f3e38a\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:1:{i:0;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:7:\\\"pending\\\";s:8:\\\"toStatus\\\";s:8:\\\"received\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"fc381aa1-1637-4a2e-bd60-7e5a6bc3f5d7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251470,\"delay\":null}', 0, NULL, 1759251470, 1759251470),
(323, 'default', '{\"uuid\":\"d8e6d7cb-21d3-4516-ab46-fd54e7d999a6\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"78106457-ab67-4532-a22c-4e4223c9fb36\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251475,\"delay\":null}', 0, NULL, 1759251475, 1759251475),
(324, 'default', '{\"uuid\":\"f5020d45-9f53-4caf-a1e1-2b74c559351b\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"received\\\";s:8:\\\"toStatus\\\";s:12:\\\"under_review\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"78106457-ab67-4532-a22c-4e4223c9fb36\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251475,\"delay\":null}', 0, NULL, 1759251475, 1759251475),
(325, 'default', '{\"uuid\":\"a4e1439c-c5bf-47fd-9442-650d8c9b955d\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:8:\\\"approved\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"6d45b5bf-8098-46a6-a529-9ea3f0604a83\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251494,\"delay\":null}', 0, NULL, 1759251494, 1759251494),
(326, 'default', '{\"uuid\":\"bf0e6db7-4ffc-4b87-a0b5-47396844db1f\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:12:\\\"under_review\\\";s:8:\\\"toStatus\\\";s:8:\\\"approved\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"6d45b5bf-8098-46a6-a529-9ea3f0604a83\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251494,\"delay\":null}', 0, NULL, 1759251494, 1759251494),
(327, 'default', '{\"uuid\":\"eae343bf-4e17-413e-b375-0b4945917d85\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:1:{i:0;s:5:\\\"files\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"approved\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e7d5ac2b-af33-4139-90bd-139a076beec2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759251494,\"delay\":null}', 0, NULL, 1759251494, 1759251494),
(328, 'default', '{\"uuid\":\"747366c8-4f76-44bf-b945-2bf0d03c8331\",\"displayName\":\"App\\\\Notifications\\\\ProductionRequestStatusChanged\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\ProductionRequestStatusChanged\\\":5:{s:7:\\\"request\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:1:{i:0;s:5:\\\"files\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:10:\\\"fromStatus\\\";s:8:\\\"approved\\\";s:8:\\\"toStatus\\\";s:7:\\\"pending\\\";s:6:\\\"reason\\\";N;s:2:\\\"id\\\";s:36:\\\"e7d5ac2b-af33-4139-90bd-139a076beec2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759251494,\"delay\":null}', 0, NULL, 1759251494, 1759251494),
(329, 'default', '{\"uuid\":\"7fbe6d68-6c33-47fd-834b-4b98e66e5ef8\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:41;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:1;s:2:\\\"id\\\";s:36:\\\"a30a9ae2-5e93-4e09-ace7-1e44b0a84be1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759252114,\"delay\":null}', 0, NULL, 1759252114, 1759252114),
(330, 'default', '{\"uuid\":\"149c0cb2-c4ba-460e-bc81-e648f905ae6d\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:21:\\\"عرض المشروع\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:20:\\\"عرضالمشروع\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:0;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:50:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/projects\\/29\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:43:\\\"المهمة:  (#41) • المشروع #29\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:35:\\\"heroicon-o-clipboard-document-check\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:29:\\\"تم إسناد مهمة لك\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"76a3ae1a-c25d-4bff-89b7-2fd839768223\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759252114,\"delay\":null}', 0, NULL, 1759252114, 1759252114),
(331, 'default', '{\"uuid\":\"ab9e2ae6-92e2-47c8-bb1b-85e65454980d\",\"displayName\":\"App\\\\Notifications\\\\DepartmentTaskMail\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:36:\\\"App\\\\Notifications\\\\DepartmentTaskMail\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:41;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";i:2;s:7:\\\"project\\\";i:3;s:10:\\\"department\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:6:\\\"reason\\\";s:9:\\\"ownership\\\";s:2:\\\"id\\\";s:36:\\\"987eb681-4732-4a2b-9c77-1cf00c37bd1f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759252114,\"delay\":null}', 0, NULL, 1759252114, 1759252114),
(332, 'default', '{\"uuid\":\"c9f70a6b-660b-430d-88ad-b9dd8ee9ff96\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:23:\\\"رقم المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"6345e71f-b979-4947-96d2-72ea37e45ac9\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759252169,\"delay\":null}', 0, NULL, 1759252169, 1759252169),
(333, 'default', '{\"uuid\":\"b0dcc24e-f4f9-4b1f-8a23-061887f51782\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"body\\\";s:23:\\\"رقم المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"2d3e58de-5217-400d-aa21-797ecd37ad35\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759252169,\"delay\":null}', 0, NULL, 1759252169, 1759252169),
(334, 'default', '{\"uuid\":\"a050b82e-c978-4468-bec0-1f46cdf0aa1a\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:17;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:81:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: طلب خامات\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"60acf393-aa9c-480a-bbbd-3a867492fc30\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759252270,\"delay\":null}', 0, NULL, 1759252270, 1759252270),
(335, 'default', '{\"uuid\":\"b6a1620b-f961-4072-9c0a-fb3290ae77ac\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:17;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"body\\\";s:81:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: طلب خامات\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"9964dc32-0e02-4bcd-b626-280682105278\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759252270,\"delay\":null}', 0, NULL, 1759252270, 1759252270),
(336, 'default', '{\"uuid\":\"b25735bc-575a-4af7-ad51-f25a7e40b350\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:50:\\\"المهمة #41 بانتظار المشتريات\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:37:\\\"تم إرسال طلب الخامات\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"9d434e25-e8e0-402a-8a1e-a5258635aa74\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759252270,\"delay\":null}', 0, NULL, 1759252270, 1759252270),
(337, 'default', '{\"uuid\":\"1e8ebda3-7721-4b72-96a3-da97088f35ed\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:37:\\\"تم إرسال طلب الخامات\\\";s:4:\\\"body\\\";s:50:\\\"المهمة #41 بانتظار المشتريات\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"cce39059-8d16-48ed-ae02-ad76edf85153\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759252270,\"delay\":null}', 0, NULL, 1759252270, 1759252270),
(338, 'default', '{\"uuid\":\"c2ba2673-dfe3-4d65-a68b-3f8a9097323f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:87:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: جاهز للتصنيع\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"3c946852-64d1-42cf-8bc9-e6279af9274d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253128,\"delay\":null}', 0, NULL, 1759253128, 1759253128),
(339, 'default', '{\"uuid\":\"f06f7bfc-365d-46b4-805b-265863508d98\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"body\\\";s:87:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: جاهز للتصنيع\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"344dfc6d-a2fb-4857-bb2e-e1a8e93dd7eb\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253128,\"delay\":null}', 0, NULL, 1759253128, 1759253128),
(340, 'default', '{\"uuid\":\"9b461160-7095-40da-a202-5d20a3f993d8\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:60:\\\"تم تحديد المواعيد وإرسال للتصنيع\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"57469995-44fa-4b50-94ff-1d6bdd9f150f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253128,\"delay\":null}', 0, NULL, 1759253128, 1759253128),
(341, 'default', '{\"uuid\":\"582bf174-403f-4dbf-b9d7-722be9cd48cf\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:60:\\\"تم تحديد المواعيد وإرسال للتصنيع\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"68ed25f1-d14a-44db-bd0c-4258ae014dfb\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253128,\"delay\":null}', 0, NULL, 1759253128, 1759253128),
(342, 'default', '{\"uuid\":\"64eb259d-7637-41b1-9c4a-77846e14726c\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:43:\\\"تم تأكيد استلام التصنيع\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"89f4f6a4-b758-47fa-b6b4-fabd0349969e\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253154,\"delay\":null}', 0, NULL, 1759253154, 1759253154),
(343, 'default', '{\"uuid\":\"f9d6d7d1-9f85-41a5-8669-70529832c068\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:43:\\\"تم تأكيد استلام التصنيع\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"fa3bef41-9cfd-4dd8-869b-ebd29ed54f69\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253154,\"delay\":null}', 0, NULL, 1759253154, 1759253154),
(344, 'default', '{\"uuid\":\"0a346cdd-ebd0-433e-8577-58d8cbcea233\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:21:\\\"بدأ التصنيع\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"baac1491-87d3-4418-b7e9-48d44bbf0ca0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253235,\"delay\":null}', 0, NULL, 1759253235, 1759253235),
(345, 'default', '{\"uuid\":\"1259175f-9e15-43bb-af13-618331be790c\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:21:\\\"بدأ التصنيع\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"6fe6b8f8-6656-467a-8696-db0747f5b93d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253235,\"delay\":null}', 0, NULL, 1759253235, 1759253235),
(346, 'default', '{\"uuid\":\"d0e2bf29-0237-4d0c-ae43-d3a7216ef68c\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:19;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:70:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: لاي\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:54:\\\"مهمة جديدة بانتظار فحص الجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"165b8017-9a78-4fda-8fcb-ecf7561663f7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253479,\"delay\":null}', 0, NULL, 1759253479, 1759253479),
(347, 'default', '{\"uuid\":\"03c0715a-4675-4116-8cee-0debb98542e5\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:19;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:54:\\\"مهمة جديدة بانتظار فحص الجودة\\\";s:4:\\\"body\\\";s:70:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: لاي\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"ce7658db-f05a-4dd1-ac59-baecdebd2919\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253479,\"delay\":null}', 0, NULL, 1759253479, 1759253479),
(348, 'default', '{\"uuid\":\"68841274-84fe-4a81-9325-5d12dbafedd3\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:43:\\\"تم إرسال التصنيع للجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"ba334806-c625-4755-81e7-ee148302ca14\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253479,\"delay\":null}', 0, NULL, 1759253479, 1759253479);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(349, 'default', '{\"uuid\":\"62ccb3b7-db9f-47e5-80a4-91e8a7de2b9d\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:15;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:43:\\\"تم إرسال التصنيع للجودة\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"9bd355db-0bb7-486a-a9fa-f5d17a81a981\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253479,\"delay\":null}', 0, NULL, 1759253479, 1759253479),
(350, 'default', '{\"uuid\":\"353b331d-d044-4a22-af12-21308b4a2578\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التصنيع)\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"3d657e9a-f99d-41d1-accf-8bad367c5ad4\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253509,\"delay\":null}', 0, NULL, 1759253509, 1759253509),
(351, 'default', '{\"uuid\":\"996af1d3-7ccb-431a-bff2-1ce887a3b948\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التصنيع)\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"163cbbc3-0b13-43b0-96ef-1090f9c73c05\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253509,\"delay\":null}', 0, NULL, 1759253509, 1759253509),
(352, 'default', '{\"uuid\":\"e343b6f2-11d4-44f0-814b-4e59fc1bdabb\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:19;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:34:\\\"مهمة جاهزة للتركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"cd683b83-f0b1-453e-a60e-41a12f1d5fcd\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253567,\"delay\":null}', 0, NULL, 1759253567, 1759253567),
(353, 'default', '{\"uuid\":\"a6c7c27b-686a-4b92-a79e-4018246b0bfc\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:19;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:34:\\\"مهمة جاهزة للتركيب\\\";s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"9de33008-5b97-45ae-b2fa-271210a1cd68\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253567,\"delay\":null}', 0, NULL, 1759253567, 1759253567),
(354, 'default', '{\"uuid\":\"71c34169-dfb7-4ea1-96d1-4939e0b643c0\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:71:\\\"تم اعتماد الجودة وتحويل المهمة للتركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"15634e35-3063-4bd0-9304-27626fa96f80\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253567,\"delay\":null}', 0, NULL, 1759253567, 1759253567),
(355, 'default', '{\"uuid\":\"7dcc0d74-aa8b-487e-a830-d4c9bfe41839\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:71:\\\"تم اعتماد الجودة وتحويل المهمة للتركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"13efe602-604d-4cff-a7a4-43475faaebb7\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253567,\"delay\":null}', 0, NULL, 1759253567, 1759253567),
(356, 'default', '{\"uuid\":\"32831735-ea6d-4f34-b7b9-2e132061f7fd\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:50:\\\"تم تأكيد استلام قسم التركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"b084f5d2-eff8-4944-8b71-854c16140679\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253673,\"delay\":null}', 0, NULL, 1759253673, 1759253673),
(357, 'default', '{\"uuid\":\"c05dbbfd-45e4-4977-a871-daa7fb22b4b9\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:50:\\\"تم تأكيد استلام قسم التركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"1298bda9-7ab7-4e71-b268-d3c1a03b3559\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253673,\"delay\":null}', 0, NULL, 1759253673, 1759253673),
(358, 'default', '{\"uuid\":\"6fa4a727-442c-46ee-b2dd-7ab913900c90\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:26:\\\"تم بدء التركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"533fa798-6a02-45cf-a3b4-378b03104e0a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253688,\"delay\":null}', 0, NULL, 1759253688, 1759253688),
(359, 'default', '{\"uuid\":\"8cd6bdb6-8dd9-4ab8-97bb-7204f487204a\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:26:\\\"تم بدء التركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"9ef74480-5acd-4b00-aac5-74dd35a18359\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253688,\"delay\":null}', 0, NULL, 1759253688, 1759253688),
(360, 'default', '{\"uuid\":\"b5569ffe-915e-4f9f-b612-71a3b2da9c19\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:19;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:54:\\\"مهمة تركيب بانتظار فحص الجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"1a40ca13-f542-46ea-9d9b-b208625afcc0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253707,\"delay\":null}', 0, NULL, 1759253707, 1759253707),
(361, 'default', '{\"uuid\":\"c75c16c8-6102-433f-af6a-463d880eb3b2\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:19;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:54:\\\"مهمة تركيب بانتظار فحص الجودة\\\";s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"3233e039-e280-4a94-9890-9e4a6cd7de99\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253707,\"delay\":null}', 0, NULL, 1759253707, 1759253707),
(362, 'default', '{\"uuid\":\"86511ada-d8c4-4eca-b36c-a5eeeab9cbf4\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:43:\\\"تم إرسال التركيب للجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"73fb778b-6523-4f7c-bb9c-c7c8336e3a67\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253707,\"delay\":null}', 0, NULL, 1759253707, 1759253707),
(363, 'default', '{\"uuid\":\"0b968d6e-c7a3-45af-9489-83135b8dd184\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:16;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:43:\\\"تم إرسال التركيب للجودة\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"6442135e-2140-4594-957e-3da9bb16e96b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253707,\"delay\":null}', 0, NULL, 1759253707, 1759253707),
(364, 'default', '{\"uuid\":\"c4305ec5-14a8-49ae-83d9-dec4bc55aa6e\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التركيب)\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"34c07401-c772-43a3-b754-a7e093786678\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253725,\"delay\":null}', 0, NULL, 1759253725, 1759253725),
(365, 'default', '{\"uuid\":\"3b274f47-38bf-419b-9a77-1b9cb54e702b\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التركيب)\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"8a5cdf52-0144-433f-b4ee-a46bfb71176a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253725,\"delay\":null}', 0, NULL, 1759253725, 1759253725),
(366, 'default', '{\"uuid\":\"67a6a3a4-2a6c-47b3-b914-0cb35872f719\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:59:\\\"تم اعتماد الجودة لما بعد التركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"8e753a87-9f35-4ef3-925b-07744d039381\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253741,\"delay\":null}', 0, NULL, 1759253741, 1759253741),
(367, 'default', '{\"uuid\":\"1c02e859-77f3-46b1-8a35-70ce54626fc1\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:59:\\\"تم اعتماد الجودة لما بعد التركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #41\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"1e82fbf1-e087-428c-ba75-83e292ca87f3\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253741,\"delay\":null}', 0, NULL, 1759253741, 1759253741),
(368, 'default', '{\"uuid\":\"2d648925-9c07-4a77-8370-197648d5dc0f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:40:\\\"المهمة #41 أُغلقت بنجاح\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:25:\\\"اكتملت المهمة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"b4fd960a-5d48-4e48-81a5-8147e92855a0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759253779,\"delay\":null}', 0, NULL, 1759253779, 1759253779),
(369, 'default', '{\"uuid\":\"a7eb3209-d7e8-44c4-aefc-6a73c6c736f0\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:22;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:25:\\\"اكتملت المهمة\\\";s:4:\\\"body\\\";s:40:\\\"المهمة #41 أُغلقت بنجاح\\\";s:3:\\\"url\\\";s:47:\\\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/tasks\\/41\\\";s:2:\\\"id\\\";s:36:\\\"6686321e-7bce-4579-b760-8efc9456cf0a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759253779,\"delay\":null}', 0, NULL, 1759253779, 1759253779);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(190) NOT NULL,
  `name` varchar(190) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_client_projects`
--

CREATE TABLE `legacy_client_projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `legacy_client_projects`
--

INSERT INTO `legacy_client_projects` (`id`, `client_id`, `project_name`, `start_date`, `end_date`, `details`, `created_at`, `updated_at`) VALUES
(2, 2, 'مشروع مصنع الرخام', '2025-09-05', '2025-09-06', NULL, '2025-09-10 19:59:57', '2025-09-10 19:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `legacy_client_project_files`
--

CREATE TABLE `legacy_client_project_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `legacy_project_id` bigint(20) UNSIGNED NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(255) DEFAULT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `legacy_client_project_files`
--

INSERT INTO `legacy_client_project_files` (`id`, `legacy_project_id`, `category`, `title`, `description`, `file_path`, `mime_type`, `file_size`, `uploaded_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'image', 'تجربة', NULL, 'legacy-projects/IMG_5948.jpg', 'image/jpeg', 38392, 1, '2025-09-09 11:14:45', '2025-09-09 11:14:45'),
(2, 1, 'image', 'تجربة', NULL, 'legacy-projects/IMG_4770.jpg', 'image/jpeg', 233239, 1, '2025-09-09 11:28:50', '2025-09-09 11:28:50');

-- --------------------------------------------------------

--
-- Table structure for table `lessons_learned`
--

CREATE TABLE `lessons_learned` (
  `lesson_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `category` enum('process','quality','schedule','cost','safety') NOT NULL,
  `lesson_description` text NOT NULL,
  `recommendations` text NOT NULL,
  `recorded_by` int(11) NOT NULL,
  `record_date` datetime DEFAULT current_timestamp(),
  `implemented` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturing_projects`
--

CREATE TABLE `manufacturing_projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `client_id` int(11) NOT NULL,
  `production_manager_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `status` enum('pending','design','procurement','production','quality_check','installation','completed','delayed') DEFAULT 'pending',
  `priority` tinyint(4) DEFAULT 2,
  `current_phase` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement_units`
--

CREATE TABLE `measurement_units` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(20) NOT NULL,
  `unit_symbol` varchar(10) NOT NULL,
  `unit_type` enum('length','weight','volume','area','count') NOT NULL,
  `base_unit` tinyint(1) DEFAULT 0,
  `conversion_factor` decimal(15,6) DEFAULT 1.000000,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
(8, '2025_08_16_161951_create_notifications_table', 6),
(9, '2025_08_18_094619_create_production_tasks_log', 7),
(10, '2025_08_18_100430_create_production_tasks_time_entries', 8),
(11, '2025_08_19_141411_create_production_tasks_material_requests', 9),
(12, '2025_08_27_091334_create_production_tasks_comments_table', 10),
(13, '2025_09_09_122457_create_legacy_client_projects_table', 11),
(14, '2025_09_10_144035_add_meta_columns_to_system_settings_table', 12);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(150) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(150) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 1),
(1, 'App\\Models\\Employee', 6),
(4, 'App\\Models\\Employee', 6),
(5, 'App\\Models\\Employee', 6),
(6, 'App\\Models\\Employee', 6),
(3, 'App\\Models\\Employee', 7),
(4, 'App\\Models\\Employee', 7),
(5, 'App\\Models\\Employee', 7),
(6, 'App\\Models\\Employee', 7),
(7, 'App\\Models\\Employee', 7),
(8, 'App\\Models\\Employee', 7),
(9, 'App\\Models\\Employee', 7),
(3, 'App\\Models\\User', 9),
(4, 'App\\Models\\User', 9),
(5, 'App\\Models\\User', 9),
(6, 'App\\Models\\User', 9),
(7, 'App\\Models\\User', 9),
(8, 'App\\Models\\User', 9),
(9, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(4, 'App\\Models\\User', 10),
(3, 'App\\Models\\User', 11),
(3, 'App\\Models\\User', 12),
(4, 'App\\Models\\User', 12),
(3, 'App\\Models\\User', 13),
(3, 'App\\Models\\User', 14),
(7, 'App\\Models\\User', 15),
(4, 'App\\Models\\User', 16),
(5, 'App\\Models\\User', 17),
(4, 'App\\Models\\User', 18),
(8, 'App\\Models\\User', 19),
(9, 'App\\Models\\User', 19),
(3, 'App\\Models\\User', 20),
(6, 'App\\Models\\User', 21),
(1, 'App\\Models\\User', 22),
(4, 'App\\Models\\User', 23),
(3, 'App\\Models\\User', 25),
(3, 'App\\Models\\User', 28);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) UNSIGNED NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('292efec7-5714-42ee-a6d4-a1b8f3e4c119', 'App\\Notifications\\ProductionRequestStatusChanged', 'App\\Models\\User', 15, '{\"title\":\"\\u062a\\u063a\\u064a\\u0651\\u0631\\u062a \\u062d\\u0627\\u0644\\u0629 \\u0637\\u0644\\u0628 \\u0627\\u0644\\u062a\\u0635\\u0646\\u064a\\u0639\",\"body\":\"\\u0627\\u0644\\u0637\\u0644\\u0628 #16: under_review \\u27f6 pending\",\"icon\":\"heroicon-o-arrow-path\",\"actions\":[{\"label\":\"\\u0639\\u0631\\u0636 \\u0627\\u0644\\u0637\\u0644\\u0628\",\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/production-requests\\/16\\/timeline\",\"openUrlInNewTab\":false}],\"type\":\"production_request_status_changed\",\"pr_id\":16,\"from_status\":\"under_review\",\"to_status\":\"pending\",\"reason\":null,\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/production-requests\\/16\\/timeline\",\"created_at\":\"2025-09-17T16:07:54+03:00\"}', NULL, '2025-09-17 16:07:54', '2025-09-17 16:07:54'),
('3b5fd2f8-31df-4024-85c5-0d51e9456ffe', 'App\\Notifications\\TaskAssignedInAppNotification', 'App\\Models\\User', 16, '{\"title\":\"\\u0645\\u0647\\u0645\\u0629 \\u062a\\u0635\\u0646\\u064a\\u0639 \\u062c\\u062f\\u064a\\u062f\\u0629 \\u0623\\u064f\\u0633\\u0646\\u062f\\u062a \\u0625\\u0644\\u064a\\u0643\",\"body\":\"\\u0627\\u0644\\u0645\\u0634\\u0631\\u0648\\u0639: \\u0627\\u0628\\u0648\\u0627\\u0628 \\u2014 \\u0627\\u0644\\u0642\\u0633\\u0645: \\u0642\\u0633\\u0645 \\u0627\\u0644\\u0623\\u0644\\u0648\\u0645\\u0646\\u064a\\u0648\\u0645 \\u2014 \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645: 2025-10-24\",\"project_id\":28,\"task_id\":39,\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/projects\\/28\\/manage-tasks\",\"action_text\":\"\\u0641\\u062a\\u062d \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629\"}', NULL, '2025-09-29 14:24:29', '2025-09-29 14:24:29'),
('3d877a04-0128-488e-b7a2-53b37bf50da1', 'App\\Notifications\\TaskAssignedInAppNotification', 'App\\Models\\User', 16, '{\"title\":\"\\u0625\\u0639\\u0627\\u062f\\u0629 \\u0625\\u0633\\u0646\\u0627\\u062f \\u0645\\u0647\\u0645\\u0629 \\u062a\\u0635\\u0646\\u064a\\u0639\",\"body\":\"\\u0627\\u0644\\u0645\\u0634\\u0631\\u0648\\u0639: \\u0628\\u0627\\u0628 \\u0627\\u0644\\u0645\\u0646\\u064a\\u0648\\u0645 \\u2014 \\u0627\\u0644\\u0642\\u0633\\u0645: \\u0642\\u0633\\u0645 \\u0627\\u0644\\u0623\\u0644\\u0648\\u0645\\u0646\\u064a\\u0648\\u0645 \\u2014 \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645: \\u063a\\u064a\\u0631 \\u0645\\u062d\\u062f\\u062f\",\"project_id\":27,\"task_id\":37,\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/projects\\/27\\/manage-tasks\",\"action_text\":\"\\u0641\\u062a\\u062d \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629\"}', NULL, '2025-09-17 17:34:43', '2025-09-17 17:34:43'),
('8f252154-f6b7-4ba2-9b09-cb7f56293f65', 'App\\Notifications\\TaskAssignedInAppNotification', 'App\\Models\\User', 16, '{\"title\":\"\\u0625\\u0639\\u0627\\u062f\\u0629 \\u0625\\u0633\\u0646\\u0627\\u062f \\u0645\\u0647\\u0645\\u0629 \\u062a\\u0635\\u0646\\u064a\\u0639\",\"body\":\"\\u0627\\u0644\\u0645\\u0634\\u0631\\u0648\\u0639: \\u0627\\u0628\\u0648\\u0627\\u0628 1 \\u2014 \\u0627\\u0644\\u0642\\u0633\\u0645: \\u0642\\u0633\\u0645 \\u0627\\u0644\\u0623\\u0644\\u0648\\u0645\\u0646\\u064a\\u0648\\u0645 \\u2014 \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645: \\u063a\\u064a\\u0631 \\u0645\\u062d\\u062f\\u062f\",\"project_id\":29,\"task_id\":41,\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/projects\\/29\\/manage-tasks\",\"action_text\":\"\\u0641\\u062a\\u062d \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629\"}', NULL, '2025-09-30 20:08:34', '2025-09-30 20:08:34'),
('93333c23-62e5-4a46-9df4-8bfa882e1176', 'App\\Notifications\\ProductionRequestStatusChanged', 'App\\Models\\Employee', 18, '{\"title\":\"\\u062a\\u063a\\u064a\\u0651\\u0631\\u062a \\u062d\\u0627\\u0644\\u0629 \\u0637\\u0644\\u0628 \\u0627\\u0644\\u062a\\u0635\\u0646\\u064a\\u0639\",\"body\":\"\\u0627\\u0644\\u0637\\u0644\\u0628 #16: received \\u27f6 under_review\",\"icon\":\"heroicon-o-arrow-path\",\"actions\":[{\"label\":\"\\u0639\\u0631\\u0636 \\u0627\\u0644\\u0637\\u0644\\u0628\",\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/production-requests\\/16\\/timeline\",\"openUrlInNewTab\":false}],\"type\":\"production_request_status_changed\",\"pr_id\":16,\"from_status\":\"received\",\"to_status\":\"under_review\",\"reason\":null,\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/production-requests\\/16\\/timeline\",\"created_at\":\"2025-09-17T15:47:21+03:00\"}', NULL, '2025-09-17 15:47:21', '2025-09-17 15:47:21'),
('bfd3ad70-ea9a-47e5-a571-46af71fc8dc7', 'App\\Notifications\\TaskAssignedInAppNotification', 'App\\Models\\User', 21, '{\"title\":\"\\u0645\\u0647\\u0645\\u0629 \\u062a\\u0635\\u0646\\u064a\\u0639 \\u062c\\u062f\\u064a\\u062f\\u0629 \\u0623\\u064f\\u0633\\u0646\\u062f\\u062a \\u0625\\u0644\\u064a\\u0643\",\"body\":\"\\u0627\\u0644\\u0645\\u0634\\u0631\\u0648\\u0639: \\u0628\\u0627\\u0628 \\u0627\\u0644\\u0645\\u0646\\u064a\\u0648\\u0645 \\u2014 \\u0627\\u0644\\u0642\\u0633\\u0645: \\u0642\\u0633\\u0645 \\u0627\\u0644\\u062e\\u0634\\u0628 \\u2014 \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645: 2025-09-30\",\"project_id\":27,\"task_id\":40,\"url\":\"https:\\/\\/modernlife.a-elsayed.com\\/admin\\/projects\\/27\\/manage-tasks\",\"action_text\":\"\\u0641\\u062a\\u062d \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629\"}', NULL, '2025-09-30 16:12:14', '2025-09-30 16:12:14');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(190) NOT NULL,
  `token` varchar(190) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `guard_name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
(68, 'access_view_production_timeline', 'web', '2025-08-04 11:52:45', '2025-08-04 11:52:45'),
(69, 'view_task_resource', 'web', '2025-08-19 12:22:31', '2025-08-19 12:22:31'),
(70, 'create_task_resource', 'web', '2025-08-19 12:22:31', '2025-08-19 12:22:31'),
(71, 'edit_task_resource', 'web', '2025-08-19 12:22:31', '2025-08-19 12:22:31'),
(72, 'delete_task_resource', 'web', '2025-08-19 12:22:31', '2025-08-19 12:22:31'),
(73, 'manage-tasks', 'web', '2025-08-19 12:42:36', '2025-08-19 12:42:36'),
(74, 'view_legacy_client_project_resource', 'web', '2025-09-10 12:24:51', '2025-09-10 12:24:51'),
(75, 'create_legacy_client_project_resource', 'web', '2025-09-10 12:24:51', '2025-09-10 12:24:51'),
(76, 'edit_legacy_client_project_resource', 'web', '2025-09-10 12:24:51', '2025-09-10 12:24:51'),
(77, 'delete_legacy_client_project_resource', 'web', '2025-09-10 12:24:51', '2025-09-10 12:24:51'),
(78, 'view_material_requests_done', 'web', '2025-09-30 13:58:23', '2025-09-30 13:58:23');

-- --------------------------------------------------------

--
-- Table structure for table `production_requests`
--

CREATE TABLE `production_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `request_type` enum('direct','indirect') NOT NULL DEFAULT 'indirect',
  `project_name` varchar(255) NOT NULL,
  `project_description` text DEFAULT NULL,
  `client_id` bigint(20) UNSIGNED NOT NULL,
  `showroom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `agreement_file` varchar(255) DEFAULT NULL,
  `status` enum('pending','received','under_review','approved','rejected','in_progress','materials_wait','materials_prep','materials_done','on_hold','completed','cancelled','submitted','deleted','created','draft') NOT NULL DEFAULT 'pending',
  `current_phase` varchar(155) DEFAULT NULL,
  `phase_status` varchar(155) DEFAULT NULL,
  `current_owner_user_id` bigint(20) DEFAULT NULL,
  `current_owner_role` varchar(155) DEFAULT NULL,
  `sent_to_owner_at` datetime DEFAULT NULL,
  `received_by_owner_at` datetime DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `submitted_at` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_request_files`
--

CREATE TABLE `production_request_files` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `production_request_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED NOT NULL,
  `estimated_cost` float(9,2) NOT NULL DEFAULT 0.00,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_request_logs`
--

CREATE TABLE `production_request_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `production_request_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `note` text DEFAULT NULL,
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `happened_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `production_request_logs`
--

INSERT INTO `production_request_logs` (`id`, `production_request_id`, `type`, `data`, `note`, `causer_id`, `happened_at`, `created_at`, `updated_at`) VALUES
(1, 17, 'created', '{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null}', 'تم إنشاء الطلب', 13, '2025-09-17 17:03:55', '2025-09-17 17:03:55', '2025-09-17 17:03:55'),
(2, 17, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null,\"sent_at\":null,\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":null}}', 'Phase: — → showroom_review | Status: — → pending | Owner: — → showroom_manager', 13, '2025-09-17 17:03:55', '2025-09-17 17:03:55', '2025-09-17 17:03:55'),
(3, 17, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner\":null,\"phase_label\":\"\",\"status_label\":\"\",\"owner_label\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة — (—) إلى مرحلة مراجعة المعرض (قيد الانتظار) | المالك: مدير المعرض', 13, '2025-09-17 17:03:55', '2025-09-17 17:03:55', '2025-09-17 17:03:55'),
(4, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:06:04.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: pending → received | Owner: showroom_manager → showroom_manager', 21, '2025-09-17 17:06:04', '2025-09-17 17:06:04', '2025-09-17 17:06:04'),
(5, 17, 'received', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":129.058284}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 21, '2025-09-17 17:06:04', '2025-09-17 17:06:04', '2025-09-17 17:06:04'),
(6, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:06:04.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:06:04.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: received → under_review | Owner: showroom_manager → showroom_manager', 21, '2025-09-17 17:06:30', '2025-09-17 17:06:30', '2025-09-17 17:06:30'),
(7, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 21, '2025-09-17 17:06:30', '2025-09-17 17:06:30', '2025-09-17 17:06:30'),
(8, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:06:04.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"rejected\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:06:04.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: under_review → rejected | Owner: showroom_manager → showroom_manager', 21, '2025-09-17 17:10:15', '2025-09-17 17:10:15', '2025-09-17 17:10:15'),
(9, 17, 'rejected', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"under_review\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"to_status\":\"rejected\",\"to_label\":\"\\u0645\\u0631\\u0641\\u0648\\u0636\"}', 'تم رفض الطلب في مرحلة مراجعة المعرض — السبب: المواصفات ناقصة', 21, '2025-09-17 17:10:15', '2025-09-17 17:10:15', '2025-09-17 17:10:15'),
(10, 17, 'status_changed', '{\"from\":\"pending\",\"to\":\"under_review\",\"note\":null}', 'تم تغيير الحالة إلى: قيد المراجعة', 13, '2025-09-17 17:13:03', '2025-09-17 17:13:03', '2025-09-17 17:13:03'),
(11, 17, 'status_changed', '{\"from\":\"under_review\",\"to\":\"approved\",\"note\":null}', 'تم تغيير الحالة إلى: مقبول', 13, '2025-09-17 17:18:00', '2025-09-17 17:18:00', '2025-09-17 17:18:00'),
(12, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"rejected\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:06:04.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:19:29.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: rejected → received | Owner: showroom_manager → showroom_manager', 21, '2025-09-17 17:19:29', '2025-09-17 17:19:29', '2025-09-17 17:19:29'),
(13, 17, 'received', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"rejected\",\"from_label\":\"\\u0645\\u0631\\u0641\\u0648\\u0636\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":934.359765}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 21, '2025-09-17 17:19:29', '2025-09-17 17:19:29', '2025-09-17 17:19:29'),
(14, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:19:29.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:19:29.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: received → under_review | Owner: showroom_manager → showroom_manager', 21, '2025-09-17 17:21:11', '2025-09-17 17:21:11', '2025-09-17 17:21:11'),
(15, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 21, '2025-09-17 17:21:11', '2025-09-17 17:21:11', '2025-09-17 17:21:11'),
(16, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:03:55.000000Z\",\"recv_at\":\"2025-09-17T14:19:29.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":null}}', 'Phase: showroom_review → factory_intake | Status: under_review → pending | Owner: showroom_manager → factory_manager', 21, '2025-09-17 17:22:38', '2025-09-17 17:22:38', '2025-09-17 17:22:38'),
(17, 17, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة مراجعة المعرض (قيد المراجعة) إلى مرحلة استلام المصنع (قيد الانتظار) | المالك: مدير المصنع', 21, '2025-09-17 17:22:38', '2025-09-17 17:22:38', '2025-09-17 17:22:38'),
(18, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:25:55.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: pending → received | Owner: factory_manager → factory_manager', 15, '2025-09-17 17:25:55', '2025-09-17 17:25:55', '2025-09-17 17:25:55'),
(19, 17, 'received', '{\"phase\":\"factory_intake\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":197.674555}', 'تم تأكيد الاستلام في مرحلة استلام المصنع', 15, '2025-09-17 17:25:55', '2025-09-17 17:25:55', '2025-09-17 17:25:55'),
(20, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:25:55.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:25:55.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: received → under_review | Owner: factory_manager → factory_manager', 15, '2025-09-17 17:26:05', '2025-09-17 17:26:05', '2025-09-17 17:26:05'),
(21, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (تم الاستلام) إلى مرحلة استلام المصنع (قيد المراجعة) | المالك: مدير المصنع', 15, '2025-09-17 17:26:05', '2025-09-17 17:26:05', '2025-09-17 17:26:05'),
(22, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:25:55.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:26:37.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: under_review → received | Owner: factory_manager → factory_manager', 15, '2025-09-17 17:26:37', '2025-09-17 17:26:37', '2025-09-17 17:26:37'),
(23, 17, 'received', '{\"phase\":\"factory_intake\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"from_status\":\"under_review\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":239.267931}', 'تم تأكيد الاستلام في مرحلة استلام المصنع', 15, '2025-09-17 17:26:37', '2025-09-17 17:26:37', '2025-09-17 17:26:37'),
(24, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:26:37.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:26:37.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: received → under_review | Owner: factory_manager → factory_manager', 15, '2025-09-17 17:28:18', '2025-09-17 17:28:18', '2025-09-17 17:28:18'),
(25, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (تم الاستلام) إلى مرحلة استلام المصنع (قيد المراجعة) | المالك: مدير المصنع', 15, '2025-09-17 17:28:19', '2025-09-17 17:28:19', '2025-09-17 17:28:19'),
(26, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:26:37.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:26:37.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: under_review → approved | Owner: factory_manager → factory_manager', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(27, 17, 'project_bootstrap', '{\"project_id\":27}', 'تم إنشاء مشروع #27 وربطه بالطلب.', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(28, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:22:38.000000Z\",\"recv_at\":\"2025-09-17T14:26:37.000000Z\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-17T14:29:26.000000Z\",\"recv_at\":null}}', 'Phase: factory_intake → department_assignment | Status: approved → pending | Owner: factory_manager → factory_manager', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(29, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0645\\u0639\\u062a\\u0645\\u062f\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (معتمد) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(30, 17, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0645\\u0639\\u062a\\u0645\\u062f\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (قيد المراجعة) إلى مرحلة استلام المصنع (معتمد) | المالك: مدير المصنع', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(31, 17, 'project_bootstrap', '{\"project_id\":27}', 'تم إنشاء مشروع #27 وربطه بالطلب.', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(32, 17, 'transition', '{\"from\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة إسناد الأقسام (قيد الانتظار) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 15, '2025-09-17 17:29:26', '2025-09-17 17:29:26', '2025-09-17 17:29:26'),
(33, 18, 'created', '{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null}', 'تم إنشاء الطلب', 14, '2025-09-28 17:51:48', '2025-09-28 17:51:48', '2025-09-28 17:51:48'),
(34, 18, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null,\"sent_at\":null,\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:51:48.000000Z\",\"recv_at\":null}}', 'Phase: — → showroom_review | Status: — → pending | Owner: — → showroom_manager', 14, '2025-09-28 17:51:48', '2025-09-28 17:51:48', '2025-09-28 17:51:48'),
(35, 18, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner\":null,\"phase_label\":\"\",\"status_label\":\"\",\"owner_label\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة — (—) إلى مرحلة مراجعة المعرض (قيد الانتظار) | المالك: مدير المعرض', 14, '2025-09-28 17:51:48', '2025-09-28 17:51:48', '2025-09-28 17:51:48'),
(36, 18, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:51:48.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:51:48.000000Z\",\"recv_at\":\"2025-09-28T14:53:20.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: pending → received | Owner: showroom_manager → showroom_manager', 21, '2025-09-28 17:53:20', '2025-09-28 17:53:20', '2025-09-28 17:53:20'),
(37, 18, 'received', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":92.378833}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 21, '2025-09-28 17:53:20', '2025-09-28 17:53:20', '2025-09-28 17:53:20'),
(38, 18, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:51:48.000000Z\",\"recv_at\":\"2025-09-28T14:53:20.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:51:48.000000Z\",\"recv_at\":\"2025-09-28T14:53:20.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: received → under_review | Owner: showroom_manager → showroom_manager', 21, '2025-09-28 17:54:00', '2025-09-28 17:54:00', '2025-09-28 17:54:00'),
(39, 18, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 21, '2025-09-28 17:54:00', '2025-09-28 17:54:00', '2025-09-28 17:54:00'),
(40, 18, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:51:48.000000Z\",\"recv_at\":\"2025-09-28T14:53:20.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":null}}', 'Phase: showroom_review → factory_intake | Status: under_review → pending | Owner: showroom_manager → factory_manager', 21, '2025-09-28 17:54:15', '2025-09-28 17:54:15', '2025-09-28 17:54:15'),
(41, 18, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة مراجعة المعرض (قيد المراجعة) إلى مرحلة استلام المصنع (قيد الانتظار) | المالك: مدير المصنع', 21, '2025-09-28 17:54:15', '2025-09-28 17:54:15', '2025-09-28 17:54:15'),
(42, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":\"2025-09-28T14:54:41.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: pending → received | Owner: factory_manager → factory_manager', 15, '2025-09-28 17:54:41', '2025-09-28 17:54:41', '2025-09-28 17:54:41'),
(43, 18, 'received', '{\"phase\":\"factory_intake\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":26.470024000000002}', 'تم تأكيد الاستلام في مرحلة استلام المصنع', 15, '2025-09-28 17:54:41', '2025-09-28 17:54:41', '2025-09-28 17:54:41'),
(44, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":\"2025-09-28T14:54:41.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":\"2025-09-28T14:54:41.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: received → under_review | Owner: factory_manager → factory_manager', 15, '2025-09-28 17:54:55', '2025-09-28 17:54:55', '2025-09-28 17:54:55'),
(45, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (تم الاستلام) إلى مرحلة استلام المصنع (قيد المراجعة) | المالك: مدير المصنع', 15, '2025-09-28 17:54:55', '2025-09-28 17:54:55', '2025-09-28 17:54:55'),
(46, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":\"2025-09-28T14:54:41.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":\"2025-09-28T14:54:41.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: under_review → approved | Owner: factory_manager → factory_manager', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(47, 18, 'project_bootstrap', '{\"project_id\":28}', 'تم إنشاء مشروع #28 وربطه بالطلب.', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(48, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:54:15.000000Z\",\"recv_at\":\"2025-09-28T14:54:41.000000Z\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:55:08.000000Z\",\"recv_at\":null}}', 'Phase: factory_intake → department_assignment | Status: approved → pending | Owner: factory_manager → factory_manager', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(49, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0645\\u0639\\u062a\\u0645\\u062f\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (معتمد) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(50, 18, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0645\\u0639\\u062a\\u0645\\u062f\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (قيد المراجعة) إلى مرحلة استلام المصنع (معتمد) | المالك: مدير المصنع', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(51, 18, 'project_bootstrap', '{\"project_id\":28}', 'تم إنشاء مشروع #28 وربطه بالطلب.', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(52, 18, 'transition', '{\"from\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة إسناد الأقسام (قيد الانتظار) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 15, '2025-09-28 17:55:08', '2025-09-28 17:55:08', '2025-09-28 17:55:08'),
(53, 18, 'transition', '{\"from\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:55:08.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"department_assignment\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:55:08.000000Z\",\"recv_at\":\"2025-09-28T14:58:36.000000Z\"}}', 'Phase: department_assignment → department_assignment | Status: pending → received | Owner: factory_manager → factory_manager', 15, '2025-09-28 17:58:36', '2025-09-28 17:58:36', '2025-09-28 17:58:36'),
(54, 18, 'received', '{\"phase\":\"department_assignment\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":208.265431}', 'تم تأكيد الاستلام في مرحلة إسناد الأقسام', 15, '2025-09-28 17:58:36', '2025-09-28 17:58:36', '2025-09-28 17:58:36'),
(55, 18, 'transition', '{\"from\":{\"phase\":\"department_assignment\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:55:08.000000Z\",\"recv_at\":\"2025-09-28T14:58:36.000000Z\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-28T14:55:08.000000Z\",\"recv_at\":\"2025-09-28T14:59:04.000000Z\"}}', 'Phase: department_assignment → department_assignment | Status: received → received | Owner: factory_manager → factory_manager', 15, '2025-09-28 17:59:04', '2025-09-28 17:59:04', '2025-09-28 17:59:04'),
(56, 18, 'received', '{\"phase\":\"department_assignment\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"from_status\":\"received\",\"from_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":236.740975}', 'تم تأكيد الاستلام في مرحلة إسناد الأقسام', 15, '2025-09-28 17:59:04', '2025-09-28 17:59:04', '2025-09-28 17:59:04'),
(57, 19, 'created', '{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null}', 'تم إنشاء الطلب', 14, '2025-09-30 17:57:40', '2025-09-30 17:57:40', '2025-09-30 17:57:40'),
(58, 19, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null,\"sent_at\":null,\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T14:57:40.000000Z\",\"recv_at\":null}}', 'Phase: — → showroom_review | Status: — → pending | Owner: — → showroom_manager', 14, '2025-09-30 17:57:40', '2025-09-30 17:57:40', '2025-09-30 17:57:40'),
(59, 19, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner\":null,\"phase_label\":\"\",\"status_label\":\"\",\"owner_label\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة — (—) إلى مرحلة مراجعة المعرض (قيد الانتظار) | المالك: مدير المعرض', 14, '2025-09-30 17:57:40', '2025-09-30 17:57:40', '2025-09-30 17:57:40'),
(60, 19, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T14:57:40.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T14:57:40.000000Z\",\"recv_at\":\"2025-09-30T16:40:34.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: pending → received | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:40:34', '2025-09-30 19:40:34', '2025-09-30 19:40:34'),
(61, 19, 'received', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":6175.002643}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 21, '2025-09-30 19:40:35', '2025-09-30 19:40:35', '2025-09-30 19:40:35'),
(62, 19, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T14:57:40.000000Z\",\"recv_at\":\"2025-09-30T16:40:34.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T14:57:40.000000Z\",\"recv_at\":\"2025-09-30T16:40:34.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: received → under_review | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:41:28', '2025-09-30 19:41:28', '2025-09-30 19:41:28'),
(63, 19, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 21, '2025-09-30 19:41:28', '2025-09-30 19:41:28', '2025-09-30 19:41:28'),
(64, 17, 'deleted', NULL, 'تم حذف الطلب', 22, '2025-09-30 19:42:43', '2025-09-30 19:42:43', '2025-09-30 19:42:43'),
(65, 18, 'deleted', NULL, 'تم حذف الطلب', 22, '2025-09-30 19:42:46', '2025-09-30 19:42:46', '2025-09-30 19:42:46'),
(66, 19, 'deleted', NULL, 'تم حذف الطلب', 22, '2025-09-30 19:42:50', '2025-09-30 19:42:50', '2025-09-30 19:42:50'),
(67, 20, 'created', '{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null}', 'تم إنشاء الطلب', 14, '2025-09-30 19:47:03', '2025-09-30 19:47:03', '2025-09-30 19:47:03'),
(68, 20, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner_role\":null,\"owner_user\":null,\"sent_at\":null,\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":null}}', 'Phase: — → showroom_review | Status: — → pending | Owner: — → showroom_manager', 14, '2025-09-30 19:47:03', '2025-09-30 19:47:03', '2025-09-30 19:47:03'),
(69, 20, 'transition', '{\"from\":{\"phase\":null,\"status\":null,\"owner\":null,\"phase_label\":\"\",\"status_label\":\"\",\"owner_label\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة — (—) إلى مرحلة مراجعة المعرض (قيد الانتظار) | المالك: مدير المعرض', 14, '2025-09-30 19:47:03', '2025-09-30 19:47:03', '2025-09-30 19:47:03'),
(70, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":\"2025-09-30T16:48:12.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: pending → received | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:48:12', '2025-09-30 19:48:12', '2025-09-30 19:48:12'),
(71, 20, 'received', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":69.59392999999999}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 21, '2025-09-30 19:48:12', '2025-09-30 19:48:12', '2025-09-30 19:48:12'),
(72, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":\"2025-09-30T16:48:12.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":\"2025-09-30T16:48:12.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: received → under_review | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:48:20', '2025-09-30 19:48:20', '2025-09-30 19:48:20'),
(73, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 21, '2025-09-30 19:48:20', '2025-09-30 19:48:20', '2025-09-30 19:48:20'),
(74, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":\"2025-09-30T16:48:12.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"rejected\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":\"2025-09-30T16:48:12.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: under_review → rejected | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:51:09', '2025-09-30 19:51:09', '2025-09-30 19:51:09'),
(75, 20, 'rejected', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"under_review\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"to_status\":\"rejected\",\"to_label\":\"\\u0645\\u0631\\u0641\\u0648\\u0636\"}', 'تم رفض الطلب في مرحلة مراجعة المعرض — السبب: ناقص', 21, '2025-09-30 19:51:09', '2025-09-30 19:51:09', '2025-09-30 19:51:09'),
(76, 20, 'status_changed', '{\"from\":\"pending\",\"to\":\"under_review\",\"note\":null}', 'تم تغيير الحالة إلى: قيد المراجعة', 21, '2025-09-30 19:54:00', '2025-09-30 19:54:00', '2025-09-30 19:54:00'),
(77, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"rejected\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:47:03.000000Z\",\"recv_at\":\"2025-09-30T16:48:12.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:55:25.000000Z\",\"recv_at\":null}}', 'Phase: showroom_review → showroom_review | Status: rejected → pending | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:55:25', '2025-09-30 19:55:25', '2025-09-30 19:55:25'),
(78, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"rejected\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0645\\u0631\\u0641\\u0648\\u0636\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'إعادة توجيه الطلب للمراجعة مرة أخرى بعد تحديث المحتوى.', 21, '2025-09-30 19:55:25', '2025-09-30 19:55:25', '2025-09-30 19:55:25'),
(79, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"pending\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:55:25.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:55:25.000000Z\",\"recv_at\":\"2025-09-30T16:56:12.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: pending → received | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:56:12', '2025-09-30 19:56:12', '2025-09-30 19:56:12'),
(80, 20, 'received', '{\"phase\":\"showroom_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":47.083448}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 21, '2025-09-30 19:56:12', '2025-09-30 19:56:12', '2025-09-30 19:56:12'),
(81, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:55:25.000000Z\",\"recv_at\":\"2025-09-30T16:56:12.000000Z\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:55:25.000000Z\",\"recv_at\":\"2025-09-30T16:56:12.000000Z\"}}', 'Phase: showroom_review → showroom_review | Status: received → under_review | Owner: showroom_manager → showroom_manager', 21, '2025-09-30 19:56:18', '2025-09-30 19:56:18', '2025-09-30 19:56:18');
INSERT INTO `production_request_logs` (`id`, `production_request_id`, `type`, `data`, `note`, `causer_id`, `happened_at`, `created_at`, `updated_at`) VALUES
(82, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"received\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"showroom_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 21, '2025-09-30 19:56:18', '2025-09-30 19:56:18', '2025-09-30 19:56:18'),
(83, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner_role\":\"showroom_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:55:25.000000Z\",\"recv_at\":\"2025-09-30T16:56:12.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":null}}', 'Phase: showroom_review → factory_intake | Status: under_review → pending | Owner: showroom_manager → factory_manager', 21, '2025-09-30 19:57:08', '2025-09-30 19:57:08', '2025-09-30 19:57:08'),
(84, 20, 'transition', '{\"from\":{\"phase\":\"showroom_review\",\"status\":\"under_review\",\"owner\":\"showroom_manager\",\"phase_label\":\"\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0639\\u0631\\u0636\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة مراجعة المعرض (قيد المراجعة) إلى مرحلة استلام المصنع (قيد الانتظار) | المالك: مدير المصنع', 21, '2025-09-30 19:57:08', '2025-09-30 19:57:08', '2025-09-30 19:57:08'),
(85, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":\"2025-09-30T16:57:50.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: pending → received | Owner: factory_manager → factory_manager', 15, '2025-09-30 19:57:50', '2025-09-30 19:57:50', '2025-09-30 19:57:50'),
(86, 20, 'received', '{\"phase\":\"factory_intake\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"from_status\":\"pending\",\"from_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"to_status\":\"received\",\"to_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"wait_seconds\":42.825472999999995}', 'تم تأكيد الاستلام في مرحلة استلام المصنع', 15, '2025-09-30 19:57:50', '2025-09-30 19:57:50', '2025-09-30 19:57:50'),
(87, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":\"2025-09-30T16:57:50.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":\"2025-09-30T16:57:50.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: received → under_review | Owner: factory_manager → factory_manager', 15, '2025-09-30 19:57:55', '2025-09-30 19:57:55', '2025-09-30 19:57:55'),
(88, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"received\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u062a\\u0645 \\u0627\\u0644\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (تم الاستلام) إلى مرحلة استلام المصنع (قيد المراجعة) | المالك: مدير المصنع', 15, '2025-09-30 19:57:55', '2025-09-30 19:57:55', '2025-09-30 19:57:55'),
(89, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":\"2025-09-30T16:57:50.000000Z\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":\"2025-09-30T16:57:50.000000Z\"}}', 'Phase: factory_intake → factory_intake | Status: under_review → approved | Owner: factory_manager → factory_manager', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(90, 20, 'project_bootstrap', '{\"project_id\":29}', 'تم إنشاء مشروع #29 وربطه بالطلب.', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(91, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:57:08.000000Z\",\"recv_at\":\"2025-09-30T16:57:50.000000Z\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:58:14.000000Z\",\"recv_at\":null}}', 'Phase: factory_intake → department_assignment | Status: approved → pending | Owner: factory_manager → factory_manager', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(92, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0645\\u0639\\u062a\\u0645\\u062f\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (معتمد) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(93, 20, 'transition', '{\"from\":{\"phase\":\"factory_intake\",\"status\":\"under_review\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0645\\u0631\\u0627\\u062c\\u0639\\u0629\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"factory_intake\",\"status\":\"approved\",\"phase_label\":\"\\u0627\\u0633\\u062a\\u0644\\u0627\\u0645 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\",\"status_label\":\"\\u0645\\u0639\\u062a\\u0645\\u062f\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة استلام المصنع (قيد المراجعة) إلى مرحلة استلام المصنع (معتمد) | المالك: مدير المصنع', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(94, 20, 'project_bootstrap', '{\"project_id\":29}', 'تم إنشاء مشروع #29 وربطه بالطلب.', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(95, 20, 'transition', '{\"from\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner\":\"factory_manager\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\",\"owner_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"},\"to\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"phase_label\":\"\\u0625\\u0633\\u0646\\u0627\\u062f \\u0627\\u0644\\u0623\\u0642\\u0633\\u0627\\u0645\",\"status_label\":\"\\u0642\\u064a\\u062f \\u0627\\u0644\\u0627\\u0646\\u062a\\u0638\\u0627\\u0631\"},\"owner_role\":\"factory_manager\",\"owner_role_label\":\"\\u0645\\u062f\\u064a\\u0631 \\u0627\\u0644\\u0645\\u0635\\u0646\\u0639\"}', 'انتقال من مرحلة إسناد الأقسام (قيد الانتظار) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 15, '2025-09-30 19:58:14', '2025-09-30 19:58:14', '2025-09-30 19:58:14'),
(96, 20, 'transition', '{\"from\":{\"phase\":\"department_assignment\",\"status\":\"pending\",\"owner_role\":\"factory_manager\",\"owner_user\":null,\"sent_at\":\"2025-09-30T16:58:14.000000Z\",\"recv_at\":null},\"to\":{\"phase\":\"closed\",\"status\":\"completed\",\"owner_role\":null,\"owner_user\":null,\"sent_at\":null,\"recv_at\":null}}', 'Phase: department_assignment → closed | Status: pending → completed | Owner: factory_manager → —', 22, '2025-09-30 20:36:19', '2025-09-30 20:36:19', '2025-09-30 20:36:19'),
(97, 20, 'request_finalized', '{\"by\":\"project_completed\"}', 'اكتمل المشروع وجميع المهام، تم إغلاق الطلب.', 22, '2025-09-30 20:36:19', '2025-09-30 20:36:19', '2025-09-30 20:36:19'),
(98, 20, 'request_finalized', '{\"by\":\"project_completed\"}', 'اكتمل المشروع وجميع المهام، تم إغلاق الطلب.', 22, '2025-09-30 20:36:19', '2025-09-30 20:36:19', '2025-09-30 20:36:19'),
(99, 20, 'deleted', NULL, 'تم حذف الطلب', 1, '2025-10-01 23:08:29', '2025-10-01 23:08:29', '2025-10-01 23:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks`
--

CREATE TABLE `production_tasks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` int(11) NOT NULL,
  `assigned_to_employee_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `status` enum('pending','assigned','received','under_review','approved','rejected','in_progress','materials_wait','materials_prep','materials_done','on_hold','completed','cancelled','waiting_production') NOT NULL DEFAULT 'pending',
  `estimated_cost` float(9,2) DEFAULT 0.00,
  `current_owner_user_id` bigint(20) DEFAULT NULL,
  `current_owner_role` varchar(150) DEFAULT NULL,
  `sent_to_owner_at` datetime DEFAULT NULL,
  `received_by_owner_at` datetime DEFAULT NULL,
  `planned_start_at` datetime DEFAULT NULL,
  `planned_end_at` datetime DEFAULT NULL,
  `planned_install_at` datetime DEFAULT NULL,
  `client_receipt` varchar(255) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_comments`
--

CREATE TABLE `production_tasks_comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `production_tasks_comments`
--

INSERT INTO `production_tasks_comments` (`id`, `task_id`, `user_id`, `body`, `attachments`, `created_at`, `updated_at`) VALUES
(1, 37, 16, 'مرحبا هذا العميل يحتاج اضافة على التصميم', '[]', '2025-09-30 17:45:56', '2025-09-30 17:45:56'),
(2, 41, 16, 'المنيوم ابيض', '[]', '2025-09-30 20:27:50', '2025-09-30 20:27:50'),
(3, 41, 22, 'لا تنسى الباب', '[]', '2025-09-30 20:28:52', '2025-09-30 20:28:52');

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_log`
--

CREATE TABLE `production_tasks_log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `causer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `happened_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_material_requests`
--

CREATE TABLE `production_tasks_material_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_by` bigint(20) UNSIGNED DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `status` enum('requested','approved','fulfilled','cancelled') NOT NULL DEFAULT 'requested',
  `estimated_cost` float(9,2) DEFAULT 0.00,
  `actual_cost` float(9,2) DEFAULT 0.00,
  `expected_delivery_at` datetime DEFAULT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `po_file` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `provided_by` bigint(20) UNSIGNED DEFAULT NULL,
  `provided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_time_entries`
--

CREATE TABLE `production_tasks_time_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `task_id` bigint(20) UNSIGNED NOT NULL,
  `started_by` bigint(20) UNSIGNED DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `duration_sec` int(10) UNSIGNED DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `production_request_id` bigint(20) UNSIGNED NOT NULL,
  `client_id` int(11) NOT NULL,
  `project_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('in_progress','completed','on_hold') NOT NULL DEFAULT 'in_progress',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_delays`
--

CREATE TABLE `project_delays` (
  `delay_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `phase_id` int(11) DEFAULT NULL,
  `delay_days` int(11) NOT NULL,
  `delay_reason` text NOT NULL,
  `responsible_party` enum('internal','external','client') NOT NULL,
  `action_taken` text DEFAULT NULL,
  `recorded_by` int(11) NOT NULL,
  `record_date` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

CREATE TABLE `project_files` (
  `file_id` int(11) NOT NULL,
  `project_id` bigint(20) UNSIGNED NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `version` varchar(20) DEFAULT NULL,
  `estimated_cost` float(9,2) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

CREATE TABLE `project_images` (
  `image_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `phase_id` int(11) DEFAULT NULL,
  `image_name` varchar(255) NOT NULL,
  `image_path` varchar(512) NOT NULL,
  `thumbnail_path` varchar(512) DEFAULT NULL,
  `image_type` enum('progress','issue','final','design','other') NOT NULL,
  `taken_by` int(11) DEFAULT NULL,
  `taken_date` datetime DEFAULT NULL,
  `uploaded_by` int(11) NOT NULL,
  `upload_date` timestamp NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quality_checks`
--

CREATE TABLE `quality_checks` (
  `check_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `phase_id` int(11) NOT NULL,
  `check_date` datetime DEFAULT current_timestamp(),
  `inspector_id` int(11) NOT NULL,
  `check_type` enum('incoming','in_process','final') NOT NULL,
  `result` enum('passed','failed','conditional') NOT NULL,
  `defects_found` int(11) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `corrective_actions` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `guard_name` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'web', '2025-07-06 07:18:11', '2025-08-19 12:24:40'),
(2, 'admin', 'web', '2025-08-04 11:57:05', '2025-08-19 12:25:27'),
(3, 'sales', 'web', '2025-08-04 12:03:25', '2025-08-19 12:52:54'),
(4, 'department_manager', 'web', '2025-08-19 12:21:56', '2025-08-19 12:21:56'),
(5, 'purchasing_manager', 'web', '2025-08-19 12:26:53', '2025-08-19 12:26:53'),
(6, 'showroom_manager', 'web', '2025-08-21 05:22:46', '2025-08-21 05:22:46'),
(7, 'factory_manager', 'web', '2025-08-21 05:22:46', '2025-08-21 05:22:46'),
(8, 'quality_manager', 'web', '2025-08-21 05:22:46', '2025-08-21 05:22:46'),
(9, 'installation_manager', 'web', '2025-08-21 05:22:46', '2025-08-21 05:22:46');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
(70, 1),
(72, 1),
(3, 2),
(4, 2),
(5, 2),
(7, 2),
(8, 2),
(9, 2),
(11, 2),
(13, 2),
(16, 2),
(17, 2),
(18, 2),
(22, 2),
(23, 2),
(24, 2),
(26, 2),
(27, 2),
(28, 2),
(30, 2),
(31, 2),
(32, 2),
(34, 2),
(35, 2),
(36, 2),
(38, 2),
(39, 2),
(40, 2),
(42, 2),
(43, 2),
(44, 2),
(46, 2),
(47, 2),
(48, 2),
(50, 2),
(51, 2),
(52, 2),
(54, 2),
(55, 2),
(56, 2),
(58, 2),
(59, 2),
(60, 2),
(62, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(70, 2),
(72, 2),
(25, 3),
(26, 3),
(27, 3),
(49, 3),
(50, 3),
(51, 3),
(67, 3),
(68, 3),
(65, 4),
(66, 4),
(67, 4),
(68, 4),
(69, 4),
(65, 5),
(69, 5),
(38, 6),
(49, 6),
(50, 6),
(51, 6),
(53, 6),
(54, 6),
(55, 6),
(65, 6),
(66, 6),
(67, 6),
(68, 6),
(69, 6),
(70, 6),
(73, 6),
(49, 7),
(51, 7),
(53, 7),
(54, 7),
(55, 7),
(65, 7),
(66, 7),
(67, 7),
(68, 7),
(69, 7),
(70, 7),
(71, 7),
(73, 7),
(75, 7),
(76, 7);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(190) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`, `created_at`, `updated_at`) VALUES
('xYVvHXANWWyJcbd5S5RTxEGVzAlwkOEoVznaEBCH', 1, '129.208.76.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiZG5JUml4VDdteFNBUERpVVRTYTA4NG5WSmdQc1JkWHFlMUo2cWRPTiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2MDoiJDJ5JDEyJDE3bEVQOXloTWRqZmp6N3JnNmVkRy5uRG5LRU9vWGpsYXZWeWlFaWNCbWZUTnp3Q0NjRHZXIjtzOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czo0ODoiaHR0cHM6Ly9tb2Rlcm5saWZlLmEtZWxzYXllZC5jb20vYWRtaW4vZW1wbG95ZWVzIjt9czo4OiJmaWxhbWVudCI7YTowOnt9czo2OiJ0YWJsZXMiO2E6MTp7czo0MDoiYjFkNmZjMzQzOTc5MWRjNzYwOGFlMTIzYWYwNDhkNWZfZmlsdGVycyI7YToxOntzOjEyOiJpc19jb21wbGV0ZWQiO2E6MTp7czo1OiJ2YWx1ZSI7aTowO319fX0=', 1759355046, '2025-10-01 20:08:02', '2025-10-01 21:44:06');

-- --------------------------------------------------------

--
-- Table structure for table `showrooms`
--

CREATE TABLE `showrooms` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city_id` bigint(20) UNSIGNED DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `showrooms`
--

INSERT INTO `showrooms` (`id`, `name`, `address`, `city_id`, `phone`, `email`, `manager_id`, `created_at`, `updated_at`) VALUES
(4, 'معرض القطيف ', 'المجيدية - شارع القدس', 17, '0535744493', 'sales@modern-life.net', 18, '2025-09-17 11:08:43', '2025-09-17 11:08:43'),
(5, 'معرض الاحساء', 'المبرز', 23, '0542485208', 'shaaban@modern-life.net', 23, '2025-09-17 16:57:02', '2025-09-17 16:57:02'),
(6, 'معرض تاروت', 'صناعية تركية', 22, '0545513228', 'Mohamed.abdulaziz@modern-life.net', 21, '2025-09-17 16:58:44', '2025-09-17 16:58:44');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `log_level` enum('info','warning','error','critical') NOT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`context`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`setting_options`)),
  `is_sensitive` tinyint(1) NOT NULL DEFAULT 0,
  `validation_rules` varchar(255) DEFAULT NULL,
  `help_text` text DEFAULT NULL,
  `setting_group` varchar(50) DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_options`, `is_sensitive`, `validation_rules`, `help_text`, `setting_group`, `setting_type`, `is_public`, `description`, `created_at`, `updated_at`) VALUES
(2, 'site_name', 'نظام إدارة التصنيع', NULL, 0, NULL, NULL, 'general', 'text', 1, 'اسم النظام المعروض في العنوان', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(3, 'system_email', 'info@factory.com', NULL, 0, NULL, NULL, 'general', 'email', 0, 'البريد الإلكتروني العام للنظام', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(4, 'default_language', 'ar', NULL, 0, NULL, NULL, 'general', 'select', 0, 'اللغة الافتراضية للنظام', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(5, 'notify_on_late_department_response', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'إرسال إشعار عند تأخر القسم عن الرد', '2025-07-09 11:41:12', '2025-09-18 09:21:54'),
(6, 'notify_manager_on_request', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'إشعار مدير المصنع عند وصول طلب جديد', '2025-07-09 11:41:12', '2025-09-18 09:21:54'),
(7, 'notification_email', 'notifications@factory.com', NULL, 0, NULL, NULL, 'notifications', 'email', 0, 'البريد المستلم للتنبيهات', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(8, 'factory_name', 'مصنع الحياة الحديثة للصناعة ', NULL, 0, NULL, NULL, 'factory', 'text', 1, 'الاسم التجاري للمصنع', '2025-07-09 11:41:12', '2025-09-17 12:46:23'),
(9, 'factory_logo', 'settings/bullet.png', NULL, 0, NULL, NULL, 'factory', 'file', 1, 'شعار المصنع الرسمي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(10, 'factory_address', 'الدمام - المدينة الصناعية', NULL, 0, NULL, NULL, 'factory', 'text', 1, 'عنوان المصنع الرئيسي', '2025-07-09 11:41:12', '2025-09-17 07:13:49'),
(11, 'factory_phone', '+966500000000', NULL, 0, NULL, NULL, 'factory', 'text', 1, 'رقم الهاتف الرئيسي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(12, 'factory_email', 'support@factory.com', NULL, 0, NULL, NULL, 'factory', 'email', 1, 'البريد الإلكتروني الرسمي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(13, 'purchasing_budget_cap_pct', '50', NULL, 0, NULL, NULL, 'general', 'text', 1, 'سقف الميزانية للمشتريات', '2025-08-21 07:48:21', '2025-09-10 12:03:29'),
(14, 'app_name', 'Modern Life', NULL, 0, NULL, NULL, 'general', 'text', 0, 'اسم النظام', '2025-09-10 11:42:32', '2025-09-17 07:13:54'),
(15, 'company_name', 'Modern Life Co.', NULL, 0, NULL, NULL, 'general', 'text', 0, 'اسم الشركة', '2025-09-10 11:42:32', '2025-09-17 07:13:58'),
(16, 'support_email', 'support@example.com', NULL, 0, 'nullable|email', 'ستُستخدم لاستقبال رسائل الدعم', 'general', 'email', 0, 'بريد الدعم', '2025-09-10 11:42:32', '2025-09-10 11:55:02'),
(17, 'support_phone', NULL, NULL, 0, 'nullable|string|max:30', NULL, 'general', 'text', 0, 'هاتف الدعم', '2025-09-10 11:42:32', '2025-09-10 12:02:17'),
(18, 'base_url', 'http://localhost:8000', NULL, 0, 'required|url', NULL, 'general', 'url', 0, 'رابط النظام', '2025-09-10 11:42:32', '2025-09-10 11:55:02'),
(19, 'brand_logo', 'settings/01K5BGXA006ZEQBX6B8XDAX6N0.png', NULL, 0, NULL, 'يفضل PNG بخلفية شفافة', 'branding', 'image', 0, 'شعار الشركة', '2025-09-10 11:55:02', '2025-09-17 12:43:22'),
(20, 'brand_favicon', NULL, NULL, 0, NULL, 'مقاس 32x32 أو 64x64', 'branding', 'image', 0, 'Favicon', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(21, 'primary_color', '#0ea5e9', NULL, 0, NULL, NULL, 'branding', 'color', 0, 'اللون الأساسي', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(22, 'secondary_color', '#22c55e', NULL, 0, NULL, NULL, 'branding', 'color', 0, 'اللون الثانوي', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(23, 'locale', 'ar', '{\"ar\": \"العربية\", \"en\": \"English\"}', 0, 'required|in:ar,en', 'تؤثر على لغة واجهة فيلامنت والتواريخ.', 'locale', 'locale', 0, 'لغة الواجهة', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(24, 'timezone', 'Asia/Riyadh', NULL, 0, 'required|string', NULL, 'locale', 'timezone', 0, 'المنطقة الزمنية', '2025-09-10 11:55:02', '2025-09-10 12:05:52'),
(25, 'date_format', 'Y-m-d', '{\"Y-m-d\": \"2025-09-09\", \"d/m/Y\": \"09/09/2025\", \"m/d/Y\": \"09/09/2025\", \"M j, Y\": \"Sep 9, 2025\"}', 0, 'required|string', 'تأثيره يظهر في أعمدة/حقول التاريخ.', 'locale', 'select', 0, 'تنسيق التاريخ', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(26, 'time_format', 'h:i A', '{\"H:i\": \"23:15\", \"h:i A\": \"11:15 PM\"}', 0, 'required|string', NULL, 'locale', 'select', 0, 'تنسيق الوقت', '2025-09-10 11:55:02', '2025-09-10 12:05:52'),
(27, 'week_starts_on', 'saturday', '{\"monday\": \"الاثنين\", \"sunday\": \"الأحد\", \"saturday\": \"السبت\"}', 0, 'required|in:saturday,sunday,monday', NULL, 'locale', 'select', 0, 'أول أيام الأسبوع', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(28, 'project_due_soon_days', '3', NULL, 0, 'required|integer|min:0', NULL, 'projects', 'number', 0, 'إنذار قبل انتهاء المشروع (أيام)', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(29, 'task_overdue_grace_hours', '24', NULL, 0, 'required|integer|min:0', NULL, 'projects', 'number', 0, 'ساعات سماح لتأخير المهام', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(30, 'max_upload_mb', '50', NULL, 0, 'required|integer|min:1|max:200', NULL, 'files', 'number', 0, 'الحد الأقصى لحجم الملف (MB)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(31, 'allowed_mime_extra', NULL, NULL, 0, NULL, 'سطر لكل نوع، مثال:\napplication/zip\ntext/csv', 'files', 'textarea', 0, 'أنواع MIME إضافية مسموحة', '2025-09-10 11:55:02', '2025-09-10 12:02:17'),
(32, 'notify_in_app', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات داخل النظام', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(33, 'notify_email', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات البريد', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(34, 'notify_slack', '0', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات Slack', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(35, 'notify_telegram', '0', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات Telegram', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(36, 'quiet_hours_start', '21:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', 'لن تُرسل إشعارات فورية خلال هذه المدة.', 'notifications', 'text', 0, 'بدء الساعات الهادئة (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(37, 'quiet_hours_end', '08:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', NULL, 'notifications', 'text', 0, 'انتهاء الساعات الهادئة (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(38, 'daily_digest_time', '09:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', NULL, 'notifications', 'text', 0, 'موعد الملخص اليومي (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(39, 'weekly_digest_day', 'sunday', '{\"friday\": \"الجمعة\", \"monday\": \"الاثنين\", \"sunday\": \"الأحد\", \"tuesday\": \"الثلاثاء\", \"saturday\": \"السبت\", \"thursday\": \"الخميس\", \"wednesday\": \"الأربعاء\"}', 0, 'nullable|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday', NULL, 'notifications', 'select', 0, 'يوم الملخص الأسبوعي', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(40, 'weekly_digest_time', '09:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', NULL, 'notifications', 'text', 0, 'موعد الملخص الأسبوعي (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(41, 'mail_from_name', 'Modern Life', NULL, 0, 'required|string|max:100', NULL, 'mail', 'text', 0, 'اسم المرسل', '2025-09-10 11:55:02', '2025-09-18 09:21:28'),
(42, 'mail_from_address', 'noreply@a-elsayed.com', NULL, 0, 'required|email', NULL, 'mail', 'email', 0, 'بريد المرسل', '2025-09-10 11:55:02', '2025-09-14 09:41:30'),
(43, 'smtp_host', 'mail.a-elsayed.com', NULL, 0, 'nullable|string', NULL, 'mail', 'text', 0, 'SMTP Host', '2025-09-10 11:55:02', '2025-09-14 09:41:30'),
(44, 'smtp_port', '587', NULL, 0, 'nullable|integer|min:1', NULL, 'mail', 'number', 0, 'SMTP Port', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(45, 'smtp_username', 'noreply@a-elsayed.com', NULL, 0, 'nullable|string', NULL, 'mail', 'text', 0, 'SMTP Username', '2025-09-10 11:55:02', '2025-09-14 09:41:30'),
(46, 'smtp_password', 'eyJpdiI6IlFSNXVRRk8vUXNHMEkzTHpSQUFHWWc9PSIsInZhbHVlIjoiTUhxRkVpQWxXY1NCVnN3dy9CNFpXQT09IiwibWFjIjoiMmE2NDMxODczNjBkOGI2NzQ2NGM3Njg2NWM3N2FjYmRjYzk4ODhkYWI4ZDEwYzk3NmZmMDUyODUzODIxNGRiMSIsInRhZyI6IiJ9', NULL, 1, 'nullable|string', 'يُحفظ مُشفّرًا من صفحة الإعدادات', 'mail', 'secret', 0, 'SMTP Password', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(47, 'smtp_encryption', 'ssl', '{\"ssl\": \"SSL\", \"tls\": \"TLS\", \"none\": \"بدون\"}', 0, 'nullable|in:tls,ssl,none', NULL, 'mail', 'select', 0, 'التشفير', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(48, 'slack_webhook_url', NULL, NULL, 0, 'nullable|url', NULL, 'integrations', 'url', 0, 'Slack Webhook URL', '2025-09-10 11:55:02', '2025-09-10 12:02:17'),
(49, 'telegram_bot_token', 'eyJpdiI6IjRBakxxTkFiUkcxeUN6YklldmVyd1E9PSIsInZhbHVlIjoiaE0rZ3NZR3RabjArajRBbWl6VE1Mdz09IiwibWFjIjoiNDAwNzkzNmViMDMxMzYyZmY3NDVjMjZjNTU3M2FiNzJiNDZhNDU5YmYyOGI3ZGI0NWY0OTNjZWJiOWUyMTdmYyIsInRhZyI6IiJ9', NULL, 1, 'nullable|string', NULL, 'integrations', 'secret', 0, 'توكن بوت تيليجرام', '2025-09-10 11:55:02', '2025-09-18 09:21:54'),
(50, 'telegram_chat_id', NULL, NULL, 0, 'nullable|string', NULL, 'integrations', 'text', 0, 'رقم محادثة تيليجرام', '2025-09-10 11:55:02', '2025-09-10 12:02:17'),
(51, 'webhook_url', NULL, NULL, 0, 'nullable|url', NULL, 'integrations', 'url', 0, 'Webhook عام', '2025-09-10 11:55:02', '2025-09-10 12:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(190) NOT NULL,
  `email` varchar(190) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(190) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'info@mdlife.com', '2025-09-16 10:50:11', '$2y$12$17lEP9yhMdjfjz7rg6edG.nDnKEOoXjlavVyiEicBmfTNzwCCcDvW', 'SEGk5h514cfkikQJEp6xboap9gUfXFPMjblTzNkMZfLYxtvRzNP41zaM2jn7', '2025-07-03 14:55:58', '2025-09-16 10:50:11'),
(13, 'كوثر عبدالعزيز الزهيري', 'salesmodernlife@gmail.com', NULL, '$2y$12$RiPT4EMzwAj61EkM0dmmO.V3hXul.uoxkPL6fafzNw86ISjIu9ZWS', NULL, '2025-09-17 09:38:18', '2025-09-17 12:58:02'),
(14, 'حوراء عيسى ال شيبان', 'eng.hawra.e@modern-life.net', NULL, '$2y$12$p8PJVbhGmmIoIdkoyxavVuNTWV9hI9cjQAHJ4axVigaLCE9u9PY8C', 'eZxJGmPH2xz9A9jT56prh25qsSO2WIb3E9IP2JSMcXvPQEdB72aT7XCjWO2q', '2025-09-17 09:48:19', '2025-09-17 13:04:43'),
(15, 'الشيخ علي البرباري', 'ali.albarbari@modern-life.net', NULL, '$2y$12$P2Z0m579lEiVM3AH7mXwOexji0ccl6z/lM8mbKAMGYDbjDWLrEvV.', NULL, '2025-09-17 09:55:51', '2025-09-17 13:04:10'),
(16, 'محمد رزق شطا', 'eng555666777@gmail.com', NULL, '$2y$12$yvG8sgtLvcKmRDNSmFyKd.CGBNe4j1MwizeW0TjHVju.Cs2xicx2y', NULL, '2025-09-17 10:19:18', '2025-09-17 13:03:08'),
(17, 'نايف مقبل سالم الحطامي', 'warehouse@modern-life.net', NULL, '$2y$12$I1m8J1E0285RCJkhwuAYnuEPGGrcNv7IqjWqmjMqLI48G4bNfnQmG', NULL, '2025-09-17 10:29:26', '2025-09-17 13:02:36'),
(18, 'عمرو احمد عمر النوساني', 'amroalnawasany9900@gmail.com', NULL, '$2y$12$myTl9.iTf7xE9FB0NQ98P.B0mt8VZCvlkIXkdZCcRDqgvmN4O34uK', NULL, '2025-09-17 10:36:19', '2025-09-17 13:01:58'),
(19, 'فهمي جمعه فهمي', 'fahmyelahly@gmail.com', NULL, '$2y$12$H6WgpdL96wGpeuLsNdLu6OCq6xbzj7N9IU.l2SEx2lpano4jWRqCS', NULL, '2025-09-17 10:44:20', '2025-09-17 13:01:24'),
(20, 'شهاب الدين وحيد محمد', 'shehab@modern-life.net', NULL, '$2y$12$dyqb7SlrHps5CZvDF8z9n.75sTtZTV1wM.MoChvjmmuIfM/Sfle7K', NULL, '2025-09-17 10:59:06', '2025-09-17 12:34:16'),
(21, 'نازك عامر', 'sales@modern-life.net', NULL, '$2y$12$NFe8JmP9mZ9njJq37yMKu.ogFD/UPAA8KVLM0zpGNRkRw/I0F4ZbO', 'tQfooMpKvjCIurBbBwliNqQWcRiYwdMtjEjfrNNCIACASwewdMthpXDEeDk8', '2025-09-17 11:05:45', '2025-09-17 12:57:12'),
(22, 'هاني ال حاجي', 'ceo@modern-life.net', NULL, '$2y$12$j6VF0VM.cc9cAozgRZje.OsV9/7zNbfZbpICkOMnt2DKkkj6sb15W', 'dNQaDLADXmwlSC7gdEk32syHSV8REh2hP53Pzc6zO5nSez4YChthcZe4EZOG', '2025-09-17 11:16:30', '2025-09-17 13:00:31'),
(23, 'عناد ناصر أحمد', 'einad3775@gmail.com', NULL, '$2y$12$19thejb8s1C/xNRwqftDKuCtb1wbEA3V1.FPgE1qb/NhtdMkmPO9C', NULL, '2025-09-17 15:42:44', '2025-09-17 15:42:44'),
(24, 'محمد أحمد عبد العزيز', 'Mohamed.abdulaziz@modern-life.net', NULL, '$2y$12$dYgXnj.fvcPQ37IrgOrNVeByIyUoQYj72uQDkUAFvWWd496jQQIRO', NULL, '2025-09-17 15:47:48', '2025-09-17 15:47:48'),
(25, 'احمد محمد صالح', 'Ahmed@modern-life.net', NULL, '$2y$12$GdY6OibVaEP/7VfeKMIWAuS.2r8YNh6vWeJnZqn8dXmiQSfOm/.ny', NULL, '2025-09-17 15:51:27', '2025-09-17 15:51:27'),
(26, 'شعبان عبدالقادر شعبان', 'shaaban@modern-life.net', NULL, '$2y$12$GlEoofrxIAP5LBT4245.I.LYrPPUXFrJJLx/f/bfPTIW0o7DYzxZu', NULL, '2025-09-17 15:58:22', '2025-09-17 15:58:22'),
(27, 'ابراهيم رضا هويدي', 'hewidybebo@gmail.com', NULL, '$2y$12$Z3znG5FyGmckr/P/V26gLOS/96OtO1f6x/HhFMuq2miw2zbVYiedm', NULL, '2025-09-17 16:05:16', '2025-09-17 16:05:16'),
(28, 'مصطفي السيد عبدالله ', 'factory@modern-life.net', NULL, '$2y$12$ss4AJ0i3c7LwyjmhpInVCOuae8GVnpiVdFXRjK8pMT9bZbaz/NMHW', NULL, '2025-09-17 16:47:44', '2025-09-17 16:47:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cities_country_id_foreign` (`country_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `client_contacts`
--
ALTER TABLE `client_contacts`
  ADD PRIMARY KEY (`contact_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`dept_id`),
  ADD KEY `idx_dept_parent` (`parent_dept_id`),
  ADD KEY `idx_dept_active` (`is_active`);

--
-- Indexes for table `department_categories`
--
ALTER TABLE `department_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `idx_employees_department` (`department_id`),
  ADD KEY `idx_employees_status` (`is_active`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `group_permissions`
--
ALTER TABLE `group_permissions`
  ADD PRIMARY KEY (`group_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legacy_client_projects`
--
ALTER TABLE `legacy_client_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `legacy_client_projects_client_id_start_date_index` (`client_id`,`start_date`),
  ADD KEY `legacy_client_projects_client_id_end_date_index` (`client_id`,`end_date`);

--
-- Indexes for table `legacy_client_project_files`
--
ALTER TABLE `legacy_client_project_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `legacy_client_project_files_legacy_project_id_foreign` (`legacy_project_id`),
  ADD KEY `legacy_client_project_files_uploaded_by_foreign` (`uploaded_by`);

--
-- Indexes for table `lessons_learned`
--
ALTER TABLE `lessons_learned`
  ADD PRIMARY KEY (`lesson_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `manufacturing_projects`
--
ALTER TABLE `manufacturing_projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `production_manager_id` (`production_manager_id`),
  ADD KEY `idx_projects_status` (`status`),
  ADD KEY `idx_projects_deadline` (`deadline_date`),
  ADD KEY `idx_projects_client` (`client_id`);

--
-- Indexes for table `measurement_units`
--
ALTER TABLE `measurement_units`
  ADD PRIMARY KEY (`unit_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `production_requests`
--
ALTER TABLE `production_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `showroom_id` (`showroom_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `current_phase` (`current_phase`),
  ADD KEY `phase_status` (`phase_status`),
  ADD KEY `current_owner_role` (`current_owner_role`),
  ADD KEY `sent_to_owner_at` (`sent_to_owner_at`),
  ADD KEY `received_by_owner_at` (`received_by_owner_at`);

--
-- Indexes for table `production_request_files`
--
ALTER TABLE `production_request_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_request_id` (`production_request_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `production_request_logs`
--
ALTER TABLE `production_request_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_tasks_log_causer_id_foreign` (`causer_id`),
  ADD KEY `production_tasks_log_task_id_happened_at_index` (`production_request_id`,`happened_at`);

--
-- Indexes for table `production_tasks`
--
ALTER TABLE `production_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tasks_project` (`project_id`),
  ADD KEY `idx_tasks_department` (`department_id`),
  ADD KEY `idx_tasks_employee` (`assigned_to_employee_id`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_owner_role` (`current_owner_role`),
  ADD KEY `idx_tasks_owner_user` (`current_owner_user_id`);

--
-- Indexes for table `production_tasks_comments`
--
ALTER TABLE `production_tasks_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_tasks_comments_task_id_foreign` (`task_id`),
  ADD KEY `production_tasks_comments_user_id_foreign` (`user_id`);

--
-- Indexes for table `production_tasks_log`
--
ALTER TABLE `production_tasks_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_tasks_log_causer_id_foreign` (`causer_id`),
  ADD KEY `production_tasks_log_task_id_happened_at_index` (`task_id`,`happened_at`);

--
-- Indexes for table `production_tasks_material_requests`
--
ALTER TABLE `production_tasks_material_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_tasks_material_requests_task_id_status_index` (`task_id`,`status`),
  ADD KEY `production_tasks_material_requests_requested_at_index` (`requested_at`);

--
-- Indexes for table `production_tasks_time_entries`
--
ALTER TABLE `production_tasks_time_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `production_tasks_time_entries_started_by_foreign` (`started_by`),
  ADD KEY `production_tasks_time_entries_task_id_started_at_index` (`task_id`,`started_at`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `production_request_id` (`production_request_id`),
  ADD KEY `projects_client_id_foreign` (`client_id`),
  ADD KEY `projects_created_by_foreign` (`created_by`);

--
-- Indexes for table `project_delays`
--
ALTER TABLE `project_delays`
  ADD PRIMARY KEY (`delay_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `phase_id` (`phase_id`),
  ADD KEY `recorded_by` (`recorded_by`);

--
-- Indexes for table `project_files`
--
ALTER TABLE `project_files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_files_project` (`project_id`),
  ADD KEY `idx_files_phase` (`department_id`),
  ADD KEY `idx_files_type` (`file_type`);

--
-- Indexes for table `project_images`
--
ALTER TABLE `project_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `taken_by` (`taken_by`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_images_project` (`project_id`),
  ADD KEY `idx_images_phase` (`phase_id`),
  ADD KEY `idx_images_type` (`image_type`),
  ADD KEY `idx_images_date` (`taken_date`);

--
-- Indexes for table `quality_checks`
--
ALTER TABLE `quality_checks`
  ADD PRIMARY KEY (`check_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `phase_id` (`phase_id`),
  ADD KEY `inspector_id` (`inspector_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `showrooms`
--
ALTER TABLE `showrooms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `showrooms_city_id_foreign` (`city_id`),
  ADD KEY `showrooms_manager_id_foreign` (`manager_id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_settings_key` (`setting_key`),
  ADD KEY `idx_settings_group` (`setting_group`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cities`
--
ALTER TABLE `cities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `client_contacts`
--
ALTER TABLE `client_contacts`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `dept_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `department_categories`
--
ALTER TABLE `department_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=370;

--
-- AUTO_INCREMENT for table `legacy_client_projects`
--
ALTER TABLE `legacy_client_projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `legacy_client_project_files`
--
ALTER TABLE `legacy_client_project_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `lessons_learned`
--
ALTER TABLE `lessons_learned`
  MODIFY `lesson_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `manufacturing_projects`
--
ALTER TABLE `manufacturing_projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement_units`
--
ALTER TABLE `measurement_units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `production_requests`
--
ALTER TABLE `production_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `production_request_files`
--
ALTER TABLE `production_request_files`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `production_request_logs`
--
ALTER TABLE `production_request_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

--
-- AUTO_INCREMENT for table `production_tasks`
--
ALTER TABLE `production_tasks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `production_tasks_comments`
--
ALTER TABLE `production_tasks_comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `production_tasks_log`
--
ALTER TABLE `production_tasks_log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=390;

--
-- AUTO_INCREMENT for table `production_tasks_material_requests`
--
ALTER TABLE `production_tasks_material_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `production_tasks_time_entries`
--
ALTER TABLE `production_tasks_time_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `project_delays`
--
ALTER TABLE `project_delays`
  MODIFY `delay_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_files`
--
ALTER TABLE `project_files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `project_images`
--
ALTER TABLE `project_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quality_checks`
--
ALTER TABLE `quality_checks`
  MODIFY `check_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `showrooms`
--
ALTER TABLE `showrooms`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

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
-- Constraints for table `production_request_files`
--
ALTER TABLE `production_request_files`
  ADD CONSTRAINT `production_f_rq_id_foreign` FOREIGN KEY (`production_request_id`) REFERENCES `production_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_tasks`
--
ALTER TABLE `production_tasks`
  ADD CONSTRAINT `fk_tasks_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`dept_id`),
  ADD CONSTRAINT `fk_tasks_employee` FOREIGN KEY (`assigned_to_employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_tasks_assigned_to_employee_id_foreign` FOREIGN KEY (`assigned_to_employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_tasks_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`dept_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_tasks_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_tasks_log`
--
ALTER TABLE `production_tasks_log`
  ADD CONSTRAINT `production_tasks_foreign` FOREIGN KEY (`task_id`) REFERENCES `production_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_tasks_material_requests`
--
ALTER TABLE `production_tasks_material_requests`
  ADD CONSTRAINT `production_tasks_material_requests_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `production_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_tasks_time_entries`
--
ALTER TABLE `production_tasks_time_entries`
  ADD CONSTRAINT `production_tasks_time_foreign` FOREIGN KEY (`task_id`) REFERENCES `production_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projects_production_request_id_foreign` FOREIGN KEY (`production_request_id`) REFERENCES `production_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `project_files`
--
ALTER TABLE `project_files`
  ADD CONSTRAINT `projects_files_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;

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
