-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 05, 2025 at 05:59 AM
-- Server version: 8.3.0
-- PHP Version: 8.3.14

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
  `action` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `entity_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `entity_id` int DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`, `created_at`, `updated_at`) VALUES
('modern_life_cache_spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:248:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"view_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"manage-roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"create_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:10:\"edit_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:12:\"delete_roles\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"view_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:18:\"create_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:16:\"edit_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:18:\"delete_permissions\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:10:\"view_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:12:\"create_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:10:\"edit_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:12:\"delete_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"manage_users\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"view_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:16:\"create_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"edit_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:16:\"delete_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"manage_employees\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:13:\"view_any_role\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:18:\"view_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:20:\"create_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:18:\"edit_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:20:\"delete_city_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:20:\"view_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:3;i:2;i:4;i:3;i:7;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:22:\"create_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:20:\"edit_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:3;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:22:\"delete_client_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:21:\"view_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:23:\"create_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:21:\"edit_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:23:\"delete_country_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:35:\"view_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:37:\"create_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:34;a:4:{s:1:\"a\";i:35;s:1:\"b\";s:35:\"edit_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:35;a:4:{s:1:\"a\";i:36;s:1:\"b\";s:37:\"delete_department_categories_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:36;a:4:{s:1:\"a\";i:37;s:1:\"b\";s:24:\"view_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:37;a:4:{s:1:\"a\";i:38;s:1:\"b\";s:26:\"create_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:6;}}i:38;a:4:{s:1:\"a\";i:39;s:1:\"b\";s:24:\"edit_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:39;a:4:{s:1:\"a\";i:40;s:1:\"b\";s:26:\"delete_department_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:40;a:4:{s:1:\"a\";i:41;s:1:\"b\";s:22:\"view_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:41;a:4:{s:1:\"a\";i:42;s:1:\"b\";s:24:\"create_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:42;a:4:{s:1:\"a\";i:43;s:1:\"b\";s:22:\"edit_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:43;a:4:{s:1:\"a\";i:44;s:1:\"b\";s:24:\"delete_employee_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:44;a:4:{s:1:\"a\";i:45;s:1:\"b\";s:24:\"view_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:45;a:4:{s:1:\"a\";i:46;s:1:\"b\";s:26:\"create_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:46;a:4:{s:1:\"a\";i:47;s:1:\"b\";s:24:\"edit_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:47;a:4:{s:1:\"a\";i:48;s:1:\"b\";s:26:\"delete_permission_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:48;a:4:{s:1:\"a\";i:49;s:1:\"b\";s:32:\"view_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:5:{i:0;i:1;i:1;i:3;i:2;i:4;i:3;i:6;i:4;i:7;}}i:49;a:4:{s:1:\"a\";i:50;s:1:\"b\";s:34:\"create_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:6;}}i:50;a:4:{s:1:\"a\";i:51;s:1:\"b\";s:32:\"edit_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:6;i:5;i:7;}}i:51;a:4:{s:1:\"a\";i:52;s:1:\"b\";s:34:\"delete_production_request_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:52;a:4:{s:1:\"a\";i:53;s:1:\"b\";s:21:\"view_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:3;i:2;i:4;i:3;i:7;}}i:53;a:4:{s:1:\"a\";i:54;s:1:\"b\";s:23:\"create_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:6;i:3;i:7;}}i:54;a:4:{s:1:\"a\";i:55;s:1:\"b\";s:21:\"edit_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:7;}}i:55;a:4:{s:1:\"a\";i:56;s:1:\"b\";s:23:\"delete_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:56;a:4:{s:1:\"a\";i:57;s:1:\"b\";s:18:\"view_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:57;a:4:{s:1:\"a\";i:58;s:1:\"b\";s:20:\"create_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:58;a:4:{s:1:\"a\";i:59;s:1:\"b\";s:18:\"edit_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:59;a:4:{s:1:\"a\";i:60;s:1:\"b\";s:20:\"delete_role_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:60;a:4:{s:1:\"a\";i:61;s:1:\"b\";s:22:\"view_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:61;a:4:{s:1:\"a\";i:62;s:1:\"b\";s:24:\"create_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:62;a:4:{s:1:\"a\";i:63;s:1:\"b\";s:22:\"edit_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:63;a:4:{s:1:\"a\";i:64;s:1:\"b\";s:24:\"delete_showroom_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:64;a:4:{s:1:\"a\";i:65;s:1:\"b\";s:27:\"access_manage_project_tasks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:6:{i:0;i:1;i:1;i:2;i:2;i:4;i:3;i:5;i:4;i:6;i:5;i:7;}}i:65;a:4:{s:1:\"a\";i:66;s:1:\"b\";s:19:\"access_view_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:66;a:4:{s:1:\"a\";i:67;s:1:\"b\";s:32:\"access_review_production_request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:67;a:4:{s:1:\"a\";i:68;s:1:\"b\";s:31:\"access_view_production_timeline\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:7:{i:0;i:1;i:1;i:2;i:2;i:3;i:3;i:4;i:4;i:5;i:5;i:6;i:6;i:7;}}i:68;a:4:{s:1:\"a\";i:69;s:1:\"b\";s:18:\"view_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:69;a:4:{s:1:\"a\";i:70;s:1:\"b\";s:20:\"create_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:1;i:1;i:2;i:2;i:7;}}i:70;a:4:{s:1:\"a\";i:71;s:1:\"b\";s:18:\"edit_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:71;a:4:{s:1:\"a\";i:72;s:1:\"b\";s:20:\"delete_task_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:2;}}i:72;a:4:{s:1:\"a\";i:73;s:1:\"b\";s:12:\"manage-tasks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:73;a:4:{s:1:\"a\";i:74;s:1:\"b\";s:35:\"view_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:7;}}i:74;a:4:{s:1:\"a\";i:75;s:1:\"b\";s:37:\"create_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:75;a:4:{s:1:\"a\";i:76;s:1:\"b\";s:35:\"edit_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:76;a:3:{s:1:\"a\";i:77;s:1:\"b\";s:37:\"delete_legacy_client_project_resource\";s:1:\"c\";s:3:\"web\";}i:77;a:3:{s:1:\"a\";i:78;s:1:\"b\";s:9:\"view_role\";s:1:\"c\";s:3:\"web\";}i:78;a:3:{s:1:\"a\";i:79;s:1:\"b\";s:11:\"create_role\";s:1:\"c\";s:3:\"web\";}i:79;a:3:{s:1:\"a\";i:80;s:1:\"b\";s:11:\"update_role\";s:1:\"c\";s:3:\"web\";}i:80;a:3:{s:1:\"a\";i:81;s:1:\"b\";s:12:\"restore_role\";s:1:\"c\";s:3:\"web\";}i:81;a:3:{s:1:\"a\";i:82;s:1:\"b\";s:16:\"restore_any_role\";s:1:\"c\";s:3:\"web\";}i:82;a:3:{s:1:\"a\";i:83;s:1:\"b\";s:14:\"replicate_role\";s:1:\"c\";s:3:\"web\";}i:83;a:3:{s:1:\"a\";i:84;s:1:\"b\";s:12:\"reorder_role\";s:1:\"c\";s:3:\"web\";}i:84;a:3:{s:1:\"a\";i:85;s:1:\"b\";s:11:\"delete_role\";s:1:\"c\";s:3:\"web\";}i:85;a:3:{s:1:\"a\";i:86;s:1:\"b\";s:15:\"delete_any_role\";s:1:\"c\";s:3:\"web\";}i:86;a:3:{s:1:\"a\";i:87;s:1:\"b\";s:17:\"force_delete_role\";s:1:\"c\";s:3:\"web\";}i:87;a:3:{s:1:\"a\";i:88;s:1:\"b\";s:21:\"force_delete_any_role\";s:1:\"c\";s:3:\"web\";}i:88;a:3:{s:1:\"a\";i:89;s:1:\"b\";s:9:\"view_city\";s:1:\"c\";s:3:\"web\";}i:89;a:3:{s:1:\"a\";i:90;s:1:\"b\";s:13:\"view_any_city\";s:1:\"c\";s:3:\"web\";}i:90;a:3:{s:1:\"a\";i:91;s:1:\"b\";s:11:\"create_city\";s:1:\"c\";s:3:\"web\";}i:91;a:3:{s:1:\"a\";i:92;s:1:\"b\";s:11:\"update_city\";s:1:\"c\";s:3:\"web\";}i:92;a:3:{s:1:\"a\";i:93;s:1:\"b\";s:12:\"restore_city\";s:1:\"c\";s:3:\"web\";}i:93;a:3:{s:1:\"a\";i:94;s:1:\"b\";s:16:\"restore_any_city\";s:1:\"c\";s:3:\"web\";}i:94;a:3:{s:1:\"a\";i:95;s:1:\"b\";s:14:\"replicate_city\";s:1:\"c\";s:3:\"web\";}i:95;a:3:{s:1:\"a\";i:96;s:1:\"b\";s:12:\"reorder_city\";s:1:\"c\";s:3:\"web\";}i:96;a:3:{s:1:\"a\";i:97;s:1:\"b\";s:11:\"delete_city\";s:1:\"c\";s:3:\"web\";}i:97;a:3:{s:1:\"a\";i:98;s:1:\"b\";s:15:\"delete_any_city\";s:1:\"c\";s:3:\"web\";}i:98;a:3:{s:1:\"a\";i:99;s:1:\"b\";s:17:\"force_delete_city\";s:1:\"c\";s:3:\"web\";}i:99;a:3:{s:1:\"a\";i:100;s:1:\"b\";s:21:\"force_delete_any_city\";s:1:\"c\";s:3:\"web\";}i:100;a:4:{s:1:\"a\";i:101;s:1:\"b\";s:11:\"view_client\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:101;a:4:{s:1:\"a\";i:102;s:1:\"b\";s:15:\"view_any_client\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:102;a:4:{s:1:\"a\";i:103;s:1:\"b\";s:13:\"create_client\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:103;a:4:{s:1:\"a\";i:104;s:1:\"b\";s:13:\"update_client\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:104;a:3:{s:1:\"a\";i:105;s:1:\"b\";s:14:\"restore_client\";s:1:\"c\";s:3:\"web\";}i:105;a:3:{s:1:\"a\";i:106;s:1:\"b\";s:18:\"restore_any_client\";s:1:\"c\";s:3:\"web\";}i:106;a:4:{s:1:\"a\";i:107;s:1:\"b\";s:16:\"replicate_client\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:107;a:3:{s:1:\"a\";i:108;s:1:\"b\";s:14:\"reorder_client\";s:1:\"c\";s:3:\"web\";}i:108;a:3:{s:1:\"a\";i:109;s:1:\"b\";s:13:\"delete_client\";s:1:\"c\";s:3:\"web\";}i:109;a:3:{s:1:\"a\";i:110;s:1:\"b\";s:17:\"delete_any_client\";s:1:\"c\";s:3:\"web\";}i:110;a:3:{s:1:\"a\";i:111;s:1:\"b\";s:19:\"force_delete_client\";s:1:\"c\";s:3:\"web\";}i:111;a:3:{s:1:\"a\";i:112;s:1:\"b\";s:23:\"force_delete_any_client\";s:1:\"c\";s:3:\"web\";}i:112;a:3:{s:1:\"a\";i:113;s:1:\"b\";s:12:\"view_country\";s:1:\"c\";s:3:\"web\";}i:113;a:3:{s:1:\"a\";i:114;s:1:\"b\";s:16:\"view_any_country\";s:1:\"c\";s:3:\"web\";}i:114;a:3:{s:1:\"a\";i:115;s:1:\"b\";s:14:\"create_country\";s:1:\"c\";s:3:\"web\";}i:115;a:3:{s:1:\"a\";i:116;s:1:\"b\";s:14:\"update_country\";s:1:\"c\";s:3:\"web\";}i:116;a:3:{s:1:\"a\";i:117;s:1:\"b\";s:15:\"restore_country\";s:1:\"c\";s:3:\"web\";}i:117;a:3:{s:1:\"a\";i:118;s:1:\"b\";s:19:\"restore_any_country\";s:1:\"c\";s:3:\"web\";}i:118;a:3:{s:1:\"a\";i:119;s:1:\"b\";s:17:\"replicate_country\";s:1:\"c\";s:3:\"web\";}i:119;a:3:{s:1:\"a\";i:120;s:1:\"b\";s:15:\"reorder_country\";s:1:\"c\";s:3:\"web\";}i:120;a:3:{s:1:\"a\";i:121;s:1:\"b\";s:14:\"delete_country\";s:1:\"c\";s:3:\"web\";}i:121;a:3:{s:1:\"a\";i:122;s:1:\"b\";s:18:\"delete_any_country\";s:1:\"c\";s:3:\"web\";}i:122;a:3:{s:1:\"a\";i:123;s:1:\"b\";s:20:\"force_delete_country\";s:1:\"c\";s:3:\"web\";}i:123;a:3:{s:1:\"a\";i:124;s:1:\"b\";s:24:\"force_delete_any_country\";s:1:\"c\";s:3:\"web\";}i:124;a:3:{s:1:\"a\";i:125;s:1:\"b\";s:15:\"view_department\";s:1:\"c\";s:3:\"web\";}i:125;a:3:{s:1:\"a\";i:126;s:1:\"b\";s:19:\"view_any_department\";s:1:\"c\";s:3:\"web\";}i:126;a:3:{s:1:\"a\";i:127;s:1:\"b\";s:17:\"create_department\";s:1:\"c\";s:3:\"web\";}i:127;a:3:{s:1:\"a\";i:128;s:1:\"b\";s:17:\"update_department\";s:1:\"c\";s:3:\"web\";}i:128;a:3:{s:1:\"a\";i:129;s:1:\"b\";s:18:\"restore_department\";s:1:\"c\";s:3:\"web\";}i:129;a:3:{s:1:\"a\";i:130;s:1:\"b\";s:22:\"restore_any_department\";s:1:\"c\";s:3:\"web\";}i:130;a:3:{s:1:\"a\";i:131;s:1:\"b\";s:20:\"replicate_department\";s:1:\"c\";s:3:\"web\";}i:131;a:3:{s:1:\"a\";i:132;s:1:\"b\";s:18:\"reorder_department\";s:1:\"c\";s:3:\"web\";}i:132;a:3:{s:1:\"a\";i:133;s:1:\"b\";s:17:\"delete_department\";s:1:\"c\";s:3:\"web\";}i:133;a:3:{s:1:\"a\";i:134;s:1:\"b\";s:21:\"delete_any_department\";s:1:\"c\";s:3:\"web\";}i:134;a:3:{s:1:\"a\";i:135;s:1:\"b\";s:23:\"force_delete_department\";s:1:\"c\";s:3:\"web\";}i:135;a:3:{s:1:\"a\";i:136;s:1:\"b\";s:27:\"force_delete_any_department\";s:1:\"c\";s:3:\"web\";}i:136;a:3:{s:1:\"a\";i:137;s:1:\"b\";s:27:\"view_department::categories\";s:1:\"c\";s:3:\"web\";}i:137;a:3:{s:1:\"a\";i:138;s:1:\"b\";s:31:\"view_any_department::categories\";s:1:\"c\";s:3:\"web\";}i:138;a:3:{s:1:\"a\";i:139;s:1:\"b\";s:29:\"create_department::categories\";s:1:\"c\";s:3:\"web\";}i:139;a:3:{s:1:\"a\";i:140;s:1:\"b\";s:29:\"update_department::categories\";s:1:\"c\";s:3:\"web\";}i:140;a:3:{s:1:\"a\";i:141;s:1:\"b\";s:30:\"restore_department::categories\";s:1:\"c\";s:3:\"web\";}i:141;a:3:{s:1:\"a\";i:142;s:1:\"b\";s:34:\"restore_any_department::categories\";s:1:\"c\";s:3:\"web\";}i:142;a:3:{s:1:\"a\";i:143;s:1:\"b\";s:32:\"replicate_department::categories\";s:1:\"c\";s:3:\"web\";}i:143;a:3:{s:1:\"a\";i:144;s:1:\"b\";s:30:\"reorder_department::categories\";s:1:\"c\";s:3:\"web\";}i:144;a:3:{s:1:\"a\";i:145;s:1:\"b\";s:29:\"delete_department::categories\";s:1:\"c\";s:3:\"web\";}i:145;a:3:{s:1:\"a\";i:146;s:1:\"b\";s:33:\"delete_any_department::categories\";s:1:\"c\";s:3:\"web\";}i:146;a:3:{s:1:\"a\";i:147;s:1:\"b\";s:35:\"force_delete_department::categories\";s:1:\"c\";s:3:\"web\";}i:147;a:3:{s:1:\"a\";i:148;s:1:\"b\";s:39:\"force_delete_any_department::categories\";s:1:\"c\";s:3:\"web\";}i:148;a:3:{s:1:\"a\";i:149;s:1:\"b\";s:13:\"view_employee\";s:1:\"c\";s:3:\"web\";}i:149;a:3:{s:1:\"a\";i:150;s:1:\"b\";s:17:\"view_any_employee\";s:1:\"c\";s:3:\"web\";}i:150;a:3:{s:1:\"a\";i:151;s:1:\"b\";s:15:\"create_employee\";s:1:\"c\";s:3:\"web\";}i:151;a:3:{s:1:\"a\";i:152;s:1:\"b\";s:15:\"update_employee\";s:1:\"c\";s:3:\"web\";}i:152;a:3:{s:1:\"a\";i:153;s:1:\"b\";s:16:\"restore_employee\";s:1:\"c\";s:3:\"web\";}i:153;a:3:{s:1:\"a\";i:154;s:1:\"b\";s:20:\"restore_any_employee\";s:1:\"c\";s:3:\"web\";}i:154;a:3:{s:1:\"a\";i:155;s:1:\"b\";s:18:\"replicate_employee\";s:1:\"c\";s:3:\"web\";}i:155;a:3:{s:1:\"a\";i:156;s:1:\"b\";s:16:\"reorder_employee\";s:1:\"c\";s:3:\"web\";}i:156;a:3:{s:1:\"a\";i:157;s:1:\"b\";s:15:\"delete_employee\";s:1:\"c\";s:3:\"web\";}i:157;a:3:{s:1:\"a\";i:158;s:1:\"b\";s:19:\"delete_any_employee\";s:1:\"c\";s:3:\"web\";}i:158;a:3:{s:1:\"a\";i:159;s:1:\"b\";s:21:\"force_delete_employee\";s:1:\"c\";s:3:\"web\";}i:159;a:3:{s:1:\"a\";i:160;s:1:\"b\";s:25:\"force_delete_any_employee\";s:1:\"c\";s:3:\"web\";}i:160;a:4:{s:1:\"a\";i:161;s:1:\"b\";s:28:\"view_legacy::client::project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:7;}}i:161;a:4:{s:1:\"a\";i:162;s:1:\"b\";s:32:\"view_any_legacy::client::project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:7;}}i:162;a:4:{s:1:\"a\";i:163;s:1:\"b\";s:30:\"create_legacy::client::project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:163;a:4:{s:1:\"a\";i:164;s:1:\"b\";s:30:\"update_legacy::client::project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:164;a:3:{s:1:\"a\";i:165;s:1:\"b\";s:31:\"restore_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:165;a:3:{s:1:\"a\";i:166;s:1:\"b\";s:35:\"restore_any_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:166;a:3:{s:1:\"a\";i:167;s:1:\"b\";s:33:\"replicate_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:167;a:3:{s:1:\"a\";i:168;s:1:\"b\";s:31:\"reorder_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:168;a:3:{s:1:\"a\";i:169;s:1:\"b\";s:30:\"delete_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:169;a:3:{s:1:\"a\";i:170;s:1:\"b\";s:34:\"delete_any_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:170;a:3:{s:1:\"a\";i:171;s:1:\"b\";s:36:\"force_delete_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:171;a:3:{s:1:\"a\";i:172;s:1:\"b\";s:40:\"force_delete_any_legacy::client::project\";s:1:\"c\";s:3:\"web\";}i:172;a:3:{s:1:\"a\";i:173;s:1:\"b\";s:15:\"view_permission\";s:1:\"c\";s:3:\"web\";}i:173;a:3:{s:1:\"a\";i:174;s:1:\"b\";s:19:\"view_any_permission\";s:1:\"c\";s:3:\"web\";}i:174;a:3:{s:1:\"a\";i:175;s:1:\"b\";s:17:\"create_permission\";s:1:\"c\";s:3:\"web\";}i:175;a:3:{s:1:\"a\";i:176;s:1:\"b\";s:17:\"update_permission\";s:1:\"c\";s:3:\"web\";}i:176;a:3:{s:1:\"a\";i:177;s:1:\"b\";s:18:\"restore_permission\";s:1:\"c\";s:3:\"web\";}i:177;a:3:{s:1:\"a\";i:178;s:1:\"b\";s:22:\"restore_any_permission\";s:1:\"c\";s:3:\"web\";}i:178;a:3:{s:1:\"a\";i:179;s:1:\"b\";s:20:\"replicate_permission\";s:1:\"c\";s:3:\"web\";}i:179;a:3:{s:1:\"a\";i:180;s:1:\"b\";s:18:\"reorder_permission\";s:1:\"c\";s:3:\"web\";}i:180;a:3:{s:1:\"a\";i:181;s:1:\"b\";s:17:\"delete_permission\";s:1:\"c\";s:3:\"web\";}i:181;a:3:{s:1:\"a\";i:182;s:1:\"b\";s:21:\"delete_any_permission\";s:1:\"c\";s:3:\"web\";}i:182;a:3:{s:1:\"a\";i:183;s:1:\"b\";s:23:\"force_delete_permission\";s:1:\"c\";s:3:\"web\";}i:183;a:3:{s:1:\"a\";i:184;s:1:\"b\";s:27:\"force_delete_any_permission\";s:1:\"c\";s:3:\"web\";}i:184;a:4:{s:1:\"a\";i:185;s:1:\"b\";s:24:\"view_production::request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:3;i:1;i:4;i:2;i:6;i:3;i:7;}}i:185;a:4:{s:1:\"a\";i:186;s:1:\"b\";s:28:\"view_any_production::request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:4:{i:0;i:3;i:1;i:4;i:2;i:6;i:3;i:7;}}i:186;a:4:{s:1:\"a\";i:187;s:1:\"b\";s:26:\"create_production::request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:187;a:4:{s:1:\"a\";i:188;s:1:\"b\";s:26:\"update_production::request\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:188;a:3:{s:1:\"a\";i:189;s:1:\"b\";s:27:\"restore_production::request\";s:1:\"c\";s:3:\"web\";}i:189;a:3:{s:1:\"a\";i:190;s:1:\"b\";s:31:\"restore_any_production::request\";s:1:\"c\";s:3:\"web\";}i:190;a:3:{s:1:\"a\";i:191;s:1:\"b\";s:29:\"replicate_production::request\";s:1:\"c\";s:3:\"web\";}i:191;a:3:{s:1:\"a\";i:192;s:1:\"b\";s:27:\"reorder_production::request\";s:1:\"c\";s:3:\"web\";}i:192;a:3:{s:1:\"a\";i:193;s:1:\"b\";s:26:\"delete_production::request\";s:1:\"c\";s:3:\"web\";}i:193;a:3:{s:1:\"a\";i:194;s:1:\"b\";s:30:\"delete_any_production::request\";s:1:\"c\";s:3:\"web\";}i:194;a:3:{s:1:\"a\";i:195;s:1:\"b\";s:32:\"force_delete_production::request\";s:1:\"c\";s:3:\"web\";}i:195;a:3:{s:1:\"a\";i:196;s:1:\"b\";s:36:\"force_delete_any_production::request\";s:1:\"c\";s:3:\"web\";}i:196;a:4:{s:1:\"a\";i:197;s:1:\"b\";s:12:\"view_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:4;i:2;i:7;}}i:197;a:4:{s:1:\"a\";i:198;s:1:\"b\";s:16:\"view_any_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:198;a:4:{s:1:\"a\";i:199;s:1:\"b\";s:14:\"create_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:199;a:4:{s:1:\"a\";i:200;s:1:\"b\";s:14:\"update_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:200;a:4:{s:1:\"a\";i:201;s:1:\"b\";s:15:\"restore_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:201;a:3:{s:1:\"a\";i:202;s:1:\"b\";s:19:\"restore_any_project\";s:1:\"c\";s:3:\"web\";}i:202;a:4:{s:1:\"a\";i:203;s:1:\"b\";s:17:\"replicate_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:203;a:4:{s:1:\"a\";i:204;s:1:\"b\";s:15:\"reorder_project\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:204;a:3:{s:1:\"a\";i:205;s:1:\"b\";s:14:\"delete_project\";s:1:\"c\";s:3:\"web\";}i:205;a:3:{s:1:\"a\";i:206;s:1:\"b\";s:18:\"delete_any_project\";s:1:\"c\";s:3:\"web\";}i:206;a:3:{s:1:\"a\";i:207;s:1:\"b\";s:20:\"force_delete_project\";s:1:\"c\";s:3:\"web\";}i:207;a:3:{s:1:\"a\";i:208;s:1:\"b\";s:24:\"force_delete_any_project\";s:1:\"c\";s:3:\"web\";}i:208;a:3:{s:1:\"a\";i:209;s:1:\"b\";s:13:\"view_showroom\";s:1:\"c\";s:3:\"web\";}i:209;a:3:{s:1:\"a\";i:210;s:1:\"b\";s:17:\"view_any_showroom\";s:1:\"c\";s:3:\"web\";}i:210;a:3:{s:1:\"a\";i:211;s:1:\"b\";s:15:\"create_showroom\";s:1:\"c\";s:3:\"web\";}i:211;a:3:{s:1:\"a\";i:212;s:1:\"b\";s:15:\"update_showroom\";s:1:\"c\";s:3:\"web\";}i:212;a:3:{s:1:\"a\";i:213;s:1:\"b\";s:16:\"restore_showroom\";s:1:\"c\";s:3:\"web\";}i:213;a:3:{s:1:\"a\";i:214;s:1:\"b\";s:20:\"restore_any_showroom\";s:1:\"c\";s:3:\"web\";}i:214;a:3:{s:1:\"a\";i:215;s:1:\"b\";s:18:\"replicate_showroom\";s:1:\"c\";s:3:\"web\";}i:215;a:3:{s:1:\"a\";i:216;s:1:\"b\";s:16:\"reorder_showroom\";s:1:\"c\";s:3:\"web\";}i:216;a:3:{s:1:\"a\";i:217;s:1:\"b\";s:15:\"delete_showroom\";s:1:\"c\";s:3:\"web\";}i:217;a:3:{s:1:\"a\";i:218;s:1:\"b\";s:19:\"delete_any_showroom\";s:1:\"c\";s:3:\"web\";}i:218;a:3:{s:1:\"a\";i:219;s:1:\"b\";s:21:\"force_delete_showroom\";s:1:\"c\";s:3:\"web\";}i:219;a:3:{s:1:\"a\";i:220;s:1:\"b\";s:25:\"force_delete_any_showroom\";s:1:\"c\";s:3:\"web\";}i:220;a:4:{s:1:\"a\";i:221;s:1:\"b\";s:9:\"view_task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:221;a:4:{s:1:\"a\";i:222;s:1:\"b\";s:13:\"view_any_task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:222;a:4:{s:1:\"a\";i:223;s:1:\"b\";s:11:\"create_task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:223;a:4:{s:1:\"a\";i:224;s:1:\"b\";s:11:\"update_task\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:224;a:3:{s:1:\"a\";i:225;s:1:\"b\";s:12:\"restore_task\";s:1:\"c\";s:3:\"web\";}i:225;a:3:{s:1:\"a\";i:226;s:1:\"b\";s:16:\"restore_any_task\";s:1:\"c\";s:3:\"web\";}i:226;a:3:{s:1:\"a\";i:227;s:1:\"b\";s:14:\"replicate_task\";s:1:\"c\";s:3:\"web\";}i:227;a:3:{s:1:\"a\";i:228;s:1:\"b\";s:12:\"reorder_task\";s:1:\"c\";s:3:\"web\";}i:228;a:3:{s:1:\"a\";i:229;s:1:\"b\";s:11:\"delete_task\";s:1:\"c\";s:3:\"web\";}i:229;a:3:{s:1:\"a\";i:230;s:1:\"b\";s:15:\"delete_any_task\";s:1:\"c\";s:3:\"web\";}i:230;a:3:{s:1:\"a\";i:231;s:1:\"b\";s:17:\"force_delete_task\";s:1:\"c\";s:3:\"web\";}i:231;a:3:{s:1:\"a\";i:232;s:1:\"b\";s:21:\"force_delete_any_task\";s:1:\"c\";s:3:\"web\";}i:232;a:4:{s:1:\"a\";i:233;s:1:\"b\";s:18:\"page_AssignedTasks\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:4;i:1;i:7;}}i:233;a:4:{s:1:\"a\";i:234;s:1:\"b\";s:28:\"page_AutoPermissionGenerator\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:234;a:4:{s:1:\"a\";i:235;s:1:\"b\";s:29:\"page_FactoryManagerTaskReview\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:3;i:1;i:7;}}i:235;a:3:{s:1:\"a\";i:236;s:1:\"b\";s:20:\"page_MyNotifications\";s:1:\"c\";s:3:\"web\";}i:236;a:4:{s:1:\"a\";i:237;s:1:\"b\";s:22:\"page_MaterialsRequests\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:5;i:2;i:7;}}i:237;a:4:{s:1:\"a\";i:238;s:1:\"b\";s:26:\"page_MaterialsRequestsDone\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:3:{i:0;i:3;i:1;i:5;i:2;i:7;}}i:238;a:4:{s:1:\"a\";i:239;s:1:\"b\";s:24:\"page_ViewMaterialRequest\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:5;}}i:239;a:3:{s:1:\"a\";i:240;s:1:\"b\";s:19:\"page_SystemSettings\";s:1:\"c\";s:3:\"web\";}i:240;a:4:{s:1:\"a\";i:241;s:1:\"b\";s:16:\"widget_MainStats\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:241;a:4:{s:1:\"a\";i:242;s:1:\"b\";s:26:\"widget_ClientsMonthlyChart\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:242;a:4:{s:1:\"a\";i:243;s:1:\"b\";s:28:\"widget_DepartmentWorkloadBar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:243;a:4:{s:1:\"a\";i:244;s:1:\"b\";s:33:\"widget_EmployeesByDepartmentDonut\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:244;a:4:{s:1:\"a\";i:245;s:1:\"b\";s:27:\"widget_ProjectsPerClientBar\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:245;a:4:{s:1:\"a\";i:246;s:1:\"b\";s:28:\"widget_RequestsPerMonthChart\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:246;a:4:{s:1:\"a\";i:247;s:1:\"b\";s:30:\"widget_TasksCompletionDoughnut\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}i:247;a:4:{s:1:\"a\";i:248;s:1:\"b\";s:28:\"widget_RequestsByStatusChart\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:7;}}}s:5:\"roles\";a:7:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:5:\"admin\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:5:\"sales\";s:1:\"c\";s:3:\"web\";}i:3;a:3:{s:1:\"a\";i:4;s:1:\"b\";s:18:\"department_manager\";s:1:\"c\";s:3:\"web\";}i:4;a:3:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"factory_manager\";s:1:\"c\";s:3:\"web\";}i:5;a:3:{s:1:\"a\";i:6;s:1:\"b\";s:16:\"showroom_manager\";s:1:\"c\";s:3:\"web\";}i:6;a:3:{s:1:\"a\";i:5;s:1:\"b\";s:18:\"purchasing_manager\";s:1:\"c\";s:3:\"web\";}}}', 1759670103, '2025-10-04 13:15:03', '2025-10-04 13:15:03'),
('modern_life_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3:timer', 'i:1759587419;', 1759587419, '2025-10-04 14:15:59', '2025-10-04 14:15:59'),
('modern_life_cache_livewire-rate-limiter:a17961fa74e9275d529f489537f179c05d50c2f3', 'i:1;', 1759587419, '2025-10-04 14:15:59', '2025-10-04 14:15:59'),
('modern_life_cache_b1d5781111d84f7b3fe45a0852e59758cd7a87e5:timer', 'i:1759537355;', 1759537355, '2025-10-04 00:21:35', '2025-10-04 00:21:35'),
('modern_life_cache_b1d5781111d84f7b3fe45a0852e59758cd7a87e5', 'i:1;', 1759537355, '2025-10-04 00:21:35', '2025-10-04 00:21:35'),
('modern_life_cache_7b52009b64fd0a2a49e6d8a939753077792b0554:timer', 'i:1759587609;', 1759587609, '2025-10-04 14:19:09', '2025-10-04 14:19:09'),
('modern_life_cache_7b52009b64fd0a2a49e6d8a939753077792b0554', 'i:1;', 1759587609, '2025-10-04 14:19:09', '2025-10-04 14:19:09'),
('modern_life_cache_fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b:timer', 'i:1759578912;', 1759578912, '2025-10-04 11:54:12', '2025-10-04 11:54:12'),
('modern_life_cache_fa35e192121eabf3dabf9f5ea6abdbcbc107ac3b', 'i:1;', 1759578912, '2025-10-04 11:54:12', '2025-10-04 11:54:12');

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `owner` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
CREATE TABLE IF NOT EXISTS `cities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `country_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cities_country_id_foreign` (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
(18, 'عرعر', 1, '2025-07-09 07:32:49', '2025-07-09 07:32:49'),
(21, 'سيهـات', 1, '2025-09-10 12:14:10', '2025-09-10 12:14:23');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `client_id` int NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `client_type` enum('individual','company') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `tax_number` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `commercial_registration` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `secondary_phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `city_id` smallint DEFAULT NULL,
  `country_id` smallint DEFAULT '1',
  `is_active` tinyint(1) DEFAULT '1',
  `credit_limit` decimal(15,2) DEFAULT '0.00',
  `payment_terms` int DEFAULT '30',
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `client_name`, `client_type`, `tax_number`, `commercial_registration`, `email`, `phone`, `secondary_phone`, `address`, `city_id`, `country_id`, `is_active`, `credit_limit`, `payment_terms`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'عميل تجريبي', 'individual', NULL, '251244222', 'new@new.com', '0501234567', NULL, NULL, 6, 1, 1, 0.00, 30, NULL, '2025-07-07 08:30:18', '2025-07-09 04:55:34'),
(2, 'عميل تجريبي 2', 'company', '23423', NULL, '434@hhh.kk', '0567567567', NULL, NULL, 7, 1, 1, 0.00, 30, NULL, '2025-09-10 19:56:19', '2025-09-10 19:56:41');

-- --------------------------------------------------------

--
-- Table structure for table `client_contacts`
--

DROP TABLE IF EXISTS `client_contacts`;
CREATE TABLE IF NOT EXISTS `client_contacts` (
  `contact_id` int NOT NULL AUTO_INCREMENT,
  `client_id` int NOT NULL,
  `contact_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`),
  KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `client_contacts`
--

INSERT INTO `client_contacts` (`contact_id`, `client_id`, `contact_name`, `position`, `email`, `phone`, `is_primary`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'جهة اتصال تجريبية', 'تجربة', 'new@new.new', '0121212121', 0, NULL, '2025-07-07 08:38:53', '2025-07-07 08:38:53'),
(2, 2, 'Ahmed Elsayed', 'تجربة', 'designer_4ever@hotmail.com', '0560645034', 0, NULL, '2025-09-10 19:57:01', '2025-09-10 19:57:01');

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

DROP TABLE IF EXISTS `countries`;
CREATE TABLE IF NOT EXISTS `countries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `code` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

DROP TABLE IF EXISTS `departments`;
CREATE TABLE IF NOT EXISTS `departments` (
  `dept_id` int NOT NULL AUTO_INCREMENT,
  `factory_id` int NOT NULL DEFAULT '1',
  `dept_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `dept_code` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `parent_dept_id` int DEFAULT '0',
  `manager_id` int DEFAULT NULL,
  `dept_type` tinyint NOT NULL,
  `location` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone_extension` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `color_code` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '#3498db',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`dept_id`),
  KEY `idx_dept_parent` (`parent_dept_id`),
  KEY `idx_dept_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`dept_id`, `factory_id`, `dept_name`, `dept_code`, `parent_dept_id`, `manager_id`, `dept_type`, `location`, `phone_extension`, `email`, `is_active`, `color_code`, `created_at`, `updated_at`) VALUES
(7, 1, 'الإدارة', 'CODE-MG', NULL, NULL, 4, NULL, NULL, 'mg@mg.com', 1, '#3498db', '2025-07-06 06:25:03', '2025-07-06 06:25:03'),
(8, 1, 'المبيعات', 'CODE-SALES', NULL, NULL, 4, NULL, NULL, 'sales@sales.com', 1, '#ad0766', '2025-07-06 06:25:36', '2025-07-06 06:25:36'),
(9, 1, 'قسم الألومنيوم', 'DEPT-ALUM', 11, 9, 5, NULL, NULL, NULL, 1, '#3498db', '2025-07-14 11:39:55', '2025-10-03 12:15:23'),
(10, 1, 'قسم الزجاج', 'CODE-GLASS', 11, NULL, 5, 'الخبر', NULL, NULL, 1, '#3498db', '2025-08-04 10:34:57', '2025-10-03 12:03:06'),
(11, 1, 'التصنيع', 'CODE-MANUF', NULL, NULL, 5, 'الخبر', NULL, NULL, 1, '#3498db', '2025-08-04 10:35:36', '2025-08-04 10:35:36'),
(12, 1, 'قسم الخشب', 'CODE-WOOD', 11, NULL, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:37:36', '2025-10-03 12:03:08'),
(13, 1, 'قسم الرخام', 'CODE-MARBLE', 11, NULL, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:38:25', '2025-10-03 12:03:12'),
(14, 1, 'قسم الحديد', 'CODE-IRON', 11, NULL, 5, NULL, NULL, NULL, 1, '#3498db', '2025-08-04 10:41:59', '2025-08-04 10:41:59'),
(15, 1, 'المشتريات', 'DEPT-PCH', 7, NULL, 6, NULL, NULL, NULL, 1, '#3498db', '2025-08-20 04:41:32', '2025-10-03 12:03:10');

-- --------------------------------------------------------

--
-- Table structure for table `department_categories`
--

DROP TABLE IF EXISTS `department_categories`;
CREATE TABLE IF NOT EXISTS `department_categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `color_code` varchar(7) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT '#95a5a6',
  `icon` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`category_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
  `national_id` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `employee_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `gender` enum('male','female') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `department_id` int DEFAULT NULL,
  `position` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `salary` decimal(15,2) DEFAULT NULL,
  `employment_type` enum('full_time','part_time','contractor') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'full_time',
  `is_active` tinyint(1) DEFAULT '1',
  `emergency_contact_name` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  KEY `idx_employees_department` (`department_id`),
  KEY `idx_employees_status` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `user_id`, `national_id`, `employee_name`, `gender`, `birth_date`, `email`, `phone`, `address`, `department_id`, `position`, `hire_date`, `salary`, `employment_type`, `is_active`, `emergency_contact_name`, `emergency_contact_phone`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 9, '2264078805', 'Showroom', 'male', '2025-07-10', 'designer_4ever@hotmail.com', '0560645034', 'Dammam', 7, 'Developer', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-07-06 06:51:31', '2025-10-03 11:47:52', NULL),
(7, 10, '123466678', 'Sales', 'male', '1992-06-09', NULL, '05012345678', NULL, 8, 'Sales', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-08-20 04:39:55', '2025-10-03 11:40:46', NULL),
(9, 12, '12365644565', 'Dept Manager', 'male', NULL, NULL, '0678678678', NULL, 9, 'Dep', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-09-10 19:21:39', '2025-10-04 13:12:35', NULL),
(10, 13, '1231431244', 'Factory Manager', NULL, NULL, NULL, '050222222', NULL, 11, 'Factory', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-10-03 10:32:34', '2025-10-04 13:14:57', NULL),
(11, 14, '4534534', 'Purchasing', NULL, NULL, NULL, '0525353455', NULL, 15, 'Purchasing', NULL, NULL, 'full_time', 1, NULL, NULL, NULL, '2025-10-03 11:48:55', '2025-10-03 11:48:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM AUTO_INCREMENT=114 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"6406dc42-2e84-4f3b-a02c-387561bf5d6e\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";N;s:6:\\\"status\\\";N;s:5:\\\"owner\\\";N;}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"4e2a7773-73af-4967-9fe1-7ffb3d385e31\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759493880,\"delay\":null}', 0, NULL, 1759493880, 1759493880),
(2, 'default', '{\"uuid\":\"09bf7cb3-013a-4812-91c2-4523729fad7a\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";N;s:6:\\\"status\\\";N;s:5:\\\"owner\\\";N;}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"4e2a7773-73af-4967-9fe1-7ffb3d385e31\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759493880,\"delay\":null}', 0, NULL, 1759493880, 1759493880),
(3, 'default', '{\"uuid\":\"d5654cf2-d790-4f1a-80af-d107d9fec774\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";N;s:6:\\\"status\\\";N;s:5:\\\"owner\\\";N;}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"2e18a4a3-bea1-46d8-a0da-c103547c7eee\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759493880,\"delay\":null}', 0, NULL, 1759493880, 1759493880),
(4, 'default', '{\"uuid\":\"5b6ec099-1308-4d3c-8b98-f02567b79fb2\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";N;s:6:\\\"status\\\";N;s:5:\\\"owner\\\";N;}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"2e18a4a3-bea1-46d8-a0da-c103547c7eee\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759493880,\"delay\":null}', 0, NULL, 1759493880, 1759493880),
(5, 'default', '{\"uuid\":\"c245c606-97c0-4c6b-b8e0-80f33c1413ee\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42898.608653999996;}s:2:\\\"id\\\";s:36:\\\"59dd9728-7c53-4cd5-a7ae-ef559d82d8b7\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536776,\"delay\":null}', 0, NULL, 1759536776, 1759536776),
(6, 'default', '{\"uuid\":\"9859b543-3162-4225-8f6b-513ad0cb7cc9\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42898.608653999996;}s:2:\\\"id\\\";s:36:\\\"59dd9728-7c53-4cd5-a7ae-ef559d82d8b7\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536776,\"delay\":null}', 0, NULL, 1759536776, 1759536776),
(7, 'default', '{\"uuid\":\"139a55aa-c5ab-45ca-9220-a549137e8c65\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42898.608653999996;}s:2:\\\"id\\\";s:36:\\\"53a8ae35-cc0c-481d-8518-51302fbe8092\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536776,\"delay\":null}', 0, NULL, 1759536776, 1759536776),
(8, 'default', '{\"uuid\":\"320ccb3a-7997-4715-8cd2-033b7269b683\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42898.608653999996;}s:2:\\\"id\\\";s:36:\\\"53a8ae35-cc0c-481d-8518-51302fbe8092\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536776,\"delay\":null}', 0, NULL, 1759536776, 1759536776),
(9, 'default', '{\"uuid\":\"8b9aff6a-1ac0-4d94-9099-0da46bf75155\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42921.620301;}s:2:\\\"id\\\";s:36:\\\"a056c972-8589-4fa6-bdff-5f3918819b39\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536799,\"delay\":null}', 0, NULL, 1759536799, 1759536799),
(10, 'default', '{\"uuid\":\"7764d9a4-4dec-44bb-9f6a-69c34286d896\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42921.620301;}s:2:\\\"id\\\";s:36:\\\"a056c972-8589-4fa6-bdff-5f3918819b39\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536799,\"delay\":null}', 0, NULL, 1759536799, 1759536799),
(11, 'default', '{\"uuid\":\"ee65352b-1270-4341-a8c8-44e7375bd352\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42921.620301;}s:2:\\\"id\\\";s:36:\\\"2ae7660a-2901-4bcf-93d5-981dda2cf252\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536799,\"delay\":null}', 0, NULL, 1759536799, 1759536799),
(12, 'default', '{\"uuid\":\"1c1d0848-4abf-4a78-a04d-863e2896f5f0\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:42921.620301;}s:2:\\\"id\\\";s:36:\\\"2ae7660a-2901-4bcf-93d5-981dda2cf252\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759536799,\"delay\":null}', 0, NULL, 1759536799, 1759536799),
(13, 'default', '{\"uuid\":\"b02602f6-1a4b-4a84-831c-e06242ec153d\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"a2195106-bd59-4bb0-bc65-c038d6b63bf0\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537024,\"delay\":null}', 0, NULL, 1759537024, 1759537024),
(14, 'default', '{\"uuid\":\"13f520e7-82ab-438b-8b61-6663dddbe62c\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"a2195106-bd59-4bb0-bc65-c038d6b63bf0\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537024,\"delay\":null}', 0, NULL, 1759537024, 1759537024),
(15, 'default', '{\"uuid\":\"72cffbd8-ec96-47ba-9601-35ad0228aecd\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"6464c07c-9168-46d8-ac16-7b8c008d4b03\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537024,\"delay\":null}', 0, NULL, 1759537024, 1759537024),
(16, 'default', '{\"uuid\":\"dcb5e62b-1d33-4694-802f-350efa3696fc\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"6464c07c-9168-46d8-ac16-7b8c008d4b03\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537024,\"delay\":null}', 0, NULL, 1759537024, 1759537024),
(17, 'default', '{\"uuid\":\"302d469b-7588-49ca-9747-dbd0b5da2aa0\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"rejected\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:12:\\\"under_review\\\";s:9:\\\"to_status\\\";s:8:\\\"rejected\\\";s:6:\\\"reason\\\";s:8:\\\"ناقص\\\";}s:2:\\\"id\\\";s:36:\\\"91af59da-e09d-44cf-a846-5d32c86e6346\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537073,\"delay\":null}', 0, NULL, 1759537073, 1759537073),
(18, 'default', '{\"uuid\":\"379c1671-3341-41bd-9eb8-a0fba5e524db\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"rejected\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:12:\\\"under_review\\\";s:9:\\\"to_status\\\";s:8:\\\"rejected\\\";s:6:\\\"reason\\\";s:8:\\\"ناقص\\\";}s:2:\\\"id\\\";s:36:\\\"91af59da-e09d-44cf-a846-5d32c86e6346\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537073,\"delay\":null}', 0, NULL, 1759537073, 1759537073),
(19, 'default', '{\"uuid\":\"3290ae7f-136f-4506-8b1b-0f15d3d650f0\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"rejected\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:12:\\\"under_review\\\";s:9:\\\"to_status\\\";s:8:\\\"rejected\\\";s:6:\\\"reason\\\";s:8:\\\"ناقص\\\";}s:2:\\\"id\\\";s:36:\\\"2db442da-5a85-4ef7-93ef-df93019f786f\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537073,\"delay\":null}', 0, NULL, 1759537073, 1759537073),
(20, 'default', '{\"uuid\":\"2db3bd3b-f305-4f16-8af4-bdc026e50e6e\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"rejected\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:12:\\\"under_review\\\";s:9:\\\"to_status\\\";s:8:\\\"rejected\\\";s:6:\\\"reason\\\";s:8:\\\"ناقص\\\";}s:2:\\\"id\\\";s:36:\\\"2db442da-5a85-4ef7-93ef-df93019f786f\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759537073,\"delay\":null}', 0, NULL, 1759537073, 1759537073),
(21, 'default', '{\"uuid\":\"4b803a96-de51-455a-ae1e-977015ec4745\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:668.6646870000001;}s:2:\\\"id\\\";s:36:\\\"702b4d27-9f47-45ef-a5af-5b281b18a3da\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539793,\"delay\":null}', 0, NULL, 1759539793, 1759539793),
(22, 'default', '{\"uuid\":\"9ed504f0-2e9b-46ba-a379-ef63a97cd3be\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:668.6646870000001;}s:2:\\\"id\\\";s:36:\\\"702b4d27-9f47-45ef-a5af-5b281b18a3da\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539793,\"delay\":null}', 0, NULL, 1759539793, 1759539793),
(23, 'default', '{\"uuid\":\"dddfa96f-9dc6-4283-a3f0-6da14d9bd4a7\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:668.6646870000001;}s:2:\\\"id\\\";s:36:\\\"255ff401-dc8f-4706-9efe-0a4bf445659c\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539793,\"delay\":null}', 0, NULL, 1759539793, 1759539793),
(24, 'default', '{\"uuid\":\"f3b19787-33d6-4323-afbf-75a3fa5c1aa0\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:668.6646870000001;}s:2:\\\"id\\\";s:36:\\\"255ff401-dc8f-4706-9efe-0a4bf445659c\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539793,\"delay\":null}', 0, NULL, 1759539793, 1759539793),
(25, 'default', '{\"uuid\":\"9b9f5696-dd5b-4882-8c05-c91f3ada297c\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"8edc4267-44d0-43de-804b-377e8dfda59d\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539807,\"delay\":null}', 0, NULL, 1759539807, 1759539807),
(26, 'default', '{\"uuid\":\"86eb1eed-8736-4d5e-b2f5-3cc9376889f9\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"8edc4267-44d0-43de-804b-377e8dfda59d\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539807,\"delay\":null}', 0, NULL, 1759539807, 1759539807),
(27, 'default', '{\"uuid\":\"18446413-0a05-47d9-b120-bbd87a5a4c61\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"a9de88cc-dda1-4805-981c-b4c28ae70fef\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539807,\"delay\":null}', 0, NULL, 1759539807, 1759539807);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(28, 'default', '{\"uuid\":\"ce31746d-e12a-4990-9758-5df65f3e0d7c\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"a9de88cc-dda1-4805-981c-b4c28ae70fef\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759539807,\"delay\":null}', 0, NULL, 1759539807, 1759539807),
(29, 'default', '{\"uuid\":\"038e3f14-2e35-497f-a651-d2d0740aa417\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"a782f3e8-3922-4cce-bc2f-ef31a8806980\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759572611,\"delay\":null}', 0, NULL, 1759572611, 1759572611),
(30, 'default', '{\"uuid\":\"77feb654-d02d-416d-be6c-533d20eda5dc\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"a782f3e8-3922-4cce-bc2f-ef31a8806980\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759572611,\"delay\":null}', 0, NULL, 1759572611, 1759572611),
(31, 'default', '{\"uuid\":\"6cb6f42e-6b24-40d9-a2d9-e824ea5472cf\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"199c91fa-080c-4fd9-b372-45ef2ba9bf54\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759572611,\"delay\":null}', 0, NULL, 1759572611, 1759572611),
(32, 'default', '{\"uuid\":\"b86ae01f-90f7-4478-a141-f2e366c00ae7\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"199c91fa-080c-4fd9-b372-45ef2ba9bf54\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759572611,\"delay\":null}', 0, NULL, 1759572611, 1759572611),
(33, 'default', '{\"uuid\":\"bb1db204-6eb4-4502-ada4-fbdd2125342c\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:879.237887;}s:2:\\\"id\\\";s:36:\\\"49d2e1cc-2dbc-488e-9ab4-02ac20f49152\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573489,\"delay\":null}', 0, NULL, 1759573489, 1759573489),
(34, 'default', '{\"uuid\":\"dd7614cb-5461-4d44-aeeb-1562e4d0ac12\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:879.237887;}s:2:\\\"id\\\";s:36:\\\"49d2e1cc-2dbc-488e-9ab4-02ac20f49152\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573489,\"delay\":null}', 0, NULL, 1759573489, 1759573489),
(35, 'default', '{\"uuid\":\"6a051103-537d-4d3c-bf00-beaaecc0bcb4\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:879.237887;}s:2:\\\"id\\\";s:36:\\\"1e53bf63-fa10-4dde-8f7d-0e180dddc796\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573489,\"delay\":null}', 0, NULL, 1759573489, 1759573489),
(36, 'default', '{\"uuid\":\"9bc24653-c327-4c36-9c14-29bd9bdaf5e8\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:879.237887;}s:2:\\\"id\\\";s:36:\\\"1e53bf63-fa10-4dde-8f7d-0e180dddc796\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573489,\"delay\":null}', 0, NULL, 1759573489, 1759573489),
(37, 'default', '{\"uuid\":\"ebabd233-674e-4a16-95b4-926e5ed62e4b\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"c9e98cd4-31cc-485a-a35a-4f7ab9130c78\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573507,\"delay\":null}', 0, NULL, 1759573507, 1759573507),
(38, 'default', '{\"uuid\":\"50cf03bd-b226-4fec-842e-ccf750252ae3\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"c9e98cd4-31cc-485a-a35a-4f7ab9130c78\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573507,\"delay\":null}', 0, NULL, 1759573507, 1759573507),
(39, 'default', '{\"uuid\":\"958ea51b-362f-4538-92e0-e581205cd6c5\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"4b98928b-1725-4cb4-9393-08314bd07d8c\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573507,\"delay\":null}', 0, NULL, 1759573507, 1759573507),
(40, 'default', '{\"uuid\":\"8d92be3e-1a87-45d8-868e-fa78b58d7038\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:16:\\\"showroom_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"4b98928b-1725-4cb4-9393-08314bd07d8c\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573507,\"delay\":null}', 0, NULL, 1759573507, 1759573507),
(41, 'default', '{\"uuid\":\"4d0fdfcd-b338-4cd5-b760-db3395d7c5c4\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"0ecd74f5-b953-4afa-b69e-c99e693632c7\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573520,\"delay\":null}', 0, NULL, 1759573520, 1759573520),
(42, 'default', '{\"uuid\":\"6781b658-a8e2-40d1-82ab-415a93bd0588\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"0ecd74f5-b953-4afa-b69e-c99e693632c7\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573520,\"delay\":null}', 0, NULL, 1759573520, 1759573520),
(43, 'default', '{\"uuid\":\"a69cd71f-388c-49fa-94fb-36d5267a26ca\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"1e6cdac4-eb30-4dde-a34e-23c63e7eb5b7\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573520,\"delay\":null}', 0, NULL, 1759573520, 1759573520),
(44, 'default', '{\"uuid\":\"2aa6cbf4-0c9b-4ec3-a670-d87800c9ef97\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:15:\\\"showroom_review\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:16:\\\"showroom_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"1e6cdac4-eb30-4dde-a34e-23c63e7eb5b7\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573520,\"delay\":null}', 0, NULL, 1759573520, 1759573520),
(45, 'default', '{\"uuid\":\"e3278052-5099-4e7a-be06-475b1d5a9ebf\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:44.446943;}s:2:\\\"id\\\";s:36:\\\"24eccea8-83ba-4982-9543-bef51e4ccccf\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573564,\"delay\":null}', 0, NULL, 1759573564, 1759573564),
(46, 'default', '{\"uuid\":\"3b5f91d2-6cb2-44fa-9fb8-bb510c1518dd\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:44.446943;}s:2:\\\"id\\\";s:36:\\\"24eccea8-83ba-4982-9543-bef51e4ccccf\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573564,\"delay\":null}', 0, NULL, 1759573564, 1759573564),
(47, 'default', '{\"uuid\":\"21c3a80c-3ca8-4d20-afc1-d4026ed01a92\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:44.446943;}s:2:\\\"id\\\";s:36:\\\"3d3fc254-9a47-42af-badc-82800e712a3f\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573564,\"delay\":null}', 0, NULL, 1759573564, 1759573564),
(48, 'default', '{\"uuid\":\"41733e2e-b501-4ec2-ba04-890301d4c009\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:7:\\\"pending\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:44.446943;}s:2:\\\"id\\\";s:36:\\\"3d3fc254-9a47-42af-badc-82800e712a3f\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573564,\"delay\":null}', 0, NULL, 1759573564, 1759573564),
(49, 'default', '{\"uuid\":\"f540cfa4-583a-4adb-bb6f-27e1103403a4\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:302.123698;}s:2:\\\"id\\\";s:36:\\\"c20550f3-8eb4-4327-be0e-b19adf7f7b14\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573822,\"delay\":null}', 0, NULL, 1759573822, 1759573822),
(50, 'default', '{\"uuid\":\"0b43a0e5-6207-47e7-8064-35863e2d1730\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:302.123698;}s:2:\\\"id\\\";s:36:\\\"c20550f3-8eb4-4327-be0e-b19adf7f7b14\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573822,\"delay\":null}', 0, NULL, 1759573822, 1759573822),
(51, 'default', '{\"uuid\":\"eee676f6-4f31-48b5-903f-474f4947bc53\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:302.123698;}s:2:\\\"id\\\";s:36:\\\"e8939b3a-4de6-48b1-afb9-21311b75baa1\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573822,\"delay\":null}', 0, NULL, 1759573822, 1759573822),
(52, 'default', '{\"uuid\":\"0fbed33d-cfac-49ac-ab52-2d3d38d4d504\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:8:\\\"received\\\";s:7:\\\"context\\\";a:4:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:11:\\\"from_status\\\";s:8:\\\"received\\\";s:9:\\\"to_status\\\";s:8:\\\"received\\\";s:12:\\\"wait_seconds\\\";d:302.123698;}s:2:\\\"id\\\";s:36:\\\"e8939b3a-4de6-48b1-afb9-21311b75baa1\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759573822,\"delay\":null}', 0, NULL, 1759573822, 1759573822),
(53, 'default', '{\"uuid\":\"53b99b2e-8118-471e-b4e3-4243875511f1\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"22d6e69c-61d8-43ec-ac83-3b0fbf610704\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574087,\"delay\":null}', 0, NULL, 1759574087, 1759574087);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(54, 'default', '{\"uuid\":\"4c357834-5b18-459e-83b5-a0d1f50a09a8\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"22d6e69c-61d8-43ec-ac83-3b0fbf610704\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574087,\"delay\":null}', 0, NULL, 1759574087, 1759574087),
(55, 'default', '{\"uuid\":\"bcb6c083-903c-4850-a2c0-8ef50c225e25\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"a2b82b8d-2bb5-4fe2-90e6-58ff376fff4a\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574087,\"delay\":null}', 0, NULL, 1759574087, 1759574087),
(56, 'default', '{\"uuid\":\"165f07b4-de3c-419a-a523-7b5c0da8f4f7\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"received\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"a2b82b8d-2bb5-4fe2-90e6-58ff376fff4a\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574087,\"delay\":null}', 0, NULL, 1759574087, 1759574087),
(57, 'default', '{\"uuid\":\"86b47bff-be37-4c87-b561-7277459e6227\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"2d8d5705-32ed-460c-bf11-bbf3c10b3dcf\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574121,\"delay\":null}', 0, NULL, 1759574121, 1759574121),
(58, 'default', '{\"uuid\":\"574935aa-845a-4783-a374-f70470742dc6\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"2d8d5705-32ed-460c-bf11-bbf3c10b3dcf\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574121,\"delay\":null}', 0, NULL, 1759574121, 1759574121),
(59, 'default', '{\"uuid\":\"125f1ebe-3fe6-4249-a094-2e9dd0b65ec2\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"9e30a817-8bbd-4ad6-9ff8-656856495e85\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574121,\"delay\":null}', 0, NULL, 1759574121, 1759574121),
(60, 'default', '{\"uuid\":\"5791cd1b-682e-4d8e-ae0e-41f82367b924\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:4:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:12:\\\"under_review\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:0;}s:2:\\\"id\\\";s:36:\\\"9e30a817-8bbd-4ad6-9ff8-656856495e85\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574121,\"delay\":null}', 0, NULL, 1759574121, 1759574121),
(61, 'default', '{\"uuid\":\"69881cc6-f91f-45ba-b445-56dd0920ce75\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:21:\\\"عرض المشروع\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:20:\\\"عرضالمشروع\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:0;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:39:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/projects\\/27\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:44:\\\"المهمة (#37) على المشروع #27\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:35:\\\"heroicon-o-clipboard-document-check\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:30:\\\"مهمة جديدة لقسمك\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"120e395b-93b9-459e-8642-353ea163e90a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(62, 'default', '{\"uuid\":\"9630a654-4d53-4f00-8b52-9182589c6d9a\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:17:\\\"project_bootstrap\\\";s:7:\\\"context\\\";a:1:{s:10:\\\"project_id\\\";i:27;}s:2:\\\"id\\\";s:36:\\\"dc40612f-49fd-4c09-9d79-899ab85b5aff\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(63, 'default', '{\"uuid\":\"c4d9a88a-15ed-4eca-b55b-c53566126a04\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:17:\\\"project_bootstrap\\\";s:7:\\\"context\\\";a:1:{s:10:\\\"project_id\\\";i:27;}s:2:\\\"id\\\";s:36:\\\"dc40612f-49fd-4c09-9d79-899ab85b5aff\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(64, 'default', '{\"uuid\":\"af029025-ae4b-449d-bf83-df7ce2b7b5d1\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:17:\\\"project_bootstrap\\\";s:7:\\\"context\\\";a:1:{s:10:\\\"project_id\\\";i:27;}s:2:\\\"id\\\";s:36:\\\"693684cd-836b-44a5-8d68-a201168a83fb\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(65, 'default', '{\"uuid\":\"0829f90d-d5e7-462d-89e3-07ff4a526a4d\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:17:\\\"project_bootstrap\\\";s:7:\\\"context\\\";a:1:{s:10:\\\"project_id\\\";i:27;}s:2:\\\"id\\\";s:36:\\\"693684cd-836b-44a5-8d68-a201168a83fb\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(66, 'default', '{\"uuid\":\"7cafd8bb-a08e-4a18-a218-da78ced6438f\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:21:\\\"department_assignment\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"98b5a706-77e9-4bac-a057-36a0c7dad843\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(67, 'default', '{\"uuid\":\"220e9029-1eae-4496-8bdb-1375e59a526b\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:1;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:21:\\\"department_assignment\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"98b5a706-77e9-4bac-a057-36a0c7dad843\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(68, 'default', '{\"uuid\":\"a42bf4ee-b2f3-4a00-9ae5-a74189bf0dc4\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:21:\\\"department_assignment\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"018e8dd6-c60b-4b09-8677-52e2c044f70e\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(69, 'default', '{\"uuid\":\"e07475e1-1c56-4b8f-9005-76dbbbe497a6\",\"displayName\":\"App\\\\Notifications\\\\ProductionPhaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":4:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:45:\\\"App\\\\Notifications\\\\ProductionPhaseNotification\\\":5:{s:2:\\\"pr\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:28:\\\"App\\\\Models\\\\ProductionRequest\\\";s:2:\\\"id\\\";i:31;s:9:\\\"relations\\\";a:8:{i:0;s:8:\\\"showroom\\\";i:1;s:16:\\\"showroom.manager\\\";i:2;s:21:\\\"showroom.manager.user\\\";i:3;s:7:\\\"project\\\";i:4;s:13:\\\"project.tasks\\\";i:5;s:24:\\\"project.tasks.department\\\";i:6;s:32:\\\"project.tasks.department.manager\\\";i:7;s:37:\\\"project.tasks.department.manager.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:4:\\\"type\\\";s:10:\\\"transition\\\";s:7:\\\"context\\\";a:4:{s:4:\\\"from\\\";a:3:{s:5:\\\"phase\\\";s:14:\\\"factory_intake\\\";s:6:\\\"status\\\";s:8:\\\"approved\\\";s:5:\\\"owner\\\";s:15:\\\"factory_manager\\\";}s:2:\\\"to\\\";a:2:{s:5:\\\"phase\\\";s:21:\\\"department_assignment\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";}s:10:\\\"owner_role\\\";s:15:\\\"factory_manager\\\";s:10:\\\"touch_sent\\\";b:1;}s:2:\\\"id\\\";s:36:\\\"018e8dd6-c60b-4b09-8677-52e2c044f70e\\\";s:11:\\\"afterCommit\\\";b:1;}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}s:11:\\\"afterCommit\\\";b:1;}\"},\"createdAt\":1759574122,\"delay\":null}', 0, NULL, 1759574122, 1759574122),
(70, 'default', '{\"uuid\":\"cd78c63f-ef97-409f-9506-2e6c65d90a0a\",\"displayName\":\"App\\\\Notifications\\\\TaskAssignedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:19:\\\"App\\\\Models\\\\Employee\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:1:{i:0;s:4:\\\"user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:42:\\\"App\\\\Notifications\\\\TaskAssignedNotification\\\":3:{s:4:\\\"task\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:25:\\\"App\\\\Models\\\\ProductionTask\\\";s:2:\\\"id\\\";i:37;s:9:\\\"relations\\\";a:2:{i:0;s:8:\\\"employee\\\";i:1;s:13:\\\"employee.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:14:\\\"isReassignment\\\";b:1;s:2:\\\"id\\\";s:36:\\\"f20cec8b-4f55-4bfe-b5f7-bf32cdfa2d15\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759574190,\"delay\":null}', 0, NULL, 1759574190, 1759574190),
(71, 'default', '{\"uuid\":\"b7414aa0-da48-4450-8dce-72181755f51b\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:92:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: إسناد من المصنع\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"6ab45a06-1b04-4b25-8276-8917c98524ee\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759574190,\"delay\":null}', 0, NULL, 1759574190, 1759574190),
(72, 'default', '{\"uuid\":\"a74ee21f-cbb4-409d-aebf-72f662be4cf7\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"body\\\";s:92:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: إسناد من المصنع\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"eb39dfa2-4496-4713-a12f-36c3e8a542cc\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759574190,\"delay\":null}', 0, NULL, 1759574190, 1759574190),
(73, 'default', '{\"uuid\":\"1589296b-e9ca-4259-a914-a74edc5bfc4a\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:23:\\\"رقم المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:50:\\\"تم إسناد المهمة لمدير القسم\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"b7d6c624-cb4f-48f2-bc3b-8d7e39a9a389\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759574190,\"delay\":null}', 0, NULL, 1759574190, 1759574190),
(74, 'default', '{\"uuid\":\"637c4c0e-ee55-4e19-9fdd-4ddb4539e24e\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:50:\\\"تم إسناد المهمة لمدير القسم\\\";s:4:\\\"body\\\";s:23:\\\"رقم المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"85510fd6-e061-46c3-add1-037370409392\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759574190,\"delay\":null}', 0, NULL, 1759574190, 1759574190),
(75, 'default', '{\"uuid\":\"7a5129d9-c7dd-4a8e-8073-57901929f259\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:23:\\\"رقم المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"59907951-2670-4484-bbf3-6454a75aeacf\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759575757,\"delay\":null}', 0, NULL, 1759575757, 1759575757),
(76, 'default', '{\"uuid\":\"f1698723-f78b-40eb-84b0-cdd8d62133a0\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:41:\\\"تم تأكيد استلام المهمة\\\";s:4:\\\"body\\\";s:23:\\\"رقم المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"49830b74-125e-4da7-97d8-7dfaa33885a3\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759575757,\"delay\":null}', 0, NULL, 1759575757, 1759575757),
(77, 'default', '{\"uuid\":\"1c6121b1-aed5-4598-835d-3c5e5a933d07\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:14;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:81:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: طلب خامات\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"bdcf7205-3370-44e1-8a5f-f82484e8155f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759576380,\"delay\":null}', 0, NULL, 1759576380, 1759576380),
(78, 'default', '{\"uuid\":\"877c9d52-1ea9-4536-a01a-75dbda66de22\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:14;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"body\\\";s:81:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: طلب خامات\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"37bac2b2-63db-4a9a-83b7-c2d17ce7a151\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759576380,\"delay\":null}', 0, NULL, 1759576380, 1759576380),
(79, 'default', '{\"uuid\":\"b234d7ef-882f-45b6-a175-fed95bfa6a96\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:50:\\\"المهمة #37 بانتظار المشتريات\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:37:\\\"تم إرسال طلب الخامات\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"c9ab2979-c3aa-473c-bc23-3187df54cfa8\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759576380,\"delay\":null}', 0, NULL, 1759576380, 1759576380);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(80, 'default', '{\"uuid\":\"1803474d-a87d-4e3f-bf6e-16f407be2e55\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:37:\\\"تم إرسال طلب الخامات\\\";s:4:\\\"body\\\";s:50:\\\"المهمة #37 بانتظار المشتريات\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"5f0c4b35-df2a-4671-8a74-e307ec66c4c0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759576380,\"delay\":null}', 0, NULL, 1759576380, 1759576380),
(81, 'default', '{\"uuid\":\"c415b6ba-31a5-437d-b378-a6cee1a642c1\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:9;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:0:{}s:4:\\\"body\\\";s:59:\\\"تم تعيين المهمة #37 كمسؤولية قسمك.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";s:23:\\\"heroicon-o-check-circle\\\";s:9:\\\"iconColor\\\";s:7:\\\"success\\\";s:6:\\\"status\\\";s:7:\\\"success\\\";s:5:\\\"title\\\";s:47:\\\"تم نقل ملكية مهمة إلى قسمك\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"7eed0778-3637-490b-aa6c-93812fc550b0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759578865,\"delay\":null}', 0, NULL, 1759578865, 1759578865),
(82, 'default', '{\"uuid\":\"9256b008-e612-446a-8300-16a479d8b5ba\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:87:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: جاهز للتصنيع\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"58e527b7-a767-4ebe-9503-05d56ff06e75\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759579378,\"delay\":null}', 0, NULL, 1759579378, 1759579378),
(83, 'default', '{\"uuid\":\"89d8aff8-f645-4081-8f80-d346bb20dd7c\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:47:\\\"لديك مهمة بانتظار الإجراء\\\";s:4:\\\"body\\\";s:87:\\\"تم تحويل ملكية المهمة إليك. ملاحظة: جاهز للتصنيع\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"d78e6168-f151-4b88-84d6-41fd05854fe1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759579378,\"delay\":null}', 0, NULL, 1759579378, 1759579378),
(84, 'default', '{\"uuid\":\"79017d81-bcef-4fd8-b10b-3bff43bdeebc\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:60:\\\"تم تحديد المواعيد وإرسال للتصنيع\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"7eb390d0-6fba-48f8-8aa6-508988f862e3\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759579378,\"delay\":null}', 0, NULL, 1759579378, 1759579378),
(85, 'default', '{\"uuid\":\"e27cf8b6-e9da-463b-8559-8658dad35919\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:60:\\\"تم تحديد المواعيد وإرسال للتصنيع\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"e2dda3e6-f84c-4ea5-a73e-ae77ce2b8478\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759579378,\"delay\":null}', 0, NULL, 1759579378, 1759579378),
(86, 'default', '{\"uuid\":\"0fa80650-f2ad-4f86-8b73-b3735be70a8e\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:43:\\\"تم تأكيد استلام التصنيع\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"51e95516-2f1f-40fb-9b2b-1b62486f8960\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759581539,\"delay\":null}', 0, NULL, 1759581539, 1759581539),
(87, 'default', '{\"uuid\":\"d6932b77-7563-4d5e-bfec-b28d971915db\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:43:\\\"تم تأكيد استلام التصنيع\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"db99c681-e22c-462d-8d25-9bbaf97c09c4\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759581539,\"delay\":null}', 0, NULL, 1759581539, 1759581539),
(88, 'default', '{\"uuid\":\"a57a74fa-65a1-4e0c-b1f8-4efdcede01ac\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:21:\\\"بدأ التصنيع\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"4b0e1e9f-273f-44f2-bb55-7b6b431f49fd\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759581553,\"delay\":null}', 0, NULL, 1759581553, 1759581553),
(89, 'default', '{\"uuid\":\"077a4af9-3a8d-460a-8277-e32dd4e3dfd0\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:21:\\\"بدأ التصنيع\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"482d4262-b37f-4923-bd7b-4546fea13b08\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759581553,\"delay\":null}', 0, NULL, 1759581553, 1759581553),
(90, 'default', '{\"uuid\":\"66712f70-649e-4ae5-8f62-2751a46b371f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:43:\\\"تم إرسال التصنيع للجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"a45412c9-de32-447f-8c94-59681ea4d9d5\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759581577,\"delay\":null}', 0, NULL, 1759581577, 1759581577),
(91, 'default', '{\"uuid\":\"d93fe501-7cd2-46a0-a485-3a59fa73e885\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:43:\\\"تم إرسال التصنيع للجودة\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"c48b2a75-5da7-4582-bd38-d63c3c3103fa\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759581577,\"delay\":null}', 0, NULL, 1759581577, 1759581577),
(92, 'default', '{\"uuid\":\"02780a92-055a-4669-a470-3a2491d1931d\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التصنيع)\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"d5211f47-7fc5-4b38-b847-49615030b971\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587160,\"delay\":null}', 0, NULL, 1759587160, 1759587160),
(93, 'default', '{\"uuid\":\"7e210365-ed11-42a3-a385-79f6194069bd\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التصنيع)\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"80eb2b37-79b7-44cc-9530-0f690983e3b6\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587160,\"delay\":null}', 0, NULL, 1759587160, 1759587160),
(94, 'default', '{\"uuid\":\"981d2e83-218d-42bb-9177-90cde127d6cf\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التصنيع)\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"722b0285-23f4-417c-9c54-c5d91173ad7c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587255,\"delay\":null}', 0, NULL, 1759587255, 1759587255),
(95, 'default', '{\"uuid\":\"f928329e-03c8-4be9-ad8a-50c60dab10a8\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التصنيع)\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"957933a1-65f2-4983-a238-b1deaaccca4c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587255,\"delay\":null}', 0, NULL, 1759587255, 1759587255),
(96, 'default', '{\"uuid\":\"4a4351f2-c7b9-4a7c-bec9-006c8e37540f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:34:\\\"مهمة جاهزة للتركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"edadb6c8-d1a3-492f-bb4a-d2059364d95b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587271,\"delay\":null}', 0, NULL, 1759587271, 1759587271),
(97, 'default', '{\"uuid\":\"49b86ce8-035c-4b6e-9627-96e9fa8bc3ee\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:34:\\\"مهمة جاهزة للتركيب\\\";s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"f275564a-8221-4f35-bb9a-fd7b631139fa\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587271,\"delay\":null}', 0, NULL, 1759587271, 1759587271),
(98, 'default', '{\"uuid\":\"57804d9e-11bf-425e-a88d-ec12c28a87ac\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:71:\\\"تم اعتماد الجودة وتحويل المهمة للتركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"814e0b53-adaa-4927-9720-6d36433ee51b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587271,\"delay\":null}', 0, NULL, 1759587271, 1759587271),
(99, 'default', '{\"uuid\":\"42bb436e-7bc5-4e1c-b4da-ef5fa6e1cea0\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:71:\\\"تم اعتماد الجودة وتحويل المهمة للتركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"f85e26c9-5b86-4d42-9d01-4cb4cb24beb3\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587271,\"delay\":null}', 0, NULL, 1759587271, 1759587271),
(100, 'default', '{\"uuid\":\"fb2777a6-a08e-4e04-85b0-b41bfa5731ea\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:50:\\\"تم تأكيد استلام قسم التركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"324edcf4-e048-4439-b0e3-3168ddb5a522\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587434,\"delay\":null}', 0, NULL, 1759587434, 1759587434),
(101, 'default', '{\"uuid\":\"f4173bae-fdbf-495d-92aa-4349e2e847e1\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:50:\\\"تم تأكيد استلام قسم التركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"61cc5ef7-a5e2-495d-8e07-a43a07950836\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587434,\"delay\":null}', 0, NULL, 1759587434, 1759587434),
(102, 'default', '{\"uuid\":\"c7b1fe8b-cb2e-4fc0-a692-ee08c3624982\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:26:\\\"تم بدء التركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"fe1650db-c718-4a30-baed-bff5f6b78849\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587450,\"delay\":null}', 0, NULL, 1759587450, 1759587450),
(103, 'default', '{\"uuid\":\"e195c2f7-ad81-4700-9a61-67a2204a2068\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:26:\\\"تم بدء التركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"6f9f8eca-001f-4878-8db5-93a8719b085a\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587450,\"delay\":null}', 0, NULL, 1759587450, 1759587450),
(104, 'default', '{\"uuid\":\"65c38ed5-e221-418c-9bf5-e6db7c96d43f\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:54:\\\"مهمة تركيب بانتظار فحص الجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"3e02af59-3c2f-4de3-81f4-e6f1b92c4763\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587466,\"delay\":null}', 0, NULL, 1759587466, 1759587466),
(105, 'default', '{\"uuid\":\"c26fcfd7-25cb-42dc-b86a-501de6302bff\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:13;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:54:\\\"مهمة تركيب بانتظار فحص الجودة\\\";s:4:\\\"body\\\";s:49:\\\"تم تحويل ملكية المهمة إليك.\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"568982a6-1a8e-49dc-abe9-f7801d9a152c\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587466,\"delay\":null}', 0, NULL, 1759587466, 1759587466),
(106, 'default', '{\"uuid\":\"0b718b20-8690-4b23-a222-4dd4678b0cc8\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:43:\\\"تم إرسال التركيب للجودة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"3621209e-5c58-40df-a32b-096246f61a58\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587466,\"delay\":null}', 0, NULL, 1759587466, 1759587466),
(107, 'default', '{\"uuid\":\"6879af17-a518-473b-82e9-80174640fff1\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:43:\\\"تم إرسال التركيب للجودة\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"66596975-4566-4d6b-8230-7673f1c3b191\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587466,\"delay\":null}', 0, NULL, 1759587466, 1759587466);
INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(108, 'default', '{\"uuid\":\"a9e7bf4a-554a-4856-ad48-7cd8dbc1aaf9\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التركيب)\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"53f7f509-1557-476a-b27f-41fc26c301f1\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587497,\"delay\":null}', 0, NULL, 1759587497, 1759587497),
(109, 'default', '{\"uuid\":\"2a9c6fe2-b946-4c2d-8ebb-305b99704c73\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:65:\\\"تم تأكيد استلام الجودة (بعد التركيب)\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"e7dfa8fc-d1c7-44e7-a96a-6c8bed21507f\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587497,\"delay\":null}', 0, NULL, 1759587497, 1759587497),
(110, 'default', '{\"uuid\":\"62b59ddd-4758-408e-bdd3-c7a7ff6ca389\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:59:\\\"تم اعتماد الجودة لما بعد التركيب\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"8f07d98f-24b0-46d4-8d0c-9f673bc94816\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587511,\"delay\":null}', 0, NULL, 1759587511, 1759587511),
(111, 'default', '{\"uuid\":\"33a6b836-9df6-47a7-a959-03ceaa3ab17b\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:59:\\\"تم اعتماد الجودة لما بعد التركيب\\\";s:4:\\\"body\\\";s:16:\\\"المهمة #37\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"0de1accd-9faa-4a5b-8d7a-a506244ad12b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587511,\"delay\":null}', 0, NULL, 1759587511, 1759587511),
(112, 'default', '{\"uuid\":\"5d1e64e2-559c-413d-97ee-31e61fd02f9c\",\"displayName\":\"Filament\\\\Notifications\\\\DatabaseNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"Filament\\\\Notifications\\\\DatabaseNotification\\\":2:{s:4:\\\"data\\\";a:11:{s:7:\\\"actions\\\";a:1:{i:0;a:21:{s:4:\\\"name\\\";s:19:\\\"عرض المهمة\\\";s:5:\\\"color\\\";N;s:5:\\\"event\\\";N;s:9:\\\"eventData\\\";a:0:{}s:17:\\\"dispatchDirection\\\";b:0;s:19:\\\"dispatchToComponent\\\";N;s:15:\\\"extraAttributes\\\";a:0:{}s:4:\\\"icon\\\";N;s:12:\\\"iconPosition\\\";E:42:\\\"Filament\\\\Support\\\\Enums\\\\IconPosition:Before\\\";s:8:\\\"iconSize\\\";N;s:10:\\\"isOutlined\\\";b:0;s:10:\\\"isDisabled\\\";b:0;s:5:\\\"label\\\";s:18:\\\"عرضالمهمة\\\";s:11:\\\"shouldClose\\\";b:0;s:16:\\\"shouldMarkAsRead\\\";b:0;s:18:\\\"shouldMarkAsUnread\\\";b:0;s:21:\\\"shouldOpenUrlInNewTab\\\";b:1;s:4:\\\"size\\\";E:39:\\\"Filament\\\\Support\\\\Enums\\\\ActionSize:Small\\\";s:7:\\\"tooltip\\\";N;s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:4:\\\"view\\\";s:31:\\\"filament-actions::button-action\\\";}}s:4:\\\"body\\\";s:40:\\\"المهمة #37 أُغلقت بنجاح\\\";s:5:\\\"color\\\";N;s:8:\\\"duration\\\";s:10:\\\"persistent\\\";s:4:\\\"icon\\\";N;s:9:\\\"iconColor\\\";N;s:6:\\\"status\\\";N;s:5:\\\"title\\\";s:25:\\\"اكتملت المهمة\\\";s:4:\\\"view\\\";s:36:\\\"filament-notifications::notification\\\";s:8:\\\"viewData\\\";a:0:{}s:6:\\\"format\\\";s:8:\\\"filament\\\";}s:2:\\\"id\\\";s:36:\\\"60f1c486-d3ef-41aa-86c0-2a0e073e819d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\"},\"createdAt\":1759587559,\"delay\":null}', 0, NULL, 1759587559, 1759587559),
(113, 'default', '{\"uuid\":\"7d8902ac-4b93-4fa8-aeca-1b81ba690300\",\"displayName\":\"App\\\\Notifications\\\\ActionHandoffNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:12;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:43:\\\"App\\\\Notifications\\\\ActionHandoffNotification\\\":4:{s:5:\\\"title\\\";s:25:\\\"اكتملت المهمة\\\";s:4:\\\"body\\\";s:40:\\\"المهمة #37 أُغلقت بنجاح\\\";s:3:\\\"url\\\";s:36:\\\"http:\\/\\/127.0.0.1:8000\\/admin\\/tasks\\/37\\\";s:2:\\\"id\\\";s:36:\\\"74d5bdcd-80b3-4baa-9cd9-e178ace6e739\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\"},\"createdAt\":1759587559,\"delay\":null}', 0, NULL, 1759587559, 1759587559);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `name` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `legacy_client_projects`
--

DROP TABLE IF EXISTS `legacy_client_projects`;
CREATE TABLE IF NOT EXISTS `legacy_client_projects` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint UNSIGNED NOT NULL,
  `project_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `details` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `legacy_client_projects_client_id_start_date_index` (`client_id`,`start_date`),
  KEY `legacy_client_projects_client_id_end_date_index` (`client_id`,`end_date`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `legacy_client_projects`
--

INSERT INTO `legacy_client_projects` (`id`, `client_id`, `project_name`, `start_date`, `end_date`, `details`, `created_at`, `updated_at`) VALUES
(2, 2, 'مشروع مصنع الرخام', '2025-09-05', '2025-09-06', NULL, '2025-09-10 19:59:57', '2025-09-10 19:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `legacy_client_project_files`
--

DROP TABLE IF EXISTS `legacy_client_project_files`;
CREATE TABLE IF NOT EXISTS `legacy_client_project_files` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `legacy_project_id` bigint UNSIGNED NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `file_path` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `mime_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `file_size` bigint UNSIGNED DEFAULT NULL,
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `legacy_client_project_files_legacy_project_id_foreign` (`legacy_project_id`),
  KEY `legacy_client_project_files_uploaded_by_foreign` (`uploaded_by`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

DROP TABLE IF EXISTS `lessons_learned`;
CREATE TABLE IF NOT EXISTS `lessons_learned` (
  `lesson_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `category` enum('process','quality','schedule','cost','safety') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `lesson_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `recommendations` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `recorded_by` int NOT NULL,
  `record_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `implemented` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`lesson_id`),
  KEY `project_id` (`project_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `manufacturing_projects`
--

DROP TABLE IF EXISTS `manufacturing_projects`;
CREATE TABLE IF NOT EXISTS `manufacturing_projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `client_id` int NOT NULL,
  `production_manager_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `deadline_date` date DEFAULT NULL,
  `actual_end_date` date DEFAULT NULL,
  `status` enum('pending','design','procurement','production','quality_check','installation','completed','delayed') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT 'pending',
  `priority` tinyint DEFAULT '2',
  `current_phase` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  KEY `production_manager_id` (`production_manager_id`),
  KEY `idx_projects_status` (`status`),
  KEY `idx_projects_deadline` (`deadline_date`),
  KEY `idx_projects_client` (`client_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `measurement_units`
--

DROP TABLE IF EXISTS `measurement_units`;
CREATE TABLE IF NOT EXISTS `measurement_units` (
  `unit_id` int NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `unit_symbol` varchar(10) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `unit_type` enum('length','weight','volume','area','count') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `base_unit` tinyint(1) DEFAULT '0',
  `conversion_factor` decimal(15,6) DEFAULT '1.000000',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE IF NOT EXISTS `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(6, 'App\\Models\\User', 9),
(3, 'App\\Models\\User', 10),
(4, 'App\\Models\\User', 12),
(9, 'App\\Models\\User', 12),
(7, 'App\\Models\\User', 13),
(8, 'App\\Models\\User', 13),
(5, 'App\\Models\\User', 14);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('6fa1c519-3c4a-4a10-9819-fcf1347bb7e6', 'App\\Notifications\\TaskAssignedInAppNotification', 'App\\Models\\User', 12, '{\"title\":\"\\u0625\\u0639\\u0627\\u062f\\u0629 \\u0625\\u0633\\u0646\\u0627\\u062f \\u0645\\u0647\\u0645\\u0629 \\u062a\\u0635\\u0646\\u064a\\u0639\",\"body\":\"\\u0627\\u0644\\u0645\\u0634\\u0631\\u0648\\u0639: \\u0645\\u0634\\u0631\\u0648\\u0639 \\u0645\\u0635\\u0646\\u0639 \\u0627\\u0644\\u0631\\u062e\\u0627\\u0645 \\u2014 \\u0627\\u0644\\u0642\\u0633\\u0645: \\u0642\\u0633\\u0645 \\u0627\\u0644\\u0623\\u0644\\u0648\\u0645\\u0646\\u064a\\u0648\\u0645 \\u2014 \\u0627\\u0644\\u062a\\u0633\\u0644\\u064a\\u0645: 2025-10-18\",\"project_id\":27,\"task_id\":37,\"url\":\"http:\\/\\/127.0.0.1:8000\\/admin\\/projects\\/27\\/manage-tasks\",\"action_text\":\"\\u0641\\u062a\\u062d \\u0627\\u0644\\u0645\\u0647\\u0645\\u0629\"}', NULL, '2025-10-04 10:36:30', '2025-10-04 10:36:30');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `token` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `guard_name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
(78, 'view_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(79, 'create_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(80, 'update_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(81, 'restore_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(82, 'restore_any_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(83, 'replicate_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(84, 'reorder_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(85, 'delete_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(86, 'delete_any_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(87, 'force_delete_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(88, 'force_delete_any_role', 'web', '2025-10-02 06:22:50', '2025-10-02 06:22:50'),
(89, 'view_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(90, 'view_any_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(91, 'create_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(92, 'update_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(93, 'restore_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(94, 'restore_any_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(95, 'replicate_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(96, 'reorder_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(97, 'delete_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(98, 'delete_any_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(99, 'force_delete_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(100, 'force_delete_any_city', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(101, 'view_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(102, 'view_any_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(103, 'create_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(104, 'update_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(105, 'restore_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(106, 'restore_any_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(107, 'replicate_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(108, 'reorder_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(109, 'delete_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(110, 'delete_any_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(111, 'force_delete_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(112, 'force_delete_any_client', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(113, 'view_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(114, 'view_any_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(115, 'create_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(116, 'update_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(117, 'restore_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(118, 'restore_any_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(119, 'replicate_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(120, 'reorder_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(121, 'delete_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(122, 'delete_any_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(123, 'force_delete_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(124, 'force_delete_any_country', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(125, 'view_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(126, 'view_any_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(127, 'create_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(128, 'update_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(129, 'restore_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(130, 'restore_any_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(131, 'replicate_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(132, 'reorder_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(133, 'delete_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(134, 'delete_any_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(135, 'force_delete_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(136, 'force_delete_any_department', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(137, 'view_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(138, 'view_any_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(139, 'create_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(140, 'update_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(141, 'restore_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(142, 'restore_any_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(143, 'replicate_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(144, 'reorder_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(145, 'delete_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(146, 'delete_any_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(147, 'force_delete_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(148, 'force_delete_any_department::categories', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(149, 'view_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(150, 'view_any_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(151, 'create_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(152, 'update_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(153, 'restore_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(154, 'restore_any_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(155, 'replicate_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(156, 'reorder_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(157, 'delete_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(158, 'delete_any_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(159, 'force_delete_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(160, 'force_delete_any_employee', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(161, 'view_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(162, 'view_any_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(163, 'create_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(164, 'update_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(165, 'restore_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(166, 'restore_any_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(167, 'replicate_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(168, 'reorder_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(169, 'delete_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(170, 'delete_any_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(171, 'force_delete_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(172, 'force_delete_any_legacy::client::project', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(173, 'view_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(174, 'view_any_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(175, 'create_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(176, 'update_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(177, 'restore_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(178, 'restore_any_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(179, 'replicate_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(180, 'reorder_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(181, 'delete_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(182, 'delete_any_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(183, 'force_delete_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(184, 'force_delete_any_permission', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(185, 'view_production::request', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(186, 'view_any_production::request', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(187, 'create_production::request', 'web', '2025-10-02 06:23:06', '2025-10-02 06:23:06'),
(188, 'update_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(189, 'restore_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(190, 'restore_any_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(191, 'replicate_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(192, 'reorder_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(193, 'delete_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(194, 'delete_any_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(195, 'force_delete_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(196, 'force_delete_any_production::request', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(197, 'view_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(198, 'view_any_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(199, 'create_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(200, 'update_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(201, 'restore_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(202, 'restore_any_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(203, 'replicate_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(204, 'reorder_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(205, 'delete_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(206, 'delete_any_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(207, 'force_delete_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(208, 'force_delete_any_project', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(209, 'view_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(210, 'view_any_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(211, 'create_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(212, 'update_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(213, 'restore_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(214, 'restore_any_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(215, 'replicate_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(216, 'reorder_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(217, 'delete_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(218, 'delete_any_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(219, 'force_delete_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(220, 'force_delete_any_showroom', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(221, 'view_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(222, 'view_any_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(223, 'create_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(224, 'update_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(225, 'restore_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(226, 'restore_any_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(227, 'replicate_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(228, 'reorder_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(229, 'delete_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(230, 'delete_any_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(231, 'force_delete_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(232, 'force_delete_any_task', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(233, 'page_AssignedTasks', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(234, 'page_AutoPermissionGenerator', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(235, 'page_FactoryManagerTaskReview', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(236, 'page_MyNotifications', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(237, 'page_MaterialsRequests', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(238, 'page_MaterialsRequestsDone', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(239, 'page_ViewMaterialRequest', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(240, 'page_SystemSettings', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(241, 'widget_MainStats', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(242, 'widget_ClientsMonthlyChart', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(243, 'widget_DepartmentWorkloadBar', 'web', '2025-10-02 06:23:07', '2025-10-02 06:23:07'),
(244, 'widget_EmployeesByDepartmentDonut', 'web', '2025-10-02 06:23:08', '2025-10-02 06:23:08'),
(245, 'widget_ProjectsPerClientBar', 'web', '2025-10-02 06:23:08', '2025-10-02 06:23:08'),
(246, 'widget_RequestsPerMonthChart', 'web', '2025-10-02 06:23:08', '2025-10-02 06:23:08'),
(247, 'widget_TasksCompletionDoughnut', 'web', '2025-10-02 06:23:08', '2025-10-02 06:23:08'),
(248, 'widget_RequestsByStatusChart', 'web', '2025-10-02 06:23:08', '2025-10-02 06:23:08');

-- --------------------------------------------------------

--
-- Table structure for table `production_requests`
--

DROP TABLE IF EXISTS `production_requests`;
CREATE TABLE IF NOT EXISTS `production_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_type` enum('direct','indirect') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'indirect',
  `project_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `project_description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `client_id` bigint UNSIGNED NOT NULL,
  `showroom_id` bigint UNSIGNED DEFAULT NULL,
  `agreement_file` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `status` enum('pending','received','under_review','approved','rejected','in_progress','materials_wait','materials_prep','materials_done','on_hold','completed','cancelled') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'pending',
  `current_phase` varchar(155) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `phase_status` varchar(155) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `current_owner_user_id` bigint DEFAULT NULL,
  `current_owner_role` varchar(155) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sent_to_owner_at` datetime DEFAULT NULL,
  `received_by_owner_at` datetime DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `showroom_id` (`showroom_id`),
  KEY `client_id` (`client_id`),
  KEY `current_phase` (`current_phase`),
  KEY `phase_status` (`phase_status`),
  KEY `current_owner_role` (`current_owner_role`),
  KEY `sent_to_owner_at` (`sent_to_owner_at`),
  KEY `received_by_owner_at` (`received_by_owner_at`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_request_files`
--

DROP TABLE IF EXISTS `production_request_files`;
CREATE TABLE IF NOT EXISTS `production_request_files` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_request_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED NOT NULL,
  `estimated_cost` float(9,2) NOT NULL DEFAULT '0.00',
  `file_path` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_request_id` (`production_request_id`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_request_logs`
--

DROP TABLE IF EXISTS `production_request_logs`;
CREATE TABLE IF NOT EXISTS `production_request_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_request_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `happened_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_tasks_log_causer_id_foreign` (`causer_id`),
  KEY `production_tasks_log_task_id_happened_at_index` (`production_request_id`,`happened_at`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `production_request_logs`
--

INSERT INTO `production_request_logs` (`id`, `production_request_id`, `type`, `data`, `note`, `causer_id`, `happened_at`, `created_at`, `updated_at`) VALUES
(1, 31, 'transition', '{\"to\": {\"phase\": \"showroom_review\", \"status\": \"pending\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد الانتظار\"}, \"from\": {\"owner\": null, \"phase\": null, \"status\": null, \"actor_name\": \"Admin\", \"owner_label\": null, \"phase_label\": \"\", \"status_label\": \"\"}, \"owner_role\": \"showroom_manager\", \"owner_role_label\": \"مدير المعرض\"}', 'انتقال من مرحلة — (—) إلى مرحلة مراجعة المعرض (قيد الانتظار) | المالك: مدير المعرض', 1, '2025-10-03 12:17:58', '2025-10-03 12:17:58', '2025-10-03 12:17:58'),
(2, 31, 'received', '{\"phase\": \"showroom_review\", \"to_label\": \"تم الاستلام\", \"to_status\": \"received\", \"actor_name\": \"Ahmed\", \"from_label\": \"قيد الانتظار\", \"from_status\": \"pending\", \"phase_label\": \"مراجعة المعرض\", \"wait_seconds\": 42898.608654}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 9, '2025-10-04 00:12:56', '2025-10-04 00:12:56', '2025-10-04 00:12:56'),
(3, 31, 'received', '{\"phase\": \"showroom_review\", \"to_label\": \"تم الاستلام\", \"to_status\": \"received\", \"actor_name\": \"Ahmed\", \"from_label\": \"تم الاستلام\", \"from_status\": \"received\", \"phase_label\": \"مراجعة المعرض\", \"wait_seconds\": 42921.620301}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 9, '2025-10-04 00:13:19', '2025-10-04 00:13:19', '2025-10-04 00:13:19'),
(4, 31, 'transition', '{\"to\": {\"phase\": \"showroom_review\", \"status\": \"under_review\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد المراجعة\"}, \"from\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"received\", \"actor_name\": \"Ahmed\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"تم الاستلام\"}, \"owner_role\": \"showroom_manager\", \"owner_role_label\": \"مدير المعرض\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 9, '2025-10-04 00:17:04', '2025-10-04 00:17:04', '2025-10-04 00:17:04'),
(5, 31, 'rejected', '{\"phase\": \"showroom_review\", \"to_label\": \"مرفوض\", \"to_status\": \"rejected\", \"actor_name\": \"Ahmed\", \"from_label\": \"قيد المراجعة\", \"from_status\": \"under_review\", \"phase_label\": \"مراجعة المعرض\"}', 'تم رفض الطلب في مرحلة مراجعة المعرض — السبب: ناقص', 9, '2025-10-04 00:17:53', '2025-10-04 00:17:53', '2025-10-04 00:17:53'),
(6, 31, 'transition', '{\"to\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"pending\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد الانتظار\"}, \"from\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"rejected\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"مرفوض\"}, \"actor_name\": \"Mohamed Ibrahim\", \"owner_role\": \"showroom_manager\", \"owner_role_label\": \"مدير المعرض\"}', 'تم تعديل بيانات الطلب بواسطة المبيعات وإعادته للمراجعة.', 10, '2025-10-04 00:52:05', '2025-10-04 00:52:05', '2025-10-04 00:52:05'),
(7, 31, 'received', '{\"phase\": \"showroom_review\", \"to_label\": \"تم الاستلام\", \"to_status\": \"received\", \"actor_name\": \"Ahmed\", \"from_label\": \"قيد الانتظار\", \"from_status\": \"pending\", \"phase_label\": \"مراجعة المعرض\", \"wait_seconds\": 668.6646870000001}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 9, '2025-10-04 01:03:13', '2025-10-04 01:03:13', '2025-10-04 01:03:13'),
(8, 31, 'transition', '{\"to\": {\"phase\": \"showroom_review\", \"status\": \"under_review\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد المراجعة\"}, \"from\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"received\", \"actor_name\": \"Ahmed\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"تم الاستلام\"}, \"owner_role\": \"showroom_manager\", \"owner_role_label\": \"مدير المعرض\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 9, '2025-10-04 01:03:27', '2025-10-04 01:03:27', '2025-10-04 01:03:27'),
(9, 31, 'transition', '{\"to\": {\"phase\": \"factory_intake\", \"status\": \"pending\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"قيد الانتظار\"}, \"from\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"under_review\", \"actor_name\": \"Ahmed\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد المراجعة\"}, \"owner_role\": \"factory_manager\", \"owner_role_label\": \"مدير المصنع\"}', 'انتقال من مرحلة مراجعة المعرض (قيد المراجعة) إلى مرحلة استلام المصنع (قيد الانتظار) | المالك: مدير المصنع', 9, '2025-10-04 10:10:10', '2025-10-04 10:10:10', '2025-10-04 10:10:10'),
(10, 31, 'received', '{\"phase\": \"showroom_review\", \"to_label\": \"تم الاستلام\", \"to_status\": \"received\", \"actor_name\": \"Ahmed\", \"from_label\": \"قيد الانتظار\", \"from_status\": \"pending\", \"phase_label\": \"مراجعة المعرض\", \"wait_seconds\": 879.237887}', 'تم تأكيد الاستلام في مرحلة مراجعة المعرض', 9, '2025-10-04 10:24:49', '2025-10-04 10:24:49', '2025-10-04 10:24:49'),
(11, 31, 'transition', '{\"to\": {\"phase\": \"showroom_review\", \"status\": \"under_review\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد المراجعة\"}, \"from\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"received\", \"actor_name\": \"Ahmed\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"تم الاستلام\"}, \"owner_role\": \"showroom_manager\", \"owner_role_label\": \"مدير المعرض\"}', 'انتقال من مرحلة مراجعة المعرض (تم الاستلام) إلى مرحلة مراجعة المعرض (قيد المراجعة) | المالك: مدير المعرض', 9, '2025-10-04 10:25:07', '2025-10-04 10:25:07', '2025-10-04 10:25:07'),
(12, 31, 'transition', '{\"to\": {\"phase\": \"factory_intake\", \"status\": \"pending\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"قيد الانتظار\"}, \"from\": {\"owner\": \"showroom_manager\", \"phase\": \"showroom_review\", \"status\": \"under_review\", \"actor_name\": \"Ahmed\", \"owner_label\": \"مدير المعرض\", \"phase_label\": \"مراجعة المعرض\", \"status_label\": \"قيد المراجعة\"}, \"owner_role\": \"factory_manager\", \"owner_role_label\": \"مدير المصنع\"}', 'انتقال من مرحلة مراجعة المعرض (قيد المراجعة) إلى مرحلة استلام المصنع (قيد الانتظار) | المالك: مدير المصنع', 9, '2025-10-04 10:25:20', '2025-10-04 10:25:20', '2025-10-04 10:25:20'),
(13, 31, 'received', '{\"phase\": \"factory_intake\", \"to_label\": \"تم الاستلام\", \"to_status\": \"received\", \"actor_name\": \"Factory manager\", \"from_label\": \"قيد الانتظار\", \"from_status\": \"pending\", \"phase_label\": \"استلام المصنع\", \"wait_seconds\": 44.446943}', 'تم تأكيد الاستلام في مرحلة استلام المصنع', 13, '2025-10-04 10:26:04', '2025-10-04 10:26:04', '2025-10-04 10:26:04'),
(14, 31, 'received', '{\"phase\": \"factory_intake\", \"to_label\": \"تم الاستلام\", \"to_status\": \"received\", \"actor_name\": \"Factory manager\", \"from_label\": \"تم الاستلام\", \"from_status\": \"received\", \"phase_label\": \"استلام المصنع\", \"wait_seconds\": 302.123698}', 'تم تأكيد الاستلام في مرحلة استلام المصنع', 13, '2025-10-04 10:30:22', '2025-10-04 10:30:22', '2025-10-04 10:30:22'),
(15, 31, 'transition', '{\"to\": {\"phase\": \"factory_intake\", \"status\": \"under_review\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"قيد المراجعة\"}, \"from\": {\"owner\": \"factory_manager\", \"phase\": \"factory_intake\", \"status\": \"received\", \"actor_name\": \"Factory manager\", \"owner_label\": \"مدير المصنع\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"تم الاستلام\"}, \"owner_role\": \"factory_manager\", \"owner_role_label\": \"مدير المصنع\"}', 'انتقال من مرحلة استلام المصنع (تم الاستلام) إلى مرحلة استلام المصنع (قيد المراجعة) | المالك: مدير المصنع', 13, '2025-10-04 10:34:47', '2025-10-04 10:34:47', '2025-10-04 10:34:47'),
(16, 31, 'transition', '{\"to\": {\"phase\": \"factory_intake\", \"status\": \"approved\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"معتمد\"}, \"from\": {\"owner\": \"factory_manager\", \"phase\": \"factory_intake\", \"status\": \"under_review\", \"actor_name\": \"Factory manager\", \"owner_label\": \"مدير المصنع\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"قيد المراجعة\"}, \"owner_role\": \"factory_manager\", \"owner_role_label\": \"مدير المصنع\"}', 'انتقال من مرحلة استلام المصنع (قيد المراجعة) إلى مرحلة استلام المصنع (معتمد) | المالك: مدير المصنع', 13, '2025-10-04 10:35:21', '2025-10-04 10:35:21', '2025-10-04 10:35:21'),
(17, 31, 'project_bootstrap', '{\"project_id\": 27}', 'تم إنشاء مشروع #27 وربطه بالطلب.', 13, '2025-10-04 10:35:22', '2025-10-04 10:35:22', '2025-10-04 10:35:22'),
(18, 31, 'transition', '{\"to\": {\"phase\": \"department_assignment\", \"status\": \"pending\", \"phase_label\": \"إسناد الأقسام\", \"status_label\": \"قيد الانتظار\"}, \"from\": {\"owner\": \"factory_manager\", \"phase\": \"factory_intake\", \"status\": \"approved\", \"actor_name\": \"Factory manager\", \"owner_label\": \"مدير المصنع\", \"phase_label\": \"استلام المصنع\", \"status_label\": \"معتمد\"}, \"owner_role\": \"factory_manager\", \"owner_role_label\": \"مدير المصنع\"}', 'انتقال من مرحلة استلام المصنع (معتمد) إلى مرحلة إسناد الأقسام (قيد الانتظار) | المالك: مدير المصنع', 13, '2025-10-04 10:35:22', '2025-10-04 10:35:22', '2025-10-04 10:35:22'),
(19, 31, 'request_finalized', '{\"by\": \"project_completed\"}', 'اكتمل المشروع وجميع المهام، تم إغلاق الطلب.', 12, '2025-10-04 14:19:19', '2025-10-04 14:19:19', '2025-10-04 14:19:19'),
(20, 31, 'request_finalized', '{\"by\": \"project_completed\"}', 'اكتمل المشروع وجميع المهام، تم إغلاق الطلب.', 12, '2025-10-04 14:19:19', '2025-10-04 14:19:19', '2025-10-04 14:19:19');

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
  `file_path` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `status` enum('pending','assigned','received','under_review','approved','rejected','in_progress','materials_wait','materials_prep','materials_done','on_hold','completed','cancelled','waiting_production') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'pending',
  `estimated_cost` float(9,2) DEFAULT '0.00',
  `current_owner_user_id` bigint DEFAULT NULL,
  `current_owner_role` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `sent_to_owner_at` datetime DEFAULT NULL,
  `received_by_owner_at` datetime DEFAULT NULL,
  `planned_start_at` datetime DEFAULT NULL,
  `planned_end_at` datetime DEFAULT NULL,
  `planned_install_at` datetime DEFAULT NULL,
  `client_receipt` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tasks_project` (`project_id`),
  KEY `idx_tasks_department` (`department_id`),
  KEY `idx_tasks_employee` (`assigned_to_employee_id`),
  KEY `idx_tasks_status` (`status`),
  KEY `idx_tasks_owner_role` (`current_owner_role`),
  KEY `idx_tasks_owner_user` (`current_owner_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_comments`
--

DROP TABLE IF EXISTS `production_tasks_comments`;
CREATE TABLE IF NOT EXISTS `production_tasks_comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `body` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_tasks_comments_task_id_foreign` (`task_id`),
  KEY `production_tasks_comments_user_id_foreign` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `production_tasks_comments`
--

INSERT INTO `production_tasks_comments` (`id`, `task_id`, `user_id`, `body`, `attachments`, `created_at`, `updated_at`) VALUES
(1, 37, 12, 'تجربة', '[]', '2025-10-04 14:20:17', '2025-10-04 14:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_log`
--

DROP TABLE IF EXISTS `production_tasks_log`;
CREATE TABLE IF NOT EXISTS `production_tasks_log` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` json DEFAULT NULL,
  `causer_id` bigint UNSIGNED DEFAULT NULL,
  `happened_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `production_tasks_log_causer_id_foreign` (`causer_id`),
  KEY `production_tasks_log_task_id_happened_at_index` (`task_id`,`happened_at`)
) ENGINE=InnoDB AUTO_INCREMENT=399 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_material_requests`
--

DROP TABLE IF EXISTS `production_tasks_material_requests`;
CREATE TABLE IF NOT EXISTS `production_tasks_material_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` bigint UNSIGNED NOT NULL,
  `department_id` bigint UNSIGNED DEFAULT NULL,
  `requested_by` bigint UNSIGNED DEFAULT NULL,
  `requested_at` timestamp NULL DEFAULT NULL,
  `status` enum('requested','approved','fulfilled','cancelled') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'requested',
  `estimated_cost` float(9,2) DEFAULT '0.00',
  `actual_cost` float(9,2) DEFAULT '0.00',
  `expected_delivery_at` datetime DEFAULT NULL,
  `po_number` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `po_file` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `invoice_no` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_file` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `approved_by` int DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `provided_by` bigint UNSIGNED DEFAULT NULL,
  `provided_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_tasks_material_requests_task_id_status_index` (`task_id`,`status`),
  KEY `production_tasks_material_requests_requested_at_index` (`requested_at`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `production_tasks_time_entries`
--

DROP TABLE IF EXISTS `production_tasks_time_entries`;
CREATE TABLE IF NOT EXISTS `production_tasks_time_entries` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` bigint UNSIGNED NOT NULL,
  `started_by` bigint UNSIGNED DEFAULT NULL,
  `started_at` timestamp NOT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `duration_sec` int UNSIGNED DEFAULT NULL,
  `reason` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `production_tasks_time_entries_started_by_foreign` (`started_by`),
  KEY `production_tasks_time_entries_task_id_started_at_index` (`task_id`,`started_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
CREATE TABLE IF NOT EXISTS `projects` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `production_request_id` bigint UNSIGNED NOT NULL,
  `client_id` int NOT NULL,
  `project_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('in_progress','completed','on_hold') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'in_progress',
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `production_request_id` (`production_request_id`),
  KEY `projects_client_id_foreign` (`client_id`),
  KEY `projects_created_by_foreign` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
  `delay_reason` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `responsible_party` enum('internal','external','client') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `action_taken` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `recorded_by` int NOT NULL,
  `record_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`delay_id`),
  KEY `project_id` (`project_id`),
  KEY `phase_id` (`phase_id`),
  KEY `recorded_by` (`recorded_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_files`
--

DROP TABLE IF EXISTS `project_files`;
CREATE TABLE IF NOT EXISTS `project_files` (
  `file_id` int NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `department_id` int DEFAULT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `file_path` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `file_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `file_size` int DEFAULT NULL,
  `uploaded_by` int DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `version` varchar(20) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `estimated_cost` float(9,2) DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `idx_files_project` (`project_id`),
  KEY `idx_files_phase` (`department_id`),
  KEY `idx_files_type` (`file_type`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_images`
--

DROP TABLE IF EXISTS `project_images`;
CREATE TABLE IF NOT EXISTS `project_images` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `phase_id` int DEFAULT NULL,
  `image_name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `image_path` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `thumbnail_path` varchar(512) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `image_type` enum('progress','issue','final','design','other') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `taken_by` int DEFAULT NULL,
  `taken_date` datetime DEFAULT NULL,
  `uploaded_by` int NOT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `location` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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
  `check_type` enum('incoming','in_process','final') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `result` enum('passed','failed','conditional') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `defects_found` int DEFAULT '0',
  `notes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `corrective_actions` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`check_id`),
  KEY `project_id` (`project_id`),
  KEY `phase_id` (`phase_id`),
  KEY `inspector_id` (`inspector_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `guard_name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

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

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`)
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
(53, 3),
(66, 3),
(67, 3),
(68, 3),
(74, 3),
(75, 3),
(76, 3),
(101, 3),
(102, 3),
(103, 3),
(104, 3),
(107, 3),
(161, 3),
(162, 3),
(163, 3),
(164, 3),
(185, 3),
(186, 3),
(187, 3),
(188, 3),
(197, 3),
(235, 3),
(237, 3),
(238, 3),
(25, 4),
(49, 4),
(51, 4),
(53, 4),
(55, 4),
(65, 4),
(66, 4),
(67, 4),
(68, 4),
(69, 4),
(73, 4),
(74, 4),
(161, 4),
(162, 4),
(185, 4),
(186, 4),
(197, 4),
(198, 4),
(221, 4),
(222, 4),
(223, 4),
(233, 4),
(65, 5),
(66, 5),
(67, 5),
(68, 5),
(237, 5),
(238, 5),
(239, 5),
(38, 6),
(49, 6),
(50, 6),
(51, 6),
(54, 6),
(65, 6),
(66, 6),
(67, 6),
(68, 6),
(185, 6),
(186, 6),
(25, 7),
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
(74, 7),
(76, 7),
(101, 7),
(102, 7),
(161, 7),
(162, 7),
(163, 7),
(164, 7),
(185, 7),
(186, 7),
(188, 7),
(197, 7),
(198, 7),
(199, 7),
(200, 7),
(201, 7),
(203, 7),
(204, 7),
(221, 7),
(222, 7),
(223, 7),
(224, 7),
(233, 7),
(234, 7),
(235, 7),
(237, 7),
(238, 7),
(241, 7),
(242, 7),
(243, 7),
(244, 7),
(245, 7),
(246, 7),
(247, 7),
(248, 7);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`, `created_at`, `updated_at`) VALUES
('8jIdYqM6WH6Zlx2MkNvqqdWuizBVnYRq5tHiDu8O', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiUmdpTkFUb2FtNVZvN2NhcllVaGJpelR4RlUwVmpqR2Q1ZFoyT3A3YSI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjYyOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcHVyY2hhc2luZy9tYXRlcmlhbHMtcmVxdWVzdHMtZG9uZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiQxN2xFUDl5aE1kamZqejdyZzZlZEcubkRuS0VPb1hqbGF2VnlpRWljQm1mVE56d0NDY0R2VyI7czo4OiJmaWxhbWVudCI7YTowOnt9czo2OiJ0YWJsZXMiO2E6MTp7czo0MDoiYjFkNmZjMzQzOTc5MWRjNzYwOGFlMTIzYWYwNDhkNWZfZmlsdGVycyI7YToxOntzOjEyOiJpc19jb21wbGV0ZWQiO2E6MTp7czo1OiJ2YWx1ZSI7aTowO319fX0=', 1759588928, '2025-10-04 10:08:34', '2025-10-04 14:42:08'),
('v4lvzkmXKUKesOiFKnO9AheSSt9pa2sxTpRURbvk', 9, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiNzBNWFdwUmhaeThQSW9ReU5LRE5jRmREUFZKNkxEVlo3N3Z0Y3I3OCI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjU5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vcHJvZHVjdGlvbi1yZXF1ZXN0cy8zMS90aW1lbGluZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjk7czoxNzoicGFzc3dvcmRfaGFzaF93ZWIiO3M6NjA6IiQyeSQxMiRhaVNNZEI0bjhXWFFDNW5mTGp3QmNlb2kzNW9wampZMS45VTg2LzdkUVBvN0I2U05sRWp6NiI7czo4OiJmaWxhbWVudCI7YTowOnt9fQ==', 1759588934, '2025-10-04 10:08:17', '2025-10-04 14:42:14'),
('qG137spbW7j26ssxiMFclXWBQe0RsJCXkA9uQMWJ', 12, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiYjdJdDZmZng4am1JQm83SHRybVhCMTR1RmxvSEVJd0tqTEh0M01KTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi90YXNrcy8zNyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEyO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkUzRpeEZXVHpzYmZFVTUzdFFRTlZ5LlB5UGRVMURGN3dqU0JnbE1XWVRxa2ZXN25IRkhSTFMiO3M6NjoidGFibGVzIjthOjE6e3M6NDA6ImIxZDZmYzM0Mzk3OTFkYzc2MDhhZTEyM2FmMDQ4ZDVmX2ZpbHRlcnMiO2E6MTp7czoxMjoiaXNfY29tcGxldGVkIjthOjE6e3M6NToidmFsdWUiO3M6MToiMCI7fX19czo4OiJmaWxhbWVudCI7YTowOnt9fQ==', 1759588970, '2025-10-04 14:16:00', '2025-10-04 14:42:50'),
('jQN6UzEwNJwHgZNJAr4yYU4G4LzLFsRouqlxEa6W', 13, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiOUVGWE5ZMVBZUGhaOG1Sa2FVWHFBa1QwOU9ldElWamdnNURSbFhIQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9teS10YXNrcyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjEzO3M6MTc6InBhc3N3b3JkX2hhc2hfd2ViIjtzOjYwOiIkMnkkMTIkL1ZKWVUxQ0VJTUE5V3BERUFNV2hNT29wV2RMQ0JJb2hoL3ZLZFI5MDg3Z2RNaVdJTkk4eU8iO3M6NjoidGFibGVzIjthOjE6e3M6NDA6ImIxZDZmYzM0Mzk3OTFkYzc2MDhhZTEyM2FmMDQ4ZDVmX2ZpbHRlcnMiO2E6MTp7czoxMjoiaXNfY29tcGxldGVkIjthOjE6e3M6NToidmFsdWUiO2k6MDt9fX1zOjg6ImZpbGFtZW50IjthOjA6e319', 1759588933, '2025-10-04 13:20:54', '2025-10-04 14:42:13');

-- --------------------------------------------------------

--
-- Table structure for table `showrooms`
--

DROP TABLE IF EXISTS `showrooms`;
CREATE TABLE IF NOT EXISTS `showrooms` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `city_id` bigint UNSIGNED DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `manager_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `showrooms_city_id_foreign` (`city_id`),
  KEY `showrooms_manager_id_foreign` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `showrooms`
--

INSERT INTO `showrooms` (`id`, `name`, `address`, `city_id`, `phone`, `email`, `manager_id`, `created_at`, `updated_at`) VALUES
(1, 'معرض القطيف', 'القطيف - شارع القدس', 17, '01314565', 'qatif@modernlife.com', 6, '2025-07-09 05:28:32', '2025-08-20 04:32:53'),
(2, 'معرض الخبر', 'الخبر', 7, '013254555', NULL, 6, '2025-08-20 04:32:16', '2025-08-20 04:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE IF NOT EXISTS `system_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `log_level` enum('info','warning','error','critical') CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `context` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `setting_options` json DEFAULT NULL,
  `is_sensitive` tinyint(1) NOT NULL DEFAULT '0',
  `validation_rules` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `help_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `setting_group` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `setting_type` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT '0',
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  KEY `idx_settings_key` (`setting_key`),
  KEY `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`, `setting_options`, `is_sensitive`, `validation_rules`, `help_text`, `setting_group`, `setting_type`, `is_public`, `description`, `created_at`, `updated_at`) VALUES
(2, 'site_name', 'نظام إدارة التصنيع', NULL, 0, NULL, NULL, 'general', 'text', 1, 'اسم النظام المعروض في العنوان', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(3, 'system_email', 'info@factory.com', NULL, 0, NULL, NULL, 'general', 'email', 0, 'البريد الإلكتروني العام للنظام', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(4, 'default_language', 'ar', NULL, 0, NULL, NULL, 'general', 'select', 0, 'اللغة الافتراضية للنظام', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(5, 'notify_on_late_department_response', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'إرسال إشعار عند تأخر القسم عن الرد', '2025-07-09 11:41:12', '2025-09-14 13:02:29'),
(6, 'notify_manager_on_request', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'إشعار مدير المصنع عند وصول طلب جديد', '2025-07-09 11:41:12', '2025-09-14 13:02:29'),
(7, 'notification_email', 'notifications@factory.com', NULL, 0, NULL, NULL, 'notifications', 'email', 0, 'البريد المستلم للتنبيهات', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(8, 'factory_name', 'مصنع الحياة الحديثة للأثاث', NULL, 0, NULL, NULL, 'factory', 'text', 1, 'الاسم التجاري للمصنع', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(9, 'factory_logo', 'settings/bullet.png', NULL, 0, NULL, NULL, 'factory', 'file', 1, 'شعار المصنع الرسمي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(10, 'factory_address', 'جدة - المدينة الصناعية', NULL, 0, NULL, NULL, 'factory', 'text', 1, 'عنوان المصنع الرئيسي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(11, 'factory_phone', '+966500000000', NULL, 0, NULL, NULL, 'factory', 'text', 1, 'رقم الهاتف الرئيسي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(12, 'factory_email', 'support@factory.com', NULL, 0, NULL, NULL, 'factory', 'email', 1, 'البريد الإلكتروني الرسمي', '2025-07-09 11:41:12', '2025-07-09 10:02:19'),
(13, 'purchasing_budget_cap_pct', '50', NULL, 0, NULL, NULL, 'general', 'text', 1, 'سقف الميزانية للمشتريات', '2025-08-21 07:48:21', '2025-09-10 12:03:29'),
(14, 'app_name', 'ModernLife', NULL, 0, NULL, NULL, 'general', 'text', 0, 'اسم النظام', '2025-09-10 11:42:32', '2025-09-10 11:55:02'),
(15, 'company_name', 'ModernLife Co.', NULL, 0, NULL, NULL, 'general', 'text', 0, 'اسم الشركة', '2025-09-10 11:42:32', '2025-09-10 11:55:02'),
(16, 'support_email', 'support@example.com', NULL, 0, 'nullable|email', 'ستُستخدم لاستقبال رسائل الدعم', 'general', 'email', 0, 'بريد الدعم', '2025-09-10 11:42:32', '2025-09-10 11:55:02'),
(17, 'support_phone', NULL, NULL, 0, 'nullable|string|max:30', NULL, 'general', 'text', 0, 'هاتف الدعم', '2025-09-10 11:42:32', '2025-09-10 12:02:17'),
(18, 'base_url', 'http://localhost:8000', NULL, 0, 'required|url', NULL, 'general', 'url', 0, 'رابط النظام', '2025-09-10 11:42:32', '2025-09-10 11:55:02'),
(19, 'brand_logo', NULL, NULL, 0, NULL, 'يفضل PNG بخلفية شفافة', 'branding', 'image', 0, 'شعار الشركة', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(20, 'brand_favicon', NULL, NULL, 0, NULL, 'مقاس 32x32 أو 64x64', 'branding', 'image', 0, 'Favicon', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(21, 'primary_color', '#0ea5e9', NULL, 0, NULL, NULL, 'branding', 'color', 0, 'اللون الأساسي', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(22, 'secondary_color', '#22c55e', NULL, 0, NULL, NULL, 'branding', 'color', 0, 'اللون الثانوي', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(23, 'locale', 'ar', '{\"ar\": \"العربية\", \"en\": \"English\"}', 0, 'required|in:ar,en', 'تؤثر على لغة واجهة فيلامنت والتواريخ.', 'locale', 'locale', 0, 'لغة الواجهة', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(24, 'timezone', 'Asia/Riyadh', NULL, 0, 'required|string', NULL, 'locale', 'timezone', 0, 'المنطقة الزمنية', '2025-09-10 11:55:02', '2025-09-10 12:05:52'),
(25, 'date_format', 'Y-m-d', '{\"Y-m-d\": \"2025-09-09\", \"d/m/Y\": \"09/09/2025\", \"m/d/Y\": \"09/09/2025\", \"M j, Y\": \"Sep 9, 2025\"}', 0, 'required|string', 'تأثيره يظهر في أعمدة/حقول التاريخ.', 'locale', 'select', 0, 'تنسيق التاريخ', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(26, 'time_format', 'h:i A', '{\"H:i\": \"23:15\", \"h:i A\": \"11:15 PM\"}', 0, 'required|string', NULL, 'locale', 'select', 0, 'تنسيق الوقت', '2025-09-10 11:55:02', '2025-09-10 12:05:52'),
(27, 'week_starts_on', 'saturday', '{\"monday\": \"الاثنين\", \"sunday\": \"الأحد\", \"saturday\": \"السبت\"}', 0, 'required|in:saturday,sunday,monday', NULL, 'locale', 'select', 0, 'أول أيام الأسبوع', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(28, 'project_due_soon_days', '7', NULL, 0, 'required|integer|min:0', NULL, 'projects', 'number', 0, 'إنذار قبل انتهاء المشروع (أيام)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(29, 'task_overdue_grace_hours', '24', NULL, 0, 'required|integer|min:0', NULL, 'projects', 'number', 0, 'ساعات سماح لتأخير المهام', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(30, 'max_upload_mb', '50', NULL, 0, 'required|integer|min:1|max:200', NULL, 'files', 'number', 0, 'الحد الأقصى لحجم الملف (MB)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(31, 'allowed_mime_extra', NULL, NULL, 0, NULL, 'سطر لكل نوع، مثال:\napplication/zip\ntext/csv', 'files', 'textarea', 0, 'أنواع MIME إضافية مسموحة', '2025-09-10 11:55:02', '2025-09-10 12:02:17'),
(32, 'notify_in_app', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات داخل النظام', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(33, 'notify_email', '1', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات البريد', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(34, 'notify_slack', '0', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات Slack', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(35, 'notify_telegram', '0', NULL, 0, NULL, NULL, 'notifications', 'boolean', 0, 'تفعيل تنبيهات Telegram', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(36, 'quiet_hours_start', '21:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', 'لن تُرسل إشعارات فورية خلال هذه المدة.', 'notifications', 'text', 0, 'بدء الساعات الهادئة (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(37, 'quiet_hours_end', '08:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', NULL, 'notifications', 'text', 0, 'انتهاء الساعات الهادئة (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(38, 'daily_digest_time', '09:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', NULL, 'notifications', 'text', 0, 'موعد الملخص اليومي (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(39, 'weekly_digest_day', 'sunday', '{\"friday\": \"الجمعة\", \"monday\": \"الاثنين\", \"sunday\": \"الأحد\", \"tuesday\": \"الثلاثاء\", \"saturday\": \"السبت\", \"thursday\": \"الخميس\", \"wednesday\": \"الأربعاء\"}', 0, 'nullable|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday', NULL, 'notifications', 'select', 0, 'يوم الملخص الأسبوعي', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(40, 'weekly_digest_time', '09:00', NULL, 0, 'nullable|regex:/^\\d{2}:\\d{2}$/', NULL, 'notifications', 'text', 0, 'موعد الملخص الأسبوعي (HH:mm)', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(41, 'mail_from_name', 'ModernLife', NULL, 0, 'required|string|max:100', NULL, 'mail', 'text', 0, 'اسم المرسل', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(42, 'mail_from_address', 'noreply@a-elsayed.com', NULL, 0, 'required|email', NULL, 'mail', 'email', 0, 'بريد المرسل', '2025-09-10 11:55:02', '2025-09-14 09:41:30'),
(43, 'smtp_host', 'mail.a-elsayed.com', NULL, 0, 'nullable|string', NULL, 'mail', 'text', 0, 'SMTP Host', '2025-09-10 11:55:02', '2025-09-14 09:41:30'),
(44, 'smtp_port', '587', NULL, 0, 'nullable|integer|min:1', NULL, 'mail', 'number', 0, 'SMTP Port', '2025-09-10 11:55:02', '2025-09-10 11:55:02'),
(45, 'smtp_username', 'noreply@a-elsayed.com', NULL, 0, 'nullable|string', NULL, 'mail', 'text', 0, 'SMTP Username', '2025-09-10 11:55:02', '2025-09-14 09:41:30'),
(46, 'smtp_password', 'eyJpdiI6InVucFRJeFAvU2ovYmJLRDZOdjRxZkE9PSIsInZhbHVlIjoielhML3NuYUdXazVDc3hxM2MrbkxZUT09IiwibWFjIjoiMGMwNDc5ZTcxYzRhOGE4MmVkOTViYjBjMTI5NDI5ZGUwZmE5NmQ5MzliMGJlYTA1YmMwZjc1NTU2MDkwOWU0ZiIsInRhZyI6IiJ9', NULL, 1, 'nullable|string', 'يُحفظ مُشفّرًا من صفحة الإعدادات', 'mail', 'secret', 0, 'SMTP Password', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(47, 'smtp_encryption', 'ssl', '{\"ssl\": \"SSL\", \"tls\": \"TLS\", \"none\": \"بدون\"}', 0, 'nullable|in:tls,ssl,none', NULL, 'mail', 'select', 0, 'التشفير', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(48, 'slack_webhook_url', NULL, NULL, 0, 'nullable|url', NULL, 'integrations', 'url', 0, 'Slack Webhook URL', '2025-09-10 11:55:02', '2025-09-10 12:02:17'),
(49, 'telegram_bot_token', 'eyJpdiI6IjRDQ09sVE5IalQzdVEwSFFLbStHdVE9PSIsInZhbHVlIjoiZC9RSzdKaHpadFRzeHlKN254UVFEUT09IiwibWFjIjoiMzE4YTEyOTY5MjE3MDA3YmZkNmFjOWZkMjE2ZmI4MmRlYjAxNzNkZjFiNDk3YTBmMmM4YWIwZjFmZmJlYzM1NiIsInRhZyI6IiJ9', NULL, 1, 'nullable|string', NULL, 'integrations', 'secret', 0, 'توكن بوت تيليجرام', '2025-09-10 11:55:02', '2025-09-14 13:02:29'),
(50, 'telegram_chat_id', NULL, NULL, 0, 'nullable|string', NULL, 'integrations', 'text', 0, 'رقم محادثة تيليجرام', '2025-09-10 11:55:02', '2025-09-10 12:02:17'),
(51, 'webhook_url', NULL, NULL, 0, 'nullable|url', NULL, 'integrations', 'url', 0, 'Webhook عام', '2025-09-10 11:55:02', '2025-09-10 12:02:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `email` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(190) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'info@a-elsayed.com', NULL, '$2y$12$17lEP9yhMdjfjz7rg6edG.nDnKEOoXjlavVyiEicBmfTNzwCCcDvW', 'MjxuhgeAFwCCqA63dD06bl3vjxGmAr70dGNaSeBOzzn8L0d5xIK5CcL7Bim1', '2025-07-03 14:55:58', '2025-07-03 14:55:58'),
(9, 'Ahmed', 'show@mdlife.com', NULL, '$2y$12$aiSMdB4n8WXQC5nfLjwBceoi35opjjY1.9U86/7dQPo7B6SNlEjz6', 'P674s6FmEtdIFeaqwUq8KScV6zTIVJDRZFBdmG4TvKwFyMRUOiRpWYQXvX0w', '2025-07-06 06:51:31', '2025-10-03 11:51:16'),
(10, 'Mohamed Ibrahim', 'sales@mdlife.com', NULL, '$2y$12$ctCGyKM1Pe2XlhwhRtmW9eK/r5PjqfdiyB46LAY4HiNlbnMHt393q', 'wFzGUrWmGvzcQKO8KjYbnjvSjhefEYHzxr9n1Aon870sMxJqB14rUdwGvkWE', '2025-08-20 04:39:55', '2025-10-03 11:40:46'),
(12, 'Dept Manager', 'dep@mdlife.com', NULL, '$2y$12$S4ixFWTzsbfEU53tQQNVy.PyPdU1DF7wjSBglMWYTqkfW7nHFHRLS', NULL, '2025-09-10 19:21:39', '2025-10-04 13:12:35'),
(13, 'Factory Manager', 'factory@mdlife.com', NULL, '$2y$12$/VJYU1CEIMA9WpDEAMWhMOopWdLCBIohh/vKdR9087gdMiWINI8yO', NULL, '2025-10-03 10:32:34', '2025-10-04 13:14:57'),
(14, 'Purchasing', 'purch@mdlife.com', NULL, '$2y$12$.VADcs5lteYpErQEtdcNsOU49wlzfRkrHh5cyywqeVgb5aK4P/IYW', NULL, '2025-10-03 11:48:55', '2025-10-03 11:48:55');

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
  ADD CONSTRAINT `production_f_rq_id_foreign` FOREIGN KEY (`production_request_id`) REFERENCES `production_requests` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

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
  ADD CONSTRAINT `production_tasks_foreign` FOREIGN KEY (`task_id`) REFERENCES `production_tasks` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `production_tasks_material_requests`
--
ALTER TABLE `production_tasks_material_requests`
  ADD CONSTRAINT `production_tasks_material_requests_task_id_foreign` FOREIGN KEY (`task_id`) REFERENCES `production_tasks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `production_tasks_time_entries`
--
ALTER TABLE `production_tasks_time_entries`
  ADD CONSTRAINT `production_tasks_time_foreign` FOREIGN KEY (`task_id`) REFERENCES `production_tasks` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

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
  ADD CONSTRAINT `projects_files_fk` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

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
