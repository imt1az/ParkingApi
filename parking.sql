-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for parking
CREATE DATABASE IF NOT EXISTS `parking` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `parking`;

-- Dumping structure for table parking.bookings
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `space_id` bigint unsigned NOT NULL,
  `start_ts` datetime NOT NULL,
  `end_ts` datetime NOT NULL,
  `hours` decimal(6,2) NOT NULL DEFAULT '0.00',
  `price_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('reserved','confirmed','checked_in','checked_out','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved',
  `hold_expires_at` datetime DEFAULT NULL,
  `checked_in_at` datetime DEFAULT NULL,
  `checked_out_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bookings_user_id_foreign` (`user_id`),
  KEY `bookings_space_id_foreign` (`space_id`),
  CONSTRAINT `bookings_space_id_foreign` FOREIGN KEY (`space_id`) REFERENCES `parking_spaces` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.bookings: ~1 rows (approximately)
INSERT INTO `bookings` (`id`, `user_id`, `space_id`, `start_ts`, `end_ts`, `hours`, `price_total`, `status`, `hold_expires_at`, `checked_in_at`, `checked_out_at`, `created_at`, `updated_at`) VALUES
	(1, 8, 3, '2025-11-08 10:00:00', '2025-11-08 12:00:00', 2.00, 120.00, 'confirmed', NULL, NULL, NULL, '2025-11-08 09:36:43', '2025-11-08 09:59:33');

-- Dumping structure for table parking.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.cache: ~0 rows (approximately)

-- Dumping structure for table parking.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.cache_locks: ~0 rows (approximately)

-- Dumping structure for table parking.failed_jobs
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

-- Dumping data for table parking.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table parking.jobs
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

-- Dumping data for table parking.jobs: ~0 rows (approximately)

-- Dumping structure for table parking.job_batches
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

-- Dumping data for table parking.job_batches: ~0 rows (approximately)

-- Dumping structure for table parking.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.migrations: ~7 rows (approximately)
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2025_11_07_191226_add_phone_and_role_to_users_table', 1),
	(5, '2025_11_07_191321_create_parking_spaces_table', 1),
	(6, '2025_11_07_191428_create_space_availability_table', 1),
	(7, '2025_11_07_191709_create_bookings_table', 1);

-- Dumping structure for table parking.parking_spaces
CREATE TABLE IF NOT EXISTS `parking_spaces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(9,6) NOT NULL,
  `lng` decimal(9,6) NOT NULL,
  `capacity` int unsigned NOT NULL DEFAULT '1',
  `height_limit` decimal(5,2) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `location` point NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parking_spaces_provider_id_foreign` (`provider_id`),
  SPATIAL KEY `idx_parking_spaces_location` (`location`),
  CONSTRAINT `parking_spaces_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.parking_spaces: ~1 rows (approximately)
INSERT INTO `parking_spaces` (`id`, `provider_id`, `title`, `description`, `address`, `lat`, `lng`, `capacity`, `height_limit`, `is_active`, `created_at`, `updated_at`, `location`) VALUES
	(3, 7, 'Basement Parking Banani', NULL, 'House 22, Road 11, Banani', 23.794500, 90.406300, 2, 2.10, 1, '2025-11-08 08:22:59', '2025-11-08 08:22:59', _binary 0x0000000001010000005917b7d1009a564008ac1c5a64cb3740);

-- Dumping structure for table parking.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table parking.sessions
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

-- Dumping data for table parking.sessions: ~2 rows (approximately)
INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('2cKTLKSH9YWEIp4pumYNpVnrHb0ZkWSRZGXHXlBt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZk5UM2dIVzBRaGRCYmRxZkVNZjJwclBFMEtqc0N5WU5oMHdJVnFJMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1762611473),
	('V634uqVlpnGDwcjwdPNfWcNqvmPbf1yZ9OSZPjNZ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiblllUzNuNnBXTU1vM3d0NkE3WHFuY0JNaHlMY3JHQldGZnZBWkpqcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1762546079);

-- Dumping structure for table parking.space_availability
CREATE TABLE IF NOT EXISTS `space_availability` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `space_id` bigint unsigned NOT NULL,
  `start_ts` datetime NOT NULL,
  `end_ts` datetime NOT NULL,
  `base_price_per_hour` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `space_availability_space_id_foreign` (`space_id`),
  CONSTRAINT `space_availability_space_id_foreign` FOREIGN KEY (`space_id`) REFERENCES `parking_spaces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.space_availability: ~1 rows (approximately)
INSERT INTO `space_availability` (`id`, `space_id`, `start_ts`, `end_ts`, `base_price_per_hour`, `is_active`, `created_at`, `updated_at`) VALUES
	(2, 3, '2025-11-08 08:00:00', '2025-11-08 23:59:00', 60.00, 1, '2025-11-08 08:23:57', '2025-11-08 08:23:57');

-- Dumping structure for table parking.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('driver','provider','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'driver',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_phone_unique` (`phone`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table parking.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `name`, `phone`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
	(7, 'imtiaz', '01684277944', NULL, NULL, '$2y$12$ZhpwkXb2m3HU5pbrRt1Q4u/zJ/ACK0VyOWm0mPdqgjR4kuoh26Npi', 'provider', NULL, '2025-11-07 15:08:58', '2025-11-07 15:08:58'),
	(8, 'ahmed', '01710951715', NULL, NULL, '$2y$12$ngCelfFKb9f9aOgqrCCApem4nv/4fmnxBncCas.b4tcD.l/gbhGJC', 'driver', NULL, '2025-11-08 09:35:19', '2025-11-08 09:35:19');

-- Dumping structure for trigger parking.trg_parking_spaces_bi
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_parking_spaces_bi` BEFORE INSERT ON `parking_spaces` FOR EACH ROW BEGIN
 SET NEW.location = ST_GeomFromText(
    CONCAT('POINT(', NEW.lng, ' ', NEW.lat, ')')
  );
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger parking.trg_parking_spaces_bu
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `trg_parking_spaces_bu` BEFORE UPDATE ON `parking_spaces` FOR EACH ROW BEGIN
 IF (NEW.lat <> OLD.lat) OR (NEW.lng <> OLD.lng) THEN
    SET NEW.location = ST_GeomFromText(
      CONCAT('POINT(', NEW.lng, ' ', NEW.lat, ')')
    );
  END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
