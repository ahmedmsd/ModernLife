-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 07, 2025 at 12:17 PM
-- Server version: 8.0.21
-- PHP Version: 8.3.23

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
-- Table structure for table `maintenance_comments`
--

DROP TABLE IF EXISTS `maintenance_comments`;
CREATE TABLE IF NOT EXISTS `maintenance_comments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `maintenance_request_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_comments_maintenance_request_id_foreign` (`maintenance_request_id`),
  KEY `maintenance_comments_user_id_foreign` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_requests`
--

DROP TABLE IF EXISTS `maintenance_requests`;
CREATE TABLE IF NOT EXISTS `maintenance_requests` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `project_id` bigint UNSIGNED NOT NULL,
  `client_id` bigint UNSIGNED NOT NULL,
  `requested_by` bigint UNSIGNED DEFAULT NULL,
  `request_date` date NOT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `images` json DEFAULT NULL,
  `status` enum('new','in_progress','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `current_owner_role` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_owner_user_id` bigint UNSIGNED DEFAULT NULL,
  `sent_to_owner_at` timestamp NULL DEFAULT NULL,
  `received_by_owner_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_requests_project_id_foreign` (`project_id`),
  KEY `maintenance_requests_client_id_foreign` (`client_id`),
  KEY `maintenance_requests_request_date_index` (`request_date`),
  KEY `maintenance_requests_status_index` (`status`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
