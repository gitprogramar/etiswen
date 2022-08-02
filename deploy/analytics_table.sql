CREATE TABLE IF NOT EXISTS `nub_analytics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `content_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'FK to the #__content table.',  
  `hit_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',  
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
