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
  PRIMARY KEY (`address_id`),
  KEY `addresses_petition_id_foreign` (`petition_id`),
  CONSTRAINT `addresses_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.addresses: ~23 rows (approximately)
INSERT INTO `addresses` (`address_id`, `petition_id`, `person_name`, `person_type`, `address_type`, `is_primary`, `phone`, `Aadhar_number`, `full_address`, `district`, `pincode`, `created_at`, `updated_at`) VALUES
	(5, 3, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '95672324745', 'Arun Nivas', 'TVM', NULL, '2026-03-19 12:10:21', '2026-03-19 12:10:21'),
	(6, 3, 'Kiran', 'Accused', 'Temporary', 1, '123456', NULL, 'Kiran Nivas', 'PTA', NULL, '2026-03-19 12:10:21', '2026-03-19 12:10:21'),
	(8, 5, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '95672324745', 'Arun Nivas', 'TVM', '695122', '2026-03-19 12:19:55', '2026-03-19 12:19:55'),
	(9, 5, 'Kiran', 'Accused', 'Temporary', 1, '123456', NULL, 'Kiran Nivas', 'PTA', '123456', '2026-03-19 12:19:55', '2026-03-19 12:19:55'),
	(10, 6, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'ANu house', 'TVM', '695122', '2026-03-20 16:57:02', '2026-03-20 16:57:02'),
	(11, 6, 'Arun', 'Complainant', 'Temporary', 0, '9567284405', '123456789123', 'Manoj Villa', 'KLM', NULL, '2026-03-20 16:57:02', '2026-03-20 16:57:02'),
	(12, 6, 'Anu', 'Complainant', 'Temporary', 1, '1234567890', '123456789987', 'Anu House', 'TVM', '695123', '2026-03-20 16:57:02', '2026-03-20 16:57:02'),
	(13, 6, 'vinu', 'Accused', 'Temporary', 1, '9876543210', NULL, 'Vinu Nivas', 'PTA', NULL, '2026-03-20 16:57:02', '2026-03-20 16:57:02'),
	(14, 6, 'vinu', 'Accused', 'Temporary', 0, '9876543210', NULL, 'Amal Nivas', 'KKD', '963258', '2026-03-20 16:57:02', '2026-03-20 16:57:02'),
	(15, 7, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'ANu house', 'TVM', NULL, '2026-03-21 04:54:04', '2026-03-21 04:54:04'),
	(16, 7, 'vinu', 'Accused', 'Temporary', 1, '9876543210', '123456789012', 'Vinu Nivas', '', NULL, '2026-03-21 04:54:04', '2026-03-21 04:54:04'),
	(17, 8, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '12345', 'hgytdft6rdftr', '', NULL, '2026-03-21 04:56:37', '2026-03-21 04:56:37'),
	(18, 8, 'aaaaa', 'Accused', 'Temporary', 1, '9876543210', NULL, 'gggggggggggggggggg', '', NULL, '2026-03-21 04:56:37', '2026-03-21 04:56:37'),
	(19, 9, 'Arun', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'ANu house', 'TVM', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54'),
	(20, 9, 'Arun', 'Complainant', 'Temporary', 0, '9567284405', '123456789123', 'Manoj Villa', 'KLM', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54'),
	(21, 9, 'Arun', 'Complainant', 'Temporary', 0, '9567284405', '123456789123', 'aaaaaaaaaaaaaaaaa', 'kkd', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54'),
	(22, 9, 'Anu', 'Complainant', 'Temporary', 1, '1234567890', NULL, 'aaaaaaaaaaaaaaaa', '', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54'),
	(23, 9, 'vinu mohsn', 'Accused', 'Temporary', 1, '9876543210', 'aaaaaaa', 'Vinu Nivas', 'PTA', NULL, '2026-03-21 05:18:54', '2026-03-21 05:18:54'),
	(26, 11, 'aravind', 'Complainant', 'Temporary', 1, '7032654548', '123456789123555', 'kseb', 'kollam', '695004', '2026-03-23 05:54:53', '2026-03-23 05:54:53'),
	(27, 12, 'nily', 'Complainant', 'Temporary', 1, '9567284405', '123456789123', 'nily house', 'kollam', '695004', '2026-03-23 05:58:17', '2026-03-23 05:58:17'),
	(28, 12, 'vinu', 'Accused', 'Temporary', 1, '9876543210', '123456789012', 'T1', 'PTA', '695004', '2026-03-23 05:58:17', '2026-03-23 05:58:17'),
	(29, 12, 'vinu', 'Accused', 'Temporary', 0, '9876543210', '123456789012', 'arun nivas', 'KKD', '695004', '2026-03-23 05:58:17', '2026-03-23 05:58:17'),
	(30, 13, 'dfaf', 'Complainant', 'Temporary', 1, '7032654548', '12345', 'dfadfa', 'TVM', '695004', '2026-03-23 10:13:53', '2026-03-23 10:13:53');

-- Dumping structure for table cpsp.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.cache: ~4 rows (approximately)
INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-764186|127.0.0.1', 'i:2;', 1774242647),
	('laravel-cache-764186|127.0.0.1:timer', 'i:1774242647;', 1774242647),
	('laravel-cache-795022|127.0.0.1', 'i:1;', 1774081192),
	('laravel-cache-795022|127.0.0.1:timer', 'i:1774081192;', 1774081192);

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
  PRIMARY KEY (`decision_id`),
  KEY `decisions_petition_id_foreign` (`petition_id`),
  KEY `decisions_decided_by_seat_id_foreign` (`decided_by_seat_id`),
  CONSTRAINT `decisions_decided_by_seat_id_foreign` FOREIGN KEY (`decided_by_seat_id`) REFERENCES `seats` (`seat_id`),
  CONSTRAINT `decisions_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.decisions: ~6 rows (approximately)
INSERT INTO `decisions` (`decision_id`, `petition_id`, `decided_by_seat_id`, `decision_remarks`, `final_remarks`, `decision_date`, `created_at`, `updated_at`) VALUES
	(1, 5, 1, 'PE', 'recommended', '2026-03-20', '2026-03-20 13:38:47', '2026-03-20 13:38:47'),
	(2, 6, 1, 'Closed', 'close', '2026-03-20', '2026-03-20 17:20:14', '2026-03-20 17:20:14'),
	(3, 3, 1, 'PE', 'PE', '2026-03-20', '2026-03-20 17:21:41', '2026-03-20 17:21:41'),
	(4, 7, 1, 'QV', 'recommended', '2026-03-21', '2026-03-21 04:55:09', '2026-03-21 04:55:09'),
	(5, 8, 1, 'Closed', 'close', '2026-03-21', '2026-03-21 05:06:03', '2026-03-21 05:06:03'),
	(6, 9, 3, 'Sent to Govt', 'sent to govt', '2026-03-21', '2026-03-21 05:20:17', '2026-03-21 05:20:17');

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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.migrations: ~19 rows (approximately)
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
	(24, '2026_03_23_140050_change_district_column_to_json_on_units_table', 7);

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
  PRIMARY KEY (`petition_id`),
  UNIQUE KEY `petitions_petition_no_unique` (`petition_no`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.petitions: ~10 rows (approximately)
INSERT INTO `petitions` (`petition_id`, `petition_no`, `date_of_petition_received`, `mode_of_petition_received`, `mode_of_petition_received_others`, `nature_of_petition`, `description`, `proposed_action`, `status`, `created_at`, `updated_at`) VALUES
	(3, '100', '2026-03-03', 'Whatsapp', NULL, 'Bribery', 'gtyfdtdctdc', 'gcdtrfdtr', 'Closed', '2026-03-19 12:10:21', '2026-03-20 17:21:41'),
	(5, '500/2021', '2026-03-10', 'Tollfree', NULL, 'Bribery', 'dsffdfffffffffffffffffffffffffffff', 'fffffffffffffffff', 'Closed', '2026-03-19 12:19:55', '2026-03-20 13:38:47'),
	(6, '26/2026', '2026-03-12', 'Whatsapp', NULL, 'Misuse of authority', 'Bribe against village officer', 'initiate PE', 'Closed', '2026-03-20 16:57:02', '2026-03-20 17:20:14'),
	(7, '100/2025', '2026-03-11', 'Tollfree', NULL, 'Fraud / financial irregularities', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', 'pe', 'Closed', '2026-03-21 04:54:04', '2026-03-21 04:55:09'),
	(8, '26/2021', '2026-03-17', 'Whatsapp', NULL, 'Misuse of authority', 'uytfdxstrygh', 'dddddddddddddddddddddddddd', 'Closed', '2026-03-21 04:56:37', '2026-03-21 05:06:03'),
	(9, '1255454', '2026-03-03', 'others', 'grghthjyh', 'Misuse of authority', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaa', 'PE', 'Sent_to_Govt', '2026-03-21 05:18:54', '2026-03-21 05:20:17'),
	(11, '1/cpsp/26', '2026-03-23', 'Direct', NULL, 'others', 'petition against officers', 'petition is received and it recommended for further necessary action', 'Received', '2026-03-23 05:54:53', '2026-03-23 05:54:53'),
	(12, '2/cpsp/2026', '2026-03-22', 'Email', NULL, 'Serious negligence', 'petition against cpo', NULL, 'Forwarded', '2026-03-23 05:58:17', '2026-03-23 06:03:24'),
	(13, '12/cpsp/26', '2026-03-23', 'Email', NULL, 'Misuse of authority', 'dfdf', NULL, 'Received', '2026-03-23 10:13:53', '2026-03-23 10:13:53'),
	(14, '28/cpsp/26', '2026-03-23', 'Tollfree', NULL, 'Fraud / financial irregularities', 'dfdsaf', NULL, 'Forwarded', '2026-03-23 10:14:36', '2026-03-24 09:27:42');

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
  PRIMARY KEY (`petition_forwarding_id`),
  KEY `petition_forwardings_petition_id_foreign` (`petition_id`),
  KEY `petition_forwardings_from_seat_id_foreign` (`from_seat_id`),
  KEY `petition_forwardings_to_unit_id_foreign` (`to_unit_id`),
  CONSTRAINT `petition_forwardings_from_seat_id_foreign` FOREIGN KEY (`from_seat_id`) REFERENCES `seats` (`seat_id`) ON DELETE CASCADE,
  CONSTRAINT `petition_forwardings_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE,
  CONSTRAINT `petition_forwardings_to_unit_id_foreign` FOREIGN KEY (`to_unit_id`) REFERENCES `units` (`unit_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.petition_forwardings: ~5 rows (approximately)
INSERT INTO `petition_forwardings` (`petition_forwarding_id`, `petition_id`, `from_seat_id`, `to_unit_id`, `director_remarks`, `forwarded_date`, `vr_ref_no`, `vr_date`, `vr_remarks`, `vr_received_at_cpsp_date`, `created_at`, `updated_at`) VALUES
	(2, 3, 1, 4, 'CCC', '2026-03-20', '1/2026', '2026-03-05', 'vE', NULL, '2026-03-20 16:04:31', '2026-03-20 17:21:08'),
	(3, 7, 1, 4, 'pe recommended', '2026-03-21', '10/2026', '2026-03-12', 'execute', NULL, '2026-03-21 04:54:30', '2026-03-21 04:54:54'),
	(4, 9, 3, 4, 'pe', '2026-03-21', '2/2025', '2026-03-13', 'remarks', NULL, '2026-03-21 05:19:27', '2026-03-21 05:19:53'),
	(6, 12, 4, 5, 'forwarded', '2026-03-23', NULL, NULL, NULL, NULL, '2026-03-23 06:03:24', '2026-03-23 06:03:24'),
	(7, 14, 3, 7, 'pe', '2026-03-24', NULL, NULL, NULL, NULL, '2026-03-24 09:27:42', '2026-03-24 09:27:42');

-- Dumping structure for table cpsp.seats
CREATE TABLE IF NOT EXISTS `seats` (
  `seat_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `seat_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`seat_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.seats: ~5 rows (approximately)
INSERT INTO `seats` (`seat_id`, `seat_name`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'CPSP I', 1, '2026-03-19 05:26:06', '2026-03-19 05:26:06'),
	(2, 'CPSP II', 1, '2026-03-19 05:28:43', '2026-03-19 05:28:43'),
	(3, 'CPSP III', 1, '2026-03-21 05:11:41', '2026-03-21 05:11:41'),
	(4, 'CPSP IV', 1, '2026-03-23 05:21:15', '2026-03-23 05:21:15'),
	(5, 'CPSP V', 1, '2026-03-23 06:49:41', '2026-03-23 06:49:41');

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.seat_unit: ~7 rows (approximately)
INSERT INTO `seat_unit` (`id`, `seat_id`, `unit_id`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, NULL, NULL),
	(3, 2, 4, NULL, NULL),
	(4, 3, 1, NULL, NULL),
	(6, 3, 3, NULL, NULL),
	(8, 4, 5, NULL, NULL),
	(9, 5, 1, NULL, NULL),
	(10, 3, 4, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.seat_users: ~8 rows (approximately)
INSERT INTO `seat_users` (`seat_user_id`, `user_id`, `seat_id`, `is_additional`, `assigned_at`, `revoked_at`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 1, 1, 0, '2026-03-19 11:56:31', NULL, 1, '2026-03-19 11:56:31', '2026-03-19 11:56:31'),
	(2, 1, 2, 1, '2026-03-19 11:56:40', '2026-03-19 11:57:14', 0, '2026-03-19 11:56:40', '2026-03-19 11:57:14'),
	(3, 3, 2, 0, '2026-03-19 11:57:14', NULL, 1, '2026-03-19 11:57:14', '2026-03-21 09:40:16'),
	(4, 4, 3, 0, '2026-03-21 05:12:16', '2026-03-21 09:38:26', 0, '2026-03-21 05:12:16', '2026-03-21 09:38:26'),
	(5, 1, 1, 1, '2026-03-21 05:12:45', '2026-03-21 05:13:23', 0, '2026-03-21 05:12:45', '2026-03-21 05:13:23'),
	(6, 5, 3, 0, '2026-03-21 09:38:26', NULL, 1, '2026-03-21 09:38:26', '2026-03-21 09:38:26'),
	(7, 5, 2, 1, '2026-03-21 09:39:41', '2026-03-21 09:40:16', 0, '2026-03-21 09:39:41', '2026-03-21 09:40:16'),
	(8, 8, 4, 0, '2026-03-23 06:01:54', NULL, 1, '2026-03-23 06:01:54', '2026-03-23 06:01:54');

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
	('Z4lpKXn1zzeFcjIcbs14JNCU4nVmQje8EBtLcly9', 4, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ3R2JXeTVjZXk1MmczSGFUMnhyRnJ2aVB2YjRpUWVmb1V2SHNSQU5UIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3BldGl0aW9ucyIsInJvdXRlIjoicGV0aXRpb25zLmluZGV4In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo0fQ==', 1774346440);

-- Dumping structure for table cpsp.units
CREATE TABLE IF NOT EXISTS `units` (
  `unit_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `district` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`unit_id`),
  UNIQUE KEY `units_unit_code_unique` (`unit_code`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.units: ~7 rows (approximately)
INSERT INTO `units` (`unit_id`, `unit_name`, `unit_code`, `created_at`, `updated_at`, `district`) VALUES
	(1, 'Special Investigation Unit I', 'SIU I', '2026-03-19 04:44:37', '2026-03-19 04:44:37', NULL),
	(3, 'TVM', 'TVM', '2026-03-19 05:25:45', '2026-03-19 05:25:45', NULL),
	(4, 'KLM', 'KLM', '2026-03-19 05:25:52', '2026-03-19 05:25:52', NULL),
	(5, 'SOUTHERN RANGE, TVPM', 'SRT', '2026-03-21 06:35:05', '2026-03-21 06:35:05', NULL),
	(6, 'EASTERN RANGE, KOTTAYAM', 'ERK', '2026-03-23 05:19:45', '2026-03-23 05:19:45', NULL),
	(7, 'CENTRAL RANGE, ERNAKULAM', 'CRE', '2026-03-23 05:20:19', '2026-03-23 05:20:19', NULL),
	(8, 'NORTHERN RANGE, KOZHIKKODE', 'NRK', '2026-03-23 06:00:19', '2026-03-23 08:50:12', '["Malappuram","Kozhikode"]');

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
  PRIMARY KEY (`upload_id`),
  KEY `uploads_petition_id_foreign` (`petition_id`),
  KEY `uploads_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `uploads_petition_id_foreign` FOREIGN KEY (`petition_id`) REFERENCES `petitions` (`petition_id`) ON DELETE CASCADE,
  CONSTRAINT `uploads_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.uploads: ~16 rows (approximately)
INSERT INTO `uploads` (`upload_id`, `petition_id`, `category`, `original_filename`, `file_path`, `uploaded_by`, `created_at`, `updated_at`) VALUES
	(3, NULL, 'Profile Photo', 'login-bg.jpg', 'profile_photos/xpLtLFK1uFrGPgAlQgQyDKCF16YiFe7Z8WI3tP1k.jpg', 3, '2026-03-19 06:05:39', '2026-03-19 06:05:39'),
	(4, 3, 'Petition Document', 'ACK858641290220724.pdf', 'petitions/3/1773922221_ACK858641290220724.pdf', 1, '2026-03-19 12:10:21', '2026-03-19 12:10:21'),
	(5, 5, 'Petition Document', 'ACK680698850160825.pdf', 'petitions/5/1773922795_ACK680698850160825.pdf', 1, '2026-03-19 12:19:55', '2026-03-19 12:19:55'),
	(6, 6, 'Petition Document', 'CARE PLUS II.pdf', 'petitions/6/1774025822_CARE PLUS II.pdf', 4, '2026-03-20 16:57:03', '2026-03-20 16:57:03'),
	(7, 3, 'Verification Report', 'DocScanner 18 Mar 2026 12-11 pm.pdf', 'petitions/vr/gBMvtAmO7TelqFhtLSOP4Rc7FmBHxY2QmifAjCyp.pdf', 4, '2026-03-20 17:21:08', '2026-03-20 17:21:08'),
	(8, 7, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/7/1774068844_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 4, '2026-03-21 04:54:05', '2026-03-21 04:54:05'),
	(9, 7, 'Verification Report', 'WhatsApp Image 2026-03-20 at 6.23.31 PM (1).jpeg', 'petitions/vr/5Om3frajI2T3rOCEqNoIj3bIpsI8t3bcrnjyh2Zj.jpg', 4, '2026-03-21 04:54:54', '2026-03-21 04:54:54'),
	(10, 9, 'Petition Document', 'WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 'petitions/9/1774070334_WhatsApp Image 2026-03-20 at 9.11.09 PM.jpeg', 4, '2026-03-21 05:18:54', '2026-03-21 05:18:54'),
	(11, 9, 'Verification Report', 'WhatsApp Image 2026-03-20 at 6.23.32 PM (1).jpeg', 'petitions/vr/t22aWpYxucjsFRdsYtdwJDHBjh6ZwWQZuZwJtPv5.jpg', 4, '2026-03-21 05:19:53', '2026-03-21 05:19:53'),
	(13, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/a6vLW08BUKYZyqdmPeFX5vc1Lv7nCatIQE7G5V11.jpg', 5, '2026-03-21 07:05:10', '2026-03-21 07:05:10'),
	(14, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/28G251z9uQA30f0XidjzXlj8DotPQTh9QQFpp4d8.jpg', 5, '2026-03-21 07:20:22', '2026-03-21 07:20:22'),
	(15, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/oZrwEPzQo6eirqlRVQGqHPJAf0qPO1Gy8uWpOWb4.jpg', 5, '2026-03-21 07:21:07', '2026-03-21 07:21:07'),
	(16, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/SZ1R77T1awFf5Y7Vidk99nG6VmocajL4QQyVJHZ8.jpg', 5, '2026-03-21 07:21:17', '2026-03-21 07:21:17'),
	(17, NULL, 'Profile Photo', 'WhatsApp Image 2026-03-20 at 6.23.31 PM.jpeg', 'profile_photos/722ZlzLDFQPgdHANxk9p5ovPQ28fpkyb9kOSKec0.jpg', 1, '2026-03-21 08:26:43', '2026-03-21 08:26:43'),
	(18, NULL, 'Profile Photo', 'profile.jpeg', 'profile_photos/hImptSOPsQZcI5v2DdBZTvQs58wrcoSvrFDEf6ce.jpg', 8, '2026-03-23 05:47:26', '2026-03-23 05:47:26'),
	(19, 13, 'Petition Document', 'DocScanner 18 Mar 2026 12-11 pm.pdf', 'petitions/13/1774260833_DocScanner 18 Mar 2026 12-11 pm.pdf', 1, '2026-03-23 10:13:53', '2026-03-23 10:13:53');

-- Dumping structure for table cpsp.users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `pen` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` enum('CPO','SCPO','ASI','SI','IP','Others') COLLATE utf8mb4_unicode_ci NOT NULL,
  `other_designation` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `photo` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `users_pen_unique` (`pen`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table cpsp.users: ~5 rows (approximately)
INSERT INTO `users` (`user_id`, `pen`, `name`, `role`, `email`, `mobile_number`, `password`, `designation`, `other_designation`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`, `photo`) VALUES
	(1, 987654, 'Admin User', 'admin', 'admin795022@example.com', '1234567890', '$2y$12$OUs63sQwCg6pdeCPUKfHCOqFRyL3vcAVR1cSTnxVQxzUUmmXFQi4C', 'ASI', NULL, NULL, NULL, '2026-03-19 06:01:29', '2026-03-21 08:26:43', 17),
	(3, 123456, 'arun', 'user', 'vishnu@gmail.com', '9567284405', '$2y$12$sT1UWRrrGdYH/mKs1pj5MuYLuB74YxUTbZT8qDdwQNjFgyUcpn8te', 'CPO', NULL, NULL, NULL, '2026-03-19 06:05:39', '2026-03-19 06:05:39', 3),
	(4, 963950, 'Normal User', 'user', 'user963950@example.com', '0987654321', '$2y$12$zystwz/jCRt6lr5u7etw5edoF7xke98/EFszl21Vhos1rnqwpzvb2', 'CPO', NULL, NULL, NULL, '2026-03-20 12:44:33', '2026-03-20 12:44:33', NULL),
	(5, 764186, 'Aswthy Mohan', 'user', 'aswathymohan@gmail.com', '7034607883', '$2y$12$6u5rPyb3x8ywk.zMkY21Ce6iX71anfzuCSIwqs0OAQvD5zT3hCJNq', 'CPO', NULL, NULL, NULL, '2026-03-21 07:05:10', '2026-03-21 07:21:17', 16),
	(8, 101010, 'Aswthy Mohan', 'user', 'aswathymohan1@gmail.com', '7034607883', '$2y$12$Tws0V4aCR4TJL9Lqq/JGFOOa5BT2kdiW5DDmY8YKc9nMcm0yz.6Oe', 'CPO', NULL, NULL, NULL, '2026-03-23 05:47:26', '2026-03-23 05:47:26', 18);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
