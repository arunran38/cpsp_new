-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.44 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for cpsp
CREATE DATABASE IF NOT EXISTS `cpsp` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `cpsp`;

-- Dumping structure for table cpsp.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `address_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `petition_id` bigint unsigned NOT NULL,
  `person_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_type` enum('Complainant','Accused') COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_type` enum('Permanent','Temporary','Office') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Permanent',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Aadhar_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `full_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pincode` varchar(6) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`address_id`),
  KEY `addresses_petition_id_foreign` (`petition_id`),
  CONSTRAINT `addresses_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.addresses: ~43 rows (approximately)
INSERT INTO `addresses` (`address_id`, `petition_id`, `person_name`, `person_type`, `address_type`, `is_primary`, `phone`, `Aadhar_number`, `full_address`, `district`, `pincode`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(5, 3, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '95672324745', 'Arun Nivas', 'TVM', NULL, '2026-03-19 12:10:21', '2026-03-19 12:10:21', NULL),
	(6, 3, 'Kiran', 'Accused', 'Temporary', 1, '123456', NULL, 'Kiran Nivas', 'PTA', NULL, '2026-03-19 12:10:21', '2026-03-19 12:10:21', NULL),
	(8, 5, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '95672324745', 'Arun Nivas', 'TVM', '695122', '2026-03-19 12:19:55', '2026-03-19 12:19:55', NULL),
	(9, 5, 'Kiran', 'Accused', 'Temporary', 1, '123456', NULL, 'Kiran Nivas', 'PTA', '123456', '2026-03-19 12:19:55', '2026-03-19 12:19:55', NULL),
	(10, 6, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'ANu house', 'TVM', '695122', '2026-03-20 16:57:02', '2026-03-20 16:57:02', NULL),
	(11, 6, 'Arun', 'Complainant', 'Temporary', 0, '9567284405', '123456789123', 'Manoj Villa', 'KLM', NULL, '2026-03-20 16:57:02', '2026-03-20 16:57:02', NULL),
	(12, 6, 'Anu', 'Complainant', 'Temporary', 1, '1234567890', '123456789987', 'Anu House', 'TVM', '695123', '2026-03-20 16:57:02', '2026-03-20 16:57:02', NULL),
	(13, 6, 'vinu', 'Accused', 'Temporary', 1, '9876543210', NULL, 'Vinu Nivas', 'PTA', NULL, '2026-03-20 16:57:02', '2026-03-20 16:57:02', NULL),
	(14, 6, 'vinu', 'Accused', 'Temporary', 0, '9876543210', NULL, 'Amal Nivas', 'KKD', '963258', '2026-03-20 16:57:02', '2026-03-20 16:57:02', NULL),
	(15, 7, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'ANu house', 'TVM', NULL, '2026-03-21 04:54:04', '2026-03-21 04:54:04', NULL),
	(16, 7, 'vinu', 'Accused', 'Temporary', 1, '9876543210', '123456789012', 'Vinu Nivas', '', NULL, '2026-03-21 04:54:04', '2026-03-21 04:54:04', NULL),
	(17, 8, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '12345', 'hgytdft6rdftr', '', NULL, '2026-03-21 04:56:37', '2026-03-21 04:56:37', NULL),
	(18, 8, 'aaaaa', 'Accused', 'Temporary', 1, '9876543210', NULL, 'gggggggggggggggggg', '', NULL, '2026-03-21 04:56:37', '2026-03-21 04:56:37', NULL),
	(19, 9, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'ANu house', 'TVM', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54', NULL),
	(20, 9, 'Arun', 'Complainant', 'Temporary', 0, '9567284405', '123456789123', 'Manoj Villa', 'KLM', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54', NULL),
	(21, 9, 'Arun', 'Complainant', 'Temporary', 0, '9567284405', '123456789123', 'aaaaaaaaaaaaaaaaa', 'kkd', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54', NULL),
	(22, 9, 'Anu', 'Complainant', 'Temporary', 1, '1234567890', NULL, 'aaaaaaaaaaaaaaaa', '', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54', NULL),
	(23, 9, 'vinu mohsn', 'Accused', 'Temporary', 1, '9876543210', 'aaaaaaa', 'Vinu Nivas', 'PTA', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54', NULL),
	(26, 11, 'aravind', 'Complainant', 'Temporary', 1, '7032654548', '123456789123555', 'kseb', 'kollam', '695004', '2026-03-23 05:54:53', '2026-03-23 05:54:53', NULL),
	(27, 12, 'nily', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'nily house', 'kollam', '695004', '2026-03-23 05:58:17', '2026-03-23 05:58:17', NULL),
	(28, 12, 'vinu', 'Accused', 'Temporary', 1, '9876543210', '123456789012', 'T1', 'PTA', '695004', '2026-03-23 05:58:17', '2026-03-23 05:58:17', NULL),
	(29, 12, 'vinu', 'Accused', 'Temporary', 0, '9876543210', '123456789012', 'arun nivas', 'KKD', '695004', '2026-03-23 05:58:17', '2026-03-23 05:58:17', NULL),
	(30, 13, 'dfaf', 'Complainant', 'Temporary', 1, '7032654548', '12345', 'dfadfa', 'TVM', '695004', '2026-03-23 10:13:53', '2026-03-23 10:13:53', NULL),
	(31, 15, 'Lijin', 'Complainant', 'Temporary', 1, '1234567890', NULL, 'Lijin Nivas', 'PTA', NULL, '2026-03-25 11:11:29', '2026-03-25 11:11:29', NULL),
	(32, 15, 'Mani', 'Accused', 'Temporary', 1, '9567284405', NULL, 'Mani', 'KLM', NULL, '2026-03-25 11:11:29', '2026-03-25 11:11:29', NULL),
	(57, 28, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', NULL, 'aaaa', 'TVM', NULL, '2026-03-26 09:14:23', '2026-03-26 09:14:23', NULL),
	(58, 28, 'aaaaa', 'Accused', 'Temporary', 1, '9876543210', NULL, 'GW9R+9HJ', '', '695004', '2026-03-26 09:14:23', '2026-03-26 09:14:23', NULL),
	(59, 29, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'arun nivas', 'TVM', '695004', '2026-03-26 09:20:03', '2026-03-26 09:20:03', NULL),
	(60, 29, 'Anu', 'Complainant', 'Temporary', 1, '1234567890', '123456789987', 'Anu Nivas', '', NULL, '2026-03-26 09:20:03', '2026-03-26 09:20:03', NULL),
	(61, 29, 'Kiran', 'Accused', 'Temporary', 1, '1234567890', NULL, 'Kiran House', 'KKD', '695002', '2026-03-26 09:20:03', '2026-03-26 09:20:03', NULL),
	(62, 29, 'Kiran', 'Accused', 'Temporary', 0, '1234567890', NULL, 'Village office', 'WYD', NULL, '2026-03-26 09:20:03', '2026-03-26 09:20:03', NULL),
	(63, 30, 'Arun', 'Complainant', 'Temporary', 1, 'aa', NULL, 'aaa', '', NULL, '2026-03-26 09:43:37', '2026-03-26 09:43:37', NULL),
	(64, 30, 'fdfg', 'Accused', 'Temporary', 1, NULL, NULL, 'gfgggggggggg', '', NULL, '2026-03-26 09:43:37', '2026-03-26 09:43:37', NULL),
	(65, 31, 'nily', 'Complainant', 'Temporary', 1, '9567284405', NULL, 'asdfg', '', NULL, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(66, 31, 'vinu', 'Accused', 'Temporary', 1, NULL, NULL, 'asdf', '', NULL, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(67, 32, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', NULL, 'aaa', '', NULL, '2026-03-27 09:36:55', '2026-03-27 09:36:55', NULL),
	(68, 32, 'vinu', 'Accused', 'Temporary', 1, '9876543210', NULL, 'GW9R+9HJ', '', '695004', '2026-03-27 09:36:55', '2026-03-27 09:36:55', NULL),
	(75, 33, 'Arun', 'Complainant', 'Permanent', 1, '9567284405', '123456789123', 'Arun Nivas', '', '695004', '2026-03-27 10:04:51', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(76, 33, 'aaaaa', 'Accused', 'Permanent', 1, '9876543210', NULL, 'GW9R+9HJ', 'PTA', NULL, '2026-03-27 10:04:51', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(77, 33, 'kinu', 'Accused', 'Office', 1, '956825814', NULL, 'aasdfgfg', '', NULL, '2026-03-27 10:04:51', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(81, 34, 'ddddddddd', 'Complainant', 'Permanent', 1, '9567284405', NULL, 'dddddddddddd', '', NULL, '2026-03-27 10:14:02', '2026-03-28 08:40:25', NULL),
	(82, 34, 'dddddddddd', 'Complainant', 'Office', 1, '1234567890', NULL, 'eeeeeeeee', '', NULL, '2026-03-27 10:14:02', '2026-03-28 08:40:25', NULL),
	(83, 34, 'vinu', 'Accused', 'Office', 1, '9876543210', NULL, 'GW9R+9HJ', '', '695004', '2026-03-27 10:14:02', '2026-03-28 08:40:25', NULL),
	(84, 35, 'Arun', 'Complainant', 'Permanent', 1, '7032654548', NULL, 'vdvv vfdf', '', NULL, '2026-03-28 07:18:11', '2026-03-28 07:18:11', NULL),
	(85, 35, 'aaaaa', 'Accused', 'Permanent', 1, 'cvcvv', NULL, 'ssvvv', '', NULL, '2026-03-28 07:18:11', '2026-03-28 07:18:11', NULL);

-- Dumping structure for table cpsp.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.cache: ~0 rows (approximately)

-- Dumping structure for table cpsp.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.cache_locks: ~0 rows (approximately)

-- Dumping structure for table cpsp.decisions
CREATE TABLE IF NOT EXISTS `decisions` (
  `decision_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `petition_id` bigint unsigned NOT NULL,
  `decided_by_seat_id` bigint unsigned NOT NULL,
  `decision_remarks` enum('PE','SC','QV','Closed','Sent to Govt','ICell') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_remarks` text COLLATE utf8mb4_unicode_ci,
  `decision_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`decision_id`),
  KEY `decisions_petition_id_foreign` (`petition_id`),
  KEY `decisions_decided_by_seat_id_foreign` (`decided_by_seat_id`),
  CONSTRAINT `decisions_decided_by_seat_id_foreign` FOREIGN KEY (`decided_by_seat_id`) REFERENCES `seats` (`seat_id`),
  CONSTRAINT `decisions_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.decisions: ~14 rows (approximately)
INSERT INTO `decisions` (`decision_id`, `petition_id`, `decided_by_seat_id`, `decision_remarks`, `final_remarks`, `decision_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(1, 5, 1, 'PE', 'recommended', '2026-03-20', '2026-03-20 13:38:47', '2026-03-20 13:38:47', NULL),
	(2, 6, 1, 'Closed', 'close', '2026-03-20', '2026-03-20 17:20:14', '2026-03-20 17:20:14', NULL),
	(3, 3, 1, 'PE', 'PE', '2026-03-20', '2026-03-20 17:21:41', '2026-03-20 17:21:41', NULL),
	(4, 7, 1, 'QV', 'recommended', '2026-03-21', '2026-03-21 04:55:09', '2026-03-21 04:55:09', NULL),
	(5, 8, 1, 'Closed', 'close', '2026-03-21', '2026-03-21 05:06:03', '2026-03-21 05:06:03', NULL),
	(6, 9, 3, 'Sent to Govt', 'sent to govt', '2026-03-21', '2026-03-21 05:20:17', '2026-03-21 05:20:17', NULL),
	(7, 14, 3, 'SC', 'yes', '2026-03-24', '2026-03-24 12:25:42', '2026-03-24 12:25:42', NULL),
	(8, 13, 3, 'Closed', 'close', '2026-03-24', '2026-03-24 12:26:46', '2026-03-24 12:26:46', NULL),
	(9, 11, 3, 'QV', 'sss', '2026-03-25', '2026-03-25 05:01:27', '2026-03-25 05:01:27', NULL),
	(10, 12, 3, 'Sent to Govt', 'govy', '2026-03-25', '2026-03-25 07:11:06', '2026-03-25 07:11:06', NULL),
	(11, 15, 3, 'QV', 'aaa', '2026-03-25', '2026-03-25 11:15:28', '2026-03-25 11:15:28', NULL),
	(12, 29, 2, 'Closed', 'close', '2026-03-26', '2026-03-26 09:22:05', '2026-03-26 09:22:05', NULL),
	(13, 28, 2, 'Closed', 'close', '2026-03-26', '2026-03-26 09:22:43', '2026-03-26 09:22:43', NULL),
	(14, 31, 2, 'Closed', 'cccc', '2026-03-27', '2026-03-27 08:12:48', '2026-03-27 08:12:48', NULL),
	(15, 35, 2, 'SC', 'fghj', '2026-03-28', '2026-03-28 07:23:23', '2026-03-28 07:23:23', NULL);

-- Dumping structure for table cpsp.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table cpsp.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.jobs: ~0 rows (approximately)

-- Dumping structure for table cpsp.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.job_batches: ~0 rows (approximately)

-- Dumping structure for table cpsp.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.migrations: ~22 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_03_17_110940_create_units_table', 1),
	(5, '2026_03_17_111059_create_seats_table', 1),
	(6, '2026_03_17_111137_create_seat_users_table', 1),
	(7, '2026_03_17_111322_create_petitions_table', 1),
	(8, '2026_03_17_111358_create_addresses_table', 1),
	(9, '2026_03_17_111438_create_petition_forwardings_table', 1),
	(10, '2026_03_17_111521_create_decisions_table', 1),
	(11, '2026_03_17_111553_create_uploads_table', 1),
	(12, '2026_03_19_104931_create_seat_unit_table', 2),
	(18, '2026_03_19_105008_remove_unit_id_from_seats_table', 3),
	(19, '2026_03_19_110544_add_photo_to_users_table', 3),
	(20, '2026_03_19_110823_make_petition_id_nullable_in_uploads_table', 3),
	(21, '2026_03_19_174359_drop_polymorphic_columns_from_uploads_table', 4),
	(22, '2026_03_20_190132_add_vr_remarks_to_petition_forwardings_table', 5),
	(23, '2026_03_20_225000_update_decision_remarks_enum', 6),
	(24, '2026_03_23_140050_change_district_column_to_json_on_units_table', 7),
	(25, '2026_03_23_140050_change_district_column_units_table', 8),
	(26, '2026_03_26_133148_add_user_and_seat_to_petitions_table', 8),
	(27, '2026_03_27_114602_add_status_and_soft_deletes_to_users_table', 9),
	(28, '2026_03_28_140427_add_deleted_at_to_petitions_and_relations_table', 10);

-- Dumping structure for table cpsp.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table cpsp.petitions
CREATE TABLE IF NOT EXISTS `petitions` (
  `petition_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `seat_id` bigint unsigned DEFAULT NULL,
  `petition_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_petition_received` date NOT NULL,
  `mode_of_petition_received` enum('Email','Whatsapp','Tollfree','Direct','Unit','Tapal','iaps','others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `mode_of_petition_received_others` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nature_of_petition` enum('Bribery','Misuse of authority','Fraud / financial irregularities','Serious negligence','others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `proposed_action` text COLLATE utf8mb4_unicode_ci,
  `status` enum('Received','Forwarded','VR_Received','Sent_to_Govt','Closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Received',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`petition_id`),
  UNIQUE KEY `petitions_petition_no_unique` (`petition_no`),
  KEY `petitions_user_id_foreign` (`user_id`),
  KEY `petitions_seat_id_foreign` (`seat_id`),
  CONSTRAINT `petitions_seat_id_foreign` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE SET NULL,
  CONSTRAINT `petitions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.petitions: ~18 rows (approximately)
INSERT INTO `petitions` (`petition_id`, `user_id`, `seat_id`, `petition_no`, `date_of_petition_received`, `mode_of_petition_received`, `mode_of_petition_received_others`, `nature_of_petition`, `description`, `proposed_action`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(3, NULL, NULL, '100', '2026-03-03', 'Whatsapp', NULL, 'Bribery', 'gtyfdtdctdc', 'gcdtrfdtr', 'Closed', '2026-03-19 12:10:21', '2026-03-20 17:21:41', NULL),
	(5, NULL, NULL, '500/2021', '2026-03-10', 'Tollfree', NULL, 'Bribery', 'dsffdfffffffffffffffffffffffffffff', 'fffffffffffffffff', 'Closed', '2026-03-19 12:19:55', '2026-03-20 13:38:47', NULL),
	(6, NULL, NULL, '26/2026', '2026-03-12', 'Whatsapp', NULL, 'Misuse of authority', 'Bribe against village officer', 'initiate PE', 'Closed', '2026-03-20 16:57:02', '2026-03-20 17:20:14', NULL),
	(7, NULL, NULL, '100/2025', '2026-03-11', 'Tollfree', NULL, 'Fraud / financial irregularities', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', 'pe', 'Closed', '2026-03-21 04:54:04', '2026-03-21 04:55:09', NULL),
	(8, NULL, NULL, '26/2021', '2026-03-17', 'Whatsapp', NULL, 'Misuse of authority', 'uytfdxstrygh', 'dddddddddddddddddddddddddd', 'Closed', '2026-03-21 04:56:37', '2026-03-21 05:06:03', NULL),
	(9, NULL, NULL, '1255454', '2026-03-03', 'others', 'grghthjyh', 'Misuse of authority', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'PE', 'Sent_to_Govt', '2026-03-21 05:18:54', '2026-03-21 05:20:17', NULL),
	(11, NULL, NULL, '1/cpsp/26', '2026-03-23', 'Direct', NULL, 'others', 'petition against officers', 'petition is received and it recommended for further necessary action', 'Closed', '2026-03-23 05:54:53', '2026-03-25 05:01:27', NULL),
	(12, NULL, NULL, '2/cpsp/2026', '2026-03-22', 'Email', NULL, 'Serious negligence', 'petition against cpo', NULL, 'Sent_to_Govt', '2026-03-23 05:58:17', '2026-03-25 07:11:06', NULL),
	(13, NULL, NULL, '12/cpsp/26', '2026-03-23', 'Email', NULL, 'Misuse of authority', 'dfdf', NULL, 'Closed', '2026-03-23 10:13:53', '2026-03-24 12:26:46', NULL),
	(14, NULL, NULL, '28/cpsp/26', '2026-03-23', 'Tollfree', NULL, 'Fraud / financial irregularities', 'dfdsaf', NULL, 'Closed', '2026-03-23 10:14:36', '2026-03-24 12:25:42', NULL),
	(15, NULL, NULL, '789/PT', '2026-03-25', 'Whatsapp', NULL, 'Bribery', 'bribe', 'PE', 'Closed', '2026-03-25 11:11:29', '2026-03-25 11:15:28', NULL),
	(28, NULL, NULL, '123456', '2026-03-26', 'Whatsapp', NULL, 'Bribery', 'aaaa', 'aaa', 'Closed', '2026-03-26 09:14:23', '2026-03-26 09:22:43', NULL),
	(29, NULL, NULL, 'PT 147/2026', '2026-03-26', 'Whatsapp', NULL, 'Misuse of authority', 'Bribe against a village officer', 'Recomended for PE', 'Closed', '2026-03-26 09:20:03', '2026-03-26 09:22:05', NULL),
	(30, NULL, NULL, '1224344', '2026-03-26', 'Direct', NULL, 'Misuse of authority', 'as', 'ggggggggggggggg', 'Received', '2026-03-26 09:43:37', '2026-03-26 09:43:37', NULL),
	(31, 3, 2, '1479/2026', '2026-03-25', 'Tollfree', NULL, 'Bribery', 'asdf', 'PE', 'Closed', '2026-03-26 11:21:00', '2026-03-27 08:12:48', NULL),
	(32, 3, 1, '256', '2026-03-27', 'Whatsapp', NULL, 'Bribery', 'aaaa', 'aaa', 'Received', '2026-03-27 09:36:55', '2026-03-27 09:36:55', NULL),
	(33, 3, 1, '125/2026', '2026-03-27', 'Direct', NULL, 'Fraud / financial irregularities', 'Petition Details', 'PE', 'Received', '2026-03-27 09:41:19', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(34, 3, 1, '124/2026', '2026-03-27', 'iaps', NULL, 'Fraud / financial irregularities', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'eeeeeeeeeeee', 'Received', '2026-03-27 10:06:00', '2026-03-28 08:40:25', NULL),
	(35, 3, 1, '78952214', '2026-03-25', 'Tollfree', NULL, 'Misuse of authority', 'bfhbgfhgfh', 'sssssssssss', 'Closed', '2026-03-28 07:18:11', '2026-03-28 07:23:23', NULL);

-- Dumping structure for table cpsp.petition_forwardings
CREATE TABLE IF NOT EXISTS `petition_forwardings` (
  `petition_forwarding_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `petition_id` bigint unsigned NOT NULL,
  `from_seat_id` bigint unsigned NOT NULL,
  `to_unit_id` bigint unsigned NOT NULL,
  `director_remarks` text COLLATE utf8mb4_unicode_ci,
  `forwarded_date` date NOT NULL,
  `vr_ref_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vr_date` date DEFAULT NULL,
  `vr_remarks` text COLLATE utf8mb4_unicode_ci,
  `vr_received_at_cpsp_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`petition_forwarding_id`),
  KEY `petition_forwardings_petition_id_foreign` (`petition_id`),
  KEY `petition_forwardings_from_seat_id_foreign` (`from_seat_id`),
  KEY `petition_forwardings_to_unit_id_foreign` (`to_unit_id`),
  CONSTRAINT `petition_forwardings_from_seat_id_foreign` FOREIGN KEY (`from_seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE,
  CONSTRAINT `petition_forwardings_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE,
  CONSTRAINT `petition_forwardings_to_unit_id_foreign` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.petition_forwardings: ~4 rows (approximately)
INSERT INTO `petition_forwardings` (`petition_forwarding_id`, `petition_id`, `from_seat_id`, `to_unit_id`, `director_remarks`, `forwarded_date`, `vr_ref_no`, `vr_date`, `vr_remarks`, `vr_received_at_cpsp_date`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(6, 12, 4, 5, 'forwarded', '2026-03-23', '589/2026', '2026-03-09', 'yyy', '2026-03-13', '2026-03-23 06:03:24', '2026-03-24 12:26:11', NULL),
	(7, 14, 3, 7, 'pe', '2026-03-24', '123/2026', '2026-03-11', 'nnnnnnnnnn', '2026-03-17', '2026-03-24 09:27:42', '2026-03-24 11:23:01', NULL),
	(9, 15, 3, 1, 'aaa', '2026-03-25', '203/2026', '2026-03-25', 'aas', '2026-03-26', '2026-03-25 11:12:53', '2026-03-25 11:14:03', NULL),
	(10, 29, 2, 1, 'PE', '2026-03-26', '2/2026', '2026-03-26', 'file closed', '2026-03-26', '2026-03-26 09:20:52', '2026-03-26 09:21:49', NULL),
	(11, 31, 2, 9, 'Investigate', '2026-03-27', '890/2026', '2026-03-27', 'Nothing', '2026-03-27', '2026-03-27 08:11:43', '2026-03-27 08:12:32', NULL),
	(12, 35, 2, 5, 'sss', '2026-03-28', '10/2026', '2026-03-28', 'ssss', '2026-03-28', '2026-03-28 07:18:21', '2026-03-28 07:18:48', NULL);

-- Dumping structure for table cpsp.seats
CREATE TABLE IF NOT EXISTS `seats` (
  `seat_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seat_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`seat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.seats: ~9 rows (approximately)
INSERT INTO `seats` (`seat_id`, `seat_name`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'CPSP I', 1, '2026-03-19 05:26:06', '2026-03-19 05:26:06'),
	(2, 'CPSP III', 1, '2026-03-19 05:28:43', '2026-03-26 04:52:19'),
	(3, 'CPSP III', 1, '2026-03-21 05:11:41', '2026-03-21 05:11:41'),
	(4, 'CPSP IV', 1, '2026-03-23 05:21:15', '2026-03-23 05:21:15'),
	(5, 'CPSP V', 1, '2026-03-23 06:49:41', '2026-03-23 06:49:41'),
	(6, 'CPSP VI', 1, '2026-03-27 06:56:52', '2026-03-27 06:56:52'),
	(7, 'CPSP VII', 1, '2026-03-27 08:04:06', '2026-03-27 08:04:06'),
	(8, 'CPSP VIII', 1, '2026-03-27 08:14:46', '2026-03-27 08:14:46'),
	(9, 'CPSP IX', 1, '2026-03-27 08:17:10', '2026-03-27 08:17:10'),
	(10, 'CPSP X', 1, '2026-03-27 11:05:35', '2026-03-27 11:05:35');

-- Dumping structure for table cpsp.seat_unit
CREATE TABLE IF NOT EXISTS `seat_unit` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seat_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `seat_unit_seat_id_foreign` (`seat_id`),
  KEY `seat_unit_unit_id_foreign` (`unit_id`),
  CONSTRAINT `seat_unit_seat_id_foreign` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE,
  CONSTRAINT `seat_unit_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.seat_unit: ~17 rows (approximately)
INSERT INTO `seat_unit` (`id`, `seat_id`, `unit_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, NULL, NULL),
	(9, 5, 1, NULL, NULL),
	(12, 2, 5, NULL, NULL),
	(13, 3, 8, NULL, NULL),
	(14, 4, 7, NULL, NULL),
	(15, 6, 10, NULL, NULL),
	(16, 3, 1, NULL, NULL),
	(17, 3, 5, NULL, NULL),
	(18, 7, 10, NULL, NULL),
	(19, 7, 9, NULL, NULL),
	(20, 7, 8, NULL, NULL),
	(21, 8, 8, NULL, NULL),
	(22, 8, 9, NULL, NULL),
	(23, 8, 10, NULL, NULL),
	(24, 5, 6, NULL, NULL),
	(25, 9, 6, NULL, NULL),
	(26, 9, 7, NULL, NULL),
	(27, 10, 11, NULL, NULL),
	(28, 10, 12, NULL, NULL),
	(29, 10, 13, NULL, NULL);

-- Dumping structure for table cpsp.seat_users
CREATE TABLE IF NOT EXISTS `seat_users` (
  `seat_user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `seat_id` bigint unsigned NOT NULL,
  `is_additional` tinyint(1) NOT NULL DEFAULT '0',
  `assigned_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`seat_user_id`),
  KEY `seat_users_user_id_foreign` (`user_id`),
  KEY `seat_users_seat_id_foreign` (`seat_id`),
  CONSTRAINT `seat_users_seat_id_foreign` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE,
  CONSTRAINT `seat_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.seat_users: ~24 rows (approximately)
INSERT INTO `seat_users` (`seat_user_id`, `user_id`, `seat_id`, `is_additional`, `assigned_at`, `revoked_at`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 0, '2026-03-19 11:56:31', '2026-03-27 07:07:16', 0, '2026-03-19 11:56:31', '2026-03-27 07:07:16'),
	(2, 1, 2, 1, '2026-03-19 11:56:40', '2026-03-19 11:57:14', 0, '2026-03-19 11:56:40', '2026-03-19 11:57:14'),
	(3, 3, 2, 0, '2026-03-19 11:57:14', '2026-03-27 07:09:58', 0, '2026-03-19 11:57:14', '2026-03-27 07:09:58'),
	(5, 1, 1, 1, '2026-03-21 05:12:45', '2026-03-21 05:13:23', 0, '2026-03-21 05:12:45', '2026-03-21 05:13:23'),
	(6, 5, 3, 0, '2026-03-21 09:38:26', '2026-03-27 07:05:30', 0, '2026-03-21 09:38:26', '2026-03-27 07:05:30'),
	(7, 5, 2, 1, '2026-03-21 09:39:41', '2026-03-21 09:40:16', 0, '2026-03-21 09:39:41', '2026-03-21 09:40:16'),
	(8, 8, 4, 0, '2026-03-23 06:01:54', '2026-03-27 07:17:24', 0, '2026-03-23 06:01:54', '2026-03-27 07:17:24'),
	(9, 8, 5, 1, '2026-03-26 04:55:48', '2026-03-27 07:13:55', 0, '2026-03-26 04:55:48', '2026-03-27 07:13:55'),
	(10, 3, 6, 0, '2026-03-27 07:09:58', '2026-03-27 07:10:12', 0, '2026-03-27 07:09:58', '2026-03-27 07:10:12'),
	(11, 3, 1, 0, '2026-03-27 07:10:12', '2026-03-27 07:13:55', 0, '2026-03-27 07:10:12', '2026-03-27 07:13:55'),
	(12, 3, 5, 0, '2026-03-27 07:13:55', '2026-03-27 07:17:36', 0, '2026-03-27 07:13:55', '2026-03-27 07:17:36'),
	(13, 1, 2, 0, '2026-03-27 07:17:06', NULL, 1, '2026-03-27 07:17:06', '2026-03-27 07:17:06'),
	(14, 10, 4, 0, '2026-03-27 07:17:24', '2026-03-27 08:05:45', 0, '2026-03-27 07:17:24', '2026-03-27 08:05:45'),
	(15, 3, 1, 0, '2026-03-27 07:29:57', NULL, 1, '2026-03-27 07:29:57', '2026-03-27 07:29:57'),
	(16, 5, 3, 0, '2026-03-27 07:30:12', '2026-03-27 08:05:41', 0, '2026-03-27 07:30:12', '2026-03-27 08:05:41'),
	(17, 10, 5, 1, '2026-03-27 07:30:54', '2026-03-27 07:33:40', 0, '2026-03-27 07:30:54', '2026-03-27 07:33:40'),
	(18, 10, 6, 1, '2026-03-27 07:31:20', '2026-03-27 11:06:28', 0, '2026-03-27 07:31:20', '2026-03-27 11:06:28'),
	(19, 3, 5, 1, '2026-03-27 07:39:11', '2026-03-27 07:41:57', 0, '2026-03-27 07:39:11', '2026-03-27 07:41:57'),
	(20, 1, 5, 1, '2026-03-27 07:49:31', NULL, 1, '2026-03-27 07:49:31', '2026-03-27 07:49:31'),
	(21, 10, 7, 1, '2026-03-27 08:04:45', '2026-03-27 08:05:02', 0, '2026-03-27 08:04:45', '2026-03-27 08:05:02'),
	(22, 5, 4, 0, '2026-03-27 08:05:52', '2026-03-27 11:06:51', 0, '2026-03-27 08:05:52', '2026-03-27 11:06:51'),
	(23, 10, 3, 0, '2026-03-27 08:05:56', NULL, 1, '2026-03-27 08:05:56', '2026-03-27 08:05:56'),
	(24, 1, 7, 1, '2026-03-27 08:06:55', NULL, 1, '2026-03-27 08:06:55', '2026-03-27 08:06:55'),
	(25, 10, 8, 1, '2026-03-27 08:15:18', NULL, 1, '2026-03-27 08:15:18', '2026-03-27 08:15:18'),
	(26, 10, 10, 1, '2026-03-27 11:06:11', '2026-03-27 11:06:18', 0, '2026-03-27 11:06:11', '2026-03-27 11:06:18'),
	(27, 13, 6, 0, '2026-03-27 11:06:34', NULL, 1, '2026-03-27 11:06:34', '2026-03-27 11:06:34'),
	(28, 5, 9, 0, '2026-03-27 11:07:07', NULL, 1, '2026-03-27 11:07:07', '2026-03-27 11:07:07'),
	(29, 5, 10, 1, '2026-03-27 11:07:23', NULL, 1, '2026-03-27 11:07:23', '2026-03-27 11:07:23'),
	(30, 13, 4, 1, '2026-03-28 07:51:36', '2026-03-28 07:51:46', 0, '2026-03-28 07:51:36', '2026-03-28 07:51:46');

-- Dumping structure for table cpsp.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.sessions: ~1 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('cL2sp4md4drdTACvOEYkYVLU45FrQJUMk5M7Y77E', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJYUzJiMGVNNW9qSjJhbzluWnF0dHNuUE5NalRsa01mcjd6ZXNlVmxiIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImFkbWluLmRhc2hib2FyZCJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1774688606),
	('KKHFzLsyEnq4fIaoNPsz8bDdnSvsdynNXBBsAD5s', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJVTUZCTnRLcG9yOWVDUFRIM2pwQXplN25rc2d5WWFyVThwdU9oY2xQIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDAiLCJyb3V0ZSI6bnVsbH19', 1774689107),
	('RQvJee1DBIAAhN6l3BPFJv1EivvZhJnrHuqe1qsM', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJNTHhmTVhDOTc4Rjd1ZzByRXdYblBtN2ozaEJMTmVWUlVsbXRTUm1zIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC9kYXNoYm9hcmQifSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9sb2dpbiIsInJvdXRlIjoibG9naW4ifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1774688934),
	('uzIo9msqdnX3yWarqnr7yhOSwzqYG9nhOmrAkM6B', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJRVG1Pbzl3cmZtTlNNQkViNFNTUVJNNms5dTNVcTQ1YkR6SFpGa2NGIiwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2FkbWluXC90cmFzaCJ9LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3BldGl0aW9ucyIsInJvdXRlIjoicGV0aXRpb25zLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjN9', 1774688448),
	('YnnE7n60U1xh01DEONBiv9NN1i1J23T4TsgTSlgx', 3, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIxSXd5MjZ4WVVJdlIzYzJVRmRNejFNMHRvTU9kd1FtMHRVREtlYk9vIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvY3BzcC50ZXN0XC9wZXRpdGlvbnNcL2NyZWF0ZSIsInJvdXRlIjoicGV0aXRpb25zLmNyZWF0ZSJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6M30=', 1774679261);

-- Dumping structure for table cpsp.units
CREATE TABLE IF NOT EXISTS `units` (
  `unit_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `units_unit_code_unique` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.units: ~11 rows (approximately)
INSERT INTO `units` (`unit_id`, `unit_name`, `unit_code`, `created_at`, `updated_at`) VALUES
	(1, 'Special Investigation Unit I', 'SIU I', '2026-03-19 04:44:37', '2026-03-19 04:44:37'),
	(5, 'SOUTHERN RANGE, TVPM', 'SRT', '2026-03-21 06:35:05', '2026-03-21 06:35:05'),
	(6, 'EASTERN RANGE, KOTTAYAM', 'ERK', '2026-03-23 05:19:45', '2026-03-23 05:19:45'),
	(7, 'CENTRAL RANGE, ERNAKULAM', 'CRE', '2026-03-23 05:20:19', '2026-03-23 05:20:19'),
	(8, 'NORTHERN RANGE, KOZHIKKODE', 'NRK', '2026-03-23 06:00:19', '2026-03-23 08:50:12'),
	(9, 'Thiruvananthapuram', 'TVM', '2026-03-26 11:18:03', '2026-03-26 11:18:10'),
	(10, 'Kasargod Unit', 'KSD', '2026-03-27 06:56:24', '2026-03-27 06:56:24'),
	(11, 'kozhikode', 'KKD', '2026-03-27 09:05:40', '2026-03-27 09:10:37'),
	(12, 'MPM', 'MPM', '2026-03-27 09:05:53', '2026-03-27 09:05:53'),
	(13, 'TSR', 'TSR', '2026-03-27 09:06:03', '2026-03-27 09:06:03'),
	(14, 'KTM', 'KTM', '2026-03-27 09:06:15', '2026-03-27 09:06:15');

-- Dumping structure for table cpsp.uploads
CREATE TABLE IF NOT EXISTS `uploads` (
  `upload_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `petition_id` bigint unsigned DEFAULT NULL,
  `category` enum('Profile Photo','Petition Document','Verification Report','Final Order','Others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`upload_id`),
  KEY `uploads_petition_id_foreign` (`petition_id`),
  KEY `uploads_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `uploads_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE,
  CONSTRAINT `uploads_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.uploads: ~40 rows (approximately)
INSERT INTO `uploads` (`upload_id`, `petition_id`, `category`, `original_filename`, `file_path`, `uploaded_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(4, 3, 'Petition Document', 'ACK858641290220724.pdf', 'petitions/3/1773922221_ACK858641290220724.pdf', 1, '2026-03-19 12:10:21', '2026-03-19 12:10:21', NULL),
	(5, 5, 'Petition Document', 'ACK680698850160825.pdf', 'petitions/5/1773922795_ACK680698850160825.pdf', 1, '2026-03-19 12:19:55', '2026-03-19 12:19:55', NULL),
	(6, 6, 'Petition Document', 'CARE PLUS II.pdf', 'petitions/6/1774025822_CARE PLUS II.pdf', NULL, '2026-03-20 16:57:03', '2026-03-27 06:02:22', NULL),
	(7, 3, 'Verification Report', 'DocScanner 18 Mar 2026 12-11 pm.pdf', 'petitions/vr/gBMvtAmO7TelqFhtLSOP4Rc7FmBHxY2QmifAjCyp.pdf', NULL, '2026-03-20 17:21:08', '2026-03-27 06:02:22', NULL),
	(8, 7, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/7/1774068844_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', NULL, '2026-03-21 04:54:05', '2026-03-27 06:02:22', NULL),
	(9, 7, 'Verification Report', 'WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 'petitions/vr/5Om3frajI2T3rOCEqNoIj3bIpsI8t3bcrnjyh2Zj.jpg', NULL, '2026-03-21 04:54:54', '2026-03-27 06:02:22', NULL),
	(10, 9, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/9/1774070334_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', NULL, '2026-03-21 05:18:54', '2026-03-27 06:02:22', NULL),
	(11, 9, 'Verification Report', 'WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 'petitions/vr/t22aWpYxucjsFRdsYtdwJDHBjh6ZwWQZuZwJtPv5.jpg', NULL, '2026-03-21 05:19:53', '2026-03-27 06:02:22', NULL),
	(13, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/a6vLW08BUKYZyqdmPeFX5vc1Lv7nCatIQE7G5V11.jpg', 5, '2026-03-21 07:05:10', '2026-03-21 07:05:10', NULL),
	(14, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/28G251z9uQA30f0XidjzXlj8DotPQTh9QQFpp4d8.jpg', 5, '2026-03-21 07:20:22', '2026-03-21 07:20:22', NULL),
	(15, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/oZrwEPzQo6eirqlRVQGqHPJAf0qPO1Gy8uWpOWb4.jpg', 5, '2026-03-21 07:21:07', '2026-03-21 07:21:07', NULL),
	(16, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/SZ1R77T1awFf5Y7Vidk99nG6VmocajL4QQyVJHZ8.jpg', 5, '2026-03-21 07:21:17', '2026-03-21 07:21:17', NULL),
	(17, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/722ZlzLDFQPgdHANxk9p5ovPQ28fpkyb9kOSKec0.jpg', 1, '2026-03-21 08:26:43', '2026-03-21 08:26:43', NULL),
	(18, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/hImptSOPsQZcI5v2DdBZTvQs58wrcoSvrFDEf6ce.jpg', 8, '2026-03-23 05:47:26', '2026-03-23 05:47:26', NULL),
	(19, 13, 'Petition Document', 'DocScanner 18 Mar 2026 12-11 pm.pdf', 'petitions/13/1774260833_DocScanner 18 Mar 2026 12-11 pm.pdf', 1, '2026-03-23 10:13:53', '2026-03-23 10:13:53', NULL),
	(20, 14, 'Verification Report', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/vr/e5gHmJGnLA407DnBW0WUDCezD9dFRp8G0pLcKEZ4.jpg', NULL, '2026-03-24 11:23:02', '2026-03-27 06:02:22', NULL),
	(21, 12, 'Verification Report', 'WhatsApp Image 2026-03-20 at 6.23.32 PM.jpeg', 'petitions/vr/7RtTL01ch9pg9OLyYRM87C44LhwjFuWTMN0Oe2Hc.jpg', NULL, '2026-03-24 12:26:11', '2026-03-27 06:02:22', NULL),
	(22, 28, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/28/1774516463_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 3, '2026-03-26 09:14:24', '2026-03-26 09:14:24', NULL),
	(23, 29, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/29/1774516803_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 3, '2026-03-26 09:20:03', '2026-03-26 09:20:03', NULL),
	(24, 30, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 'petitions/30/1774518217_WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 3, '2026-03-26 09:43:37', '2026-03-26 09:43:37', NULL),
	(25, 30, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 'petitions/30/1774518217_WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 3, '2026-03-26 09:43:37', '2026-03-26 09:43:37', NULL),
	(26, 30, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'petitions/30/1774518217_WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 3, '2026-03-26 09:43:37', '2026-03-26 09:43:37', NULL),
	(27, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/1774521419_profile.jpeg', 10, '2026-03-26 10:36:59', '2026-03-26 10:36:59', NULL),
	(28, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'profile_photos/1774521430_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 10, '2026-03-26 10:37:10', '2026-03-26 10:37:10', NULL),
	(29, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/1774523917_profile.jpeg', 10, '2026-03-26 11:18:37', '2026-03-26 11:18:37', NULL),
	(30, 31, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.10.46 PM.jpeg', 'petitions/31/1774524060_WhatsApp Image 2026-03-20 at 9.10.46 PM.jpeg', 3, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(31, 31, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 'petitions/31/1774524060_WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 3, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(32, 31, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.32 PM.jpeg', 'petitions/31/1774524060_WhatsApp Image 2026-03-20 at 6.23.32 PM.jpeg', 3, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(33, 31, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 'petitions/31/1774524060_WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 3, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(34, 31, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'petitions/31/1774524060_WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 3, '2026-03-26 11:21:00', '2026-03-26 11:21:00', NULL),
	(35, NULL, 'Profile Photo', 'images.png', 'profile_photos/1774590946_images.png', NULL, '2026-03-27 05:55:46', '2026-03-27 06:01:16', NULL),
	(37, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 'profile_photos/1774593319_WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 5, '2026-03-27 06:35:19', '2026-03-27 06:35:19', NULL),
	(38, 31, 'Verification Report', 'Office-Order_A2-32901_2025_DVACB-555 (1).pdf', 'petitions/vr/QRboylekee7A0hly4hmyYybvBOXN1kcpIioSVyXv.pdf', 3, '2026-03-27 08:12:32', '2026-03-27 08:12:32', NULL),
	(39, NULL, 'Profile Photo', 'images.png', 'profile_photos/1774602690_images.png', 13, '2026-03-27 09:11:30', '2026-03-27 09:11:30', NULL),
	(40, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/1774602713_WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 13, '2026-03-27 09:11:53', '2026-03-27 09:11:53', NULL),
	(41, 33, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 'petitions/33/1774604479_WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 3, '2026-03-27 09:41:19', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(42, 33, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.32 PM.jpeg', 'petitions/33/1774604479_WhatsApp Image 2026-03-20 at 6.23.32 PM.jpeg', 3, '2026-03-27 09:41:19', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(43, 33, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 'petitions/33/1774604479_WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 3, '2026-03-27 09:41:19', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(44, 33, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'petitions/33/1774604479_WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 3, '2026-03-27 09:41:19', '2026-03-28 08:40:15', '2026-03-28 08:40:15'),
	(46, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/1774611334_profile_1.jpeg', 1, '2026-03-27 11:35:35', '2026-03-27 11:35:35', NULL),
	(48, NULL, 'Others', 'test', 'test_file.jpg', 1, '2026-03-28 06:56:54', '2026-03-28 06:56:54', NULL),
	(49, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/1774681144_profile_1.jpeg', 1, '2026-03-28 06:59:05', '2026-03-28 06:59:05', NULL),
	(51, NULL, 'Profile Photo', 'Virat-Kohli-for-RCB-e1774149206738.jpg', 'profile_photos/1774681244_profile_3.jpg', 3, '2026-03-28 07:00:44', '2026-03-28 07:00:44', NULL),
	(52, 35, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/35/1774682291_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 3, '2026-03-28 07:18:11', '2026-03-28 07:18:11', NULL),
	(53, 35, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.10.46 PM.jpeg', 'petitions/35/1774682291_WhatsApp Image 2026-03-20 at 9.10.46 PM.jpeg', 3, '2026-03-28 07:18:11', '2026-03-28 07:18:11', NULL),
	(54, 35, 'Petition Document', 'WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 'petitions/35/1774682291_WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 3, '2026-03-28 07:18:11', '2026-03-28 07:18:11', NULL),
	(55, 35, 'Verification Report', 'profile.jpeg', 'petitions/vr/lqo6tbMI2mViAc7cyXiaa0qpyGkZ7otAJNRP6M7y.jpg', 3, '2026-03-28 07:18:48', '2026-03-28 07:18:48', NULL),
	(56, 35, 'Final Order', 'CARE PLUS II.pdf', 'Uploads/EOLxmhqiSPmhtr24NOZVZ9IXRYSkV7m9bLmeXe3Z.pdf', 3, '2026-03-28 07:23:23', '2026-03-28 07:23:23', NULL);

-- Dumping structure for table cpsp.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pen` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `status` enum('Active','Transferred') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Active',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` enum('CPO','SCPO','ASI','SI','IP','Others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `other_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `photo` bigint unsigned DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_pen_unique` (`pen`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.users: ~7 rows (approximately)
INSERT INTO `users` (`user_id`, `pen`, `name`, `role`, `status`, `email`, `mobile_number`, `password`, `designation`, `other_designation`, `remember_token`, `created_at`, `updated_at`, `photo`, `deleted_at`) VALUES
	(1, 987654, 'Admin', 'admin', 'Active', 'admin795022@example.com', '1234567890', '$2y$12$dExQaRnSJsnIhak5lN3kHuuBgTn1fXj6UgQrk.YuTCrqlzWAnqclC', 'ASI', NULL, NULL, '2026-03-19 06:01:29', '2026-03-28 06:59:05', 49, NULL),
	(3, 123456, 'arun', 'user', 'Active', 'vishn000u@gmail.com', '9567284405', '$2y$12$sT1UWRrrGdYH/mKs1pj5MuYLuB74YxUTbZT8qDdwQNjFgyUcpn8te', 'CPO', NULL, NULL, '2026-03-19 06:05:39', '2026-03-28 07:00:44', 51, NULL),
	(5, 764186, 'Aswthy Mohan', 'user', 'Active', 'aswathymohan@gmail.com', '7034607883', '$2y$12$6u5rPyb3x8ywk.zMkY21Ce6iX71anfzuCSIwqs0OAQvD5zT3hCJNq', 'CPO', NULL, NULL, '2026-03-21 07:05:10', '2026-03-27 07:06:05', 37, NULL),
	(8, 101010, 'Aswthy Mohan', 'user', 'Active', 'aswathymohan1@gmail.com', '7034607883', '$2y$12$Tws0V4aCR4TJL9Lqq/JGFOOa5BT2kdiW5DDmY8YKc9nMcm0yz.6Oe', 'CPO', NULL, NULL, '2026-03-23 05:47:26', '2026-03-27 06:23:57', 18, '2026-03-27 06:23:57'),
	(9, 999999, 'Admin Test', 'admin', 'Active', 'admin_test@example.com', '1234567890', '$2y$12$Nf4Asmc0DhD86VU3z5DONO.OCDr5I2naV7S2O5pXagLMoPA34Aa0.', 'CPO', NULL, NULL, '2026-03-24 12:49:01', '2026-03-27 06:27:38', NULL, '2026-03-27 06:27:38'),
	(10, 456789, 'Lijin', 'user', 'Active', 'lijin@gmail.com', '9876543210', '$2y$12$VzV62v5no.Vd12SZpY6iYen6p3K3rMk9cF5TV2OhqF6p8b/nhjrVS', 'CPO', NULL, NULL, '2026-03-26 10:36:59', '2026-03-26 11:18:37', 29, NULL),
	(13, 852369, 'Vishnu Sarath', 'user', 'Active', 'vishnu@gmail.com', '9633398416', '$2y$12$nCzoqHEd26OplGDi0wQHbePvPeGsPXCfqnjorY1TNqYITn9QdbCmi', 'CPO', NULL, NULL, '2026-03-27 09:11:30', '2026-03-27 09:11:53', 40, NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
