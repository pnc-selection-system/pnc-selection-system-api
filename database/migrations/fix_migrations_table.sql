-- ============================================================
-- FIX: Register already-existing tables in the migrations table
-- Run this in phpMyAdmin, MySQL Workbench, or your MySQL CLI
-- ============================================================

-- 1. Create the migrations table if it doesn't exist
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Register the 'schools' migration as already completed (batch 1)
-- so Laravel will skip it in the future
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES ('2026_07_10_000004_create_schools_table', 1);

-- 3. Register any other migrations that are already applied
-- (uncomment and add others if needed)
-- INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('2026_07_06_071743_create_provinces_table', 1);
-- INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('2026_07_10_000001_create_districts_table', 1);
-- INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('2026_07_10_000002_create_communes_table', 1);
-- INSERT IGNORE INTO `migrations` (`migration`, `batch`) VALUES ('2026_07_10_000003_create_villages_table', 1);
