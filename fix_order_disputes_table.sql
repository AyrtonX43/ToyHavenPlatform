-- Fix for missing order_disputes table on live server
-- Run this SQL directly on your live database

CREATE TABLE IF NOT EXISTS `order_disputes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('not_received','damaged','wrong_item','incomplete','other') NOT NULL DEFAULT 'not_received',
  `description` text NOT NULL,
  `evidence_images` json DEFAULT NULL,
  `status` enum('open','investigating','resolved','closed') NOT NULL DEFAULT 'open',
  `assigned_to` bigint(20) UNSIGNED DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `resolution_type` enum('refund','replacement','partial_refund','no_action') DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_disputes_order_id_index` (`order_id`),
  KEY `order_disputes_user_id_index` (`user_id`),
  KEY `order_disputes_seller_id_index` (`seller_id`),
  KEY `order_disputes_status_index` (`status`),
  KEY `order_disputes_assigned_to_index` (`assigned_to`),
  CONSTRAINT `order_disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_disputes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_disputes_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `sellers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_disputes_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_disputes_resolved_by_foreign` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
