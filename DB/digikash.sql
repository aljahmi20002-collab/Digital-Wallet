-- Digikash release SQL dump
-- Generated: 2026-05-22T20:44:45+00:00
-- Tables: total=102, data-stripped=53 (auto-detected=41, manual-only=12)
-- Source driver: mysql

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;


-- ----------------------------------------------------------
-- Table: admins
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `google2fa_secret` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `dismissed_notices` json DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: agent_commission_rule_assignments
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `agent_commission_rule_assignments`;
CREATE TABLE `agent_commission_rule_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `agent_commission_rule_id` bigint unsigned NOT NULL,
  `operation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `priority` smallint unsigned NOT NULL DEFAULT '100',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_rule_operation_unique` (`agent_id`,`agent_commission_rule_id`,`operation_type`),
  KEY `agent_rule_assignment_lookup` (`agent_id`,`status`,`operation_type`),
  KEY `agent_rule_assignment_rule_fk` (`agent_commission_rule_id`),
  KEY `agent_commission_rule_assignments_operation_type_index` (`operation_type`),
  KEY `agent_commission_rule_assignments_priority_index` (`priority`),
  KEY `agent_commission_rule_assignments_status_index` (`status`),
  CONSTRAINT `agent_rule_assignment_agent_fk` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_rule_assignment_rule_fk` FOREIGN KEY (`agent_commission_rule_id`) REFERENCES `agent_commission_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: agent_commission_rules
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `agent_commission_rules`;
CREATE TABLE `agent_commission_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `applies_globally` tinyint(1) NOT NULL DEFAULT '0',
  `priority` smallint unsigned NOT NULL DEFAULT '100',
  `operation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
  `currency_id` bigint unsigned DEFAULT NULL,
  `min_amount` decimal(18,8) NOT NULL DEFAULT '0.00000000',
  `max_amount` decimal(18,8) DEFAULT NULL,
  `calculation_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_rate` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `fixed_amount` decimal(18,8) NOT NULL DEFAULT '0.00000000',
  `min_commission` decimal(18,8) DEFAULT NULL,
  `max_commission` decimal(18,8) DEFAULT NULL,
  `effective_from` timestamp NULL DEFAULT NULL,
  `effective_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `agent_rules_global_lookup` (`status`,`applies_globally`,`operation_type`,`priority`),
  KEY `agent_commission_rules_currency_id_operation_type_index` (`currency_id`,`operation_type`),
  KEY `agent_commission_rules_status_index` (`status`),
  KEY `agent_commission_rules_applies_globally_index` (`applies_globally`),
  KEY `agent_commission_rules_priority_index` (`priority`),
  KEY `agent_commission_rules_operation_type_index` (`operation_type`),
  KEY `agent_commission_rules_calculation_type_index` (`calculation_type`),
  KEY `agent_commission_rules_effective_from_index` (`effective_from`),
  KEY `agent_commission_rules_effective_until_index` (`effective_until`),
  CONSTRAINT `agent_commission_rules_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `agent_commission_rules` (`id`, `name`, `status`, `applies_globally`, `priority`, `operation_type`, `currency_id`, `min_amount`, `max_amount`, `calculation_type`, `percentage_rate`, `fixed_amount`, `min_commission`, `max_commission`, `effective_from`, `effective_until`, `created_at`, `updated_at`) VALUES
(1, 'Global Cash-In Starter Rate', 1, 1, 100, 'cash_in', NULL, '0.00000000', '1000.00000000', 'percentage', '0.2500', '0.00000000', '0.10000000', '3.00000000', NULL, NULL, '2026-05-10 17:37:16', '2026-05-10 17:37:16'),
(2, 'Global Cash-Out Counter Rate', 1, 1, 100, 'cash_out', NULL, '0.00000000', '1000.00000000', 'percentage', '0.4500', '0.00000000', '0.20000000', '5.00000000', NULL, NULL, '2026-05-10 17:37:16', '2026-05-10 17:37:16'),
(5, 'Micro Transaction Fixed Counter Fee', 1, 0, 80, 'all', NULL, '0.00000000', '50.00000000', 'fixed', '0.0000', '0.15000000', NULL, NULL, NULL, NULL, '2026-05-10 17:37:16', '2026-05-10 17:37:16'),
(6, 'Urban Premium Cash-Out Rate', 1, 0, 70, 'cash_out', NULL, '0.00000000', NULL, 'percentage', '0.5500', '0.00000000', '0.25000000', '20.00000000', NULL, NULL, '2026-05-10 17:37:16', '2026-05-10 17:37:16'),
(7, 'Rural Access Any Operation Bonus', 1, 0, 75, 'all', NULL, '0.00000000', NULL, 'percentage', '0.5000', '0.00000000', '0.20000000', '25.00000000', NULL, NULL, '2026-05-10 17:37:16', '2026-05-10 17:37:16'),
(8, 'New Agent Launch Incentive', 1, 0, 60, 'all', NULL, '0.00000000', '500.00000000', 'percentage', '0.6500', '0.00000000', '0.25000000', '8.00000000', NULL, NULL, '2026-05-10 17:37:16', '2026-05-10 17:37:16'),
(9, 'High Volume Cash-In Capped Rate', 1, 1, 110, 'cash_in', NULL, '1000.01000000', NULL, 'percentage', '0.1500', '0.00000000', '1.00000000', '10.00000000', NULL, NULL, '2026-05-10 18:06:26', '2026-05-10 18:06:26'),
(10, 'High Volume Cash-Out Capped Rate', 1, 1, 110, 'cash_out', NULL, '1000.01000000', NULL, 'percentage', '0.2500', '0.00000000', '1.00000000', '15.00000000', NULL, NULL, '2026-05-10 18:06:26', '2026-05-10 18:06:26');


-- ----------------------------------------------------------
-- Table: agent_currencies
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `agent_currencies`;
CREATE TABLE `agent_currencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_currencies_agent_id_currency_id_unique` (`agent_id`,`currency_id`),
  KEY `agent_currencies_currency_id_is_primary_index` (`currency_id`,`is_primary`),
  KEY `agent_currencies_is_primary_index` (`is_primary`),
  CONSTRAINT `agent_currencies_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_currencies_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: agent_operations
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `agent_operations`;
CREATE TABLE `agent_operations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `agent_id` bigint unsigned NOT NULL,
  `customer_user_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `agent_wallet_id` bigint unsigned NOT NULL,
  `customer_wallet_id` bigint unsigned NOT NULL,
  `commission_rule_id` bigint unsigned DEFAULT NULL,
  `agent_transaction_id` bigint unsigned DEFAULT NULL,
  `customer_transaction_id` bigint unsigned DEFAULT NULL,
  `commission_transaction_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(18,8) NOT NULL,
  `commission_amount` decimal(18,8) NOT NULL DEFAULT '0.00000000',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `note` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_operations_reference_unique` (`reference`),
  KEY `agent_operations_currency_id_foreign` (`currency_id`),
  KEY `agent_operations_agent_wallet_id_foreign` (`agent_wallet_id`),
  KEY `agent_operations_customer_wallet_id_foreign` (`customer_wallet_id`),
  KEY `agent_operations_commission_rule_id_foreign` (`commission_rule_id`),
  KEY `agent_operations_agent_transaction_id_foreign` (`agent_transaction_id`),
  KEY `agent_operations_customer_transaction_id_foreign` (`customer_transaction_id`),
  KEY `agent_operations_commission_transaction_id_foreign` (`commission_transaction_id`),
  KEY `agent_operations_agent_id_type_status_index` (`agent_id`,`type`,`status`),
  KEY `agent_operations_customer_user_id_status_index` (`customer_user_id`,`status`),
  KEY `agent_operations_type_index` (`type`),
  KEY `agent_operations_status_index` (`status`),
  KEY `agent_operations_processed_at_index` (`processed_at`),
  CONSTRAINT `agent_operations_agent_id_foreign` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_operations_agent_transaction_id_foreign` FOREIGN KEY (`agent_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agent_operations_agent_wallet_id_foreign` FOREIGN KEY (`agent_wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_operations_commission_rule_id_foreign` FOREIGN KEY (`commission_rule_id`) REFERENCES `agent_commission_rules` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agent_operations_commission_transaction_id_foreign` FOREIGN KEY (`commission_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agent_operations_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_operations_customer_transaction_id_foreign` FOREIGN KEY (`customer_transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `agent_operations_customer_user_id_foreign` FOREIGN KEY (`customer_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agent_operations_customer_wallet_id_foreign` FOREIGN KEY (`customer_wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: agents
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `agents`;
CREATE TABLE `agents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `agent_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `qr_token` varchar(48) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `qr_token_rotated_at` timestamp NULL DEFAULT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `agent_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `commission` double NOT NULL DEFAULT '0',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agents_agent_code_unique` (`agent_code`),
  UNIQUE KEY `agents_qr_token_unique` (`qr_token`),
  KEY `agents_user_id_foreign` (`user_id`),
  KEY `agents_currency_id_foreign` (`currency_id`),
  KEY `agents_agent_name_index` (`agent_name`),
  KEY `agents_status_index` (`status`),
  CONSTRAINT `agents_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `agents_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: background_task_logs
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `background_task_logs`;
CREATE TABLE `background_task_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `task_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `command_signature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'running',
  `options` json DEFAULT NULL,
  `output` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `error_message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `duration_ms` int DEFAULT NULL,
  `executed_by` bigint unsigned DEFAULT NULL,
  `trigger_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `background_task_logs_executed_by_foreign` (`executed_by`),
  KEY `background_task_logs_task_key_status_index` (`task_key`,`status`),
  KEY `background_task_logs_created_at_index` (`created_at`),
  CONSTRAINT `background_task_logs_executed_by_foreign` FOREIGN KEY (`executed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: blog_categories
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `blog_categories`;
CREATE TABLE `blog_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` json NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_categories_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blog_categories` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`) VALUES
(3, '{\"en\": \"Security & Safety\", \"es\": \"Seguridad y Protección\"}', 'security-safety', 1, '2025-04-05 15:25:05', '2025-04-11 15:09:52'),
(4, '{\"en\": \"Multi Currency\", \"es\": \"Multimoneda\"}', 'multi-currency', 1, '2025-04-05 15:25:29', '2025-04-11 15:09:23'),
(5, '{\"en\": \"Wallet Tips\", \"es\": \"Consejos de Billetera\"}', 'wallet-tips', 1, '2025-04-05 15:25:45', '2025-04-11 15:08:33'),
(6, '{\"en\": \"Finance Management\", \"es\": \"Gestión Financiera\"}', 'finance-management', 1, '2025-04-11 15:10:12', '2025-04-11 15:10:12');


-- ----------------------------------------------------------
-- Table: blogs
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE `blogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` json NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `excerpt` json DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` json DEFAULT NULL,
  `meta_description` json DEFAULT NULL,
  `meta_keywords` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin_id` bigint unsigned DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`),
  KEY `blogs_category_id_foreign` (`category_id`),
  KEY `blogs_admin_id_foreign` (`admin_id`),
  CONSTRAINT `blogs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `blogs_user_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: businesses
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `businesses`;
CREATE TABLE `businesses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `business_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `trading_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `registration_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `incorporation_date` date DEFAULT NULL,
  `incorporation_country` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `industry` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mcc_code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_country_code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `documents` json DEFAULT NULL,
  `beneficial_owners` json DEFAULT NULL,
  `kyc_status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `businesses_user_id_foreign` (`user_id`),
  CONSTRAINT `businesses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: cache
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: cache_locks
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: cardholders
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `cardholders`;
CREATE TABLE `cardholders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `middle_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_country_code` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `nationality` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `place_of_birth` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `relation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_type` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_issue_country` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_issue_date` date DEFAULT NULL,
  `id_expiry` date DEFAULT NULL,
  `tax_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_country` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `occupation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employer` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annual_income` decimal(14,2) DEFAULT NULL,
  `source_of_funds` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pep_flag` tinyint(1) NOT NULL DEFAULT '0',
  `sanctions_flag` tinyint(1) NOT NULL DEFAULT '0',
  `card_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'personal',
  `businesses_id` bigint unsigned DEFAULT NULL,
  `kyc_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `kyc_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_proof_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kyc_documents` json DEFAULT NULL,
  `note` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cardholders_user_id_foreign` (`user_id`),
  CONSTRAINT `cardholders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: currencies
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `currencies`;
CREATE TABLE `currencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('crypto','fiat') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_rate` float DEFAULT '0',
  `rate_live` tinyint(1) NOT NULL DEFAULT '0',
  `auto_wallet` tinyint(1) NOT NULL DEFAULT '0',
  `default` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `currencies` (`id`, `flag`, `name`, `code`, `symbol`, `type`, `exchange_rate`, `rate_live`, `auto_wallet`, `default`, `status`, `created_at`, `updated_at`) VALUES
(1, 'images/2025-03-12_11-31-27_dollar_sign_cxc0.png', 'United States Dollar', 'USD', '$', 'fiat', 1, 0, 1, '1', '1', '2024-11-10 07:23:21', '2026-05-13 13:32:21'),
(5, 'images/2025/05/17/20250517_165736_euro_QPPH.png', 'Euro', 'EUR', '€', 'fiat', 0.85, 0, 1, '0', '1', '2024-11-15 16:15:50', '2026-05-13 13:32:26'),
(11, 'images/2025/05/17/20250517_162042_usdt_9Lqn.png', 'Tether', 'USDT', '₮', 'crypto', 1, 0, 0, '0', '1', '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(12, 'images/2025/05/18/20250518_171444_south_african_rand_wKoN.png', 'South African Rand', 'ZAR', 'R', 'fiat', 16.63, 0, 0, '0', '1', '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(30, NULL, 'BTC', 'BTC', 'BTC', 'crypto', 1, 0, 0, '0', '1', '2026-04-23 05:50:28', '2026-04-23 05:50:28'),
(31, NULL, 'ETH', 'ETH', 'ETH', 'crypto', 1, 0, 0, '0', '1', '2026-04-23 05:50:28', '2026-04-23 05:50:28'),
(32, 'images/2026/04/30/20260430_183112_13893862_Tdcf.png', 'Saudi Riyal', 'SAR', 'SAR', 'fiat', 3.75, 0, 1, '0', '1', '2026-04-29 12:21:34', '2026-04-30 18:31:12'),
(33, 'images/2026/04/30/20260430_141223_naira_Idzd.png', 'Nigerian Naira', 'NGN', '₦', 'fiat', 1375.12, 0, 0, '0', '1', '2026-04-30 14:12:23', '2026-04-30 14:12:23');


-- ----------------------------------------------------------
-- Table: currency_roles
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `currency_roles`;
CREATE TABLE `currency_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` bigint unsigned NOT NULL,
  `role_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_limit` double DEFAULT NULL,
  `max_limit` double DEFAULT NULL,
  `fee_type` enum('fixed','percent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'fixed = fixed fee, percent = percentage fee',
  `fee` double DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `currency_roles_currency_id_foreign` (`currency_id`),
  CONSTRAINT `currency_roles_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `currency_roles` (`id`, `currency_id`, `role_name`, `min_limit`, `max_limit`, `fee_type`, `fee`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'sender', 10, 100, 'percent', 5, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:21'),
(2, 1, 'request_money', 10, 100, 'fixed', 20, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:21'),
(3, 1, 'exchange', 10, 100, 'percent', 2, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:21'),
(4, 1, 'payment', 10, 100, 'percent', 2, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:21'),
(5, 1, 'withdraw', 0, NULL, NULL, 0, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:21'),
(6, 5, 'sender', 25, 200, 'fixed', 2, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:26'),
(7, 5, 'request_money', 25, 200, 'fixed', 15, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:26'),
(8, 5, 'exchange', 10, 200, 'fixed', 5, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:26'),
(9, 5, 'payment', 25, 200, 'fixed', 15, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:26'),
(10, 5, 'withdraw', 0, NULL, NULL, 0, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:26'),
(51, 5, 'voucher', 10, 300, 'fixed', 5, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:26'),
(52, 1, 'voucher', 10, 250, 'percent', 4, 1, '2024-12-04 18:59:49', '2026-05-13 13:32:21'),
(65, 11, 'sender', 10, 10000, 'percent', 2, 1, '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(66, 11, 'request_money', 10, 100, 'percent', 15, 0, '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(67, 11, 'exchange', 10, 1000, 'percent', 5, 1, '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(68, 11, 'voucher', 0, NULL, 'percent', 5, 0, '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(69, 11, 'payment', 0, NULL, 'percent', 15, 0, '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(70, 11, 'withdraw', 0, NULL, NULL, 0, 0, '2025-05-17 16:20:42', '2025-05-17 17:18:59'),
(71, 12, 'sender', 10, 1000, 'percent', 2, 1, '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(72, 12, 'request_money', 10, 1000, 'percent', 15, 0, '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(73, 12, 'exchange', 10, 1000, 'percent', 5, 1, '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(74, 12, 'voucher', 10, 1000, 'percent', 5, 0, '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(75, 12, 'payment', 10, 1000, 'percent', 15, 0, '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(76, 12, 'withdraw', 0, NULL, NULL, 0, 0, '2025-05-18 17:14:44', '2026-04-29 11:19:05'),
(77, 33, 'sender', 10, 100000, 'percent', 0, 1, '2026-04-30 14:12:23', '2026-04-30 14:12:23'),
(78, 33, 'request_money', 10, 100000, 'percent', 0, 1, '2026-04-30 14:12:23', '2026-04-30 14:12:23'),
(79, 33, 'exchange', 10, 10000, 'percent', 0, 1, '2026-04-30 14:12:23', '2026-04-30 14:12:23'),
(80, 33, 'voucher', 10, 10000, 'percent', 0, 1, '2026-04-30 14:12:23', '2026-04-30 14:12:23'),
(81, 33, 'payment', 10, 10000, 'percent', 0, 1, '2026-04-30 14:12:23', '2026-04-30 14:12:23'),
(82, 33, 'withdraw', 0, NULL, NULL, 0, 1, '2026-04-30 14:12:23', '2026-04-30 14:12:23'),
(83, 32, 'sender', 10, 10000, 'percent', 0, 1, '2026-04-30 18:29:37', '2026-04-30 18:31:12'),
(84, 32, 'request_money', 10, 10000, 'percent', 2, 0, '2026-04-30 18:29:37', '2026-04-30 18:31:12'),
(85, 32, 'exchange', 0, NULL, 'percent', 0, 0, '2026-04-30 18:29:37', '2026-04-30 18:31:12'),
(86, 32, 'voucher', 10, 10000, 'percent', 2, 0, '2026-04-30 18:29:37', '2026-04-30 18:31:12'),
(87, 32, 'payment', 10, 10000, 'percent', 2, 1, '2026-04-30 18:29:37', '2026-04-30 18:31:12'),
(88, 32, 'withdraw', 0, NULL, NULL, 0, 1, '2026-04-30 18:29:37', '2026-04-30 18:31:12'),
(89, 5, 'gift_card', 10, 300, 'fixed', 5, 1, '2026-05-19 02:50:42', '2026-05-19 02:50:42'),
(90, 1, 'gift_card', 10, 250, 'percent', 4, 1, '2026-05-19 02:50:42', '2026-05-19 02:50:42'),
(91, 11, 'gift_card', 0, NULL, 'percent', 5, 0, '2026-05-19 02:50:42', '2026-05-19 02:50:42'),
(92, 12, 'gift_card', 10, 1000, 'percent', 5, 0, '2026-05-19 02:50:42', '2026-05-19 02:50:42'),
(93, 33, 'gift_card', 10, 10000, 'percent', 0, 1, '2026-05-19 02:50:42', '2026-05-19 02:50:42'),
(94, 32, 'gift_card', 10, 10000, 'percent', 2, 0, '2026-05-19 02:50:42', '2026-05-19 02:50:42');


-- ----------------------------------------------------------
-- Table: custom_codes
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `custom_codes`;
CREATE TABLE `custom_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `custom_codes` (`id`, `type`, `content`, `status`, `created_at`, `updated_at`) VALUES
(1, 'css', '/*\r\n|--------------------------------------------------------------------------\r\n| Demo CSS Playground\r\n|--------------------------------------------------------------------------\r\n*/\r\n\r\n.about-section-demo {\r\n	position: relative;\r\n    background: white;\r\n}\r\n\r\n.demo-playground-card {\r\n    background: #f9fafb;\r\n    border: 2px dashed #60a5fa;\r\n    border-radius: 14px;\r\n    padding: 24px 26px;\r\n    max-width: 360px;\r\n    margin: 40px auto 24px auto;\r\n    text-align: center;\r\n    font-family: \'Inter\', Arial, sans-serif;\r\n    color: #1e293b;\r\n    transition: background 0.2s, border 0.2s;\r\n}\r\n\r\n.demo-playground-card h3 {\r\n    color: #2563eb;\r\n    margin-top: 0;\r\n    margin-bottom: 8px;\r\n    font-size: 1.18rem;\r\n    font-weight: 600;\r\n}\r\n\r\n/* Example button style */\r\n.demo-playground-btn {\r\n    background: #2563eb;\r\n    color: #fff;\r\n    border: none;\r\n    border-radius: 20px;\r\n    padding: 8px 26px;\r\n    font-weight: 600;\r\n    font-size: 1rem;\r\n    cursor: pointer;\r\n    margin-top: 10px;\r\n    transition: background 0.18s;\r\n}\r\n.demo-playground-btn:hover {\r\n    background: #1e40af;\r\n}', 1, '2025-05-09 07:07:49', '2025-07-21 05:17:06');


-- ----------------------------------------------------------
-- Table: custom_landings
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `custom_landings`;
CREATE TABLE `custom_landings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `folder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `file_count` int unsigned NOT NULL DEFAULT '0',
  `total_size` bigint unsigned NOT NULL DEFAULT '0',
  `source_checksum` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `published_at` timestamp NULL DEFAULT NULL,
  `html_updated_at` timestamp NULL DEFAULT NULL,
  `last_validated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `custom_landings_folder_unique` (`folder`),
  KEY `custom_landings_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `custom_landings` (`id`, `name`, `folder`, `status`, `file_count`, `total_size`, `source_checksum`, `published_at`, `html_updated_at`, `last_validated_at`, `created_at`, `updated_at`) VALUES
(8, 'Digital Wallet Landing', 'digital-wallet-landing-1752500682', 0, 0, 0, NULL, '2026-05-03 11:42:15', NULL, NULL, '2025-07-14 13:44:42', '2026-05-03 11:42:53'),
(10, 'Virtual Card Landing', 'virtual-card-landing-1752500798', 0, 0, 0, NULL, NULL, NULL, NULL, '2025-07-14 13:46:38', '2026-05-03 11:42:15');


-- ----------------------------------------------------------
-- Table: deposit_methods
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `deposit_methods`;
CREATE TABLE `deposit_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_gateway_id` int DEFAULT NULL COMMENT 'Payment gateway id',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'auto = automatic, manual = manual',
  `method_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_deposit` double NOT NULL,
  `max_deposit` double NOT NULL,
  `conversion_rate_live` tinyint(1) DEFAULT NULL,
  `conversion_rate` double DEFAULT NULL,
  `charge_type` enum('fixed','percent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'fixed = fixed charge, percent = percent charge',
  `charge` double NOT NULL,
  `user_charge` double DEFAULT NULL,
  `user_charge_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `merchant_charge` double DEFAULT NULL,
  `merchant_charge_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent',
  `fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `receive_payment_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deposit_methods_user_charge_index` (`user_charge`),
  KEY `deposit_methods_merchant_charge_index` (`merchant_charge`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `deposit_methods` (`id`, `payment_gateway_id`, `logo`, `name`, `type`, `method_code`, `currency`, `currency_symbol`, `min_deposit`, `max_deposit`, `conversion_rate_live`, `conversion_rate`, `charge_type`, `charge`, `user_charge`, `user_charge_type`, `merchant_charge`, `merchant_charge_type`, `fields`, `receive_payment_details`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Paypal USD', 'auto', 'paypal-usd', 'USD', '$', 100, 2000, 0, 1, 'percent', 10, 10, 'percent', 5, 'percent', NULL, NULL, 1, '2024-08-16 02:38:54', '2025-07-20 06:04:49'),
(2, NULL, 'images/2025/05/17/20250517_160209_usdt_KZwB.png', 'USDT (TRC20)', 'manual', 'usdt_trc20', 'USDT', 'USDT', 10, 1000, NULL, 1, 'fixed', 2, NULL, 'percent', NULL, 'percent', '[{\"name\":\"TX Hash\",\"type\":\"text\",\"validation\":\"required\"}]', '<div><p><strong>Network:</strong> TRC20</p><p><strong>Wallet:</strong> <code>TYZf91m4fjrHtTgNyB5q3ovzk9bt1c1gU8</code></p><p>After sending, enter your <strong>TX Hash</strong> below.</p><p style=\"margin:0;\">Only TRC20 transfers are accepted.</p></div>', 1, '2024-08-16 02:42:57', '2025-05-17 17:17:53'),
(19, NULL, 'images/2025/05/17/20250517_165513_payment_yypt.png', 'Mobile Wallet (USD)', 'manual', 'mobile-usd', 'USD', '$', 5, 500, NULL, 1, 'percent', 2, NULL, 'percent', NULL, 'percent', '{\"2\":{\"name\":\"Sender Name\",\"type\":\"text\",\"validation\":\"required\"},\"3\":{\"name\":\"Mobile Number Used\",\"type\":\"text\",\"validation\":\"required\"}}', '<p><strong>Send payment to:</strong> <code>+1 (305) 123-4567</code></p><p>After sending, provide:</p><p><b>  Sender Name</b></p><p><b>  Mobile Number used</b></p><p style=\"margin:0;\">Make sure the amount matches exactly.</p>', 1, '2025-05-17 16:55:13', '2025-05-17 17:14:44'),
(20, 2, NULL, 'Stripe Usd', 'auto', 'stripe-usd', 'USD', '$', 10, 1000, 0, 1, 'percent', 10, 10, 'percent', 5, 'percent', NULL, NULL, 1, '2025-05-17 17:36:12', '2025-07-20 06:05:04'),
(21, 3, NULL, 'Mollie', 'auto', 'mollie-usd', 'USD', '$', 10, 100, 0, 0.89, 'percent', 2, NULL, 'percent', NULL, 'percent', NULL, NULL, 1, '2025-05-18 09:00:29', '2025-05-18 16:01:31'),
(23, 5, NULL, 'Coinbase', 'auto', 'coinbase-usd', 'USD', '$', 10, 10000, 0, 1, 'percent', 5, 5, 'percent', 2, 'percent', NULL, NULL, 1, '2025-05-18 16:52:28', '2025-07-29 04:03:04'),
(24, 6, NULL, 'Paystack', 'auto', 'paystack-ngn', 'NGN', '₦', 10, 1000, 0, 18.11, 'percent', 3, 3, 'percent', 2, 'percent', NULL, NULL, 1, '2025-05-18 16:59:38', '2026-04-30 14:16:25'),
(25, 7, NULL, 'Flutterwave', 'auto', 'flutterwave-usd', 'USD', '$', 10, 1000, 0, 1, 'percent', 0.8, NULL, 'percent', NULL, 'percent', NULL, NULL, 1, '2025-05-18 17:00:34', '2025-05-18 17:00:34'),
(26, 8, NULL, 'Cryptomus', 'auto', 'cryptomus-usdt', 'USDT', '₮', 10, 1000, 0, 1, 'percent', 0.6, NULL, 'percent', NULL, 'percent', NULL, NULL, 0, '2025-05-18 17:01:14', '2025-05-19 06:32:09'),
(29, 13, NULL, 'Moneroo', 'auto', 'moneroo-usd', 'USD', '$', 10, 100, 0, 1, 'percent', 5, 5, 'percent', 2, 'percent', NULL, NULL, 1, '2025-07-26 06:19:36', '2025-07-26 06:34:54'),
(30, 4, NULL, '2Checkout', 'auto', 'twocheckout-usd', 'USD', '$', 10, 100, 0, 1, 'fixed', 5, 5, 'fixed', 2, 'fixed', NULL, NULL, 1, '2025-07-29 04:47:25', '2025-07-29 04:47:25'),
(31, 10, NULL, 'Strowallet', 'auto', 'strowallet-usd', 'USD', '$', 10, 1000, 0, 1, 'percent', 5, 5, 'percent', 2, 'percent', NULL, NULL, 1, '2025-08-27 11:49:13', '2025-08-27 11:51:22'),
(32, 31, 'general/static/gateway/bitnob.png', 'Bitnob USDT', 'auto', 'bitnob_usdt', 'USDT', '$', 5, 100000, 1, 1, 'percent', 1, 1, 'percent', 0.5, 'percent', '\"{\\\"chain\\\":{\\\"type\\\":\\\"select\\\",\\\"label\\\":\\\"Chain\\\",\\\"options\\\":{\\\"tron\\\":\\\"TRON (TRC20)\\\",\\\"bsc\\\":\\\"BSC (BEP20)\\\",\\\"ethereum\\\":\\\"Ethereum (ERC20)\\\"},\\\"required\\\":true}}\"', NULL, 1, '2026-04-28 14:31:02', '2026-04-28 14:31:02'),
(33, 32, 'general/static/gateway/paymob.png', 'Paymob Saudi Riyal', 'auto', 'paymob-sar', 'SAR', 'SAR', 1, 100000, 0, 1, 'fixed', 0, 0, 'fixed', 0, 'fixed', '[]', NULL, 1, '2026-04-29 12:21:34', '2026-04-29 12:21:34');


-- ----------------------------------------------------------
-- Table: failed_jobs
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=595 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: feature_access_rules
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `feature_access_rules`;
CREATE TABLE `feature_access_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `feature_id` bigint unsigned NOT NULL,
  `panel` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'user | merchant | api',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Feature is shown in menus, dashboards, widgets for this panel.',
  `is_accessible` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Feature routes, actions, and API endpoints respond for this panel.',
  `conditions` json DEFAULT NULL COMMENT 'Extensible access rules: requires_kyc, requires_auth, countries_allowed, plans_allowed, approval_required, beta, etc.',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feature_access_rules_feature_id_panel_unique` (`feature_id`,`panel`),
  KEY `feature_access_rules_panel_index` (`panel`),
  CONSTRAINT `feature_access_rules_feature_id_foreign` FOREIGN KEY (`feature_id`) REFERENCES `features` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `feature_access_rules` (`id`, `feature_id`, `panel`, `is_visible`, `is_accessible`, `conditions`, `created_at`, `updated_at`) VALUES
(1, 1, 'user', 1, 1, '{\"requires_kyc\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-04-21 04:50:10'),
(2, 1, 'merchant', 1, 1, '{\"requires_kyc\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-04-21 04:54:56'),
(3, 2, 'user', 1, 1, '{\"requires_kyc\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-04-21 04:49:17'),
(4, 2, 'merchant', 1, 1, '{\"requires_kyc\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-04-21 04:49:17'),
(5, 3, 'user', 1, 1, '{\"requires_kyc\": true, \"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(6, 3, 'merchant', 1, 1, '{\"requires_kyc\": true, \"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(7, 4, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-13 04:38:17'),
(8, 4, 'merchant', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-13 04:38:17'),
(9, 5, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-13 04:38:43'),
(10, 5, 'merchant', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-13 04:38:43'),
(11, 6, 'user', 1, 1, '{\"requires_kyc\": true, \"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(12, 6, 'merchant', 1, 1, '{\"requires_kyc\": true, \"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(13, 7, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(14, 7, 'merchant', 1, 1, '{\"requires_kyc\": false, \"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(15, 8, 'user', 1, 1, '{\"requires_kyc\": true, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-11 08:48:47'),
(16, 8, 'merchant', 1, 1, '{\"requires_kyc\": true, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-11 08:48:47'),
(17, 9, 'user', 1, 1, '{\"requires_kyc\": true, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-19 19:34:11'),
(18, 9, 'merchant', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-19 19:34:11'),
(19, 10, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-16 08:17:00'),
(20, 10, 'merchant', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-16 08:17:00'),
(21, 11, 'user', 1, 1, '{\"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(22, 11, 'merchant', 1, 1, '{\"requires_auth\": true}', '2026-04-20 15:42:31', '2026-04-20 15:42:31'),
(23, 12, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-13 04:40:45'),
(24, 12, 'merchant', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-20 15:42:31', '2026-05-13 04:40:45'),
(29, 15, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-23 03:12:19', '2026-05-19 19:34:11'),
(30, 15, 'merchant', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-04-23 03:12:19', '2026-05-13 04:39:10'),
(31, 16, 'user', 1, 1, '{\"requires_kyc\": false}', '2026-05-01 02:07:52', '2026-05-01 02:07:52'),
(32, 16, 'merchant', 1, 1, '{\"requires_kyc\": false}', '2026-05-01 02:07:52', '2026-05-01 02:07:52'),
(33, 16, 'agent', 1, 1, '{\"requires_kyc\": false}', '2026-05-01 02:07:52', '2026-05-01 02:07:52'),
(34, 17, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-09 02:05:51', '2026-05-13 15:29:50'),
(35, 17, 'merchant', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-09 02:05:51', '2026-05-13 04:39:56'),
(36, 17, 'agent', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-09 02:05:51', '2026-05-13 15:29:50'),
(37, 7, 'agent', 1, 1, '{\"requires_kyc\": false}', '2026-05-09 02:05:51', '2026-05-09 02:05:51'),
(38, 18, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:06:16', '2026-05-11 08:49:02'),
(39, 1, 'agent', 1, 1, '{\"requires_kyc\": false}', '2026-05-11 02:15:10', '2026-05-11 02:15:10'),
(40, 2, 'agent', 1, 1, '{\"requires_kyc\": true}', '2026-05-11 02:15:10', '2026-05-11 02:15:10'),
(41, 3, 'agent', 1, 1, '{\"requires_kyc\": true}', '2026-05-11 02:15:10', '2026-05-11 02:15:10'),
(42, 4, 'agent', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-13 04:38:17'),
(43, 5, 'agent', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-13 04:38:43'),
(44, 15, 'agent', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-11 08:49:36'),
(45, 6, 'agent', 1, 1, '{\"requires_kyc\": true}', '2026-05-11 02:15:10', '2026-05-11 02:15:10'),
(46, 8, 'agent', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-11 08:48:47'),
(47, 18, 'merchant', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-11 08:49:02'),
(48, 18, 'agent', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-11 08:49:02'),
(49, 9, 'agent', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-19 19:34:11'),
(50, 10, 'agent', 0, 0, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-11 08:49:14'),
(51, 11, 'agent', 1, 1, '[]', '2026-05-11 02:15:10', '2026-05-11 02:15:10'),
(52, 12, 'agent', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-11 02:15:10', '2026-05-13 04:40:45'),
(53, 19, 'user', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-13 11:27:32', '2026-05-13 11:33:38'),
(54, 19, 'merchant', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-13 11:27:32', '2026-05-13 11:32:40'),
(55, 19, 'agent', 1, 1, '{\"requires_kyc\": false, \"requires_phone\": false, \"countries_allowed\": []}', '2026-05-13 11:27:32', '2026-05-13 11:32:40'),
(56, 20, 'user', 1, 1, '{\"requires_kyc\": false}', '2026-05-19 02:49:49', '2026-05-19 02:49:49'),
(57, 20, 'merchant', 1, 1, '{\"requires_kyc\": false}', '2026-05-19 02:49:49', '2026-05-19 02:49:49'),
(58, 20, 'agent', 1, 1, '{\"requires_kyc\": false}', '2026-05-19 02:49:49', '2026-05-19 02:49:49');


-- ----------------------------------------------------------
-- Table: features
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `features`;
CREATE TABLE `features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `is_core` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Disabling a core feature typically breaks a business flow and should be confirmed by the admin.',
  `meta` json DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `features_key_unique` (`key`),
  KEY `features_category_index` (`category`),
  KEY `features_is_enabled_index` (`is_enabled`),
  KEY `features_sort_order_index` (`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `features` (`id`, `key`, `label`, `category`, `description`, `icon`, `is_enabled`, `is_core`, `meta`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'deposit_money', 'Deposit Money', 'money_movement', 'Allow users to top up their wallet using any active payment gateway or manual deposit method.', '', 1, 1, '[]', 1, '2026-04-20 15:42:31', '2026-04-21 05:41:04'),
(2, 'withdraw_money', 'Withdraw Money', 'money_movement', 'Allow users to transfer wallet balance to linked bank, mobile money, or crypto payout methods.', '', 1, 1, '[]', 2, '2026-04-20 15:42:31', '2026-04-23 17:18:45'),
(3, 'send_money', 'Send Money', 'money_movement', 'Allow users to transfer wallet balance instantly to another platform user.', '', 1, 0, '[]', 3, '2026-04-20 15:42:31', '2026-04-21 05:04:45'),
(4, 'request_money', 'Request Money', 'money_movement', 'Allow users to raise payment requests that another user can approve and pay from their wallet.', '', 1, 0, '[]', 4, '2026-04-20 15:42:31', '2026-04-21 03:29:57'),
(5, 'exchange_money', 'Exchange Money', 'money_movement', 'Let users convert balance between supported wallet currencies using the exchange rate engine.', '', 1, 0, '[]', 5, '2026-04-20 15:42:31', '2026-04-21 04:44:56'),
(6, 'bank_transfer', 'Bank Transfer Payouts', 'money_movement', 'Expose linked-bank withdraw methods to end users. Disabling hides bank payout options everywhere.', '', 1, 0, '[]', 8, '2026-04-20 15:42:31', '2026-05-09 02:05:51'),
(7, 'payment_link', 'Payment Links', 'business', 'Allow users, merchants and agents to generate shareable payment links that anyone can pay from a wallet or supported gateway.', '', 1, 0, '[]', 9, '2026-04-20 15:42:31', '2026-05-09 02:05:51'),
(8, 'merchant_payment', 'Merchant Payment', 'business', 'Allow customers to pay registered merchants from their wallet or connected payment methods.', '', 1, 0, '[]', 10, '2026-04-20 15:42:31', '2026-05-09 02:05:51'),
(9, 'p2p_marketplace', 'P2P Marketplace', 'p2p', 'Peer-to-peer ads, trading rooms, payment methods, and disputes. Disabling hides every P2P surface.', '', 1, 0, '[]', 13, '2026-04-20 15:42:31', '2026-05-11 02:06:16'),
(10, 'virtual_card', 'Virtual Cards', 'cards', 'Issue, manage, and transact on virtual cards backed by the configured card providers.', '', 1, 0, '[]', 14, '2026-04-20 15:42:31', '2026-05-11 02:35:21'),
(11, 'referral_program', 'Referral Program', 'engagement', 'Referral link, referral tree, and referral reward settings exposed to the end user.', '', 1, 0, '[]', 15, '2026-04-20 15:42:31', '2026-05-11 02:06:16'),
(12, 'vouchers', 'Vouchers', 'engagement', 'Redeemable gift/top-up vouchers for wallet balance. Controls both creation and redemption surfaces.', '', 1, 0, '[]', 17, '2026-04-20 15:42:31', '2026-05-13 11:27:32'),
(15, 'wallet_earn', 'Wallet Earn', 'money_movement', 'Allow users to stake wallet balances in supported currencies and earn scheduled rewards.', '', 1, 0, '[]', 6, '2026-04-23 03:12:19', '2026-04-23 17:12:45'),
(16, 'agent_program', 'Agent Program', 'business', 'Enable agent registration, agent login, and the agent dashboard. When disabled, every agent surface is hidden across the platform.', '', 1, 0, '[]', 11, '2026-05-01 02:07:52', '2026-05-11 02:27:23'),
(17, 'mobile_recharge', 'Mobile Recharge', 'money_movement', 'Allow users, merchants and agents to recharge mobile numbers from wallet balance through the configured provider.', '', 1, 0, '[]', 7, '2026-05-09 02:05:51', '2026-05-09 02:05:51'),
(18, 'subscription_system', 'Subscription System', 'business', 'Enable subscription plans, checkout, active plan status, renewals, cancellations, and subscription history for users.', '', 1, 0, '[]', 12, '2026-05-11 02:06:16', '2026-05-11 02:06:16'),
(19, 'user_ranks', 'User Ranks', 'engagement', 'Manage rank progression, wallet limits, referral levels, and reward tiers from Feature Management.', '', 1, 0, '[]', 16, '2026-05-13 11:27:32', '2026-05-13 11:27:32'),
(20, 'gift_cards', 'Gift Cards', 'engagement', 'Designed gift cards funded from a wallet, delivered by email with a private redeem link and a public preview page.', '', 1, 0, '[]', 18, '2026-05-19 02:49:49', '2026-05-19 02:49:49');


-- ----------------------------------------------------------
-- Table: footer_items
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `footer_items`;
CREATE TABLE `footer_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `footer_section_id` bigint unsigned NOT NULL,
  `label` json DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `url_type` varchar(96) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_id` bigint unsigned DEFAULT NULL,
  `social_id` bigint unsigned DEFAULT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `footer_items_footer_section_id_foreign` (`footer_section_id`),
  CONSTRAINT `footer_items_footer_section_id_foreign` FOREIGN KEY (`footer_section_id`) REFERENCES `footer_sections` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `footer_items` (`id`, `footer_section_id`, `label`, `content`, `url_type`, `url`, `page_id`, `social_id`, `icon`, `order`, `status`, `created_at`, `updated_at`) VALUES
(8, 6, '{\"en\": \"Privacy Policy\", \"es\": \"política de privacidad\"}', '{\"en\":\"dfgdfgfgj\",\"es\":null}', 'page', 'http://digikash.test/', 5, NULL, 'fa-solid fa-angles-right', 2, 1, '2025-04-04 16:42:32', '2025-07-17 17:28:13'),
(9, 6, '{\"en\": \"Terms & Conditions\", \"es\": \"Tranvías y servicios\"}', '{\"en\":\"fgjfgj\",\"es\":null}', 'page', 'http://digikash.test/', 12, NULL, 'fa-solid fa-angles-right', 1, 1, '2025-04-07 17:49:47', '2025-07-17 17:28:13'),
(10, 7, '{\"en\": \"All In One Powerful Platform\", \"es\": \"Una Plataforma Todo en Uno\"}', '{\"en\":\"Manage money easily with Digikash. Send, receive, deposit, and pay merchants \\u2014 all from one smart wallet.\",\"es\":\"Administra tu dinero f\\u00e1cilmente con Digikash. Env\\u00eda, recibe, deposita y paga a comerciantes desde una \\u00fanica billetera inteligente.\"}', 'none', NULL, NULL, NULL, 'fa-solid fa-angles-right', 1, 1, '2025-04-08 10:45:06', '2025-04-08 17:39:22'),
(11, 5, '{\"en\": \"About Us\", \"es\": \"Sobre nosotras\"}', '{\"en\":null,\"es\":null}', 'page', NULL, 4, NULL, 'fa-solid fa-angles-right', 1, 1, '2025-04-08 11:24:28', '2025-04-08 17:20:33'),
(12, 8, '{\"en\": \"Facebook\", \"es\": null}', '{\"en\":null,\"es\":null}', 'social', 'https://www.facebook.com/yourpage', NULL, 4, 'fa-solid fa-angles-right', 1, 1, '2025-04-08 15:41:48', '2025-04-08 17:19:40'),
(13, 8, '{\"en\": \"Twitter\", \"es\": null}', '{\"en\":null,\"es\":null}', 'social', NULL, NULL, 5, 'fa-solid fa-angles-right', 2, 1, '2025-04-08 15:42:41', '2025-04-08 17:16:53'),
(14, 8, '{\"en\": \"Linkedin\", \"es\": null}', '{\"en\":null,\"es\":null}', 'social', NULL, NULL, 6, 'fa-solid fa-angles-right', 3, 1, '2025-04-08 15:42:58', '2025-04-08 17:16:55'),
(15, 7, '{\"en\": \"Fast, Secure, and Seamless Transactions\", \"es\": \"Transacciones Rápidas, Seguras y Sin Interrupciones\"}', '{\"en\":\"Enjoy instant transfers, top-notch security, and real-time balance updates with Digikash.\",\"es\":\"Disfruta de transferencias instant\\u00e1neas, m\\u00e1xima seguridad y actualizaciones de saldo en tiempo real con Digikash.\"}', 'none', NULL, NULL, NULL, 'fa-solid fa-angles-right', 2, 1, '2025-04-08 17:32:28', '2025-05-21 07:30:17');


-- ----------------------------------------------------------
-- Table: footer_sections
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `footer_sections`;
CREATE TABLE `footer_sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` json NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `footer_sections` (`id`, `title`, `type`, `status`, `order`, `created_at`, `updated_at`) VALUES
(5, '{\"en\": \"Page\", \"es\": \"Página\"}', 'page', 1, 2, '2025-04-04 03:26:47', '2025-04-08 17:49:22'),
(6, '{\"en\": \"Useful Links\", \"es\": \"Enlaces útiles\"}', 'link', 1, 3, '2025-04-04 03:26:59', '2025-07-17 17:27:45'),
(7, '{\"en\": \"About\", \"es\": \"Acerca de\"}', 'text', 1, 1, '2025-04-07 17:19:20', '2025-04-08 17:49:22'),
(8, '{\"en\": \"Social Link\", \"es\": \"Enlace social\"}', 'social', 1, 4, '2025-04-08 15:37:47', '2025-07-17 17:27:45');


-- ----------------------------------------------------------
-- Table: gift_card_templates
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `gift_card_templates`;
CREATE TABLE `gift_card_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `preset_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'premium',
  `background_color` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_color` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ribbon_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_amount` decimal(15,2) DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('active','draft','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gift_card_templates_slug_unique` (`slug`),
  KEY `gift_card_templates_status_sort_order_index` (`status`,`sort_order`),
  KEY `gift_card_templates_category_index` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gift_card_templates` (`id`, `name`, `slug`, `category`, `preset_key`, `background_color`, `text_color`, `ribbon_text`, `default_amount`, `image`, `thumbnail`, `status`, `sort_order`, `used_count`, `created_at`, `updated_at`) VALUES
(1, 'Confetti Pop', 'confetti-pop', 'Birthday', 'birthday', NULL, NULL, 'Happy Birthday', NULL, NULL, NULL, 'active', 1, 3, '2026-05-19 02:38:54', '2026-05-19 22:50:42'),
(2, 'Rose Garden', 'rose-garden', 'Birthday', 'anniversary', NULL, NULL, 'Happy Birthday', NULL, NULL, NULL, 'active', 2, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(3, 'Pine & Lights', 'pine-lights', 'Holiday', 'holiday', NULL, NULL, 'Season\'s Greetings', NULL, NULL, NULL, 'active', 3, 1, '2026-05-19 02:38:54', '2026-05-19 14:55:48'),
(4, 'Midnight Frost', 'midnight-frost', 'Holiday', 'premium', NULL, NULL, 'Season\'s Greetings', NULL, NULL, NULL, 'active', 4, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(5, 'Golden Hour', 'golden-hour', 'Thank You', 'thankyou', NULL, NULL, 'With Gratitude', NULL, NULL, NULL, 'active', 5, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(6, 'Quiet Thanks', 'quiet-thanks', 'Thank You', 'premium', NULL, NULL, 'With Gratitude', NULL, NULL, NULL, 'active', 6, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(7, 'Eternal Plum', 'eternal-plum', 'Anniversary', 'anniversary', NULL, NULL, 'Happy Anniversary', NULL, NULL, NULL, 'active', 7, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(8, 'Sky Sparkle', 'sky-sparkle', 'Congratulations', 'congrats', NULL, NULL, 'Congratulations', NULL, NULL, NULL, 'active', 8, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(9, 'Sun Burst', 'sun-burst', 'Congratulations', 'thankyou', NULL, NULL, 'Congratulations', NULL, NULL, NULL, 'active', 9, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(10, 'Navy Classic', 'navy-classic', 'General', 'premium', NULL, NULL, 'A Gift For You', NULL, NULL, NULL, 'active', 10, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10'),
(11, 'Evergreen', 'evergreen', 'General', 'holiday', NULL, NULL, 'A Gift For You', NULL, NULL, NULL, 'active', 11, 0, '2026-05-19 02:38:54', '2026-05-19 11:45:10');


-- ----------------------------------------------------------
-- Table: gift_cards
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `gift_cards`;
CREATE TABLE `gift_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `gift_card_template_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `recipient_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_user_id` bigint unsigned DEFAULT NULL,
  `sender_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci,
  `delivery_method` enum('email','wallet','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email',
  `scheduled_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('pending','scheduled','delivered','redeemed','expired','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `redeemed_by` bigint unsigned DEFAULT NULL,
  `redeemed_wallet_id` bigint unsigned DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `gift_cards_code_unique` (`code`),
  KEY `gift_cards_gift_card_template_id_foreign` (`gift_card_template_id`),
  KEY `gift_cards_currency_id_foreign` (`currency_id`),
  KEY `gift_cards_recipient_user_id_foreign` (`recipient_user_id`),
  KEY `gift_cards_redeemed_by_foreign` (`redeemed_by`),
  KEY `gift_cards_redeemed_wallet_id_foreign` (`redeemed_wallet_id`),
  KEY `gift_cards_user_id_status_index` (`user_id`,`status`),
  KEY `gift_cards_recipient_email_status_index` (`recipient_email`,`status`),
  KEY `gift_cards_expires_at_index` (`expires_at`),
  CONSTRAINT `gift_cards_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_cards_gift_card_template_id_foreign` FOREIGN KEY (`gift_card_template_id`) REFERENCES `gift_card_templates` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_cards_recipient_user_id_foreign` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_cards_redeemed_by_foreign` FOREIGN KEY (`redeemed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_cards_redeemed_wallet_id_foreign` FOREIGN KEY (`redeemed_wallet_id`) REFERENCES `wallets` (`id`) ON DELETE SET NULL,
  CONSTRAINT `gift_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: ip_blocks
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `ip_blocks`;
CREATE TABLE `ip_blocks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `blocked_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_blocks_ip_address_unique` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (no data)


-- ----------------------------------------------------------
-- Table: job_batches
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: jobs
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=3757 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: kyc_submissions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `kyc_submissions`;
CREATE TABLE `kyc_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kyc_template_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `submission_data` json DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kyc_submissions_kyc_template_id_foreign` (`kyc_template_id`),
  KEY `kyc_submissions_user_id_foreign` (`user_id`),
  CONSTRAINT `kyc_submissions_kyc_template_id_foreign` FOREIGN KEY (`kyc_template_id`) REFERENCES `kyc_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kyc_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: kyc_templates
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `kyc_templates`;
CREATE TABLE `kyc_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fields` json NOT NULL,
  `applicable_to` json NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kyc_templates_title_unique` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `kyc_templates` (`id`, `title`, `description`, `fields`, `applicable_to`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Passport Verify', 'Verify identity using a valid passport by uploading a clear copy of the photo and details page.', '[{\"type\": \"text\", \"label\": \"Full Name\", \"required\": \"true\"}, {\"type\": \"text\", \"label\": \"Phone Number\", \"required\": \"true\"}, {\"type\": \"file\", \"label\": \"Passport Copy\", \"required\": \"true\"}]', '[\"user\", \"merchant\"]', 1, '2025-02-23 02:46:34', '2025-04-22 06:45:04'),
(2, 'NID Verify', 'Verify identity using a National ID by uploading a clear front and back copy', '[{\"type\": \"text\", \"label\": \"NID Number\", \"required\": \"true\"}, {\"type\": \"file\", \"label\": \"NID Front Image\", \"required\": \"true\"}, {\"type\": \"file\", \"label\": \"NID Back Image\", \"required\": \"true\"}]', '[\"user\"]', 1, '2025-02-23 08:18:10', '2025-05-12 20:36:36');


-- ----------------------------------------------------------
-- Table: languages
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `languages`;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flag` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_rtl` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `languages` (`id`, `flag`, `name`, `code`, `is_default`, `is_rtl`, `status`, `created_at`, `updated_at`) VALUES
(1, 'images/2025-02-27_15-27-02_united_states_ytAv.png', 'English', 'en', 1, 0, 1, '2024-07-11 08:24:52', '2025-06-28 14:17:51'),
(20, 'images/2025-03-21_16-49-25_spain_TehZ.png', 'Spain', 'es', 0, 0, 1, '2025-03-21 16:49:25', '2025-06-28 14:17:51');


-- ----------------------------------------------------------
-- Table: login_activities
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `login_activities`;
CREATE TABLE `login_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `platform` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `browser` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `login_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=321 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: merchant_currencies
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `merchant_currencies`;
CREATE TABLE `merchant_currencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant_currencies_merchant_id_currency_id_unique` (`merchant_id`,`currency_id`),
  KEY `merchant_currencies_currency_id_is_primary_index` (`currency_id`,`is_primary`),
  KEY `merchant_currencies_is_primary_index` (`is_primary`),
  CONSTRAINT `merchant_currencies_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `merchant_currencies_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: merchant_deposit_methods
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `merchant_deposit_methods`;
CREATE TABLE `merchant_deposit_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_id` bigint unsigned NOT NULL,
  `deposit_method_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchant_deposit_methods_unique` (`merchant_id`,`deposit_method_id`),
  KEY `merchant_deposit_methods_deposit_method_id_foreign` (`deposit_method_id`),
  CONSTRAINT `merchant_deposit_methods_deposit_method_id_foreign` FOREIGN KEY (`deposit_method_id`) REFERENCES `deposit_methods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `merchant_deposit_methods_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: merchants
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `merchants`;
CREATE TABLE `merchants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `merchant_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `business_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `business_logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fee` double NOT NULL DEFAULT '0',
  `api_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `api_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_api_key` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_api_secret` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `test_merchant_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sandbox_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `webhook_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_mode` enum('sandbox','production') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sandbox',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `merchants_merchant_key_unique` (`merchant_key`),
  UNIQUE KEY `merchants_site_url_unique` (`site_url`),
  KEY `merchants_user_id_foreign` (`user_id`),
  KEY `merchants_currency_id_foreign` (`currency_id`),
  KEY `merchants_business_name_index` (`business_name`),
  KEY `merchants_status_index` (`status`),
  KEY `merchants_test_api_key_index` (`test_api_key`),
  KEY `merchants_test_merchant_key_index` (`test_merchant_key`),
  KEY `merchants_sandbox_enabled_index` (`sandbox_enabled`),
  KEY `merchants_webhook_url_index` (`webhook_url`),
  CONSTRAINT `merchants_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `merchants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: messages
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` bigint unsigned DEFAULT NULL,
  `ticket_id` bigint unsigned NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_admin_id_foreign` (`admin_id`),
  KEY `messages_ticket_id_foreign` (`ticket_id`),
  CONSTRAINT `messages_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  CONSTRAINT `messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: migrations
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2024_07_05_014145_create_admins_table', 1),
(5, '2024_07_05_120640_create_settings_table', 1),
(6, '2024_07_06_093402_create_plugins_table', 1),
(7, '2024_07_08_060826_create_permission_tables', 1),
(8, '2024_07_08_124553_create_staff_table', 1),
(9, '2018_08_29_200844_create_languages_table', 2),
(10, '2018_08_29_205156_create_translations_table', 2),
(11, '2024_07_11_042348_create_languages_table', 3),
(12, '0001_01_01_000000_create_users_table', 4),
(13, '2024_08_11_083809_create_payment_gateways_table', 5),
(14, '2024_08_11_090520_create_deposit_methods_table', 5),
(16, '2024_10_27_042832_create_currencies_table', 6),
(17, '2024_11_12_040813_create_wallets_table', 7),
(20, '2024_11_16_150322_create_transactions_table', 8),
(21, '2024_12_04_031019_create_currency_roles_table', 8),
(23, '2024_12_23_060751_create_vouchers_table', 9),
(26, '2024_12_27_141240_create_withdraw_methods_table', 10),
(27, '2024_12_27_141326_create_withdraw_schedules_table', 10),
(28, '2024_12_29_164640_create_withdraw_accounts_table', 11),
(29, '2025_01_07_150435_create_referrals_table', 12),
(30, '2025_01_08_002400_create_rewards_table', 12),
(31, '2025_01_21_032753_create_support_categories_table', 13),
(34, '2025_01_21_033403_create_messages_table', 15),
(35, '2025_01_21_033417_create_tickets_table', 16),
(38, '2025_01_25_033412_create_user_ranks_table', 17),
(42, '2025_01_31_161024_create_merchants_table', 18),
(43, '2025_02_06_072549_create_personal_access_tokens_table', 19),
(45, '2024_09_12_080045_create_notifications_table', 20),
(50, '2025_02_20_090013_create_kyc_templates_table', 21),
(51, '2025_02_20_141033_create_kyc_submissions_table', 21),
(52, '2025_03_10_134122_create_login_activities_table', 22),
(53, '2025_03_10_150952_create_i_p_blocks_table', 23),
(55, '2025_03_12_162814_create_user_features_table', 24),
(59, '2025_03_18_170040_create_pages_table', 25),
(60, '2025_03_18_170126_create_page_components_table', 25),
(61, '2025_03_18_170201_create_page_component_contents_table', 25),
(62, '2025_03_27_193454_create_navigations_table', 26),
(63, '2025_04_01_042811_create_footer_sections_table', 27),
(65, '2025_04_01_042834_create_footer_items_table', 28),
(66, '2025_04_04_173541_create_socials_table', 29),
(67, '2025_04_05_070247_create_blog_categories_table', 30),
(68, '2025_04_05_070334_create_blogs_table', 30),
(69, '2025_04_06_121632_create_site_seos_table', 31),
(70, '2025_04_14_104122_create_subscribers_table', 32),
(73, '2025_04_24_011128_create_notification_templates_table', 33),
(74, '2025_04_24_011503_create_notification_template_channels_table', 33),
(75, '2025_05_08_155310_create_custom_codes_table', 34),
(76, '2025_06_08_044546_add_token_and_expires_at_to_transactions_table', 35),
(82, '2025_06_09_161907_create_virtual_card_requests_table', 36),
(103, '0002_01_01_000000_add_state_and_postal_code_to_users_table', 37),
(104, '2025_06_08_044546_create_virtual_card_providers_table', 37),
(105, '2025_06_10_015927_create_virtual_cards_table', 37),
(106, '2025_06_17_125731_create_personal_access_tokens_table', 37),
(107, '2025_06_21_142932_create_virtual_card_fee_settings_table', 37),
(108, '2025_06_21_180221_create_cardholders_table', 37),
(109, '2025_06_21_185311_create_businesses_table', 37),
(110, '2025_07_02_153950_add_cardholder_id_to_virtual_card_requests_table', 36),
(111, '2025_07_14_041924_create_custom_landings_table', 38),
(112, '2025_07_20_044207_add_user_merchant_charges_to_withdraw_methods_table', 39),
(113, '2025_07_20_053613_add_user_merchant_charges_to_deposit_methods_table', 40),
(114, '2025_07_21_085832_add_business_fields_to_users_table', 41),
(115, '2025_07_21_125506_create_referral_contents_table', 42),
(116, '2025_07_27_210000_add_test_credentials_to_merchants_table', 43),
(117, '2025_07_27_213500_refactor_merchant_webhooks_and_environment', 44),
(119, '2025_08_28_102710_add_initial_load_amount_to_virtual_card_requests_table', 45),
(120, '2025_08_28_102900_add_additional_issue_fee_percent_to_virtual_card_providers_table', 46),
(121, '2025_08_28_220510_update_virtual_card_fee_settings_add_fee_percent_drop_fee_type', 47),
(122, '2025_08_29_082700_update_virtual_card_fee_settings_add_fee_percent_drop_fee_type', 48),
(123, '2025_10_12_000001_create_p2p_payment_methods_table', 48),
(124, '2025_10_12_000002_create_p2p_offers_table', 48),
(125, '2025_10_12_000003_create_p2p_orders_table', 48),
(126, '2025_10_12_000004_create_p2p_disputes_table', 48),
(127, '2025_10_13_004200_update_settings_val_nullable', 48),
(128, '2025_10_13_142600_create_p2p_offer_feedback_table', 48),
(129, '2026_02_01_000001_create_p2p_settings_table', 48),
(130, '2026_02_24_000001_add_logo_to_p2p_payment_methods_table', 48),
(131, '2026_02_24_000002_add_order_id_to_p2p_offer_feedback_table', 48),
(132, '2026_02_26_000001_create_p2p_promotion_packages_table', 48),
(133, '2026_02_26_000002_create_p2p_offer_promotions_table', 48),
(134, '2026_02_26_000003_create_p2p_offer_promotion_purchases_table', 48),
(135, '2026_03_01_000004_add_builder_fields_to_p2p_promotion_packages_table', 48),
(136, '2026_03_11_154700_create_p2p_payment_accounts_table', 48),
(137, '2026_03_11_154800_add_payment_account_fields_to_p2p_orders_table', 48),
(138, '2026_03_11_203000_upgrade_p2p_payment_schema_for_dynamic_fields', 48),
(139, '2026_03_23_120000_add_p2p_dispute_uniqueness_and_performance_indexes', 48),
(140, '2026_04_20_134508_create_features_table', 49),
(141, '2026_04_20_134513_create_feature_access_rules_table', 49),
(142, '2026_04_21_000002_add_p2p_trading_suspension_to_users_table', 50),
(143, '2026_04_21_142031_widen_p2p_settings_country_columns', 51),
(153, '2026_04_23_023155_create_wallet_earn_stakes_table', 52),
(154, '2026_04_23_023229_create_wallet_earn_rewards_table', 52),
(155, '2026_04_23_072633_add_wallet_earn_highlight_fields_to_wallet_earn_plans_table', 53),
(156, '2026_04_23_023132_create_wallet_earn_plans_table', 54),
(157, '2026_04_23_111021_add_icon_to_wallet_earn_plans_table', 55),
(158, '2026_04_25_063107_create_background_task_logs_table', 56),
(159, '2026_04_25_113300_create_subscription_plans_table', 57),
(160, '2026_04_25_113301_create_subscription_plan_features_table', 58),
(161, '2026_04_25_113302_create_user_subscriptions_table', 58),
(163, '2026_04_25_113303_create_subscription_transactions_table', 59),
(164, '2026_04_26_121037_create_subscription_plan_prices_table', 59),
(165, '2026_04_26_121216_add_billing_cycle_to_user_subscriptions_table', 59),
(166, '2026_04_26_164203_add_discount_to_subscription_plan_prices_table', 60),
(167, '2026_04_28_100000_add_capabilities_to_virtual_card_providers_table', 61),
(168, '2026_04_29_120000_relax_p2p_payment_accounts_unique_for_soft_deletes', 62),
(169, '2026_04_29_165300_add_brand_color_and_display_label_to_virtual_card_providers_table', 62),
(170, '2026_04_29_180000_add_provider_universal_fields_to_cardholders_and_businesses', 62),
(171, '2026_04_29_190000_add_theme_to_virtual_card_requests', 63),
(172, '2026_04_29_200000_add_supported_countries_to_virtual_card_providers', 64),
(173, '2026_04_30_000001_widen_payment_gateways_withdraw_field', 65),
(174, '2026_04_30_010000_relax_virtual_card_columns_for_failed_issuance', 66),
(175, '2026_04_30_020000_restrict_bitnob_virtual_card_provider_to_visa', 66),
(176, '2026_04_30_021000_update_demo_bitnob_cardholder_public_kyc_images', 67),
(177, '2026_04_30_020000_add_paystack_payout_support', 68),
(178, '2026_04_30_030000_add_paymob_payment_gateway', 69),
(179, '2026_05_01_000000_add_stripe_payout_support', 70),
(181, '2026_05_01_099000_add_role_to_users_table', 72),
(183, '2026_05_01_120000_create_payment_links_table', 74),
(184, '2026_05_01_130000_add_merchant_to_payment_links_table', 74),
(185, '2026_05_03_055534_add_ui_metadata_to_permissions_table', 75),
(186, '2026_05_03_091936_add_production_metadata_to_custom_landings_table', 76),
(187, '2026_05_03_120911_add_wallet_pin_to_users_table', 77),
(188, '2026_05_05_123515_create_notification_preferences_table', 78),
(189, '2026_05_06_150313_backfill_agent_profiles_for_agent_users', 79),
(190, '2026_05_07_010000_create_project_updater_tables', 80),
(191, '2026_05_08_195052_create_mobile_recharges_table', 81),
(192, '2026_05_08_195055_create_phone_verification_codes_table', 81),
(193, '2026_05_08_195059_add_phone_verified_at_to_users_table', 81),
(194, '2026_05_09_014620_add_phone_verification_enabled_to_users_table', 82),
(195, '2026_05_09_120010_add_country_to_mobile_recharges_table', 82),
(196, '2026_05_09_120020_create_mobile_recharge_providers_table', 83),
(197, '2026_05_09_134729_add_code_to_plugins_table', 84),
(200, '2026_05_01_100000_create_agents_table', 85),
(201, '2026_05_10_164242_create_agent_commission_rules_table', 85),
(202, '2026_05_10_164243_create_agent_operations_table', 85),
(203, '2026_05_10_172020_create_agent_commission_rule_assignments_table', 86),
(204, '2026_05_11_154051_create_agent_currencies_table', 87),
(205, '2026_05_11_210000_add_static_qr_fields_to_agents_table', 88),
(206, '2026_05_13_040940_create_merchant_deposit_methods_table', 89),
(207, '2026_05_13_041000_normalize_deposit_methods_schema', 89),
(208, '2026_05_13_093621_create_merchant_currencies_table', 89),
(209, '2026_05_16_071845_add_dismissed_notices_to_admins_table', 90),
(210, '2026_05_19_021706_create_gift_card_templates_table', 91),
(211, '2026_05_19_021709_create_gift_cards_table', 91),
(212, '2026_05_19_025027_backfill_gift_card_currency_roles', 92),
(213, '2026_05_19_112543_add_default_amount_to_gift_card_templates_table', 93),
(214, '2026_05_19_231107_add_signup_bonus_tracking_to_users_table', 94),
(215, '2026_05_19_192002_backfill_missing_page_components', 95),
(216, '2026_05_19_203547_add_breadcrumb_columns_to_pages_table', 96),
(217, '2026_05_20_010000_add_theme_to_page_components_table', 96),
(218, '2026_05_20_010100_drop_content_fields_from_page_components_table', 97),
(219, '2026_05_20_010200_add_page_id_to_page_components_table', 98);


-- ----------------------------------------------------------
-- Table: mobile_recharge_providers
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `mobile_recharge_providers`;
CREATE TABLE `mobile_recharge_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plugin_id` bigint unsigned DEFAULT NULL,
  `code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `supported_countries` json DEFAULT NULL,
  `supported_currencies` json DEFAULT NULL,
  `fee_fixed` decimal(18,8) NOT NULL DEFAULT '0.00000000',
  `fee_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `min_amount` decimal(18,8) NOT NULL DEFAULT '0.00000000',
  `max_amount` decimal(18,8) DEFAULT NULL,
  `config` json DEFAULT NULL,
  `order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_recharge_providers_code_unique` (`code`),
  KEY `mobile_recharge_providers_plugin_id_foreign` (`plugin_id`),
  KEY `mobile_recharge_providers_driver_index` (`driver`),
  KEY `mobile_recharge_providers_status_index` (`status`),
  KEY `mobile_recharge_providers_is_default_index` (`is_default`),
  CONSTRAINT `mobile_recharge_providers_plugin_id_foreign` FOREIGN KEY (`plugin_id`) REFERENCES `plugins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `mobile_recharge_providers` (`id`, `plugin_id`, `code`, `name`, `driver`, `logo`, `description`, `status`, `is_default`, `supported_countries`, `supported_currencies`, `fee_fixed`, `fee_percent`, `min_amount`, `max_amount`, `config`, `order`, `created_at`, `updated_at`) VALUES
(1, 15, 'sandbox', 'Sandbox (Testing)', 'sandbox', 'general/static/plugins/sandbox-recharge.svg', 'Local sandbox driver. Use it to verify wallet flows without contacting any real provider.', 1, 1, NULL, NULL, '23.00000000', '0.00', '10.00000000', '10000.00000000', '[]', 1, '2026-05-09 07:15:58', '2026-05-09 18:21:30'),
(2, 16, 'http', 'Generic HTTP API', 'http', 'general/static/plugins/http-recharge.svg', 'Bring your own provider that exposes a REST endpoint and bearer-token auth.', 1, 0, NULL, NULL, '0.00000000', '0.00', '10.00000000', '10000.00000000', '[]', 2, '2026-05-09 07:15:58', '2026-05-09 14:01:20'),
(3, 17, 'reloadly', 'Reloadly (Global Airtime)', 'reloadly', 'general/static/plugins/reloadly-recharge.svg', 'Global airtime aggregator covering 180+ countries. Supports both sandbox and production environments.', 1, 0, NULL, NULL, '0.00000000', '2.00', '1.00000000', '50000.00000000', '{\"default_country\": \"BD\", \"use_local_amount\": true}', 3, '2026-05-09 07:15:58', '2026-05-09 18:21:30');


-- ----------------------------------------------------------
-- Table: mobile_recharges
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `mobile_recharges`;
CREATE TABLE `mobile_recharges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `transaction_id` bigint unsigned DEFAULT NULL,
  `phone_number` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` decimal(18,8) NOT NULL,
  `fee` decimal(18,8) NOT NULL DEFAULT '0.00000000',
  `total_amount` decimal(18,8) NOT NULL,
  `currency` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `provider_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mobile_recharges_wallet_id_foreign` (`wallet_id`),
  KEY `mobile_recharges_transaction_id_foreign` (`transaction_id`),
  KEY `mobile_recharges_user_id_status_index` (`user_id`,`status`),
  KEY `mobile_recharges_phone_number_created_at_index` (`phone_number`,`created_at`),
  KEY `mobile_recharges_provider_reference_index` (`provider_reference`),
  KEY `mobile_recharges_status_index` (`status`),
  KEY `mobile_recharges_country_index` (`country`),
  CONSTRAINT `mobile_recharges_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `mobile_recharges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `mobile_recharges_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: model_has_permissions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `model_has_permissions`;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: model_has_roles
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `model_has_roles`;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: navigations
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `navigations`;
CREATE TABLE `navigations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` json NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_id` bigint unsigned DEFAULT NULL,
  `order` int unsigned NOT NULL DEFAULT '0',
  `target` enum('_self','_blank') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '_self',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `navigations_slug_unique` (`slug`),
  KEY `navigations_page_id_foreign` (`page_id`),
  CONSTRAINT `navigations_page_id_foreign` FOREIGN KEY (`page_id`) REFERENCES `pages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `navigations` (`id`, `name`, `slug`, `page_id`, `order`, `target`, `is_active`, `created_at`, `updated_at`) VALUES
(15, '{\"en\": \"Home\", \"es\": \"Hogar\"}', '/', 1, 1, '_self', 1, '2025-04-08 10:09:07', '2025-05-04 07:25:04'),
(16, '{\"en\": \"About\", \"es\": \"Acerca de\"}', 'about', 4, 2, '_self', 1, '2025-04-08 10:10:10', '2025-05-04 07:25:04'),
(17, '{\"en\": \"Privacy\", \"es\": \"Privacidad\"}', 'privacy', 5, 3, '_self', 1, '2025-04-08 10:10:31', '2025-05-25 08:28:25'),
(19, '{\"en\": \"Blog\", \"es\": \"Blog\"}', 'blog', 2, 4, '_self', 1, '2025-04-12 10:32:14', '2025-04-12 10:32:14');


-- ----------------------------------------------------------
-- Table: notification_preferences
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `notification_preferences`;
CREATE TABLE `notification_preferences` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `tune_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `tune_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_tune` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_notifiable_unique` (`notifiable_type`,`notifiable_id`),
  KEY `notification_preferences_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: notification_template_channels
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `notification_template_channels`;
CREATE TABLE `notification_template_channels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL,
  `channel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notification_template_channels_template_id_foreign` (`template_id`),
  CONSTRAINT `notification_template_channels_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `notification_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=304 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notification_template_channels` (`id`, `template_id`, `channel`, `title`, `message`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'email', 'New KYC Submission', 'User {user} submitted a KYC verification request using {kyc_type}. Please review it.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(2, 1, 'push', 'KYC Submission Alert', '{user} submitted KYC ({kyc_type}).', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(3, 1, 'sms', NULL, '{user} submitted KYC type: {kyc_type}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(4, 2, 'email', 'Your KYC is Approved', 'Your KYC verification using {kyc_type} has been approved. You can now access all features.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(5, 2, 'push', 'KYC Approved', 'Your KYC ({kyc_type}) is approved.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(6, 2, 'sms', NULL, 'Your KYC ({kyc_type}) is approved.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(7, 3, 'email', 'Your KYC Was Rejected', 'We’re sorry, your KYC verification using {kyc_type} has been rejected. Reason: {rejection_reason}. Please re-submit with valid documents.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(8, 3, 'push', 'KYC Rejected', 'Your KYC ({kyc_type}) was rejected. Reason: {rejection_reason}', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(9, 3, 'sms', NULL, 'KYC ({kyc_type}) rejected. Reason: {rejection_reason}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(10, 4, 'email', 'Deposit Completed', 'Your deposit of {amount} via {method} has been successfully completed. Transaction ID: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(11, 4, 'push', 'Deposit Confirmed', 'Deposit {amount} via {method} is now complete.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(12, 4, 'sms', NULL, 'Deposit {amount} via {method} successful. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(13, 5, 'email', 'Auto Deposit Logged', 'User {user} has completed an automatic deposit of {amount} via {method}. Transaction ID: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(14, 5, 'push', 'New Auto Deposit', '{user} completed auto deposit of {amount}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(15, 5, 'sms', NULL, '{user} deposited {amount} via {method}. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(16, 6, 'email', 'Deposit Request Received', 'We have received your deposit request of {amount} via {method}. Transaction ID: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(17, 6, 'push', 'Request Submitted', 'You submitted a deposit request for {amount}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(18, 6, 'sms', NULL, 'Deposit {amount} via {method} submitted. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(19, 7, 'email', 'Deposit Request Submitted', 'User {user} submitted a deposit request of {amount} via {method}. Trx: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(20, 7, 'push', 'Deposit Request Alert', '{user} requested deposit of {amount}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(21, 7, 'sms', NULL, '{user} requested deposit {amount}. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(22, 8, 'email', 'Deposit Approved', 'Your deposit of {amount} via {method} has been approved and added to your wallet.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(23, 8, 'push', 'Deposit Approved', '{amount} added to your balance.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(24, 8, 'sms', NULL, 'Deposit {amount} approved.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(25, 9, 'email', 'Deposit Rejected', 'Your deposit of {amount} via {method} was rejected. Reason: {reason}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(26, 9, 'push', 'Deposit Declined', 'Deposit {amount} rejected. Reason: {reason}', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(27, 9, 'sms', NULL, 'Deposit {amount} rejected. Reason: {reason}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(28, 10, 'email', 'Money Sent', 'You sent {amount} to {recipient}. Trx: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(29, 10, 'push', 'Money Sent', '{amount} sent to {recipient}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(30, 10, 'sms', NULL, 'Sent {amount} to {recipient}. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(31, 11, 'email', 'Money Received', 'You received {amount} from {sender}. Trx: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(32, 11, 'push', 'Money Received', '{amount} received from {sender}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(33, 11, 'sms', NULL, 'Received {amount} from {sender}. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(34, 12, 'email', 'Request Sent', 'You requested {amount} from {recipient}. Trx: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(35, 12, 'push', 'Request Sent', '{amount} requested from {recipient}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(36, 12, 'sms', NULL, 'Requested {amount} from {recipient}. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(37, 13, 'email', 'Request Received', 'You have a request of {amount} from {sender}. Trx: {trx}.', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(38, 13, 'push', 'Request Received', 'Incoming request {amount} from {sender}.', 1, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(39, 13, 'sms', NULL, 'Request of {amount} from {sender}. Trx: {trx}', 0, '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(40, 14, 'email', 'Request Approved', 'Your money request of {amount} has been approved by {receiver}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(41, 14, 'push', 'Request Approved', 'Request of {amount} approved by {receiver}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(42, 14, 'sms', NULL, 'Approved: {amount} by {receiver}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(43, 15, 'email', 'Request Rejected', 'Your money request of {amount} was rejected by {receiver}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(44, 15, 'push', 'Request Rejected', 'Request of {amount} rejected by {receiver}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(45, 15, 'sms', NULL, 'Rejected: {amount} by {receiver}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(46, 16, 'email', 'Exchange Completed', 'You exchanged {from_amount} {from_currency} to {to_amount} {to_currency}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(47, 16, 'push', 'Exchange Successful', 'Exchanged {from_amount} {from_currency} → {to_amount} {to_currency}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(48, 16, 'sms', NULL, 'Exchanged {from_amount} to {to_amount}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(49, 17, 'email', 'Voucher Redeemed', 'You redeemed voucher {voucher_code} worth {amount}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(50, 17, 'push', 'Voucher Redeemed', '{amount} added from voucher.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(51, 17, 'sms', NULL, 'Voucher {voucher_code} redeemed. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(52, 18, 'email', 'Payment Successful', 'Paid {amount} to {merchant}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(53, 18, 'push', 'Payment Completed', 'Payment of {amount} successful.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(54, 18, 'sms', NULL, 'Paid {amount} to {merchant}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(55, 19, 'email', 'Payment Received', 'Received {amount} from payer. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(56, 19, 'push', 'New Payment', '{payer} paid you {amount}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(57, 19, 'sms', NULL, 'Payment of {amount} by {payer}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(58, 20, 'email', 'Balance Credited', 'Admin {admin} added {amount}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(59, 20, 'push', 'Balance Added', '{amount} credited by admin.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(60, 20, 'sms', NULL, 'Added {amount} by admin {admin}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(61, 21, 'email', 'Balance Deducted', 'Admin {admin} deducted {amount}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(62, 21, 'push', 'Balance Deducted', '{amount} deducted by admin.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(63, 21, 'sms', NULL, 'Deducted {amount} by {admin}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(64, 22, 'email', 'Withdraw Request', 'User {user} requested withdrawal of {amount} via {method}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(65, 22, 'push', 'Withdraw Request', '{user} requested {amount} withdraw.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(66, 22, 'sms', NULL, '{user} requested withdraw {amount}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(67, 23, 'email', 'Auto Withdraw Logged', 'User {user} completed auto withdrawal of {amount}. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(68, 23, 'push', 'Auto Withdraw Completed', '{user} auto withdrew {amount}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(69, 23, 'sms', NULL, '{user} auto withdraw {amount}. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(70, 24, 'email', 'Withdraw Request Submitted', 'Your withdrawal request of {amount} via {method} has been submitted. Trx: {trx}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(71, 24, 'push', 'Withdraw Requested', 'You requested withdraw of {amount}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(72, 24, 'sms', NULL, 'Withdraw {amount} requested. Trx: {trx}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(73, 25, 'email', 'Withdraw Approved', 'Your withdrawal of {amount} via {method} has been approved.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(74, 25, 'push', 'Withdraw Approved', '{amount} approved for withdrawal.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(75, 25, 'sms', NULL, 'Withdrawal {amount} approved.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(76, 26, 'email', 'Withdraw Rejected', 'Your withdrawal of {amount} via {method} was rejected. Reason: {reason}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(77, 26, 'push', 'Withdraw Rejected', 'Withdraw {amount} rejected.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(78, 26, 'sms', NULL, 'Withdrawal {amount} rejected. Reason: {reason}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(79, 27, 'email', 'Referral Reward', 'You earned {amount} for referring {referred_user}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(80, 27, 'push', 'Referral Reward', 'Earned {amount} reward.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(81, 27, 'sms', NULL, 'Referral reward {amount} earned.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(82, 28, 'email', 'Reward Granted', 'You received {amount}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(83, 28, 'push', 'Reward Received', '{amount} reward credited for achieving a new rank..', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(84, 28, 'sms', NULL, 'Reward {amount} received.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(85, 29, 'email', 'New Ticket Submitted', 'User {user} opened ticket #{ticket_number}: {subject}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(86, 29, 'push', 'New Support Ticket', 'Ticket {ticket_number} created by {user}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(87, 29, 'sms', NULL, 'New ticket #{ticket_number} from {user}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(88, 30, 'email', 'Support Reply', 'We replied to ticket {ticket_number}: {subject}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(89, 30, 'push', 'Ticket Reply', 'Reply to ticket #{ticket_number}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(90, 30, 'sms', NULL, 'Reply for ticket #{ticket_number}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(91, 31, 'email', 'Reply on Ticket #{ticket_number}', 'User {user} replied to ticket #{ticket_number}: {subject}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(92, 31, 'push', 'Ticket #{ticket_number} Reply', '{user} replied to support ticket #{ticket_number}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(93, 31, 'sms', NULL, 'User {user} replied on ticket #{ticket_number}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(94, 32, 'email', 'Ticket Closed', 'Your ticket #{ticket_number} is now closed.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(95, 32, 'push', 'Ticket Closed', 'Ticket {ticket_number} closed.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(96, 32, 'sms', NULL, 'Ticket #{ticket_number} closed.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(97, 33, 'email', 'New Merchant Shop Request', 'Merchant {user} requested a new shop named \"{business_name}\" with website: {site_url} and contact: {business_email}. Please review.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(98, 33, 'push', 'New Merchant Request', '{user} submitted new shop: {business_name}.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(99, 33, 'sms', NULL, 'Shop \"{business_name}\" requested by {user}.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(100, 34, 'email', 'Shop Approved', 'Good news! Your shop \"{business_name}\" has been approved. You can now start accepting payments.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(101, 34, 'push', 'Shop Approved', 'Shop \"{business_name}\" is approved.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(102, 34, 'sms', NULL, 'Shop \"{business_name}\" approved. Start using it.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(103, 35, 'email', 'Shop Request Rejected', 'Sorry, your shop \"{business_name}\" was rejected. Reason: {rejection_reason}. Please update and resubmit.', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(104, 35, 'push', 'Shop Rejected', 'Your shop \"{business_name}\" was rejected.', 1, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(105, 35, 'sms', NULL, 'Shop \"{business_name}\" rejected. Reason: {rejection_reason}', 0, '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(220, 37, 'email', 'New Virtual Card Request', 'User {user} submitted a virtual card request for {network} network (wallet: {wallet}). Please review and approve.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(221, 37, 'push', 'Virtual Card Request', '{user} requested a {network} card.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(222, 37, 'sms', NULL, 'Virtual card request: {user}, {network}, {wallet}', 0, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(223, 62, 'email', 'Welcome — your signup bonus is here!', 'Hi {name}, we\'ve credited {amount} to your wallet as a welcome bonus. Start exploring your dashboard now.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(224, 62, 'push', 'Signup Bonus Received', 'You received {amount} as a welcome bonus.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(225, 62, 'sms', NULL, 'Welcome bonus of {amount} credited to your wallet.', 0, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(226, 36, 'email', 'Your Virtual Card is Ready!', 'Congratulations! Your {card_network} card (****{last4}) has been approved and added to your wallet ({wallet}). Issuing fee: {fee}.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(227, 36, 'push', 'Virtual Card Approved', 'Your {card_network} card is approved.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(228, 36, 'sms', NULL, '{card_network} card (****{last4}) approved.', 0, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(229, 45, 'email', 'New Wallet Earn Stake Pending Review', '{user} created a {amount} Wallet Earn stake in {plan}. Expected profit: {expected_profit}. Transaction: {trx}. Please review it.', 1, '2026-05-19 23:13:25', '2026-05-19 23:13:25'),
(230, 45, 'push', 'Wallet Earn Review Needed', '{user} staked {amount} in {plan}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(231, 45, 'sms', NULL, 'Wallet Earn review: {user}, {amount}, {plan}, trx {trx}', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(232, 46, 'email', 'Wallet Earn Stake Created', 'Your {amount} Wallet Earn stake in {plan} was created with {status} status. Expected profit: {expected_profit}. Next payout: {next_payout_at}. Maturity: {maturity_date}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(233, 46, 'push', 'Wallet Earn Stake Created', 'Your {amount} stake in {plan} is {status}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(234, 46, 'sms', NULL, 'Wallet Earn stake {amount} in {plan} is {status}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(235, 47, 'email', 'Wallet Earn Stake Approved', 'Your {amount} stake in {plan} is approved and active. Expected profit: {expected_profit}. First payout: {next_payout_at}. Maturity: {maturity_date}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(236, 47, 'push', 'Wallet Earn Approved', '{plan} stake is active. First payout: {next_payout_at}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(237, 47, 'sms', NULL, 'Wallet Earn approved: {amount} in {plan}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(238, 48, 'email', 'Wallet Earn Stake Rejected', 'Your {amount} stake in {plan} was rejected and the principal was returned. Reason: {review_note}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(239, 48, 'push', 'Wallet Earn Rejected', '{plan} stake was rejected. Principal returned.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(240, 48, 'sms', NULL, 'Wallet Earn rejected: {amount} in {plan}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(241, 49, 'email', 'Wallet Earn Stake Canceled', 'Your {amount} stake in {plan} was canceled and the principal was returned. Note: {review_note}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(242, 49, 'push', 'Wallet Earn Canceled', '{plan} stake was canceled. Principal returned.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(243, 49, 'sms', NULL, 'Wallet Earn canceled: {amount} in {plan}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(244, 50, 'email', 'Wallet Earn Reward Paid', 'Reward payout #{payout_number} from {plan} has been paid: {profit}. Total profit paid so far: {paid_profit}. Next payout: {next_payout_at}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(245, 50, 'push', 'Wallet Earn Reward Paid', 'Payout #{payout_number} paid: {profit}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(246, 50, 'sms', NULL, 'Wallet Earn payout #{payout_number}: {profit}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(247, 51, 'email', 'Wallet Earn Stake Completed', 'Your {amount} stake in {plan} is complete. Total profit paid: {paid_profit}. Principal returned: {principal_returned}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(248, 51, 'push', 'Wallet Earn Completed', '{plan} completed. Profit paid: {paid_profit}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(249, 51, 'sms', NULL, 'Wallet Earn completed: {plan}, profit {paid_profit}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(250, 52, 'email', 'Your {plan} Trial Has Started', 'Your free trial for {plan} has started. Billing cycle: {cycle}. Trial ends at {trial_ends_at}. Auto-renew: {auto_renew}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(251, 52, 'push', '{plan} Trial Started', 'Your trial runs until {trial_ends_at}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(252, 52, 'sms', NULL, '{plan} trial started. Ends: {trial_ends_at}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(253, 53, 'email', 'Your {plan} Subscription is Active', 'Your {plan} subscription is active. Billing cycle: {cycle}. Amount charged: {amount}. Access runs through {period_end}. Transaction: {trx}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(254, 53, 'push', 'Subscription Active', '{plan} is active until {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(255, 53, 'sms', NULL, '{plan} active. Charged {amount}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(256, 54, 'email', 'Subscription Switched to {plan}', 'Your subscription was switched from {previous_plan} to {plan}. Billing cycle: {cycle}. Prorated credit: {credit}. Charged: {charge}. Access runs through {period_end}. Transaction: {trx}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(257, 54, 'push', 'Plan Switched', 'You switched from {previous_plan} to {plan}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(258, 54, 'sms', NULL, 'Subscription switched to {plan}. Charged {charge}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(259, 55, 'email', '{plan} Subscription Renewed', 'Your {plan} subscription has been renewed for {cycle}. Amount charged: {amount}. New access end: {period_end}. Transaction: {trx}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(260, 55, 'push', 'Subscription Renewed', '{plan} renewed until {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(261, 55, 'sms', NULL, '{plan} renewed. Charged {amount}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(262, 56, 'email', '{plan} Trial Converted', 'Your {plan} trial converted to an active subscription. Billing cycle: {cycle}. Amount charged: {amount}. Access runs through {period_end}. Transaction: {trx}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(263, 56, 'push', 'Trial Converted', '{plan} is now active until {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(264, 56, 'sms', NULL, '{plan} trial converted. Charged {amount}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(265, 57, 'email', '{plan} Renewal Needs Attention', 'We could not complete renewal for {plan}. Your grace period runs until {grace_ends_at}. Please add funds or renew before access expires.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(266, 57, 'push', 'Grace Period Started', '{plan} grace access ends at {grace_ends_at}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(267, 57, 'sms', NULL, '{plan} renewal failed. Grace ends {grace_ends_at}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(268, 58, 'email', '{plan} Subscription Expired', 'Your {plan} subscription has expired. Status: {status}. Renew your subscription to restore premium access.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(269, 58, 'push', 'Subscription Expired', '{plan} access has expired.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(270, 58, 'sms', NULL, '{plan} subscription expired.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(271, 59, 'email', '{plan} Subscription Cancelled', 'Your {plan} subscription cancellation is confirmed by {cancelled_by}. Current status: {status}. Access is available until {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(272, 59, 'push', 'Subscription Cancelled', '{plan} cancellation confirmed. Access until {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(273, 59, 'sms', NULL, '{plan} cancelled. Access until {period_end}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(274, 60, 'email', '{plan} Subscription Activated', 'Your {plan} subscription was activated by the admin team. Billing cycle: {cycle}. Access runs through {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(275, 60, 'push', 'Subscription Activated', '{plan} is active until {period_end}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(276, 60, 'sms', NULL, '{plan} activated until {period_end}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(277, 38, 'email', 'New Agent Account Request', 'User {user} submitted a new agent account request for \"{agent_name}\" (code: {agent_code}). Supported currencies: {currencies}. Operating note: {operating_note}. Please review and approve.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(278, 38, 'push', 'Agent Account Request', '{user} requested an agent account for {agent_name} ({currencies}).', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(279, 38, 'sms', NULL, 'Agent request: {user}, {agent_name}, {currencies}', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(280, 39, 'email', 'Your Agent Account is Approved', 'Congratulations! Your agent account \"{agent_name}\" has been approved and is now live for these currencies: {currencies}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(281, 39, 'push', 'Agent Approved', 'Your agent account \"{agent_name}\" is approved for {currencies}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(282, 39, 'sms', NULL, 'Agent approved: {agent_name} ({currencies})', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(283, 40, 'email', 'Agent Request Rejected', 'Your agent account request for \"{agent_name}\" ({currencies}) was rejected. Reason: {rejection_reason}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(284, 40, 'push', 'Agent Rejected', 'Your agent request \"{agent_name}\" was rejected.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(285, 40, 'sms', NULL, 'Agent rejected: {agent_name} ({currencies}) - {rejection_reason}', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(286, 41, 'email', 'QR Cash-Out Waiting for Cash Handover', '{customer} confirmed {amount} cash-out at {agent_name}. Reference: {reference}. Pay cash only after matching this reference.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(287, 41, 'push', 'QR Cash-Out Waiting', '{customer} confirmed {amount}. Match reference {reference} before paying cash.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(288, 41, 'sms', NULL, 'QR cash-out {amount}, ref {reference}. Pay cash after matching.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(289, 42, 'email', 'Cash-Out OTP', 'Your cash-out OTP for {amount} at {agent_name} is {otp}. It expires in {expires_minutes} minutes. Share it only with the counter agent when you are present.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(290, 42, 'push', 'Cash-Out OTP', 'OTP {otp} for {amount} at {agent_name}. Expires in {expires_minutes} minutes.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(291, 42, 'sms', NULL, 'Cash-out OTP {otp} for {amount} at {agent_name}. Expires in {expires_minutes} min.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(292, 43, 'email', 'Cash-Out Confirmed', 'You confirmed {amount} cash-out at {agent_name}. Reference: {reference}. Collect cash from the agent after they match the reference.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(293, 43, 'push', 'Cash-Out Confirmed', '{amount} cash-out confirmed. Reference: {reference}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(294, 43, 'sms', NULL, 'Cash-out confirmed {amount}. Ref {reference}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(295, 44, 'email', 'Cash Paid by Agent', '{agent_name} marked {amount} cash-out as paid. Reference: {reference}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(296, 44, 'push', 'Cash Paid', '{agent_name} marked {amount} as cash paid. Ref: {reference}.', 1, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(297, 44, 'sms', NULL, 'Cash paid by {agent_name}. {amount}, ref {reference}.', 0, '2026-05-19 23:13:26', '2026-05-19 23:13:26'),
(301, 61, 'email', 'Gift Card Redeemed Successfully', 'Your gift card {gift_code} for {amount} has been redeemed and credited to your wallet. Transaction reference: {trx}.', 1, '2026-05-19 19:03:19', '2026-05-19 19:03:19'),
(302, 61, 'push', 'Gift Card Redeemed', '{amount} has been added to your wallet from gift card {gift_code}.', 1, '2026-05-19 19:03:19', '2026-05-19 19:03:19'),
(303, 61, 'sms', NULL, 'Gift card {gift_code} redeemed: {amount} added to your wallet.', 0, '2026-05-19 19:03:19', '2026-05-19 19:03:19');


-- ----------------------------------------------------------
-- Table: notification_templates
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `notification_templates`;
CREATE TABLE `notification_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `action_type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `info` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variables` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_templates_identifier_unique` (`identifier`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notification_templates` (`id`, `identifier`, `user_type`, `action_type`, `name`, `icon`, `info`, `variables`, `created_at`, `updated_at`) VALUES
(1, 'kyc_admin_notify_submission', 'admin', 'requested', 'KYC Verification Requested', 'kyc-alert', 'Admin alert when a user submits a KYC verification request.', '[\"user\", \"kyc_type\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(2, 'kyc_user_notify_approved', 'user', 'approved', 'KYC Approved', 'kyc-approved', 'Notifies user when their KYC is approved by admin.', '[\"kyc_type\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(3, 'kyc_user_notify_rejected', 'user', 'rejected', 'KYC Rejected', 'kyc-rejected', 'Notifies user when their KYC verification is rejected by admin.', '[\"kyc_type\", \"rejection_reason\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(4, 'deposit_user_auto_success', 'user', 'completed', 'Automatic Deposit Completed', 'deposit-auto', 'Triggered after user completes auto deposit using gateway.', '[\"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(5, 'deposit_admin_auto_processed', 'admin', 'logged', 'Auto Deposit Logged', 'deposit-log', 'Admin log for user deposit made automatically via gateway.', '[\"user\", \"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(6, 'deposit_user_submitted', 'user', 'requested', 'Manual Deposit Submitted', 'deposit-request', 'Triggered when user submits a manual deposit request.', '[\"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(7, 'deposit_admin_notify_submission', 'admin', 'requested', 'Manual Deposit Requested', 'deposit-alert', 'Admin alert when user submits manual deposit.', '[\"user\", \"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(8, 'deposit_user_approved', 'user', 'completed', 'Deposit Approval Notification', 'deposit-success', 'Sent to user after admin approves manual deposit.', '[\"amount\", \"method\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(9, 'deposit_user_rejected', 'user', 'rejected', 'Deposit Rejected Notification', 'deposit-failed', 'Sent when admin rejects user deposit request.', '[\"amount\", \"method\", \"reason\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(10, 'send_money_user_sent', 'user', 'completed', 'Money Transfer Confirmation', 'send-money', 'Notify user after sending money successfully.', '[\"amount\", \"recipient\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(11, 'send_money_user_received', 'user', 'completed', 'Money Received Notification', 'receive-money', 'Notify user when they receive money from another wallet.', '[\"amount\", \"sender\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(12, 'request_money_user_requested', 'user', 'requested', 'Money Request Submitted', 'request-money', 'Notify requestor when they send a money request.', '[\"amount\", \"recipient\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(13, 'request_money_user_received', 'user', 'requested', 'Money Request Received', 'request-received', 'Notify user of incoming money request.', '[\"amount\", \"sender\", \"trx\"]', '2025-05-20 11:14:36', '2025-05-20 11:14:36'),
(14, 'request_money_user_approved', 'user', 'completed', 'Money Request Approved', 'request-approved', 'Notify requestor when their money request is approved by the recipient.', '[\"amount\", \"receiver\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(15, 'request_money_user_rejected', 'user', 'rejected', 'Money Request Rejected', 'request-rejected', 'Notify requestor when their money request is rejected by the recipient.', '[\"amount\", \"receiver\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(16, 'exchange_money_user_exchanged', 'user', 'completed', 'Currency Exchange Completed', 'exchange-money', 'Notify user after successful currency exchange.', '[\"from_amount\", \"from_currency\", \"to_amount\", \"to_currency\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(17, 'voucher_user_redeemed', 'user', 'completed', 'Voucher Redemption Confirmed', 'voucher', 'Notify user after redeeming voucher successfully.', '[\"amount\", \"voucher_code\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(18, 'payment_user_made', 'user', 'completed', 'Wallet Payment Completed', 'wallet-payment', 'Notify user when they pay via wallet.', '[\"amount\", \"merchant\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(19, 'payment_user_received', 'user', 'completed', 'Payment Received from User', 'wallet-receive', 'Notify receiver when wallet payment is received.', '[\"amount\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(20, 'admin_balance_added', 'user', 'completed', 'Balance Added by Admin', 'balance-add', 'Notify user when admin adds wallet balance.', '[\"amount\", \"admin\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(21, 'admin_balance_subtracted', 'user', 'completed', 'Balance Deducted by Admin', 'balance-subtract', 'Notify user when admin deducts wallet balance.', '[\"amount\", \"admin\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(22, 'withdraw_admin_manual_submitted', 'admin', 'requested', 'Manual Withdraw Requested', 'withdraw-alert', 'Notify admin when user requests withdrawal.', '[\"user\", \"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(23, 'withdraw_admin_auto_processed', 'admin', 'logged', 'Auto Withdraw Processed', 'withdraw-log', 'Notify admin when auto withdrawal completes.', '[\"user\", \"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(24, 'withdraw_user_requested', 'user', 'requested', 'Withdraw Request Submitted', 'withdraw-request', 'Notify user after submitting withdrawal request.', '[\"amount\", \"method\", \"trx\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(25, 'withdraw_user_approved', 'user', 'completed', 'Withdraw Approved', 'withdraw-success', 'Notify user when admin approves withdrawal.', '[\"amount\", \"method\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(26, 'withdraw_user_rejected', 'user', 'rejected', 'Withdraw Rejected', 'withdraw-failed', 'Notify user if withdrawal is rejected.', '[\"amount\", \"method\", \"reason\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(27, 'reward_user_referral', 'user', 'completed', 'Referral Reward Earned', 'reward-referral', 'Notify user when they earn referral reward.', '[\"amount\", \"referred_user\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(28, 'reward_user_system', 'user', 'completed', 'Achievement Reward Granted', 'reward-achievement', 'Notify user when system reward is granted.', '[\"amount\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(29, 'support_user_created', 'admin', 'created', 'Support Ticket Created', 'support-open', 'Notify admin when a user opens support ticket.', '[\"user\", \"ticket_number\", \"subject\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(30, 'support_admin_replied', 'user', 'completed', 'Support Reply Notification', 'support-reply-admin', 'Notify user when admin replies to ticket.', '[\"ticket_number\", \"subject\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(31, 'support_user_replied', 'admin', 'completed', 'Support Ticket Reply from User', 'support-reply', 'Notify admin when a user replies to a support ticket.', '[\"user\", \"ticket_number\", \"subject\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(32, 'support_user_closed', 'user', 'completed', 'Support Ticket Closed', 'support-closed', 'Notify user when ticket is closed.', '[\"ticket_number\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(33, 'merchant_admin_notify_shop_request', 'admin', 'requested', 'Merchant Shop Request', 'merchant-alert', 'Admin alert when a merchant submits a new shop/business for approval.', '[\"user\", \"business_name\", \"business_email\", \"site_url\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(34, 'merchant_user_notify_shop_approved', 'user', 'approved', 'Merchant Shop Approved', 'merchant-approved', 'Notifies merchant when their shop is approved by admin.', '[\"business_name\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(35, 'merchant_user_notify_shop_rejected', 'user', 'rejected', 'Merchant Shop Rejected', 'merchant-rejected', 'Notifies merchant when their shop is rejected by admin.', '[\"business_name\", \"rejection_reason\"]', '2025-05-20 11:14:37', '2025-05-20 11:14:37'),
(36, 'virtual_card_user_approved', 'user', 'approved', 'Virtual Card Approved', 'card-approved', 'User is notified when their virtual card request is approved.', '[\"card_network\", \"last4\", \"wallet\", \"fee\"]', '2025-06-17 03:55:47', '2025-06-17 03:55:47'),
(37, 'virtual_card_admin_notify_request', 'admin', 'requested', 'Virtual Card Request Submission', 'card-request', 'Admin is alerted when a user submits a new virtual card request.', '[\"user\", \"network\", \"wallet\"]', '2025-06-17 03:55:47', '2025-06-17 03:55:47'),
(38, 'agent_admin_notify_request', 'admin', 'requested', 'Agent Request Submission', 'card-request', 'Admin is alerted when a user submits a new agent registration request.', '[\"user\", \"agent_name\", \"agent_code\", \"currencies\", \"operating_note\", \"email\", \"phone\"]', '2026-05-01 02:07:51', '2026-05-11 23:08:08'),
(39, 'agent_user_notify_request_approved', 'user', 'approved', 'Agent Request Approved', 'card-approved', 'User is notified when their agent registration request is approved.', '[\"agent_name\", \"currencies\"]', '2026-05-01 02:07:51', '2026-05-11 23:08:09'),
(40, 'agent_user_notify_request_rejected', 'user', 'rejected', 'Agent Request Rejected', 'card-request', 'User is notified when their agent registration request is rejected.', '[\"agent_name\", \"currencies\", \"rejection_reason\"]', '2026-05-01 02:07:51', '2026-05-11 23:08:09'),
(41, 'agent_qr_cash_out_requested', 'user', 'requested', 'Agent QR Cash-Out Request', 'qrcode', 'Agent is notified when a customer confirms cash-out by scanning the static agent QR.', '[\"agent_name\", \"customer\", \"amount\", \"reference\", \"currency\", \"cash_out_link\"]', '2026-05-11 23:08:09', '2026-05-11 23:08:09'),
(42, 'agent_assisted_cash_out_otp', 'user', 'requested', 'Agent Assisted Cash-Out OTP', 'password', 'Customer receives an OTP before an agent can complete assisted cash-out from the counter.', '[\"agent_name\", \"customer\", \"amount\", \"otp\", \"expires_minutes\"]', '2026-05-11 23:08:09', '2026-05-11 23:08:09'),
(43, 'agent_qr_cash_out_customer_confirmed', 'user', 'requested', 'Customer QR Cash-Out Confirmed', 'send-money', 'Customer is notified after confirming wallet debit from an agent QR cash-out.', '[\"agent_name\", \"amount\", \"reference\"]', '2026-05-11 23:08:09', '2026-05-11 23:08:09'),
(44, 'agent_qr_cash_out_cash_paid', 'user', 'completed', 'Agent QR Cash Paid', 'card-approved', 'Customer is notified when the agent marks QR cash-out cash handover as paid.', '[\"agent_name\", \"amount\", \"reference\"]', '2026-05-11 23:08:09', '2026-05-11 23:08:09'),
(45, 'wallet_earn_admin_stake_pending', 'admin', 'requested', 'Wallet Earn Stake Pending Review', 'trending-up', 'Admin is alerted when a Wallet Earn stake is waiting for manual review.', '[\"user\", \"plan\", \"amount\", \"expected_profit\", \"status\", \"trx\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(46, 'wallet_earn_user_stake_created', 'user', 'created', 'Wallet Earn Stake Created', 'trending-up', 'User is notified after creating a Wallet Earn stake.', '[\"plan\", \"amount\", \"expected_profit\", \"status\", \"next_payout_at\", \"maturity_date\", \"trx\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(47, 'wallet_earn_user_stake_approved', 'user', 'approved', 'Wallet Earn Stake Approved', 'card-approved', 'User is notified when a pending Wallet Earn stake is approved.', '[\"plan\", \"amount\", \"expected_profit\", \"next_payout_at\", \"maturity_date\", \"review_note\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(48, 'wallet_earn_user_stake_rejected', 'user', 'rejected', 'Wallet Earn Stake Rejected', 'card-request', 'User is notified when a pending Wallet Earn stake is rejected and principal is returned.', '[\"plan\", \"amount\", \"review_note\", \"trx\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(49, 'wallet_earn_user_stake_canceled', 'user', 'rejected', 'Wallet Earn Stake Canceled', 'card-request', 'User is notified when a Wallet Earn stake is canceled and principal is returned.', '[\"plan\", \"amount\", \"review_note\", \"trx\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(50, 'wallet_earn_user_reward_paid', 'user', 'completed', 'Wallet Earn Reward Paid', 'money-plus', 'User is notified when a Wallet Earn reward payout is credited.', '[\"plan\", \"profit\", \"payout_number\", \"paid_profit\", \"next_payout_at\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(51, 'wallet_earn_user_stake_completed', 'user', 'completed', 'Wallet Earn Stake Completed', 'card-approved', 'User is notified when a Wallet Earn stake reaches completion.', '[\"plan\", \"amount\", \"paid_profit\", \"principal_returned\", \"maturity_date\"]', '2026-05-13 11:27:07', '2026-05-13 11:27:07'),
(52, 'subscription_user_trial_started', 'user', 'created', 'Subscription Trial Started', 'notification', 'User is notified when a paid subscription trial starts without an immediate charge.', '[\"plan\", \"cycle\", \"trial_ends_at\", \"auto_renew\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(53, 'subscription_user_started', 'user', 'created', 'Subscription Started', 'card-approved', 'User is notified when a subscription becomes active after checkout.', '[\"plan\", \"cycle\", \"amount\", \"period_end\", \"trx\", \"auto_renew\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(54, 'subscription_user_plan_switched', 'user', 'created', 'Subscription Plan Switched', 'layer', 'User is notified when they switch from one subscription plan to another.', '[\"previous_plan\", \"plan\", \"cycle\", \"charge\", \"credit\", \"period_end\", \"trx\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(55, 'subscription_user_renewed', 'user', 'completed', 'Subscription Renewed', 'card-approved', 'User is notified when a subscription renewal succeeds.', '[\"plan\", \"cycle\", \"amount\", \"period_end\", \"trx\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(56, 'subscription_user_trial_converted', 'user', 'completed', 'Subscription Trial Converted', 'card-approved', 'User is notified when a trial converts to a paid active subscription.', '[\"plan\", \"cycle\", \"amount\", \"period_end\", \"trx\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(57, 'subscription_user_grace_started', 'user', 'failed', 'Subscription Grace Period Started', 'warning-2', 'User is notified when a renewal or trial conversion fails and grace access starts.', '[\"plan\", \"period_end\", \"grace_ends_at\", \"auto_renew\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(58, 'subscription_user_expired', 'user', 'failed', 'Subscription Expired', 'warning-2', 'User is notified when subscription access expires.', '[\"plan\", \"status\", \"period_end\", \"grace_ends_at\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(59, 'subscription_user_cancelled', 'user', 'rejected', 'Subscription Cancelled', 'close', 'User is notified when subscription cancellation is confirmed.', '[\"plan\", \"cancelled_by\", \"period_end\", \"status\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(60, 'subscription_user_admin_activated', 'user', 'approved', 'Subscription Activated by Admin', 'card-approved', 'User is notified when an admin manually activates a subscription.', '[\"plan\", \"cycle\", \"period_end\"]', '2026-05-13 11:31:55', '2026-05-13 11:31:55'),
(61, 'gift_card_redeemed', 'user', 'completed', 'Gift Card Redeemed', 'card-approved', 'User is notified when a gift card they hold is successfully redeemed into their wallet.', '[\"amount\", \"gift_code\", \"trx\"]', '2026-05-19 16:40:04', '2026-05-19 16:40:04'),
(62, 'signup_bonus_credited', 'user', 'approved', 'Signup Bonus Credited', 'reward', 'User is notified when their welcome / signup bonus has been credited to their wallet.', '[\"amount\", \"name\"]', '2026-05-19 23:13:25', '2026-05-19 23:13:25');


-- ----------------------------------------------------------
-- Table: notifications
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_disputes
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_disputes`;
CREATE TABLE `p2p_disputes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `raised_by` bigint unsigned NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `resolution` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `p2p_disputes_order_unique` (`order_id`),
  KEY `p2p_disputes_order_id_raised_by_index` (`order_id`,`raised_by`),
  KEY `p2p_disputes_raised_by_foreign` (`raised_by`),
  KEY `p2p_disputes_status_created_idx` (`status`,`created_at`),
  CONSTRAINT `p2p_disputes_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `p2p_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_disputes_raised_by_foreign` FOREIGN KEY (`raised_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_offer_feedback
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_offer_feedback`;
CREATE TABLE `p2p_offer_feedback` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `p2p_offer_feedback_order_id_user_id_unique` (`order_id`,`user_id`),
  KEY `p2p_offer_feedback_offer_id_index` (`offer_id`),
  KEY `p2p_offer_feedback_user_id_index` (`user_id`),
  KEY `p2p_offer_feedback_order_id_index` (`order_id`),
  CONSTRAINT `p2p_offer_feedback_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `p2p_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_feedback_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `p2p_orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_feedback_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_offer_payment_method
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_offer_payment_method`;
CREATE TABLE `p2p_offer_payment_method` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `p2p_offer_payment_method_offer_id_payment_method_id_unique` (`offer_id`,`payment_method_id`),
  KEY `p2p_offer_payment_method_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `p2p_offer_payment_method_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `p2p_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_payment_method_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `p2p_payment_methods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (no data)


-- ----------------------------------------------------------
-- Table: p2p_offer_promotion_purchases
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_offer_promotion_purchases`;
CREATE TABLE `p2p_offer_promotion_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned DEFAULT NULL,
  `trx_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_price` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `base_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_amount` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `paid_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_rate` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `duration_minutes` int unsigned NOT NULL DEFAULT '0',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `p2p_offer_promotion_purchases_offer_id_index` (`offer_id`),
  KEY `p2p_offer_promotion_purchases_user_id_index` (`user_id`),
  KEY `p2p_offer_promotion_purchases_trx_id_index` (`trx_id`),
  KEY `p2p_offer_promotion_purchases_ends_at_index` (`ends_at`),
  KEY `p2p_offer_promotion_purchases_package_id_foreign` (`package_id`),
  KEY `p2p_offer_promotion_purchases_wallet_id_foreign` (`wallet_id`),
  CONSTRAINT `p2p_offer_promotion_purchases_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `p2p_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_promotion_purchases_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `p2p_promotion_packages` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `p2p_offer_promotion_purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_promotion_purchases_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_offer_promotions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_offer_promotions`;
CREATE TABLE `p2p_offer_promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `package_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned DEFAULT NULL,
  `trx_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `base_price` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `base_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `paid_amount` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `paid_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exchange_rate` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `starts_at` timestamp NULL DEFAULT NULL,
  `ends_at` timestamp NULL DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `p2p_offer_promotions_offer_id_unique` (`offer_id`),
  KEY `p2p_offer_promotions_user_id_index` (`user_id`),
  KEY `p2p_offer_promotions_status_ends_at_index` (`status`,`ends_at`),
  KEY `p2p_offer_promotions_package_id_index` (`package_id`),
  KEY `p2p_offer_promotions_trx_id_index` (`trx_id`),
  KEY `p2p_offer_promotions_wallet_id_foreign` (`wallet_id`),
  CONSTRAINT `p2p_offer_promotions_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `p2p_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_promotions_package_id_foreign` FOREIGN KEY (`package_id`) REFERENCES `p2p_promotion_packages` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `p2p_offer_promotions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offer_promotions_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_offers
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_offers`;
CREATE TABLE `p2p_offers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `side` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(24,8) NOT NULL,
  `min_amount` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `max_amount` decimal(24,8) DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `payment_window_minutes` int NOT NULL DEFAULT '45',
  `terms` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `p2p_offers_user_id_wallet_id_index` (`user_id`,`wallet_id`),
  KEY `p2p_offers_wallet_id_foreign` (`wallet_id`),
  KEY `p2p_offers_status_side_idx` (`status`,`side`),
  KEY `p2p_offers_user_status_idx` (`user_id`,`status`),
  CONSTRAINT `p2p_offers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_offers_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_orders
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_orders`;
CREATE TABLE `p2p_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `offer_id` bigint unsigned NOT NULL,
  `maker_id` bigint unsigned NOT NULL,
  `taker_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned DEFAULT NULL,
  `payer_payment_account_id` bigint unsigned DEFAULT NULL,
  `receiver_payment_account_id` bigint unsigned DEFAULT NULL,
  `payer_payment_account_snapshot` json DEFAULT NULL,
  `receiver_payment_account_snapshot` json DEFAULT NULL,
  `price` decimal(24,8) NOT NULL,
  `amount` decimal(24,8) NOT NULL,
  `maker_fee` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `taker_fee` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `total` decimal(24,8) NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `paid_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `disputed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `trx_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `p2p_orders_offer_id_maker_id_taker_id_index` (`offer_id`,`maker_id`,`taker_id`),
  KEY `p2p_orders_payment_method_id_index` (`payment_method_id`),
  KEY `p2p_orders_payer_payment_account_id_index` (`payer_payment_account_id`),
  KEY `p2p_orders_receiver_payment_account_id_index` (`receiver_payment_account_id`),
  KEY `p2p_orders_status_expires_idx` (`status`,`expires_at`),
  KEY `p2p_orders_maker_status_idx` (`maker_id`,`status`),
  KEY `p2p_orders_taker_status_idx` (`taker_id`,`status`),
  KEY `p2p_orders_wallet_status_idx` (`wallet_id`,`status`),
  KEY `p2p_orders_trx_id_idx` (`trx_id`),
  CONSTRAINT `p2p_orders_maker_id_foreign` FOREIGN KEY (`maker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_orders_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `p2p_offers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_orders_payer_payment_account_id_foreign` FOREIGN KEY (`payer_payment_account_id`) REFERENCES `p2p_payment_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `p2p_orders_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `p2p_payment_methods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `p2p_orders_receiver_payment_account_id_foreign` FOREIGN KEY (`receiver_payment_account_id`) REFERENCES `p2p_payment_accounts` (`id`) ON DELETE SET NULL,
  CONSTRAINT `p2p_orders_taker_id_foreign` FOREIGN KEY (`taker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_orders_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_payment_accounts
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_payment_accounts`;
CREATE TABLE `p2p_payment_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `payment_method_id` bigint unsigned NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `account_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `field_values` json DEFAULT NULL,
  `display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `p2p_payment_accounts_user_id_payment_method_id_index` (`user_id`,`payment_method_id`),
  KEY `p2p_payment_accounts_payment_method_id_foreign` (`payment_method_id`),
  CONSTRAINT `p2p_payment_accounts_payment_method_id_foreign` FOREIGN KEY (`payment_method_id`) REFERENCES `p2p_payment_methods` (`id`) ON DELETE CASCADE,
  CONSTRAINT `p2p_payment_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: p2p_payment_methods
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_payment_methods`;
CREATE TABLE `p2p_payment_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instructions` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fields` json DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `p2p_payment_methods_status_country_idx` (`status`,`country`),
  KEY `p2p_payment_methods_status_sort_idx` (`status`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `p2p_payment_methods` (`id`, `name`, `logo`, `country`, `instructions`, `fields`, `sort_order`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'bKash', 'images/p2p/payment-methods/bkash.svg', 'BD', 'Use your own verified bKash wallet. Confirm the sender number, amount, and reference before marking a trade as paid.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"bkash_number\", \"type\": \"text\", \"label\": \"bKash Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"account_type\", \"type\": \"select\", \"label\": \"Account Type\", \"options\": [\"Personal\", \"Agent\", \"Merchant\"], \"required\": true, \"sort_order\": 3}, {\"key\": \"default_reference\", \"type\": \"text\", \"label\": \"Default Reference / Note\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 10, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(2, 'Nagad', 'images/p2p/payment-methods/nagad.svg', 'BD', 'Save the Nagad number that is registered in your own name. Recheck the cash out or transfer reference before releasing crypto.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"nagad_number\", \"type\": \"text\", \"label\": \"Nagad Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"account_type\", \"type\": \"select\", \"label\": \"Account Type\", \"options\": [\"Personal\", \"Merchant\"], \"required\": true, \"sort_order\": 3}, {\"key\": \"district\", \"type\": \"text\", \"label\": \"District\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 20, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(3, 'Rocket', 'images/p2p/payment-methods/rocket.svg', 'BD', 'Use the Rocket wallet linked to your Dutch-Bangla Bank profile. The account owner name must match your verified account information.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"rocket_number\", \"type\": \"text\", \"label\": \"Rocket Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"account_type\", \"type\": \"select\", \"label\": \"Account Type\", \"options\": [\"Personal\", \"Agent\"], \"required\": true, \"sort_order\": 3}, {\"key\": \"linked_branch\", \"type\": \"text\", \"label\": \"Linked Branch (Optional)\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 30, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(4, 'Upay', 'images/p2p/payment-methods/upay.svg', 'BD', 'Save your Upay wallet details exactly as registered. Use the note field if you need to instruct traders to mention a specific reference.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"upay_number\", \"type\": \"text\", \"label\": \"Upay Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"account_type\", \"type\": \"select\", \"label\": \"Account Type\", \"options\": [\"Personal\", \"Merchant\"], \"required\": true, \"sort_order\": 3}, {\"key\": \"payment_note\", \"type\": \"textarea\", \"label\": \"Payment Note\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 40, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(5, 'SureCash', 'images/p2p/payment-methods/surecash.svg', 'BD', 'Use only a SureCash wallet that belongs to you. Confirm the registered mobile number and exact transfer amount before releasing funds.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"surecash_number\", \"type\": \"text\", \"label\": \"SureCash Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"institution_name\", \"type\": \"text\", \"label\": \"Institution / Agent Name\", \"options\": [], \"required\": false, \"sort_order\": 3}, {\"key\": \"default_reference\", \"type\": \"text\", \"label\": \"Default Reference\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 50, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(6, 'CellFin', 'images/p2p/payment-methods/cellfin.svg', 'BD', 'Use your verified CellFin-enabled Islami Bank account. Share clear receiving details for mobile transfer or bank transfer as needed.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"registered_mobile\", \"type\": \"text\", \"label\": \"Registered Mobile Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"bank_name\", \"type\": \"text\", \"label\": \"Bank Name\", \"options\": [], \"required\": true, \"sort_order\": 3}, {\"key\": \"account_number\", \"type\": \"text\", \"label\": \"Account Number\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 60, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(7, 'Bangladesh Bank Transfer', 'images/p2p/payment-methods/bangladesh-bank-transfer.svg', 'BD', 'Only Bangladesh bank accounts in your own name are supported. Share the exact bank, branch, and account details needed for EFT, NPSB, BEFTN, or regular transfer.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"bank_name\", \"type\": \"select\", \"label\": \"Bank Name\", \"options\": [\"DBBL\", \"BRAC Bank\", \"Islami Bank\", \"City Bank\", \"Eastern Bank\", \"Sonali Bank\", \"IFIC Bank\", \"Prime Bank\", \"Southeast Bank\", \"Other\"], \"required\": true, \"sort_order\": 2}, {\"key\": \"account_number\", \"type\": \"text\", \"label\": \"Account Number\", \"options\": [], \"required\": true, \"sort_order\": 3}, {\"key\": \"branch_name\", \"type\": \"text\", \"label\": \"Branch Name\", \"options\": [], \"required\": true, \"sort_order\": 4}, {\"key\": \"routing_number\", \"type\": \"text\", \"label\": \"Routing Number\", \"options\": [], \"required\": false, \"sort_order\": 5}, {\"key\": \"transfer_type\", \"type\": \"select\", \"label\": \"Preferred Transfer Type\", \"options\": [\"BEFTN\", \"NPSB\", \"RTGS\", \"Regular Transfer\"], \"required\": false, \"sort_order\": 6}]', 70, 1, '2026-04-03 09:55:28', '2026-05-19 16:12:49', NULL),
(8, 'Bangladesh Card to Card', 'images/p2p/payment-methods/bangladesh-card-to-card.svg', 'BD', 'Use only your own Bangladesh-issued debit or credit card details for supported card-to-card settlement flows.', '[{\"key\": \"card_holder_name\", \"type\": \"text\", \"label\": \"Card Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"bank_name\", \"type\": \"text\", \"label\": \"Issuing Bank\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"card_last_four\", \"type\": \"text\", \"label\": \"Card Last 4 Digits\", \"options\": [], \"required\": true, \"sort_order\": 3}, {\"key\": \"registered_mobile\", \"type\": \"text\", \"label\": \"Registered Mobile Number\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 80, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(9, 'Wise Account', 'images/p2p/payment-methods/wise-account.svg', NULL, 'Ensure your Wise profile is verified. Use only an account that belongs to you and matches your KYC information.', '[{\"key\": \"profile_name\", \"type\": \"text\", \"label\": \"Profile Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"wise_email\", \"type\": \"text\", \"label\": \"Wise Email\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"wise_currency\", \"type\": \"select\", \"label\": \"Preferred Currency\", \"options\": [\"USD\", \"EUR\", \"GBP\", \"AUD\", \"SGD\", \"BDT\"], \"required\": true, \"sort_order\": 3}, {\"key\": \"wise_profile_id\", \"type\": \"text\", \"label\": \"Wise Profile ID\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 110, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(10, 'Payoneer', 'images/p2p/payment-methods/payoneer.svg', NULL, 'We transfer only to verified Payoneer accounts. Make sure the registered name matches your DigiKash profile.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"payoneer_customer_id\", \"type\": \"text\", \"label\": \"Payoneer Customer ID\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"payoneer_email\", \"type\": \"text\", \"label\": \"Payoneer Email\", \"options\": [], \"required\": true, \"sort_order\": 3}]', 120, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(11, 'PayPal', 'images/p2p/payment-methods/paypal.svg', NULL, 'Use only a PayPal account owned by you. Double-check the PayPal email and account type before sharing it with traders.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"paypal_email\", \"type\": \"text\", \"label\": \"PayPal Email\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"paypal_type\", \"type\": \"select\", \"label\": \"PayPal Account Type\", \"options\": [\"Personal\", \"Business\"], \"required\": false, \"sort_order\": 3}]', 130, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(12, 'SEPA Bank Transfer', 'images/p2p/payment-methods/sepa-bank-transfer.svg', NULL, 'Send or receive payments only from a bank account that matches your verified name. Include the provided reference when required.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"iban\", \"type\": \"text\", \"label\": \"IBAN\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"bank_name\", \"type\": \"text\", \"label\": \"Bank Name\", \"options\": [], \"required\": true, \"sort_order\": 3}, {\"key\": \"swift_bic\", \"type\": \"text\", \"label\": \"SWIFT / BIC\", \"options\": [], \"required\": false, \"sort_order\": 4}]', 140, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(13, 'US Domestic Wire', 'images/p2p/payment-methods/us-domestic-wire.svg', 'US', 'Only ACH or domestic wire transfers from accounts in your own name are accepted.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"account_number\", \"type\": \"text\", \"label\": \"Account Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"routing_number\", \"type\": \"text\", \"label\": \"Routing Number (ABA)\", \"options\": [], \"required\": true, \"sort_order\": 3}, {\"key\": \"bank_name\", \"type\": \"text\", \"label\": \"Bank Name\", \"options\": [], \"required\": true, \"sort_order\": 4}]', 150, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(14, 'UPI Transfer', 'images/p2p/payment-methods/upi-transfer.svg', 'IN', 'Use only UPI IDs that belong to you. Confirm the transaction in your UPI app before marking the trade as paid.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"upi_id\", \"type\": \"text\", \"label\": \"UPI ID (VPA)\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"upi_app\", \"type\": \"select\", \"label\": \"Preferred UPI App\", \"options\": [\"PhonePe\", \"Google Pay\", \"Paytm\", \"BHIM\", \"Other\"], \"required\": false, \"sort_order\": 3}]', 160, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(15, 'PIX Instant Transfer', 'images/p2p/payment-methods/pix-instant-transfer.svg', 'BR', 'Provide the PIX key that matches your verified CPF or CNPJ. PIX transfers usually settle instantly.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"pix_key_type\", \"type\": \"select\", \"label\": \"PIX Key Type\", \"options\": [\"CPF\", \"CNPJ\", \"EMAIL\", \"PHONE\", \"EVP\"], \"required\": true, \"sort_order\": 2}, {\"key\": \"pix_key_value\", \"type\": \"text\", \"label\": \"PIX Key Value\", \"options\": [], \"required\": true, \"sort_order\": 3}]', 170, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(16, 'GCash', 'images/p2p/payment-methods/gcash.svg', 'PH', 'GCash number must match your verified mobile number. Confirm successful receipt before releasing funds.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"gcash_number\", \"type\": \"text\", \"label\": \"GCash Mobile Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"gcash_email\", \"type\": \"text\", \"label\": \"GCash Email\", \"options\": [], \"required\": false, \"sort_order\": 3}]', 180, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL),
(17, 'M-Pesa Kenya', 'images/p2p/payment-methods/mpesa-kenya.svg', 'KE', 'We only support Safaricom M-Pesa wallets. Use the phone number that is registered in your own name.', '[{\"key\": \"account_holder_name\", \"type\": \"text\", \"label\": \"Account Holder Name\", \"options\": [], \"required\": true, \"sort_order\": 1}, {\"key\": \"mpesa_phone\", \"type\": \"text\", \"label\": \"M-Pesa Phone Number\", \"options\": [], \"required\": true, \"sort_order\": 2}, {\"key\": \"id_number\", \"type\": \"text\", \"label\": \"National ID (Optional)\", \"options\": [], \"required\": false, \"sort_order\": 3}]', 190, 1, '2026-04-03 09:55:28', '2026-04-03 09:55:28', NULL);


-- ----------------------------------------------------------
-- Table: p2p_promotion_packages
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_promotion_packages`;
CREATE TABLE `p2p_promotion_packages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `base_currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_minutes` int unsigned NOT NULL DEFAULT '0',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `visibility` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PUBLIC',
  `billing_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FIXED',
  `daily_price` decimal(24,8) DEFAULT NULL,
  `per_trade_fee` decimal(24,8) DEFAULT NULL,
  `auto_renew_allowed` tinyint(1) NOT NULL DEFAULT '0',
  `features` json DEFAULT NULL,
  `accent_color` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_priority` int unsigned NOT NULL DEFAULT '0',
  `applies_to` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BOTH',
  `allowed_categories` json DEFAULT NULL,
  `max_active_per_user` int unsigned DEFAULT NULL,
  `max_impressions` bigint unsigned DEFAULT NULL,
  `cooldown_after_expiry_minutes` int unsigned DEFAULT NULL,
  `refund_policy` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NON_REFUNDABLE',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `p2p_promotion_packages_status_sort_order_index` (`status`,`sort_order`),
  KEY `p2p_promotion_packages_name_index` (`name`),
  KEY `p2p_promo_pkg_status_visibility_sort` (`status`,`visibility`,`sort_order`),
  KEY `p2p_promo_pkg_applies_to` (`applies_to`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `p2p_promotion_packages` (`id`, `name`, `price`, `base_currency`, `duration_minutes`, `sort_order`, `visibility`, `billing_type`, `daily_price`, `per_trade_fee`, `auto_renew_allowed`, `features`, `accent_color`, `search_priority`, `applies_to`, `allowed_categories`, `max_active_per_user`, `max_impressions`, `cooldown_after_expiry_minutes`, `refund_policy`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Starter Boost', '2.50000000', 'EUR', 720, 1, 'PUBLIC', 'FIXED', NULL, NULL, 0, '{\"featured_listing\": true, \"highlighted_card\": true}', 'BLUE', 15, 'BOTH', '[\"ALL\"]', 1, 3500, 180, 'NON_REFUNDABLE', 1, '2026-04-21 11:24:57', '2026-04-21 14:08:32', NULL),
(2, 'Buyer Priority', '3.90000000', 'EUR', 1440, 2, 'PUBLIC', 'FIXED', NULL, NULL, 1, '{\"featured_badge\": true, \"featured_listing\": true, \"search_priority_boost\": true}', 'BLUE', 28, 'BUY', '[\"CRYPTO\", \"LOCAL_PAYMENT\"]', 2, 7000, 120, 'NON_REFUNDABLE', 1, '2026-04-21 11:24:57', '2026-04-21 14:08:32', NULL),
(3, 'Seller Spotlight', '4.80000000', 'EUR', 1440, 3, 'PUBLIC', 'FIXED', NULL, NULL, 1, '{\"featured_badge\": true, \"highlighted_card\": true}', 'GOLD', 40, 'SELL', '[\"CRYPTO\", \"GIFT_CARD\"]', 2, 9000, 90, 'NON_REFUNDABLE', 1, '2026-04-21 11:24:57', '2026-04-21 14:08:32', NULL),
(4, 'Marketplace Daily Reach', '0.00000000', 'EUR', 4320, 4, 'PUBLIC', 'DAILY_PRICE', '3.25000000', NULL, 1, '{\"featured_listing\": true, \"highlighted_card\": true, \"search_priority_boost\": true}', 'BLUE', 52, 'BOTH', '[\"ALL\"]', 3, 18000, 60, 'NON_REFUNDABLE', 1, '2026-04-21 11:24:57', '2026-04-21 14:08:32', NULL),
(5, 'High Volume Trader Fee Saver', '0.00000000', 'EUR', 10080, 5, 'PUBLIC', 'PER_TRADE_FEE', NULL, '0.35000000', 1, '{\"featured_badge\": true, \"search_priority_boost\": true}', 'RED', 65, 'BOTH', '[\"CRYPTO\", \"LOCAL_PAYMENT\", \"GIFT_CARD\"]', 5, 30000, 30, 'NON_REFUNDABLE', 1, '2026-04-21 11:24:57', '2026-04-21 14:08:32', NULL),
(6, 'Elite Marketplace Dominance', '14.90000000', 'EUR', 4320, 6, 'PUBLIC', 'FIXED', NULL, NULL, 1, '{\"featured_badge\": true, \"featured_listing\": true, \"highlighted_card\": true, \"search_priority_boost\": true}', 'GOLD', 95, 'BOTH', '[\"ALL\"]', 2, 75000, 15, 'NON_REFUNDABLE', 1, '2026-04-21 11:24:57', '2026-04-21 14:08:32', NULL);


-- ----------------------------------------------------------
-- Table: p2p_settings
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `p2p_settings`;
CREATE TABLE `p2p_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `maker_fee_pct` decimal(8,4) NOT NULL DEFAULT '0.2000',
  `taker_fee_pct` decimal(8,4) NOT NULL DEFAULT '0.4000',
  `order_expiry_minutes` int unsigned NOT NULL DEFAULT '45',
  `dispute_window_minutes` int unsigned NOT NULL DEFAULT '120',
  `min_amount` decimal(18,8) NOT NULL DEFAULT '1.00000000',
  `max_amount` decimal(18,8) DEFAULT NULL,
  `allowed_countries` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `blocked_countries` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `p2p_settings` (`id`, `enabled`, `maker_fee_pct`, `taker_fee_pct`, `order_expiry_minutes`, `dispute_window_minutes`, `min_amount`, `max_amount`, `allowed_countries`, `blocked_countries`, `created_at`, `updated_at`) VALUES
(1, 1, '0.2000', '0.4000', 45, 120, '1.00000000', NULL, NULL, NULL, '2026-04-03 09:55:26', '2026-04-21 14:32:28');


-- ----------------------------------------------------------
-- Table: page_component_repeated_contents
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `page_component_repeated_contents`;
CREATE TABLE `page_component_repeated_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `component_id` bigint unsigned NOT NULL,
  `content_data` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_component_contents_component_id_foreign` (`component_id`),
  CONSTRAINT `page_component_contents_component_id_foreign` FOREIGN KEY (`component_id`) REFERENCES `page_components` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_component_repeated_contents` (`id`, `component_id`, `content_data`, `created_at`, `updated_at`) VALUES
(7, 2, '{\"about_text\": {\"en\": \"Experience fast and secure money transfers across the globe.\", \"es\": \"Experimenta transferencias de dinero rápidas y seguras en todo el mundo\"}, \"about_title\": {\"en\": \"Secure Transactions\", \"es\": \"Transacciones Seguras\"}, \"about_icon_class\": \"fas fa-hand-holding-dollar\"}', '2025-04-10 06:28:50', '2025-04-10 12:37:08'),
(8, 2, '{\"about_text\": {\"en\": \"Access your funds anytime, anywhere with multi-currency support.\", \"es\": \"Accede a tus fondos en todo momento con soporte multimoneda.\"}, \"about_title\": {\"en\": \"Global Wallet Access\", \"es\": \"Acceso Global a la Billetera\"}, \"about_icon_class\": \"fas fa-wallet\"}', '2025-04-10 06:30:04', '2025-04-10 12:33:39'),
(15, 4, '{\"feature_text\": {\"en\": \"Enjoy a smooth, consistent wallet experience across all devices with DigiKash, ensuring reliability and efficiency every time.\", \"es\": \"Disfruta de una experiencia de billetera fluida y consistente en todos los dispositivos con DigiKash, garantizando confiabilidad y eficiencia en todo momento.\"}, \"feature_title\": {\"en\": \"Stable Usability\", \"es\": \"Usabilidad Estable\"}, \"feature_icon_class\": \"icon-icon7\"}', '2025-04-10 23:27:20', '2025-07-15 05:50:21'),
(16, 4, '{\"feature_text\": {\"en\": \"Easily create and manage multiple wallets for personal savings, business transactions, and global investments through DigiKash.\", \"es\": \"Crea y gestiona fácilmente múltiples carteras para ahorros personales, transacciones comerciales e inversiones globales con DigiKash.\"}, \"feature_title\": {\"en\": \"Different Wallet\", \"es\": \"Cartera Diferente\"}, \"feature_icon_class\": \"icon-icon8\"}', '2025-04-10 23:27:20', '2025-04-10 23:27:20'),
(17, 4, '{\"feature_text\": {\"en\": \"Send, receive, and exchange money in multiple currencies instantly, all while enjoying the best rates through DigiKash.\", \"es\": \"Envía, recibe e intercambia dinero en múltiples monedas al instante, disfrutando siempre de las mejores tarifas con DigiKash.\"}, \"feature_title\": {\"en\": \"Multiple Currency\", \"es\": \"Moneda Múltiple\"}, \"feature_icon_class\": \"icon-icon9\"}', '2025-04-10 23:27:20', '2025-04-10 23:27:20'),
(18, 4, '{\"feature_text\": {\"en\": \"Experience ultra-fast money transfers worldwide with minimal fees, powered by DigiKash’s cutting-edge technology.\", \"es\": \"Experimenta transferencias de dinero ultrarrápidas en todo el mundo con tarifas mínimas, impulsadas por la tecnología avanzada de DigiKash.\"}, \"feature_title\": {\"en\": \"Fast Transaction\", \"es\": \"Transacción Rápida\"}, \"feature_icon_class\": \"icon-icon9\"}', '2025-04-10 23:27:20', '2025-04-10 23:27:20'),
(19, 4, '{\"feature_text\": {\"en\": \"Keep your money safe with DigiKash, where every transaction is protected by advanced, bank-grade security protocols.\", \"es\": \"Mantén tu dinero seguro con DigiKash, donde cada transacción está protegida por protocolos de seguridad avanzados de nivel bancario.\"}, \"feature_title\": {\"en\": \"Safe Transaction\", \"es\": \"Transacción Segura\"}, \"feature_icon_class\": \"icon-icon11\"}', '2025-04-10 23:27:20', '2025-04-10 23:27:20'),
(20, 4, '{\"feature_text\": {\"en\": \"Enjoy flexible deposit and withdrawal options with DigiKash, supporting multiple secure methods for your convenience.\", \"es\": \"Disfruta de opciones flexibles de depósito y retiro con DigiKash, que admite múltiples métodos seguros para tu comodidad.\"}, \"feature_title\": {\"en\": \"Various Method\", \"es\": \"Varios Métodos\"}, \"feature_icon_class\": \"icon-icon12\"}', '2025-04-10 23:27:20', '2025-04-10 23:27:20'),
(25, 5, '{\"service_text\": {\"en\": \"Send money instantly between DigiKash wallets worldwide with just a few clicks.\", \"es\": \"Envía dinero instantáneamente entre billeteras DigiKash en todo el mundo con solo unos clics.\"}, \"service_image\": \"images/2025/04/10/20250410_183859_01_6Eac.jpg\", \"service_title\": {\"en\": \"Instant Wallet Transfers\", \"es\": \"Transferencias Instantáneas de Billetera\"}}', '2025-04-11 00:37:02', '2025-06-30 12:04:55'),
(26, 5, '{\"service_text\": {\"en\": \"Operate with various global currencies and easily convert funds within DigiKash.\", \"es\": \"Opera con diversas monedas globales y convierte fondos fácilmente dentro de DigiKash.\"}, \"service_image\": \"images/2025/04/10/20250410_183908_02_Nd1O.jpg\", \"service_title\": {\"en\": \"Multi-Currency Support\", \"es\": \"Soporte Multimoneda\"}}', '2025-04-11 00:37:02', '2025-04-10 18:39:08'),
(27, 5, '{\"service_text\": {\"en\": \"Accept and make secure online payments for goods and services using DigiKash.\", \"es\": \"Acepta y realiza pagos en línea seguros para bienes y servicios utilizando DigiKash.\"}, \"service_image\": \"images/2025/04/10/20250410_183921_03_RLju.jpg\", \"service_title\": {\"en\": \"Secure Online Payments\", \"es\": \"Pagos en Línea Seguros\"}}', '2025-04-11 00:37:02', '2025-04-10 18:39:21'),
(28, 5, '{\"service_text\": {\"en\": \"Grow your business with DigiKash’s flexible and secure merchant payment solutions.\", \"es\": \"Haz crecer tu negocio con las soluciones de pago flexibles y seguras de DigiKash.\"}, \"service_image\": \"images/2025/04/10/20250410_190030_19199349_4V2O.jpg\", \"service_title\": {\"en\": \"Merchant Payment Solutions\", \"es\": \"Soluciones de Pago para Comerciantes\"}}', '2025-04-11 00:37:02', '2025-06-30 12:05:05'),
(29, 6, '{\"step_title\": {\"en\": \"Choose a Service\", \"es\": \"Elige un Servicio\"}, \"step_icon_class\": \"icon-icon1\", \"step_description\": {\"en\": \"Select the service you need from our platform with ease.\", \"es\": \"Selecciona el servicio que necesitas desde nuestra plataforma con facilidad.\"}}', '2025-04-11 07:54:42', '2025-04-11 07:54:42'),
(30, 6, '{\"step_title\": {\"en\": \"Define Requirements\", \"es\": \"Define Requisitos\"}, \"step_icon_class\": \"icon-icon2\", \"step_description\": {\"en\": \"Specify the requirements to help us tailor the solution for you.\", \"es\": \"Especifica los requisitos para que podamos adaptar la solución para ti.\"}}', '2025-04-11 07:54:42', '2025-04-11 07:54:42'),
(31, 6, '{\"step_title\": {\"en\": \"Request a Meeting\", \"es\": \"Solicitar una Reunión\"}, \"step_icon_class\": \"icon-icon3\", \"step_description\": {\"en\": \"Schedule a meeting with our team to discuss your project details.\", \"es\": \"Programa una reunión con nuestro equipo para discutir los detalles de tu proyecto.\"}}', '2025-04-11 07:54:42', '2025-04-11 07:54:42'),
(32, 6, '{\"step_title\": {\"en\": \"Final Solution Delivery\", \"es\": \"Entrega de la Solución Final\"}, \"step_icon_class\": \"icon-icon4\", \"step_description\": {\"en\": \"Receive the final solution that meets your expectations.\", \"es\": \"Recibe la solución final que cumpla con tus expectativas.\"}}', '2025-04-11 07:54:42', '2025-04-11 07:54:42'),
(37, 7, '{\"counter_title\": {\"en\": \"Total User\", \"es\": \"Total de Usuarios\"}, \"counter_number\": \"25623\", \"counter_prefix\": \"\", \"counter_suffix\": \"\"}', '2025-04-11 13:11:55', '2025-04-11 13:11:55'),
(38, 7, '{\"counter_title\": {\"en\": \"Total Money Sent\", \"es\": \"Total de Dinero Enviado\"}, \"counter_number\": \"3.5\", \"counter_prefix\": \"$\", \"counter_suffix\": \"M\"}', '2025-04-11 13:11:55', '2025-04-11 13:11:55'),
(39, 7, '{\"counter_title\": {\"en\": \"Total Received\", \"es\": \"Total Recibido\"}, \"counter_number\": \"6.5\", \"counter_prefix\": \"$\", \"counter_suffix\": \"M\"}', '2025-04-11 13:11:55', '2025-04-11 13:11:55'),
(40, 7, '{\"counter_title\": {\"en\": \"Daily Transaction\", \"es\": \"Transacciones Diarias\"}, \"counter_number\": \"59623\", \"counter_prefix\": \"\", \"counter_suffix\": \"\"}', '2025-04-11 13:11:55', '2025-04-11 13:11:55'),
(41, 8, '{\"feature_icon\": \"images/2025/04/11/20250411_123558_icon1_Y2MR.png\", \"feature_text\": {\"en\": \"Easily transfer money between your wallets and bank accounts securely.\", \"es\": \"Transfiere dinero fácilmente entre tus billeteras y cuentas bancarias de forma segura.\"}, \"feature_title\": {\"en\": \"Account Transfers\", \"es\": \"Transferencias de Cuenta\"}}', '2025-04-11 18:22:47', '2025-04-11 12:35:58'),
(42, 8, '{\"feature_icon\": \"images/2025/04/11/20250411_123605_icon2_bv3D.png\", \"feature_text\": {\"en\": \"Settle your transactions flexibly at your convenience with real-time tracking.\", \"es\": \"Liquida tus transacciones de manera flexible y con seguimiento en tiempo real.\"}, \"feature_title\": {\"en\": \"Flexible Settlement\", \"es\": \"Liquidación Flexible\"}}', '2025-04-11 18:22:47', '2025-04-11 12:36:05'),
(43, 8, '{\"feature_icon\": \"images/2025/04/11/20250411_123612_icon3_FBbg.png\", \"feature_text\": {\"en\": \"Quickly match your transactions for complete financial clarity and reports.\", \"es\": \"Conciliación rápida de transacciones para claridad y reportes financieros.\"}, \"feature_title\": {\"en\": \"Easy Reconciliation\", \"es\": \"Conciliación Fácil\"}}', '2025-04-11 18:22:47', '2025-04-11 12:36:12'),
(44, 8, '{\"feature_icon\": \"images/2025/04/11/20250411_123619_icon4_Ay3L.png\", \"feature_text\": {\"en\": \"Multiple payment channels to send and receive funds globally with ease.\", \"es\": \"Múltiples canales de pago para enviar y recibir fondos globalmente.\"}, \"feature_title\": {\"en\": \"Payment Channel Options\", \"es\": \"Opciones de Canales de Pago\"}}', '2025-04-11 18:22:47', '2025-04-11 12:36:19'),
(45, 8, '{\"feature_icon\": \"images/2025/04/11/20250411_123627_icon5_1Bfh.png\", \"feature_text\": {\"en\": \"Settle payments safely with our bank-grade encrypted settlement network.\", \"es\": \"Liquida pagos de forma segura con nuestra red de cifrado de nivel bancario.\"}, \"feature_title\": {\"en\": \"Secure Settlements\", \"es\": \"Liquidaciones Seguras\"}}', '2025-04-11 18:22:47', '2025-04-11 12:36:27'),
(46, 8, '{\"feature_icon\": \"images/2025/04/11/20250411_123634_icon6_DdCm.png\", \"feature_text\": {\"en\": \"Test all transactions risk-free with our fully functional sandbox mode.\", \"es\": \"Prueba todas las transacciones sin riesgo con nuestro modo sandbox funcional.\"}, \"feature_title\": {\"en\": \"Fully Featured Sandbox\", \"es\": \"Sandbox Completamente Funcional\"}}', '2025-04-11 18:22:47', '2025-04-11 12:36:34'),
(47, 9, '{\"rating\": \"5\", \"client_name\": {\"en\": \"Kristin Watson\", \"es\": \"Kristin Watson\"}, \"client_image\": \"images/2025/04/11/20250411_133154_client1_ubBg.jpg\", \"comment_text\": {\"en\": \"Using DigiKash has made my international transactions effortless and fast.\", \"es\": \"Usar DigiKash ha hecho que mis transacciones internacionales sean fáciles y rápidas.\"}, \"client_position\": {\"en\": \"Web Designer\", \"es\": \"Diseñador Web\"}}', '2025-04-11 19:30:55', '2025-04-11 13:31:54'),
(48, 9, '{\"rating\": \"4\", \"client_name\": {\"en\": \"Brooklyn Simmons\", \"es\": \"Brooklyn Simmons\"}, \"client_image\": \"images/2025/04/11/20250411_133220_client2_ZQos.jpg\", \"comment_text\": {\"en\": \"A reliable platform to manage and send money worldwide, highly recommended!\", \"es\": \"Una plataforma confiable para gestionar y enviar dinero en todo el mundo, ¡muy recomendada!\"}, \"client_position\": {\"en\": \"App Developer\", \"es\": \"Desarrollador de Aplicaciones\"}}', '2025-04-11 19:30:55', '2025-04-11 13:32:20'),
(49, 9, '{\"rating\": \"4\", \"client_name\": {\"en\": \"Darlene Robertson\", \"es\": \"Darlene Robertson\"}, \"client_image\": \"images/2025/04/11/20250411_133227_client3_U5Uj.jpg\", \"comment_text\": {\"en\": \"The best wallet app I have ever used — smooth experience and strong security.\", \"es\": \"La mejor aplicación de billetera que he usado: experiencia fluida y gran seguridad.\"}, \"client_position\": {\"en\": \"Freelancer\", \"es\": \"Freelancer\"}}', '2025-04-11 19:30:55', '2025-04-11 13:32:27'),
(50, 10, '{\"name\": {\"en\": \"Darlene Robertson\", \"es\": \"Darlene Robertson\"}, \"team_image\": \"images/2025/04/11/20250411_134922_team1_Ma8U.jpg\", \"designation\": {\"en\": \"Financial Advisor\", \"es\": \"Asesora Financiera\"}, \"twitter_url\": \"https://twitter.com/darlene\", \"facebook_url\": \"https://facebook.com/darlene\", \"linkedin_url\": \"https://linkedin.com/in/darlene\", \"pinterest_url\": \"https://pinterest.com/darlene\"}', '2025-04-11 19:48:44', '2025-04-11 13:49:22'),
(51, 10, '{\"name\": {\"en\": \"Leslie Alexander\", \"es\": \"Leslie Alexander\"}, \"team_image\": \"images/2025/04/11/20250411_134929_team2_8O92.jpg\", \"designation\": {\"en\": \"Account Manager\", \"es\": \"Gerente de Cuentas\"}, \"twitter_url\": \"https://twitter.com/leslie\", \"facebook_url\": \"https://facebook.com/leslie\", \"linkedin_url\": \"https://linkedin.com/in/leslie\", \"pinterest_url\": \"https://pinterest.com/leslie\"}', '2025-04-11 19:48:44', '2025-04-11 13:49:29'),
(52, 10, '{\"name\": {\"en\": \"Ralph Edwards\", \"es\": \"Ralph Edwards\"}, \"team_image\": \"images/2025/04/11/20250411_134935_team3_S0Un.jpg\", \"designation\": {\"en\": \"Payment Solutions Expert\", \"es\": \"Experto en Soluciones de Pago\"}, \"twitter_url\": \"https://twitter.com/ralph\", \"facebook_url\": \"https://facebook.com/ralph\", \"linkedin_url\": \"https://linkedin.com/in/ralph\", \"pinterest_url\": \"https://pinterest.com/ralph\"}', '2025-04-11 19:48:44', '2025-04-11 13:49:35'),
(53, 35, '{\"pillar_text\": {\"en\": \"Multi-signature cold vaults insured by Lloyd\'s syndicates — your keys answer to you alone.\"}, \"pillar_title\": {\"en\": \"Sovereign Custody\"}, \"pillar_icon_class\": \"fa-solid fa-shield-halved\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(54, 35, '{\"pillar_text\": {\"en\": \"Curated yield programmes calibrated by tenured strategists — never algorithmic, always deliberate.\"}, \"pillar_title\": {\"en\": \"Composed Growth\"}, \"pillar_icon_class\": \"fa-solid fa-chart-line\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(55, 35, '{\"pillar_text\": {\"en\": \"A concierge available across twelve time zones. No phone trees, no scripts, no surprises.\"}, \"pillar_title\": {\"en\": \"Discreet Access\"}, \"pillar_icon_class\": \"fa-solid fa-vault\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(56, 36, '{\"service_link\": \"#\", \"service_text\": {\"en\": \"A self-custodied vault for fiat and digital assets, sealed with biometric multi-factor protocol.\"}, \"service_title\": {\"en\": \"Sovereign Wallet\"}, \"service_icon_class\": \"fa-solid fa-vault\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(57, 36, '{\"service_link\": \"#\", \"service_text\": {\"en\": \"Move capital in 38 currencies with private-banking discretion and same-day settlement.\"}, \"service_title\": {\"en\": \"Cross-Border Transfer\"}, \"service_icon_class\": \"fa-solid fa-globe\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(58, 36, '{\"service_link\": \"#\", \"service_text\": {\"en\": \"Curated annual yields from 6% to 14% — vetted by our investment council, never automated.\"}, \"service_title\": {\"en\": \"Yield Programmes\"}, \"service_icon_class\": \"fa-solid fa-coins\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(59, 36, '{\"service_link\": \"#\", \"service_text\": {\"en\": \"The DigiKash Black — a metal card in seven finishes, accepted in 211 countries.\"}, \"service_title\": {\"en\": \"Private Card\"}, \"service_icon_class\": \"fa-solid fa-credit-card\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(60, 36, '{\"service_link\": \"#\", \"service_text\": {\"en\": \"Multi-generational succession plans, codified on-chain and notarised across three jurisdictions.\"}, \"service_title\": {\"en\": \"Estate Trust\"}, \"service_icon_class\": \"fa-solid fa-scale-balanced\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(61, 36, '{\"service_link\": \"#\", \"service_text\": {\"en\": \"A dedicated relationship director available across twelve time zones, day and night.\"}, \"service_title\": {\"en\": \"Concierge Advisory\"}, \"service_icon_class\": \"fa-solid fa-handshake\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(62, 37, '{\"feature_text\": {\"en\": \"Air-gapped vaults across three continents, audited quarterly by Big Four firms.\"}, \"feature_title\": {\"en\": \"Military-Grade Custody\"}, \"feature_number\": \"01\", \"feature_icon_class\": \"fa-solid fa-shield-halved\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(63, 37, '{\"feature_text\": {\"en\": \"Move six-figure sums in under twelve seconds, with zero perceptible friction.\"}, \"feature_title\": {\"en\": \"Instantaneous Settlement\"}, \"feature_number\": \"02\", \"feature_icon_class\": \"fa-solid fa-bolt\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(64, 37, '{\"feature_text\": {\"en\": \"A named relationship director, accessible by encrypted line — never a queue.\"}, \"feature_title\": {\"en\": \"Personal Director\"}, \"feature_number\": \"03\", \"feature_icon_class\": \"fa-solid fa-user-tie\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(65, 37, '{\"feature_text\": {\"en\": \"Curated allocations across fiat, digital and metals — assembled, never auto-generated.\"}, \"feature_title\": {\"en\": \"Composed Portfolios\"}, \"feature_number\": \"04\", \"feature_icon_class\": \"fa-solid fa-chart-pie\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(66, 37, '{\"feature_text\": {\"en\": \"Hold and remit in 38 currencies; settle in 211 territories without intermediary fees.\"}, \"feature_title\": {\"en\": \"Borderless by Design\"}, \"feature_number\": \"05\", \"feature_icon_class\": \"fa-solid fa-globe\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(67, 37, '{\"feature_text\": {\"en\": \"Identity-shielded transfers, sealed statements, and no data resale — ever.\"}, \"feature_title\": {\"en\": \"Absolute Discretion\"}, \"feature_number\": \"06\", \"feature_icon_class\": \"fa-solid fa-eye-slash\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(68, 38, '{\"spfeat_text\": {\"en\": \"Face, fingerprint and voiceprint, layered into a single signature.\"}, \"spfeat_title\": {\"en\": \"Biometric Seal\"}, \"spfeat_icon_class\": \"fa-solid fa-fingerprint\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(69, 38, '{\"spfeat_text\": {\"en\": \"Hand-picked yield instruments from 6% to 14%, reviewed monthly.\"}, \"spfeat_title\": {\"en\": \"Curated Yields\"}, \"spfeat_icon_class\": \"fa-solid fa-coins\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(70, 38, '{\"spfeat_text\": {\"en\": \"Move capital without revealing your counterparty\'s identity.\"}, \"spfeat_title\": {\"en\": \"Discreet Transfer\"}, \"spfeat_icon_class\": \"fa-solid fa-paper-plane\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(71, 38, '{\"spfeat_text\": {\"en\": \"Composed allocations across fiat, gold and digital assets — at a glance.\"}, \"spfeat_title\": {\"en\": \"Live Portfolio\"}, \"spfeat_icon_class\": \"fa-solid fa-chart-line\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(72, 38, '{\"spfeat_text\": {\"en\": \"Cold vaults insured by Lloyd\'s, multi-signature by default.\"}, \"spfeat_title\": {\"en\": \"Sovereign Custody\"}, \"spfeat_icon_class\": \"fa-solid fa-shield-halved\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(73, 38, '{\"spfeat_text\": {\"en\": \"A named director on the line within ninety seconds — every hour.\"}, \"spfeat_title\": {\"en\": \"24/7 Concierge\"}, \"spfeat_icon_class\": \"fa-solid fa-headset\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(74, 41, '{\"counter_title\": {\"en\": \"Private Members\"}, \"counter_number\": \"12\", \"counter_prefix\": \"\", \"counter_suffix\": \"M+\", \"counter_decimals\": \"0\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(75, 41, '{\"counter_title\": {\"en\": \"Uptime, Five Years\"}, \"counter_number\": \"99.9\", \"counter_prefix\": \"\", \"counter_suffix\": \"%\", \"counter_decimals\": \"1\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(76, 41, '{\"counter_title\": {\"en\": \"Assets Under Custody\"}, \"counter_number\": \"4.2\", \"counter_prefix\": \"$\", \"counter_suffix\": \"B\", \"counter_decimals\": \"1\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(77, 41, '{\"counter_title\": {\"en\": \"Jurisdictions Served\"}, \"counter_number\": \"180\", \"counter_prefix\": \"\", \"counter_suffix\": \"+\", \"counter_decimals\": \"0\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(78, 42, '{\"step_title\": {\"en\": \"Request Invitation\"}, \"step_number\": \"01\", \"step_icon_class\": \"fa-solid fa-envelope-open-text\", \"step_description\": {\"en\": \"A brief, encrypted introduction to determine fit.\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(79, 42, '{\"step_title\": {\"en\": \"Verify Identity\"}, \"step_number\": \"02\", \"step_icon_class\": \"fa-solid fa-id-card\", \"step_description\": {\"en\": \"Biometric and document review, completed in under nine minutes.\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(80, 42, '{\"step_title\": {\"en\": \"Activate Wallet\"}, \"step_number\": \"03\", \"step_icon_class\": \"fa-solid fa-vault\", \"step_description\": {\"en\": \"Sovereign keys generated and sealed within your private vault.\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(81, 42, '{\"step_title\": {\"en\": \"Begin Earning\"}, \"step_number\": \"04\", \"step_icon_class\": \"fa-solid fa-champagne-glasses\", \"step_description\": {\"en\": \"Allocate, transfer, or simply hold — your director awaits.\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(82, 43, '{\"rating\": \"5\", \"client_name\": {\"en\": \"Adrien Whitlock\"}, \"client_image\": \"images/golden/testimonial-1.svg\", \"comment_text\": {\"en\": \"DigiKash carries the patience of a private bank and the precision of a Swiss movement. Three years in, I have yet to encounter a single click that felt rushed.\"}, \"client_position\": {\"en\": \"Chairman · Whitlock Holdings\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(83, 43, '{\"rating\": \"5\", \"client_name\": {\"en\": \"Margaux Le Roy\"}, \"client_image\": \"images/golden/testimonial-2.svg\", \"comment_text\": {\"en\": \"My director answers within ninety seconds, signs his messages, and remembers my daughters\' names. This is wealth tooling as it ought to be — quiet, attentive, exact.\"}, \"client_position\": {\"en\": \"Family Office Principal\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(84, 43, '{\"rating\": \"5\", \"client_name\": {\"en\": \"Yusuf Demir\"}, \"client_image\": \"images/golden/testimonial-3.svg\", \"comment_text\": {\"en\": \"We tested four custodians for our succession trust. DigiKash was the only one whose statement of holdings I would frame. The yield was simply the dividend on that taste.\"}, \"client_position\": {\"en\": \"Trustee, Demir Heritage Foundation\"}}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(85, 44, '{\"name\": {\"en\": \"Elena Marchetti\"}, \"email\": \"elena@digikash.com\", \"team_image\": \"images/golden/team-1.svg\", \"designation\": {\"en\": \"Chief Custodian\"}, \"twitter_url\": \"#\", \"facebook_url\": \"\", \"linkedin_url\": \"#\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(86, 44, '{\"name\": {\"en\": \"Soren Halvorsen\"}, \"email\": \"soren@digikash.com\", \"team_image\": \"images/golden/team-2.svg\", \"designation\": {\"en\": \"Head of Yield Council\"}, \"twitter_url\": \"#\", \"facebook_url\": \"\", \"linkedin_url\": \"#\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24'),
(87, 44, '{\"name\": {\"en\": \"Aisha Okonkwo\"}, \"email\": \"aisha@digikash.com\", \"team_image\": \"images/golden/team-3.svg\", \"designation\": {\"en\": \"Director, Concierge\"}, \"twitter_url\": \"#\", \"facebook_url\": \"\", \"linkedin_url\": \"#\"}', '2026-05-20 07:22:24', '2026-05-20 07:22:24');


-- ----------------------------------------------------------
-- Table: page_components
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `page_components`;
CREATE TABLE `page_components` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `component_icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `component_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_data` json NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'classic',
  `sort` int DEFAULT NULL,
  `repeated_content` tinyint(1) NOT NULL DEFAULT '0',
  `page_id` int DEFAULT NULL,
  `is_protected` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Status of the component',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_components_theme_index` (`theme`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_components` (`id`, `component_icon`, `component_name`, `component_key`, `content_data`, `type`, `theme`, `sort`, `repeated_content`, `page_id`, `is_protected`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'general/static/component/banner.png', 'Banner', 'banner', '{\"heading\": {\"en\": \"Manage Your Money More Quickly\", \"es\": \"Administra tu dinero más rápido\"}, \"button_url\": \"/user/dashboard\", \"subheading\": {\"en\": \"Welcome to DigiKash Wallet\", \"es\": \"Bienvenido a la billetera DigiKash\"}, \"button_text\": {\"en\": \"My Account\", \"es\": \"Mi Billetera\"}, \"description\": {\"en\": \"Send, receive, deposit, request, invest, and exchange money globally in multiple currencies — easily, quickly, and safely, with great rates and low fees.\", \"es\": \"Envía, recibe, deposita, solicita, invierte y cambia dinero globalmente en múltiples monedas, de manera fácil, rápida y segura, con excelentes tarifas y bajas comisiones.\"}, \"shape_image_1\": \"images/2025/04/10/20250410_060504_frame_1_YXLr.png\", \"shape_image_2\": \"images/2025/04/10/20250410_060504_frame_2_P1Qe.png\", \"shape_image_3\": \"images/2025/04/10/20250410_060444_frame_3_T2YI.png\", \"hero_main_image\": \"images/2025/04/10/20250410_055802_hero_1_Q0SS.png\", \"background_image\": \"images/2025/04/10/20250410_055802_hero_bg_SfUo.png\"}', 'static', 'classic', 1, 0, NULL, 0, 1, NULL, '2025-05-20 14:12:15'),
(2, 'general/static/component/about.png', 'About', 'about', '{\"heading\": {\"en\": \"Why Choose DigiKash\", \"es\": \"Por qué elegir DigiKash\"}, \"button_url\": \"/user/wallet/list\", \"main_image\": \"images/2025/04/10/20250410_072254_a_1_OtCO.png\", \"subheading\": {\"en\": \"About DigiKash\", \"es\": \"Sobre DigiKash\"}, \"button_text\": {\"en\": \"Open Wallet\", \"es\": \"Abrir Billetera\"}, \"description\": {\"en\": \"Manage your money easily with DigiKash. Send, receive, and exchange funds instantly in multiple currencies — all with low fees and great rates.\", \"es\": \"Administra tu dinero fácilmente con DigiKash. Envía, recibe y cambia fondos al instante en múltiples monedas, con bajas comisiones y excelentes tarifas.\"}, \"bg_shape_image\": \"images/2025/04/10/20250410_072335_bg_shape_06d9.png\", \"title_bar_image\": \"images/2025/04/10/20250410_072622_title_bar_8RzP.png\", \"content_shape_image\": \"images/2025/04/10/20250410_072425_content_shape_HCJ4.png\", \"about_tool_shape_image\": \"images/2025/04/10/20250410_072604_tool_shape_Evex.png\", \"additional_description\": {\"en\": \"DigiKash empowers you to control your finances with ease — send, store, and spend globally with unmatched speed and security.\", \"es\": \"DigiKash te permite controlar tus finanzas con facilidad: envía, guarda y gasta a nivel global con velocidad y seguridad incomparables.\"}}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-07-19 05:36:51'),
(4, 'general/static/component/feature.png', 'Feature', 'feature', '{\"heading\": {\"en\": \"Special Key Features\", \"es\": \"Características Clave Especiales\"}, \"subheading\": {\"en\": \"Features\", \"es\": \"Características\"}, \"title_bar_image\": \"images/2025/04/10/20250410_164848_title_bar_EDPV.png\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-04-10 16:54:28'),
(5, 'general/static/component/services.png', 'Services', 'service', '{\"heading\": {\"en\": \"Our Perfect Solutions\", \"es\": \"Nuestras Soluciones Perfectas\"}, \"subheading\": {\"en\": \"Our Services\", \"es\": \"Nuestros Servicios\"}, \"title_bar_image\": \"images/2025/04/10/20250410_183109_title_bar_IAxb.png\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-04-10 18:31:09'),
(6, 'general/static/component/work_process.png', 'Work Process', 'work_process', '{\"heading\": {\"en\": \"Our Process\", \"es\": \"Nuestro Proceso\"}, \"subheading\": {\"en\": \"How It Works\", \"es\": \"Cómo Funciona\"}, \"title_bar_image\": \"images/2025/04/11/20250411_015314_title_bar_qAs5.png\", \"line_shape_image\": \"images/2025/04/11/20250411_015314_line_41DR.png\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-04-11 01:53:14'),
(7, 'general/static/component/offer.png', 'Offer & Counter', 'offer', '{\"button_url\": \"/register\", \"button_text\": {\"en\": \"Sign Up Now\", \"es\": \"Regístrate Ahora\"}, \"offer_title\": {\"en\": \"Get the Bonus Offer $99\", \"es\": \"Obtén la Oferta de Bono de $99\"}, \"background_image\": \"images/2025/04/11/20250411_070408_cta_offer_bg_BJfW.jpg\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-05-20 14:20:16'),
(8, 'general/static/component/special-feature.png', 'Special Feature', 'special_feature', '{\"heading\": {\"en\": \"Exploring Our Special Features\", \"es\": \"Explorando Nuestras Funciones Especiales\"}, \"subheading\": {\"en\": \"Special Features\", \"es\": \"Funciones Especiales\"}, \"description\": {\"en\": \"Easily manage your money, transfer securely, and track all transactions with real-time settlement and multi-currency support.\", \"es\": \"Administra fácilmente tu dinero, transfiere de forma segura y rastrea todas las transacciones con liquidaciones en tiempo real y soporte multimoneda.\"}, \"title_bar_image\": \"images/2025/04/11/20250411_123549_title_bar_7LBF.png\", \"feature_center_image\": \"images/2025/04/11/20250411_122827_feature_image_qsKq.jpg\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-04-11 12:35:49'),
(9, 'general/static/component/testimonial.png', 'Testimonial', 'testimonial', '{\"heading\": {\"en\": \"What People Say About DigiKash\", \"es\": \"Lo que la gente dice sobre DigiKash\"}, \"subheading\": {\"en\": \"Our Testimonials\", \"es\": \"Nuestros Testimonios\"}, \"title_bar_image\": \"images/2025/04/11/20250411_132416_title_bar_5ZSI.png\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-04-11 13:24:16'),
(10, 'general/static/component/team.png', 'Team', 'team', '{\"heading\": {\"en\": \"DigiKash Expert Team Members\", \"es\": \"Miembros Expertos del Equipo de DigiKash\"}, \"subheading\": {\"en\": \"Our Experts\", \"es\": \"Nuestros Expertos\"}, \"title_bar_image\": \"images/2025/04/11/20250411_134907_title_bar_wEBm.png\"}', 'static', 'classic', 1, 1, NULL, 0, 1, NULL, '2025-04-11 13:49:07'),
(11, 'general/static/component/blog.png', 'Blog', 'blog', '{\"heading\": {\"en\": \"Our Latest Blog Posts\", \"es\": \"Nuestras Últimas Entradas de Blog\"}, \"button_url\": \"/blog\", \"subheading\": {\"en\": \"Our Blog\", \"es\": \"Nuestro Blog\"}, \"button_text\": {\"en\": \"View All Blogs\", \"es\": \"Ver Todos los Blogs\"}, \"title_bar_image\": \"images/2025/04/11/20250411_145046_title_bar_cZ0e.png\"}', 'static', 'classic', 1, 0, NULL, 0, 1, NULL, '2025-05-20 14:18:04'),
(12, 'general/static/component/blog-standard.png', 'Blog Standard', 'blog_standard', '{}', 'static', 'classic', 1, 0, 2, 1, 1, NULL, '2025-04-11 14:50:46'),
(13, 'general/static/component/contact.png', 'Contact', 'contact', '{\"heading\": {\"en\": \"Let’s Get in Touch\", \"es\": \"Pongámonos en contacto\"}, \"subheading\": {\"en\": \"Contact Us\", \"es\": \"Contáctanos\"}, \"contact_image\": \"images/2025/04/13/20250413_181952_c1_MWgM.jpg\", \"title_bar_image\": \"images/2025/04/13/20250413_181952_title_bar_Hti0.png\"}', 'static', 'classic', 1, 0, NULL, 0, 1, NULL, '2025-04-13 18:19:52'),
(14, 'general/static/component/payment.png', 'Payment Partners', 'payment_partner', '{\"section_heading\": {\"en\": \"Our Payment Partners\", \"es\": \"Nuestros socios de pago\"}}', 'static', 'classic', 1, 0, NULL, 0, 1, NULL, '2025-04-14 09:25:21'),
(29, 'general/static/component/subscribed.png', 'Subscribed', 'subscribed', '{\"heading\": {\"en\": \"Get Subscribed Today!\", \"es\": \"¡Suscríbete hoy!\"}, \"button_text\": {\"en\": \"Subscribe\", \"es\": \"Suscribirse\"}, \"email_image\": \"images/2025/04/14/20250414_101517_email_xhcQ.png\", \"small_title\": {\"en\": \"Don’t Miss Our Future Updates!\", \"es\": \"¡No te pierdas nuestras futuras actualizaciones!\"}, \"dot_shape_image\": \"images/2025/04/14/20250414_101517_dot_shape_uPFR.png\"}', 'static', 'classic', 1, 0, NULL, 0, 1, NULL, '2025-04-14 10:15:17'),
(30, 'images/2025/05/17/20250517_010841_compliant_1_DM5w.png', 'Privecy', 'privecy', '{\"content\": {\"en\": \"<div><h3><span style=\\\"font-weight:bolder;\\\"><span style=\\\"font-weight:bolder;\\\">Privacy Policy</span></span></h3></div><div><p>At <span style=\\\"font-weight:bolder;\\\">Digikash</span>, your privacy is our priority. This policy outlines how we collect, use, and protect your information when you use our wallet services.</p></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">1. Information We Collect</span></h4></div><div><p>We may collect the following types of data:</p></div><div><span style=\\\"font-weight:bolder;\\\">  <span style=\\\"font-weight:bolder;\\\">Personal Details:</span> Name, email address, phone number</span></div><div><span style=\\\"font-weight:bolder;\\\">  <span style=\\\"font-weight:bolder;\\\">Transactional Data:</span> Deposits, withdrawals, transfers</span></div><div><span style=\\\"font-weight:bolder;\\\">  <span style=\\\"font-weight:bolder;\\\">Device Information:</span> IP address, browser type</span></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">2. How We Use Your Information</span></h4></div><div><p>Your information is used to:</p></div><div><span style=\\\"font-weight:bolder;\\\">  Deliver and improve our wallet services</span></div><div><span style=\\\"font-weight:bolder;\\\">  Prevent fraudulent or unauthorized access</span></div><div><span style=\\\"font-weight:bolder;\\\">  Comply with applicable legal and regulatory obligations</span></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">3. Data Security</span></h4></div><div><p>We secure your personal information using encryption, firewalls, and two-factor authentication. These measures help ensure the safety and confidentiality of your data.</p></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">4. Third-Party Sharing</span></h4></div><div><p>We do not sell or rent your personal data. Information may be shared only with:</p></div><div><span style=\\\"font-weight:bolder;\\\">  Government or legal authorities when required</span></div><div><span style=\\\"font-weight:bolder;\\\">  Trusted service providers, such as payment processors</span></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">5. Your Rights</span></h4></div><div><p>You have the right to:</p></div><div><span style=\\\"font-weight:bolder;\\\">  Access the personal data we hold about you</span></div><div><span style=\\\"font-weight:bolder;\\\">  Request corrections or deletions of your data</span></div><div><span style=\\\"font-weight:bolder;\\\">  Withdraw consent to data processing at any time</span></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">6. Cookies</span></h4></div><div><p>We use cookies to enhance your experience and analyze usage trends. You may control or disable cookies through your browser settings.</p></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">7. Policy Updates</span></h4></div><div><p>This privacy policy may be updated occasionally. Any significant changes will be posted on our platform to keep you informed.</p></div><div><span style=\\\"font-weight:bolder;\\\"><br></span></div><div><h4><span style=\\\"font-weight:bolder;\\\">8. Contact Us</span></h4></div><div><p>If you have questions or concerns regarding this policy, you may contact us at <a href=\\\"mailto:support@digikash.com\\\">support@digikash.com</a>.</p></div><div><br></div>\", \"es\": \"<p>Política de PrivacidadEn Digikash, la privacidad de nuestros usuarios es una prioridad. Esta política explica cómo recopilamos, usamos y protegemos su información cuando utiliza nuestros servicios de billetera digital.1. Información que RecopilamosPodemos recopilar los siguientes tipos de datos:  Datos personales: Nombre, dirección de correo electrónico, número de teléfono  Datos de transacciones: Depósitos, retiros, transferencias  Información del dispositivo: Dirección IP, tipo de navegador2. Cómo Usamos Su InformaciónUtilizamos su información para:  Ofrecer y mejorar nuestros servicios  Prevenir fraudes y accesos no autorizados  Cumplir con obligaciones legales y regulatorias3. Seguridad de los DatosProtegemos su información mediante cifrado, cortafuegos y autenticación de dos factores. Estas medidas garantizan la confidencialidad y seguridad de sus datos.4. Compartición con TercerosNo vendemos ni alquilamos su información personal. Solo podrá compartirse con:  Autoridades legales cuando sea requerido  Proveedores de servicios confiables, como procesadores de pagos5. Sus DerechosUsted tiene derecho a:  Acceder a su información personal  Solicitar la corrección o eliminación de sus datos  Retirar su consentimiento en cualquier momento6. CookiesUtilizamos cookies para mejorar su experiencia y analizar el uso del sitio. Puede controlar o desactivar las cookies desde la configuración de su navegador.7. Actualizaciones de la PolíticaEsta política puede actualizarse ocasionalmente. Cualquier cambio importante será notificado a través de nuestra plataforma.8. ContáctenosSi tiene preguntas o inquietudes sobre esta política, puede escribirnos a support@digikash.com.</p>\"}}', 'dynamic', 'classic', NULL, 0, NULL, 0, 1, '2025-05-17 01:08:41', '2025-07-14 19:22:06'),
(31, 'images/2025/05/17/20250517_013710_terms_and_conditions_mIcA.png', 'Terms & Conditions', 'terms_conditions', '{\"content\": {\"en\": \"<h3><span style=\\\"font-weight:bolder;\\\">Terms and Conditions</span></h3><p>These Terms and Conditions govern your use of the Digikash wallet platform. By registering or using our services, you agree to comply with the following terms. Please read them carefully.</p><p><br></p><h4>1. Acceptance of Terms</h4><p>By accessing or using Digikash, you confirm that you have read, understood, and agree to be bound by these Terms. If you do not agree, you may not use our services.</p><p><br></p><h4>2. User Eligibility</h4><p>You must be at least 18 years old or the legal age in your jurisdiction to use this platform. By creating an account, you confirm that all registration information provided is accurate and up-to-date.</p><p><br></p><h4>3. Account Security</h4><p>You are responsible for maintaining the confidentiality of your account credentials. Digikash will not be liable for any loss or damage resulting from unauthorized access to your account.</p><p><br></p><h4>4. Acceptable Use</h4><p>You agree not to misuse the service for illegal activities, including but not limited to fraud, money laundering, or any activity that violates applicable laws and regulations.</p><p><br></p><h4>5. Transactions and Fees</h4><p>All transactions are subject to applicable fees, which may vary depending on the service. Digikash reserves the right to modify fee structures at any time with prior notice.</p><p><br></p><h4>6. Service Availability</h4><p>We strive to maintain uninterrupted service but do not guarantee 100% uptime. Scheduled maintenance or unforeseen issues may result in temporary unavailability.</p><p><br></p><h4>7. Suspension or Termination</h4><p>We reserve the right to suspend or terminate your account without notice if we suspect any breach of these Terms or any unlawful activity.</p><p><br></p><h4>8. Limitation of Liability</h4><p>Digikash is not liable for any indirect, incidental, or consequential damages arising out of your use or inability to use the platform.</p><p><br></p><h4>9. Changes to Terms</h4><p>We may revise these Terms from time to time. Updated versions will be posted on our platform, and continued use of the service constitutes acceptance of the revised terms.</p><p><br></p><h4>10. Contact Information</h4><p>If you have any questions or concerns regarding these Terms, please contact us at <a href=\\\"mailto:support@digikash.com\\\">support@digikash.com</a>.</p><div><br></div>\", \"es\": \"<p>Términos y CondicionesEstos Términos y Condiciones regulan el uso de la plataforma Digikash. Al registrarse o utilizar nuestros servicios, usted acepta cumplir con los siguientes términos. Por favor, léalos detenidamente.1. Aceptación de los TérminosAl acceder o utilizar Digikash, usted confirma que ha leído, entendido y aceptado estos Términos. Si no está de acuerdo, no debe utilizar nuestros servicios.2. Elegibilidad del UsuarioDebe tener al menos 18 años de edad o la edad legal en su jurisdicción para utilizar esta plataforma. Al crear una cuenta, confirma que la información proporcionada es precisa y actualizada.3. Seguridad de la CuentaUsted es responsable de mantener la confidencialidad de sus credenciales. Digikash no será responsable por pérdidas o daños derivados del acceso no autorizado a su cuenta.4. Uso AceptableUsted se compromete a no utilizar el servicio para actividades ilegales, incluyendo pero no limitándose al fraude, lavado de dinero o cualquier acción que viole las leyes y regulaciones aplicables.5. Transacciones y TarifasTodas las transacciones están sujetas a tarifas aplicables, que pueden variar según el servicio. Digikash se reserva el derecho de modificar la estructura de tarifas en cualquier momento con previo aviso.6. Disponibilidad del ServicioNos esforzamos por mantener un servicio continuo, pero no garantizamos una disponibilidad del 100%. El mantenimiento programado o problemas imprevistos pueden causar interrupciones temporales.7. Suspensión o TerminaciónNos reservamos el derecho de suspender o cancelar su cuenta sin previo aviso si se sospecha una violación de estos Términos o cualquier actividad ilegal.8. Limitación de ResponsabilidadDigikash no será responsable por daños indirectos, incidentales o consecuentes derivados del uso o la imposibilidad de usar la plataforma.9. Cambios en los TérminosPodemos actualizar estos Términos ocasionalmente. Las versiones actualizadas se publicarán en nuestra plataforma, y el uso continuo del servicio implica su aceptación.10. Información de ContactoSi tiene preguntas o inquietudes sobre estos Términos, puede contactarnos a través de support@digikash.com.</p>\"}}', 'dynamic', 'classic', NULL, 0, NULL, 0, 1, '2025-05-17 01:37:10', '2025-07-14 19:18:12'),
(32, NULL, 'Subscription Plans', 'subscription_plans', '{\"heading\": {\"en\": \"Choose a plan\", \"es\": \"\"}, \"subheading\": {\"en\": \"Transparent pricing\", \"es\": \"\"}, \"description\": {\"en\": \"Upgrade or downgrade anytime — no hidden fees, no lock-in.\", \"es\": \"\"}}', 'static', 'classic', NULL, 0, NULL, 0, 1, '2026-04-25 17:32:04', '2026-04-27 00:35:17'),
(33, NULL, 'Wallet Earn', 'wallet_earn', '{\"heading\": {\"en\": \"Put your wallet to work\", \"es\": \"\"}, \"subheading\": {\"en\": \"Grow your money\", \"es\": \"\"}, \"description\": {\"en\": \"Stake your funds and earn guaranteed returns. Flexible terms, transparent payouts, zero surprises.\", \"es\": \"\"}}', 'static', 'classic', NULL, 0, NULL, 0, 1, '2026-04-27 00:42:52', '2026-04-27 03:14:16'),
(34, NULL, 'Golden Hero', 'banner', '{\"eyebrow\": {\"en\": \"Private Digital Wealth\"}, \"heading\": {\"en\": \"A discreet vault for the\\nmodern __connoisseur__ of capital.\"}, \"vault_tier\": \"PRIVATE · BLACK\", \"description\": {\"en\": \"DigiKash is a private, fully-licensed digital wallet engineered for those who measure wealth in decades, not quarters. Hold, grow and move capital across borders with the composure of a century-old bank — rendered weightless.\"}, \"vault_brand\": \"DIGIKASH\", \"vault_yield\": \"12.50%\", \"vault_holder\": \"A. WHITLOCK\", \"vault_number\": \"4519 •••• •••• 2208\", \"vault_balance\": \"$ 248,310.06\", \"vault_expires\": \"08 / 31\", \"vault_monogram\": \"DK\", \"primary_button_url\": \"/user/register\", \"primary_button_text\": {\"en\": \"Open Private Wallet\"}, \"secondary_button_url\": \"/contact\", \"secondary_button_text\": {\"en\": \"Request Invitation\"}}', 'static', 'golden', 2, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(35, NULL, 'Golden About', 'about', '{\"eyebrow\": {\"en\": \"About DigiKash\"}, \"heading\": {\"en\": \"The Standard of __Digital Wealth.__\"}, \"stat_icon\": \"fa-solid fa-users\", \"button_url\": \"/about\", \"stat_label\": {\"en\": \"Private Members\"}, \"stat_value\": \"12M+\", \"button_text\": {\"en\": \"Discover Our Story\"}, \"description\": {\"en\": \"We are custodians, not merely a platform. Every facet of DigiKash — from cold-storage architecture to the calligraphy of a statement — is shaped by a single conviction: that tomorrow\'s fortunes deserve the patience and precision of the great houses of finance.\"}, \"portrait_image\": \"images/golden/about-wallet-mockup.svg\"}', 'static', 'golden', 3, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-20 09:33:35'),
(36, NULL, 'Golden Services', 'service', '{\"eyebrow\": {\"en\": \"Our Services\"}, \"heading\": {\"en\": \"A Suite Tailored to the\\nConsidered __Few.__\"}}', 'static', 'golden', 4, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(37, NULL, 'Golden Features', 'feature', '{\"eyebrow\": {\"en\": \"Why DigiKash\"}, \"heading\": {\"en\": \"An Heirloom Reimagined as __Software.__\"}}', 'static', 'golden', 5, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(38, NULL, 'Golden Special Features', 'special_feature', '{\"eyebrow\": {\"en\": \"Inside the Wallet\"}, \"heading\": {\"en\": \"A Single Pane for __All Your Standing.__\"}, \"phone_name\": \"Mr. Whitlock\", \"phone_balance\": \"$ 248,310.06\", \"phone_greeting\": {\"en\": \"Good evening\"}}', 'static', 'golden', 6, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(39, NULL, 'Golden Wallet Earn', 'wallet_earn', '{\"eyebrow\": {\"en\": \"Grow Your Capital\"}, \"heading\": {\"en\": \"Wallet Earn __Programmes.__\"}, \"description\": {\"en\": \"Three curated yield instruments, each calibrated to a different temperament. All returns are net, all principal is insured, all approvals are personal.\"}}', 'static', 'golden', 7, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(40, NULL, 'Golden Membership Tiers', 'subscription_plans', '{\"eyebrow\": {\"en\": \"Membership Tiers\"}, \"heading\": {\"en\": \"Choose Your __Caliber.__\"}, \"description\": {\"en\": \"\"}}', 'static', 'golden', 8, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(41, NULL, 'Golden Counters', 'offer', '{\"eyebrow\": {\"en\": \"A Quiet Promise\"}, \"heading\": {\"en\": \"Where __Discretion__ Meets Performance.\"}, \"button_url\": \"/user/register\", \"button_text\": {\"en\": \"Open Private Wallet\"}, \"description\": {\"en\": \"Join twelve million members who measure prosperity in poise. Applications are reviewed personally — never algorithmically.\"}}', 'static', 'golden', 9, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(42, NULL, 'Golden Onboarding', 'work_process', '{\"eyebrow\": {\"en\": \"The Onboarding\"}, \"heading\": {\"en\": \"Four Considered __Steps.__\"}}', 'static', 'golden', 10, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(43, NULL, 'Golden Testimony', 'testimonial', '{\"eyebrow\": {\"en\": \"Testimony\"}, \"heading\": {\"en\": \"Voices From the __Membership.__\"}}', 'static', 'golden', 11, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(44, NULL, 'Golden Stewards', 'team', '{\"eyebrow\": {\"en\": \"The Stewards\"}, \"heading\": {\"en\": \"A Council of __Custodians.__\"}}', 'static', 'golden', 12, 1, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(45, NULL, 'Golden Dispatches', 'blog', '{\"eyebrow\": {\"en\": \"Dispatches\"}, \"heading\": {\"en\": \"From the __Editor\'s__ Desk.\"}, \"button_url\": \"/blog\", \"button_text\": {\"en\": \"View All\"}}', 'static', 'golden', 13, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(46, NULL, 'Golden Concierge', 'contact', '{\"eyebrow\": {\"en\": \"Concierge\"}, \"heading\": {\"en\": \"Begin a Confidential __Conversation.__\"}, \"description\": {\"en\": \"Share a moment with our private director. Every message is read, every reply is signed.\"}, \"visual_image\": \"images/golden/contact-concierge.svg\", \"visual_caption\": {\"en\": \"24 / 7 Concierge\"}}', 'static', 'golden', 14, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-20 07:23:18'),
(47, NULL, 'Golden Payment Partners', 'payment_partner', '{\"section_heading\": {\"en\": \"Trusted by Global Payment Networks\"}}', 'static', 'golden', 15, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(48, NULL, 'Golden Newsletter', 'subscribed', '{\"eyebrow\": {\"en\": \"The Quarterly Dispatch\"}, \"heading\": {\"en\": \"Receive Our __Quarterly__ Dispatch.\"}, \"button_text\": {\"en\": \"Subscribe\"}, \"description\": {\"en\": \"Four times a year, a private letter on markets, custody, and the craft of preserving wealth. Never shared, never sold.\"}}', 'static', 'golden', 16, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59'),
(49, NULL, 'Golden Dynamic Block', 'dynamic', '{\"content\": {\"en\": \"<p>Your custom HTML content here.</p>\"}}', 'dynamic', 'golden', 17, 0, NULL, 0, 1, '2026-05-19 21:42:59', '2026-05-19 21:42:59');


-- ----------------------------------------------------------
-- Table: pages
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` json DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `component_ids` json NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `breadcrumb` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_breadcrumb` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `title`, `slug`, `component_ids`, `type`, `breadcrumb`, `is_breadcrumb`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '{\"en\": \"Home\", \"es\": \"Hogar\"}', '/', '[\"1\", \"2\", \"4\", \"33\", \"32\", \"5\", \"6\", \"7\", \"8\", \"9\", \"10\", \"11\", \"13\", \"14\", \"29\"]', 'static', NULL, 0, 1, '2025-03-26 18:31:30', '2026-04-27 00:46:15'),
(2, '{\"en\": \"Blog\", \"es\": \"Blog\"}', 'blog', '[\"12\"]', 'static', NULL, 1, 1, '2025-04-12 09:09:57', '2025-04-14 08:35:28'),
(4, '{\"en\": \"About\", \"es\": \"acerca de\"}', 'about', '[\"2\"]', 'dynamic', NULL, 1, 1, '2025-04-08 10:06:54', '2025-05-25 02:23:48'),
(5, '{\"en\": \"Privacy\", \"es\": \"Privacidad\"}', 'privacy', '[\"30\"]', 'dynamic', NULL, 1, 1, '2025-04-08 10:07:11', '2025-05-25 08:28:15'),
(12, '{\"en\": \"Terms & Conditions\", \"es\": \"Términos y Condiciones\"}', 'terms-conditions', '[\"31\"]', 'dynamic', NULL, 1, 1, '2025-05-17 01:38:05', '2025-05-17 01:38:19');


-- ----------------------------------------------------------
-- Table: password_reset_tokens
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (no data)


-- ----------------------------------------------------------
-- Table: payment_gateways
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `payment_gateways`;
CREATE TABLE `payment_gateways` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Code for payment gateway e.g. paypal, stripe, razorpay',
  `currencies` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Json encoded currencies',
  `credentials` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Json encoded credentials',
  `withdraw_field` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Json schema for withdraw form fields',
  `ipn` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `payment_gateways` (`id`, `logo`, `name`, `code`, `currencies`, `credentials`, `withdraw_field`, `ipn`, `status`, `created_at`, `updated_at`) VALUES
(1, 'general/static/gateway/paypal.png', 'Paypal', 'paypal', '[\"USD\", \"EUR\", \"GBP\", \"CAD\", \"AUD\", \"JPY\", \"SGD\", \"NZD\", \"CHF\", \"SEK\", \"NOK\", \"DKK\", \"PLN\", \"HUF\", \"CZK\", \"ILS\", \"BRL\", \"MXN\", \"HKD\", \"TWD\", \"TRY\", \"INR\", \"RUB\", \"ZAR\", \"MYR\", \"THB\", \"IDR\", \"PHP\", \"NGN\", \"GHS\"]', '{\"client_id\":\"AU8UU5EUmN3osoPAAGOwuthxSDmXUi1E8C52inCAKL6M_zrroAj8ibGyzT-_nRK5MxxKkUw3O14VCMf7\",\"client_secret\":\"EGpkCB9YVSIuk53wXD2xIPZGCzMv-0XN603ioeOGIzYHJiUHDfCuSrAUN8wTT6W7KJFC265DN-rCPFC7\",\"app_id\":\"APP-80W284485P519543T\",\"sandbox\":\"1\"}', 'email', 1, 1, '2024-08-11 19:35:35', '2025-07-30 00:05:31'),
(2, 'general/static/gateway/stripe.png', 'Stripe', 'stripe', '[\"USD\",\"AUD\",\"BRL\",\"CAD\",\"CHF\",\"DKK\",\"EUR\",\"GBP\",\"HKD\",\"INR\",\"JPY\",\"MXN\",\"MYR\",\"NOK\",\"NZD\",\"PLN\",\"SEK\",\"SGD\"]', '{\"stripe_key\":\"pk_test_51QCDexGMiQWh4ibfOPw9hZolWrnVD8Y3VSxJH9sSbwb0jGEfA1n3kztLwGTiztJtfLsJ87MP0ZycGMJiUW8A3d2000Twic22WG\",\"stripe_secret\":\"sk_test_51QCDexGMiQWh4ibfKcun6XAlwtBGf01KeBaEsGBfeQzyWmn04mInGDXT5cYxOVXGJcC0l1rwuH7c3rkxjGX5KABC00tGislRIA\",\"webhook_secret\":\"whsec_PWkKsIVVBmmhIksj8tCWzLz4eQfF945P\"}', '[{\"name\":\"connected_account_id\",\"type\":\"text\",\"label\":\"Connected Account ID\",\"placeholder\":\"Optional: acct_...\",\"validation\":\"nullable\"},{\"name\":\"destination\",\"type\":\"text\",\"label\":\"Destination ID\",\"placeholder\":\"Optional: ba_... or card_...\",\"validation\":\"nullable\"},{\"name\":\"method\",\"type\":\"select\",\"label\":\"Payout Method\",\"validation\":\"required\",\"options\":{\"standard\":\"Standard\",\"instant\":\"Instant\"}},{\"name\":\"source_type\",\"type\":\"select\",\"label\":\"Source Balance\",\"validation\":\"nullable\",\"options\":{\"card\":\"Card\",\"bank_account\":\"Bank Account\",\"fpx\":\"FPX\"}},{\"name\":\"statement_descriptor\",\"type\":\"text\",\"label\":\"Statement Descriptor\",\"placeholder\":\"Optional, max 22 characters\",\"validation\":\"nullable\"}]', 1, 1, '2025-04-14 15:14:11', '2026-04-30 19:12:06'),
(3, 'general/static/gateway/mollie.png', 'Mollie', 'mollie', '[\"EUR\", \"USD\", \"GBP\", \"CAD\", \"AUD\", \"CHF\", \"DKK\", \"NOK\", \"SEK\", \"PLN\", \"CZK\", \"HUF\", \"RON\", \"BGN\", \"HRK\", \"ISK\", \"ZAR\"]', '{\"api_key\":\"test_intSTCDEBaDSu28D6DUpn5wnQhTnzB\"}', NULL, 0, 1, '2025-04-14 15:14:11', '2025-05-18 16:00:23'),
(4, 'general/static/gateway/twocheckout.png', '2Checkout', 'twocheckout', '[\"USD\",\"EUR\",\"GBP\",\"CAD\",\"AUD\"]', '{\"merchant_code\":\"...\",\"secret_key\":\"...\",\"sandbox\":true}', NULL, 1, 1, NULL, '2025-07-29 10:36:24'),
(5, 'general/static/gateway/coinbase.png', 'Coinbase', 'coinbase', '[\"USD\", \"EUR\", \"GBP\", \"CAD\", \"AUD\", \"JPY\", \"BTC\", \"ETH\", \"LTC\", \"BCH\", \"XRP\", \"EOS\"]', '{\"api_key\":\"8ef6c4ca-f5c7-4717-9d9a-002adf7e7590\",\"webhook_secret\":\"b789f547-8954-4880-89ae-5a0233006647\"}', NULL, 1, 1, '2025-04-14 15:14:11', '2025-04-14 15:14:11'),
(6, 'general/static/gateway/paystack.png', 'Paystack', 'paystack', '[\"NGN\", \"USD\", \"GBP\", \"EUR\", \"GHS\", \"KES\", \"ZAR\", \"UGX\", \"TZS\", \"RWF\"]', '{\"public_key\":\"pk_test_b5e4a4477cb7a0a897972a5ba5fc819acafbc638\",\"secret_key\":\"sk_test_434461a79ce3d904e004076eba06ab6a02665d57\",\"merchant_email\":\"coevs.dev@gmail.com\"}', '[{\"name\":\"recipient_type\",\"type\":\"select\",\"label\":\"Recipient Type\",\"validation\":\"required\",\"options\":{\"nuban\":\"NUBAN (Nigeria bank)\",\"ghipss\":\"GHIPSS (Ghana bank)\",\"mobile_money\":\"Mobile Money\",\"basa\":\"BASA (South Africa bank)\"}},{\"name\":\"bank_code\",\"type\":\"text\",\"label\":\"Bank Code\",\"validation\":\"required\"},{\"name\":\"account_number\",\"type\":\"text\",\"label\":\"Account Number\",\"validation\":\"required\"},{\"name\":\"account_name\",\"type\":\"text\",\"label\":\"Account Holder Name\",\"validation\":\"required\"}]', 1, 1, '2025-04-14 15:14:11', '2026-04-30 13:57:58'),
(7, 'general/static/gateway/flutterwave.png', 'Flutterwave', 'flutterwave', '[\"USD\", \"EUR\", \"GBP\", \"NGN\", \"GHS\", \"KES\", \"ZAR\", \"UGX\", \"TZS\", \"RWF\", \"CAD\", \"AUD\", \"JPY\", \"INR\"]', '{\"public_key\":\"FLWPUBK_TEST-9a294e81b66857f0f0f3e1f793d90e3f-X\",\"secret_key\":\"FLWSECK_TEST-ff0c925381c35872203637a5aa7a59d0-X\",\"encryption_key\":\"FLWSECK_TEST21afba65b376\"}', NULL, 1, 1, '2025-04-14 15:14:11', '2025-04-14 15:14:11'),
(8, 'general/static/gateway/cryptomus.png', 'Cryptomus', 'cryptomus', '[\"BCH\",\"BNB\",\"BTC\",\"BUSD\",\"CGPT\",\"DAI\",\"DASH\",\"DOFE\",\"ETH\",\"LTC\",\"MATIC\",\"TON\",\"TRX\",\"USDC\",\"USDT\",\"VERSE\",\"XMR\"]\r\n\r\n', '{\"api_key\":\"pk_test_uQ4LFWCBE3dT84uQnt7ycL7p9WcSwjkSPQaZbik3ChoWO0egw51f4EAaZQ\",\"merchant_id\":\"c26b80a8-9549-4a66-bb53-774f12809249\"}', NULL, 0, 1, '2025-04-14 15:14:11', '2025-05-19 06:31:51'),
(9, 'general/static/gateway/moneroo.svg', 'Moneroo', 'moneroo', '[\"USD\",\"EUR\",\"NGN\",\"GHS\",\"KES\",\"TZS\",\"UGX\",\"XAF\",\"XOF\",\"ZAR\",\"ZMW\",\"RWF\",\"CDF\",\"GNF\",\"MWK\"]', '{\"api_key\":\"pvk_sandbox_teb330|01K120C7BN2TXPT6D1BYQFEZ24\",\"api_secret\":\"digikash\",\"sandbox\":\"1\"}', NULL, 1, 1, NULL, '2026-04-30 19:18:29'),
(10, 'general/static/gateway/strowallet.png', 'Strowallet', 'strowallet', '[\"USD\",\"NGN\"]', '{\"public_key\":\"public_key\",\"secret_key\":\"secret_key\",\"sandbox\":true}', NULL, 0, 1, NULL, '2026-04-28 16:16:34'),
(11, 'general/static/gateway/binance.png', 'Binance Pay', 'binance', '[\"USDT\",\"BTC\",\"ETH\",\"BNB\",\"BUSD\",\"USD\",\"EUR\"]', '{\"certificate_sn\":\"certificate_sn\",\"private_key\":\"private_key\",\"sandbox\":true}', NULL, 1, 1, NULL, '2025-07-29 13:30:10'),
(12, 'general/static/gateway/airtel.png', 'Airtel Money', 'airtel', '[\"UGX\",\"KES\",\"TZS\",\"RWF\",\"ZMW\"]', '{\"client_id\":\"client_id\",\"client_secret\":\"client_secret\",\"country\":\"UG\",\"currency\":\"UGX\",\"sandbox\":true}', NULL, 1, 1, NULL, '2025-07-29 13:41:55'),
(13, 'general/static/gateway/blockchain.png', 'Blockchain.info', 'blockchain', '[\"BTC\"]', '{\"receive_address\":\"receive_address\",\"callback_secret\":\"callback_secret\",\"required_confirmations\":1}', NULL, 1, 1, NULL, NULL),
(14, 'general/static/gateway/blockio.png', 'Block.io', 'blockio', '[\"BTC\",\"LTC\",\"DOGE\"]', '{\"api_key\":\"api_key\",\"required_confirmations\":1}', NULL, 1, 1, NULL, '2025-07-29 13:42:03'),
(15, 'general/static/gateway/btcpayserver.png', 'BTCPay Server', 'bitpayserver', '[\"BTC\",\"USD\",\"EUR\",\"GBP\"]', '{\"server_url\":\"https:\\/\\/your-btcpay-server.com\",\"api_token\":\"api_token\"}', NULL, 1, 1, NULL, NULL),
(16, 'general/static/gateway/cashmaal.png', 'Cashmaal', 'cashmaal', '[\"USD\",\"EUR\",\"GBP\",\"PKR\"]', '{\"web_id\":\"web_id\"}', NULL, 1, 1, NULL, NULL),
(17, 'general/static/gateway/coingate.png', 'CoinGate', 'coingate', '[\"EUR\",\"USD\",\"BTC\",\"ETH\",\"LTC\"]', '{\"auth_token\":\"auth_token\",\"receive_currency\":\"EUR\",\"sandbox\":false}', NULL, 1, 1, NULL, '2025-07-29 13:42:19'),
(18, 'general/static/gateway/coinpayments.svg', 'CoinPayments', 'coinpayments', '[\"BTC\",\"ETH\",\"LTC\",\"USDT\",\"USD\",\"EUR\"]', '{\"public_key\":\"public_key\",\"private_key\":\"private_key\",\"ipn_secret\":\"ipn_secret\",\"currency2\":\"BTC\"}', NULL, 1, 1, NULL, NULL),
(19, 'general/static/gateway/instamojo.png', 'Instamojo', 'instamojo', '[\"INR\"]', '{\"api_key\":\"api_key\",\"auth_token\":\"auth_token\",\"phone\":\"9999999999\",\"sandbox\":false}', NULL, 1, 1, NULL, '2025-07-29 13:42:30'),
(20, 'general/static/gateway/mtn.png', 'MTN Mobile Money', 'mtn', '[\"UGX\",\"GHS\",\"ZAR\",\"XAF\",\"EUR\"]', '{\"subscription_key\":\"subscription_key\",\"user_id\":\"user_id\",\"api_key\":\"api_key\",\"test_msisdn\":\"256774290781\",\"sandbox\":true}', NULL, 1, 1, NULL, '2025-07-29 13:42:36'),
(21, 'general/static/gateway/nowpayments.png', 'NOWPayments', 'nowpayments', '[\"BTC\",\"ETH\",\"USDT\",\"LTC\",\"BCH\",\"USD\",\"EUR\"]', '{\"api_key\":\"api_key\",\"ipn_secret\":\"ipn_secret\",\"pay_currency\":\"BTC\",\"sandbox\":false}', NULL, 1, 1, NULL, '2025-07-29 13:41:40'),
(22, 'general/static/gateway/razorpay.png', 'Razorpay', 'razorpay', '[\"INR\",\"USD\",\"EUR\",\"GBP\"]', '{\"key_id\":\"key_id\",\"key_secret\":\"key_secret\"}', NULL, 1, 1, NULL, NULL),
(23, 'general/static/gateway/voguepay.png', 'Voguepay', 'voguepay', '[\"NGN\",\"USD\",\"GBP\",\"EUR\"]', '{\"merchant_id\":\"merchant_id\"}', NULL, 1, 1, NULL, NULL),
(31, 'general/static/gateway/bitnob.png', 'Bitnob', 'bitnob', '[\"USD\",\"NGN\",\"KES\",\"GHS\",\"USDT\"]', '{\"client_id\":\"7e09cd3e-7a76-412d-9df0-12aebacac6c4\",\"hmac_key\":\"hsk.087026e93e634c859b971f71fd9a210c.4967d36af6f9424ba0da0a256ea0b1a1cbed4f2498ca429988ab24f06f78eac1\",\"public_key\":\"pk.df4be8d0a6214f2ca324202b326898ce.3bb91bf790c443cf895d9b4a59e53e65761573a92c0f4a8dbecdf3cc89990b6b\",\"secret_key\":\"sk.f5b2a26f0c5c488bbfd4df1dd2b2f762.ccc20f2f19c94caa9b63137315f706cbb8390b1e9c974c8ca4c10daaf235a387\",\"lightning_key\":\"ln.07943973ebed4eb2b72a036fc7359105.c6cf505aefc84d649edf43cf4d95135f623de5eaee5b4e00884e71938f209d21\",\"webhook_secret\":\"c4d7fea73754bb68bde7\",\"sandbox\":true,\"signature_algo\":\"sha256\"}', '{\"destination_type\":{\"type\":\"select\",\"label\":\"Destination\",\"options\":{\"bank\":\"Bank Account\",\"mobile_money\":\"Mobile Money\"},\"required\":true},\"country\":{\"type\":\"text\",\"label\":\"Country (ISO-2)\",\"required\":true},\"bank_code\":{\"type\":\"text\",\"label\":\"Bank \\/ Mobile-money code\",\"required\":true},\"account_number\":{\"type\":\"text\",\"label\":\"Account number\",\"required\":true},\"account_name\":{\"type\":\"text\",\"label\":\"Account holder name\",\"required\":true}}', 1, 1, NULL, '2026-04-30 12:34:07'),
(32, 'general/static/default/payment-gateway.png', 'Paymob', 'paymob', '[\"EGP\",\"SAR\",\"AED\",\"OMR\",\"USD\"]', '{\"api_key\":\"ZXlKaGJHY2lPaUpJVXpVeE1pSXNJblI1Y0NJNklrcFhWQ0o5LmV5SmpiR0Z6Y3lJNklrMWxjbU5vWVc1MElpd2ljSEp2Wm1sc1pWOXdheUk2TVRFeU5ETXNJbTVoYldVaU9pSnBibWwwYVdGc0luMC5JNWY4aTI1U2ZqRDJUTTRkeURPal9GMi04X2J1WnlFckxGMFptTnBhOHJYbWlGWnl4OWxUeWMzNFRPQ3h0cXJyZGNyR1JQSHVnZjN3TkQwYW5mVDNkZw==\",\"secret_key\":\"sau_sk_test_b687bcf055f889cf872b8904644737593d3259fbc1249d743a052e22c6b177fd\",\"public_key\":\"sau_pk_test_ZmCnnWtMmISeebnY4r06rqQdtAEXoyx2\",\"payment_methods\":\"15182\",\"hmac\":\"AE0BDA8F01E4B67FE0CAC3C7DA74D9CA\",\"base_url\":\"https:\\/\\/ksa.paymob.com\",\"sandbox\":\"1\"}', NULL, 1, 1, '2026-04-30 17:26:31', '2026-04-30 18:13:18');


-- ----------------------------------------------------------
-- Table: payment_links
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `payment_links`;
CREATE TABLE `payment_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `merchant_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `wallet_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `amount` decimal(18,2) DEFAULT NULL,
  `min_amount` decimal(18,2) DEFAULT NULL,
  `max_amount` decimal(18,2) DEFAULT NULL,
  `merchant_fee` decimal(8,4) DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `max_payments` int unsigned DEFAULT NULL,
  `payments_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_links_token_unique` (`token`),
  KEY `payment_links_currency_id_foreign` (`currency_id`),
  KEY `payment_links_user_id_status_index` (`user_id`,`status`),
  KEY `payment_links_wallet_reference_index` (`wallet_reference`),
  KEY `payment_links_status_index` (`status`),
  KEY `payment_links_merchant_id_foreign` (`merchant_id`),
  KEY `payment_links_user_merchant_status_idx` (`user_id`,`merchant_id`,`status`),
  CONSTRAINT `payment_links_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `payment_links_merchant_id_foreign` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payment_links_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: permissions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_summary` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=676 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `category`, `category_display_name`, `category_icon`, `category_summary`, `category_description`, `name`, `display_name`, `description`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'dashboard-stats', 'Dashboard Stats', 'View high-level platform metrics and operational dashboard cards.', 'admin', NULL, '2026-05-19 21:43:11'),
(2, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'transactions-chart', 'Transactions Chart', 'View transaction charts and finance activity trends.', 'admin', NULL, '2026-05-19 21:43:11'),
(3, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'wallet-balance', 'Wallet Balance', 'View wallet balance summaries and money movement totals.', 'admin', NULL, '2026-05-19 21:43:11'),
(4, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'earning-chart', 'Earning Chart', 'View earning charts and revenue performance widgets.', 'admin', NULL, '2026-05-19 21:43:11'),
(5, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'wallet-growth', 'Wallet Growth', 'View wallet growth trends and adoption metrics.', 'admin', NULL, '2026-05-19 21:43:11'),
(6, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'wallet-latest-transactions', 'Wallet Latest Transactions', 'View the latest wallet transactions on the dashboard.', 'admin', NULL, '2026-05-19 21:43:11'),
(7, 'dashboard', 'Dashboard', 'cil-speedometer', 'Stats, charts, wallets', 'Dashboard metrics, wallet snapshots, growth charts, and latest platform activity.', 'wallet-latest-users', 'Wallet Latest Users', 'View newly joined users from dashboard widgets.', 'admin', NULL, '2026-05-19 21:43:11'),
(8, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-list', 'User List', 'View User List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(9, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-create', 'User Create', 'Create new User Create records through admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(10, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-manage', 'User Manage', 'Manage User Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(11, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-delete', 'User Delete', 'Delete User Delete records when business rules allow it.', 'admin', NULL, '2026-05-19 21:43:11'),
(12, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-activity-log', 'User Activity Log', 'Access the User Activity Log admin capability.', 'admin', NULL, '2026-05-19 21:43:11'),
(13, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-login-as', 'User Login As', 'Sign in as a user for support and account troubleshooting.', 'admin', NULL, '2026-05-19 21:43:11'),
(14, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-balance-manage', 'User Balance Manage', 'Adjust user balances through approved admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(15, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'user-features-manage', 'User Features Manage', 'Enable or disable feature access for individual users.', 'admin', NULL, '2026-05-19 21:43:11'),
(16, 'role', 'Roles & Permissions', 'role', 'Access roles, policies', 'Role creation, editing, deletion, and permission assignment controls.', 'role-list', 'Role List', 'View Role List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(17, 'role', 'Roles & Permissions', 'role', 'Access roles, policies', 'Role creation, editing, deletion, and permission assignment controls.', 'role-create', 'Role Create', 'Create new Role Create records through admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(18, 'role', 'Roles & Permissions', 'role', 'Access roles, policies', 'Role creation, editing, deletion, and permission assignment controls.', 'role-edit', 'Role Edit', 'Update Role Edit records and save approved changes.', 'admin', NULL, '2026-05-19 21:43:11'),
(19, 'role', 'Roles & Permissions', 'role', 'Access roles, policies', 'Role creation, editing, deletion, and permission assignment controls.', 'role-delete', 'Role Delete', 'Delete Role Delete records when business rules allow it.', 'admin', NULL, '2026-05-19 21:43:11'),
(20, 'staff', 'Staff Management', 'badge-account', 'Admins, staff profiles', 'Internal admin accounts, staff visibility, and profile management controls.', 'staff-list', 'Staff List', 'View Staff List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(21, 'staff', 'Staff Management', 'badge-account', 'Admins, staff profiles', 'Internal admin accounts, staff visibility, and profile management controls.', 'staff-create', 'Staff Create', 'Create new Staff Create records through admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(22, 'staff', 'Staff Management', 'badge-account', 'Admins, staff profiles', 'Internal admin accounts, staff visibility, and profile management controls.', 'staff-edit', 'Staff Edit', 'Update Staff Edit records and save approved changes.', 'admin', NULL, '2026-05-19 21:43:11'),
(23, 'merchant', 'Merchant Management', 'merchant', 'Merchants, requests', 'Merchant onboarding, review, profile management, and request notifications.', 'merchant-list', 'Merchant List', 'View Merchant List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(24, 'merchant', 'Merchant Management', 'merchant', 'Merchants, requests', 'Merchant onboarding, review, profile management, and request notifications.', 'merchant-manage', 'Merchant Manage', 'Manage Merchant Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(25, 'merchant', 'Merchant Management', 'merchant', 'Merchants, requests', 'Merchant onboarding, review, profile management, and request notifications.', 'merchant-request-notification', 'Merchant Request Notification', 'Receive or manage admin notifications for Merchant Request Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(26, 'agent', 'Agent Management', 'agent', 'Agents, requests', 'Agent onboarding, review, profile management, and request notifications.', 'agent-list', 'Agent List', 'View Agent List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(27, 'agent', 'Agent Management', 'agent', 'Agents, requests', 'Agent onboarding, review, profile management, and request notifications.', 'agent-manage', 'Agent Manage', 'Manage Agent Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(28, 'agent', 'Agent Management', 'agent', 'Agents, requests', 'Agent onboarding, review, profile management, and request notifications.', 'agent-request-notification', 'Agent Request Notification', 'Receive or manage admin notifications for Agent Request Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(29, 'kyc', 'KYC Management', 'kyc', 'KYC review, templates', 'Identity verification queues, approval actions, templates, and compliance notices.', 'kyc-list', 'KYC List', 'View KYC List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(30, 'kyc', 'KYC Management', 'kyc', 'KYC review, templates', 'Identity verification queues, approval actions, templates, and compliance notices.', 'kyc-action', 'KYC Action', 'Approve, reject, or review submitted KYC verification requests.', 'admin', NULL, '2026-05-19 21:43:11'),
(31, 'kyc', 'KYC Management', 'kyc', 'KYC review, templates', 'Identity verification queues, approval actions, templates, and compliance notices.', 'kyc-notification', 'KYC Notification', 'Receive or manage admin notifications for KYC Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(32, 'kyc', 'KYC Management', 'kyc', 'KYC review, templates', 'Identity verification queues, approval actions, templates, and compliance notices.', 'kyc-template-list', 'KYC Template List', 'View KYC Template List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(33, 'kyc', 'KYC Management', 'kyc', 'KYC review, templates', 'Identity verification queues, approval actions, templates, and compliance notices.', 'kyc-template-manage', 'KYC Template Manage', 'Create and update KYC form templates and required fields.', 'admin', NULL, '2026-05-19 21:43:11'),
(34, 'virtual-card', 'Virtual Cards', 'virtual-card', 'Cards, requests, providers', 'Virtual card requests, card actions, provider settings, and admin notifications.', 'virtual-card-list', 'Virtual Card List', 'View Virtual Card List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(35, 'virtual-card', 'Virtual Cards', 'virtual-card', 'Cards, requests, providers', 'Virtual card requests, card actions, provider settings, and admin notifications.', 'virtual-card-action', 'Virtual Card Action', 'Run review, approval, rejection, or operational actions for Virtual Card Action.', 'admin', NULL, '2026-05-19 21:43:11'),
(36, 'virtual-card', 'Virtual Cards', 'virtual-card', 'Cards, requests, providers', 'Virtual card requests, card actions, provider settings, and admin notifications.', 'virtual-card-notification', 'Virtual Card Notification', 'Receive or manage admin notifications for Virtual Card Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(37, 'virtual-card', 'Virtual Cards', 'virtual-card', 'Cards, requests, providers', 'Virtual card requests, card actions, provider settings, and admin notifications.', 'virtual-card-provider-manage', 'Virtual Card Provider Manage', 'Configure virtual card providers and card issuing rules.', 'admin', NULL, '2026-05-19 21:43:11'),
(38, 'deposit', 'Deposits', 'wallet-plus', 'Requests, methods', 'Deposit requests, payment methods, operational actions, and admin notifications.', 'deposit-list', 'Deposit List', 'View Deposit List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(39, 'deposit', 'Deposits', 'wallet-plus', 'Requests, methods', 'Deposit requests, payment methods, operational actions, and admin notifications.', 'deposit-action', 'Deposit Action', 'Run review, approval, rejection, or operational actions for Deposit Action.', 'admin', NULL, '2026-05-19 21:43:11'),
(40, 'deposit', 'Deposits', 'wallet-plus', 'Requests, methods', 'Deposit requests, payment methods, operational actions, and admin notifications.', 'deposit-method-list', 'Deposit Method List', 'View Deposit Method List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(41, 'deposit', 'Deposits', 'wallet-plus', 'Requests, methods', 'Deposit requests, payment methods, operational actions, and admin notifications.', 'deposit-method-manage', 'Deposit Method Manage', 'Create and update deposit methods, limits, and availability.', 'admin', NULL, '2026-05-19 21:43:11'),
(42, 'deposit', 'Deposits', 'wallet-plus', 'Requests, methods', 'Deposit requests, payment methods, operational actions, and admin notifications.', 'deposit-notification', 'Deposit Notification', 'Receive or manage admin notifications for Deposit Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(43, 'withdraw', 'Withdrawals', 'withdraw-1', 'Requests, schedules', 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.', 'withdraw-list', 'Withdraw List', 'View Withdraw List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(44, 'withdraw', 'Withdrawals', 'withdraw-1', 'Requests, schedules', 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.', 'withdraw-action', 'Withdraw Action', 'Run review, approval, rejection, or operational actions for Withdraw Action.', 'admin', NULL, '2026-05-19 21:43:11'),
(45, 'withdraw', 'Withdrawals', 'withdraw-1', 'Requests, schedules', 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.', 'withdraw-method-list', 'Withdraw Method List', 'View Withdraw Method List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(46, 'withdraw', 'Withdrawals', 'withdraw-1', 'Requests, schedules', 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.', 'withdraw-method-manage', 'Withdraw Method Manage', 'Create and update withdrawal methods, limits, and availability.', 'admin', NULL, '2026-05-19 21:43:11'),
(47, 'withdraw', 'Withdrawals', 'withdraw-1', 'Requests, schedules', 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.', 'withdraw-schedule', 'Withdraw Schedule', 'Manage scheduled withdrawal windows and payout timing.', 'admin', NULL, '2026-05-19 21:43:11'),
(48, 'withdraw', 'Withdrawals', 'withdraw-1', 'Requests, schedules', 'Withdrawal requests, payout methods, schedules, actions, and admin notifications.', 'withdraw-notification', 'Withdraw Notification', 'Receive or manage admin notifications for Withdraw Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(49, 'payment', 'Payment Gateways', 'payment', 'Gateways, credentials', 'Payment provider listing, gateway credentials, and provider configuration.', 'payment-gateway-list', 'Payment Gateway List', 'View Payment Gateway List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(50, 'payment', 'Payment Gateways', 'payment', 'Gateways, credentials', 'Payment provider listing, gateway credentials, and provider configuration.', 'payment-gateway-configure', 'Payment Gateway Configure', 'Update payment gateway credentials and provider settings.', 'admin', NULL, '2026-05-19 21:43:11'),
(51, 'subscription', 'Subscriptions', 'layer', 'Plans, subscribers', 'Subscription plans, user subscriptions, and subscription lifecycle operations.', 'subscription-list', 'Subscription List', 'View Subscription List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(52, 'subscription', 'Subscriptions', 'layer', 'Plans, subscribers', 'Subscription plans, user subscriptions, and subscription lifecycle operations.', 'subscription-manage', 'Subscription Manage', 'Manage Subscription Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(53, 'wallet-earn', 'Wallet Earn', 'trending-up', 'Earn plans, stakes', 'Earn plans, staking activity, and wallet earning program management.', 'wallet-earn-list', 'Wallet Earn List', 'View Wallet Earn List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(54, 'wallet-earn', 'Wallet Earn', 'trending-up', 'Earn plans, stakes', 'Earn plans, staking activity, and wallet earning program management.', 'wallet-earn-manage', 'Wallet Earn Manage', 'Manage Wallet Earn Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(55, 'payment-link', 'Payment Links', 'payment', 'Links, status, review', 'Payment link listing, review, status changes, and cleanup controls.', 'payment-link-list', 'Payment Link List', 'View Payment Link List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(56, 'payment-link', 'Payment Links', 'payment', 'Links, status, review', 'Payment link listing, review, status changes, and cleanup controls.', 'payment-link-manage', 'Payment Link Manage', 'Manage Payment Link Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(57, 'background-tasks', 'Background Tasks', 'apps-1', 'Tasks, queues, jobs', 'Background task visibility, manual task runs, and queue operations.', 'background-task-list', 'Background Task List', 'View Background Task List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(58, 'background-tasks', 'Background Tasks', 'apps-1', 'Tasks, queues, jobs', 'Background task visibility, manual task runs, and queue operations.', 'background-task-run', 'Background Task Run', 'Run approved background tasks manually from the admin panel.', 'admin', NULL, '2026-05-19 21:43:11'),
(59, 'background-tasks', 'Background Tasks', 'apps-1', 'Tasks, queues, jobs', 'Background task visibility, manual task runs, and queue operations.', 'queue-manage', 'Queue Manage', 'Review and manage queue operations and background job flow.', 'admin', NULL, '2026-05-19 21:43:11'),
(60, 'site-settings', 'Site Settings', 'site-setting', 'Brand, security, system', 'Platform-wide settings, brand controls, security options, and system preferences.', 'site-setting-view', 'Site Setting View', 'View site-wide configuration, branding, security, and system settings.', 'admin', NULL, '2026-05-19 21:43:11'),
(61, 'site-settings', 'Site Settings', 'site-setting', 'Brand, security, system', 'Platform-wide settings, brand controls, security options, and system preferences.', 'site-setting-update', 'Site Setting Update', 'Update site-wide configuration, branding, security, and system settings.', 'admin', NULL, '2026-05-19 21:43:11'),
(62, 'language', 'Languages', 'translate', 'Locales, translations', 'Language availability, translation creation, and localization management.', 'language-list', 'Language List', 'View Language List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(63, 'language', 'Languages', 'translate', 'Locales, translations', 'Language availability, translation creation, and localization management.', 'language-create', 'Language Create', 'Create new Language Create records through admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(64, 'language', 'Languages', 'translate', 'Locales, translations', 'Language availability, translation creation, and localization management.', 'language-manage', 'Language Manage', 'Manage Language Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(65, 'navigation', 'Navigation', 'list-2', 'Menus, public links', 'Public navigation structure, menus, labels, and link organization.', 'navigation-manage', 'Navigation Manage', 'Create and organize public menus, links, and navigation groups.', 'admin', NULL, '2026-05-19 21:43:11'),
(66, 'page', 'Pages', 'page', 'Pages, footer CMS', 'CMS pages, footer content, page creation, editing, and deletion controls.', 'page-list', 'Page List', 'View Page List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(67, 'page', 'Pages', 'page', 'Pages, footer CMS', 'CMS pages, footer content, page creation, editing, and deletion controls.', 'page-create', 'Page Create', 'Create new Page Create records through admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(68, 'page', 'Pages', 'page', 'Pages, footer CMS', 'CMS pages, footer content, page creation, editing, and deletion controls.', 'page-edit', 'Page Edit', 'Update Page Edit records and save approved changes.', 'admin', NULL, '2026-05-19 21:43:11'),
(69, 'page', 'Pages', 'page', 'Pages, footer CMS', 'CMS pages, footer content, page creation, editing, and deletion controls.', 'page-delete', 'Page Delete', 'Delete Page Delete records when business rules allow it.', 'admin', NULL, '2026-05-19 21:43:11'),
(70, 'page', 'Pages', 'page', 'Pages, footer CMS', 'CMS pages, footer content, page creation, editing, and deletion controls.', 'page-footer-manage', 'Page Footer Manage', 'Manage footer content, sections, links, and public page blocks.', 'admin', NULL, '2026-05-19 21:43:11'),
(71, 'component', 'Page Components', 'layer', 'Reusable content blocks', 'Reusable page sections, content blocks, and component management.', 'component-list', 'Component List', 'View Component List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(72, 'component', 'Page Components', 'layer', 'Reusable content blocks', 'Reusable page sections, content blocks, and component management.', 'component-manage', 'Component Manage', 'Manage Component Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(73, 'blog', 'Blog & Categories', 'blog', 'Posts, categories', 'Blog posts, editorial categories, publishing, editing, and cleanup.', 'blog-list', 'Blog List', 'View Blog List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(74, 'blog', 'Blog & Categories', 'blog', 'Posts, categories', 'Blog posts, editorial categories, publishing, editing, and cleanup.', 'blog-create', 'Blog Create', 'Create new Blog Create records through admin workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(75, 'blog', 'Blog & Categories', 'blog', 'Posts, categories', 'Blog posts, editorial categories, publishing, editing, and cleanup.', 'blog-edit', 'Blog Edit', 'Update Blog Edit records and save approved changes.', 'admin', NULL, '2026-05-19 21:43:11'),
(76, 'blog', 'Blog & Categories', 'blog', 'Posts, categories', 'Blog posts, editorial categories, publishing, editing, and cleanup.', 'blog-delete', 'Blog Delete', 'Delete Blog Delete records when business rules allow it.', 'admin', NULL, '2026-05-19 21:43:11'),
(77, 'blog', 'Blog & Categories', 'blog', 'Posts, categories', 'Blog posts, editorial categories, publishing, editing, and cleanup.', 'blog-category-list', 'Blog Category List', 'View Blog Category List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(78, 'blog', 'Blog & Categories', 'blog', 'Posts, categories', 'Blog posts, editorial categories, publishing, editing, and cleanup.', 'blog-category-manage', 'Blog Category Manage', 'Manage Blog Category Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(79, 'subscriber', 'Subscribers', 'email', 'Audience, newsletter', 'Subscriber records, newsletter audience visibility, and communication controls.', 'subscriber-list', 'Subscriber List', 'View Subscriber List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(80, 'subscriber', 'Subscribers', 'email', 'Audience, newsletter', 'Subscriber records, newsletter audience visibility, and communication controls.', 'subscriber-manage', 'Subscriber Manage', 'Manage Subscriber Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(81, 'social', 'Social Links', 'social-link', 'Profiles, public links', 'Social profile links, public channel visibility, and link management.', 'social-list', 'Social List', 'View Social List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(82, 'social', 'Social Links', 'social-link', 'Profiles, public links', 'Social profile links, public channel visibility, and link management.', 'social-manage', 'Social Manage', 'Manage Social Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(83, 'transaction', 'Transactions', 'transaction-2', 'Ledger, activity', 'Transaction history, ledger visibility, and financial activity review.', 'transaction-list', 'Transaction List', 'View Transaction List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(84, 'p2p', 'P2P Trading', 'p2p_trading', 'Marketplace, disputes', 'P2P marketplace settings, payment methods, dispute handling, and promotions.', 'p2p-manage', 'P2P Manage', 'Manage P2P Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(85, 'p2p', 'P2P Trading', 'p2p_trading', 'Marketplace, disputes', 'P2P marketplace settings, payment methods, dispute handling, and promotions.', 'p2p-method-manage', 'P2P Method Manage', 'Manage P2P payment methods and marketplace payment options.', 'admin', NULL, '2026-05-19 21:43:11'),
(86, 'p2p', 'P2P Trading', 'p2p_trading', 'Marketplace, disputes', 'P2P marketplace settings, payment methods, dispute handling, and promotions.', 'p2p-dispute-manage', 'P2P Dispute Manage', 'Review and resolve P2P trade disputes and escalation cases.', 'admin', NULL, '2026-05-19 21:43:11'),
(87, 'ranking', 'User Ranking', 'ranking', 'Ranks, loyalty tiers', 'User ranking rules, loyalty tiers, and progression controls.', 'ranking-manage', 'Ranking Manage', 'Manage Ranking Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(88, 'referral', 'Referral Program', 'referral', 'Rewards, referrals', 'Referral rewards, program settings, and referral activity management.', 'referral-manage', 'Referral Manage', 'Manage Referral Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(89, 'user', 'User Management', 'users-1', 'Users, balances, access', 'Customer records, balances, activity logs, login access, and feature overrides.', 'custom-notify-users', 'Custom Notify Users', 'Send targeted custom notifications to selected users.', 'admin', NULL, '2026-05-19 21:43:11'),
(90, 'notification', 'Notifications', 'notification', 'Templates, plugins', 'Notification records, channel plugins, templates, and message configuration.', 'notification-list', 'Notification List', 'View Notification List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(91, 'notification', 'Notifications', 'notification', 'Templates, plugins', 'Notification records, channel plugins, templates, and message configuration.', 'notification-plugin-list', 'Notification Plugin List', 'View notification integrations and delivery channel plugins.', 'admin', NULL, '2026-05-19 21:43:11'),
(92, 'notification', 'Notifications', 'notification', 'Templates, plugins', 'Notification records, channel plugins, templates, and message configuration.', 'notification-template-list', 'Notification Template List', 'View Notification Template List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(93, 'notification', 'Notifications', 'notification', 'Templates, plugins', 'Notification records, channel plugins, templates, and message configuration.', 'notification-template-manage', 'Notification Template Manage', 'Create and update reusable notification templates.', 'admin', NULL, '2026-05-19 21:43:11'),
(94, 'support', 'Support Center', 'support', 'Tickets, replies', 'Support tickets, categories, replies, assignment flow, and ticket notifications.', 'support-ticket-list', 'Support Ticket List', 'View Support Ticket List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(95, 'support', 'Support Center', 'support', 'Tickets, replies', 'Support tickets, categories, replies, assignment flow, and ticket notifications.', 'support-ticket-category-manage', 'Support Ticket Category Manage', 'Create and organize ticket categories for support workflows.', 'admin', NULL, '2026-05-19 21:43:11'),
(96, 'support', 'Support Center', 'support', 'Tickets, replies', 'Support tickets, categories, replies, assignment flow, and ticket notifications.', 'support-ticket-manage', 'Support Ticket Manage', 'Reply to, assign, update, and resolve customer support tickets.', 'admin', NULL, '2026-05-19 21:43:11'),
(97, 'support', 'Support Center', 'support', 'Tickets, replies', 'Support tickets, categories, replies, assignment flow, and ticket notifications.', 'support-ticket-notification', 'Support Ticket Notification', 'Receive or manage admin notifications for Support Ticket Notification.', 'admin', NULL, '2026-05-19 21:43:11'),
(98, 'seo', 'SEO Management', 'seo', 'Metadata, search', 'SEO metadata, page search presentation, and discoverability settings.', 'seo-manage', 'SEO Manage', 'Manage SEO Manage settings, records, and operational controls.', 'admin', NULL, '2026-05-19 21:43:11'),
(99, 'currency', 'Currency Management', 'money-cog', 'Currencies, fees', 'Currency availability, exchange settings, role limits, and fee controls.', 'currency-manage', 'Currency Manage', 'Manage currencies, availability, rates, fees, and finance limits.', 'admin', NULL, '2026-05-19 21:43:11'),
(100, 'plugins', 'Plugins', 'cil-fork', 'Integrations, add-ons', 'Installed plugins, integration controls, and add-on management.', 'plugins-manage', 'Plugins Manage', 'Enable, disable, and configure installed platform plugins.', 'admin', NULL, '2026-05-19 21:43:11'),
(101, 'feature', 'Feature Management', 'feature-management', 'Toggles, rules', 'Feature switches, rollout rules, and panel access controls.', 'feature-list', 'Feature List', 'View Feature List records, tables, and module indexes.', 'admin', NULL, '2026-05-19 21:43:11'),
(102, 'feature', 'Feature Management', 'feature-management', 'Toggles, rules', 'Feature switches, rollout rules, and panel access controls.', 'feature-manage', 'Feature Manage', 'Control feature toggles, access rules, and module availability.', 'admin', NULL, '2026-05-19 21:43:11'),
(103, 'app', 'Application Tools', 'app', 'Cache, info, style', 'Application info, cache actions, optimization tools, and style manager access.', 'app-info', 'Application Info', 'View application, environment, and server health information.', 'admin', NULL, '2026-05-19 21:43:11'),
(104, 'app', 'Application Tools', 'app', 'Cache, info, style', 'Application info, cache actions, optimization tools, and style manager access.', 'style-manager', 'Style Manager', 'Customize backend styling and visual presentation settings.', 'admin', NULL, '2026-05-19 21:43:11'),
(105, 'app', 'Application Tools', 'app', 'Cache, info, style', 'Application info, cache actions, optimization tools, and style manager access.', 'app-clear-cache', 'Application Clear Cache', 'Clear application cache from the admin maintenance tools.', 'admin', NULL, '2026-05-19 21:43:11'),
(106, 'app', 'Application Tools', 'app', 'Cache, info, style', 'Application info, cache actions, optimization tools, and style manager access.', 'app-optimize', 'Application Optimize', 'Run application optimization actions from maintenance tools.', 'admin', NULL, '2026-05-19 21:43:11'),
(213, 'custom-landing', 'Custom Landing Pages', 'custom-landing', 'Campaign pages, publishing', 'Secure custom landing uploads, previews, publishing, and HTML editing controls.', 'custom-landing-list', 'Custom Landing List', 'View custom landing pages, publishing status, archive metadata, and preview links.', 'admin', '2026-05-03 10:16:41', '2026-05-19 21:43:11'),
(214, 'custom-landing', 'Custom Landing Pages', 'custom-landing', 'Campaign pages, publishing', 'Secure custom landing uploads, previews, publishing, and HTML editing controls.', 'custom-landing-manage', 'Custom Landing Manage', 'Upload, validate, publish, edit, and delete custom landing page bundles.', 'admin', '2026-05-03 10:16:41', '2026-05-19 21:43:11'),
(216, 'app', 'Application Tools', 'app', 'Cache, info, style', 'Application info, cache actions, optimization tools, and style manager access.', 'project-updater-view', 'Project Updater View', 'View project license status, update checks, changelog, and update history.', 'admin', '2026-05-07 02:48:31', '2026-05-19 21:43:11'),
(217, 'app', 'Application Tools', 'app', 'Cache, info, style', 'Application info, cache actions, optimization tools, and style manager access.', 'project-updater-manage', 'Project Updater Manage', 'Activate project licenses and install verified project update packages.', 'admin', '2026-05-07 02:48:31', '2026-05-19 21:43:11'),
(326, 'mobile-recharge', 'Mobile Recharge', 'mobile-recharge', 'Top-ups, providers, history', 'Mobile recharge history, provider configuration, fees, limits, and operational settings.', 'mobile-recharge-list', 'Mobile Recharge List', 'View Mobile Recharge List records, tables, and module indexes.', 'admin', '2026-05-09 02:14:13', '2026-05-19 21:43:11'),
(327, 'mobile-recharge', 'Mobile Recharge', 'mobile-recharge', 'Top-ups, providers, history', 'Mobile recharge history, provider configuration, fees, limits, and operational settings.', 'mobile-recharge-manage', 'Mobile Recharge Manage', 'Manage Mobile Recharge Manage settings, records, and operational controls.', 'admin', '2026-05-09 02:14:13', '2026-05-19 21:43:11'),
(438, 'agent', 'Agent Management', 'agent', 'Agents, requests', 'Agent onboarding, review, profile management, and request notifications.', 'agent-commission-rules-manage', 'Agent Commission Rules Manage', 'Create and update agent commission rules for cash-in, cash-out, amount ranges, currencies, and agent-specific rates.', 'admin', '2026-05-10 17:07:48', '2026-05-19 21:43:11'),
(439, 'gift-card', 'Gift Cards', 'tags', 'Cards, templates, designs', 'Issued gift cards, cancellations, template catalog, design presets, and template reordering.', 'gift-card-list', 'Gift Card List', 'View all issued gift cards, recipients, statuses, and redemption history.', 'admin', '2026-05-19 19:08:53', '2026-05-19 21:43:11'),
(440, 'gift-card', 'Gift Cards', 'tags', 'Cards, templates, designs', 'Issued gift cards, cancellations, template catalog, design presets, and template reordering.', 'gift-card-manage', 'Gift Card Manage', 'Cancel pending, scheduled, or delivered gift cards and review redemption activity.', 'admin', '2026-05-19 19:08:53', '2026-05-19 21:43:11'),
(441, 'gift-card', 'Gift Cards', 'tags', 'Cards, templates, designs', 'Issued gift cards, cancellations, template catalog, design presets, and template reordering.', 'gift-card-template-list', 'Gift Card Template List', 'View the gift card template catalog, design presets, and usage stats.', 'admin', '2026-05-19 19:08:53', '2026-05-19 21:43:11'),
(442, 'gift-card', 'Gift Cards', 'tags', 'Cards, templates, designs', 'Issued gift cards, cancellations, template catalog, design presets, and template reordering.', 'gift-card-template-manage', 'Gift Card Template Manage', 'Create, edit, delete, reorder, and toggle gift card design templates.', 'admin', '2026-05-19 19:08:53', '2026-05-19 21:43:11'),
(673, 'theme-manager', 'Theme Manager', 'quick-style', 'Site theme switcher', 'Pick the active visual theme (Classic / Golden) that drives the public landing page and the builder component library.', 'theme-manager-view', 'Theme Manager View', 'Access the Theme Manager View admin capability.', 'admin', '2026-05-19 21:43:11', '2026-05-19 21:43:11'),
(674, 'theme-manager', 'Theme Manager', 'quick-style', 'Site theme switcher', 'Pick the active visual theme (Classic / Golden) that drives the public landing page and the builder component library.', 'theme-manager-update', 'Theme Manager Update', 'Update Theme Manager Update records and save approved changes.', 'admin', '2026-05-19 21:43:11', '2026-05-19 21:43:11');


-- ----------------------------------------------------------
-- Table: personal_access_tokens
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: phone_verification_codes
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `phone_verification_codes`;
CREATE TABLE `phone_verification_codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `phone_number` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT '0',
  `sent_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `phone_verification_codes_user_id_phone_number_index` (`user_id`,`phone_number`),
  KEY `phone_verification_codes_expires_at_verified_at_index` (`expires_at`,`verified_at`),
  CONSTRAINT `phone_verification_codes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: plugins
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fields_blade` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `credentials` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `plugins` (`id`, `type`, `name`, `code`, `fields_blade`, `credentials`, `logo`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'general', 'Google reCAPTCHA v3', 'google-recaptcha', NULL, '{\"recaptcha_key\":\"6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI\",\"recaptcha_secret\":\"6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe\"}', 'general/static/plugins/google-recaptcha.svg', 'reCAPTCHA v3 helps you detect abusive traffic on your website without user interaction\r\n', 0, NULL, '2025-06-09 09:27:29'),
(2, 'general', 'Facebook Messenger', 'fb', NULL, '{\"page_id\":\"990335491009901\"}', 'general/static/plugins/fb.png', 'Messenger is a proprietary instant messaging app and platform developed by Meta\r\n\r\n', 0, NULL, '2024-08-25 17:07:39'),
(3, 'general', 'Google Analytics 4', 'google-analytics', NULL, '{\"ga_measurement_id\":\"G-XXXXXXXXXX\"}', 'general/static/plugins/google-analytics.png', 'Google Analytics 4 is an analytics service that lets you to measure traffic and engagement across your websites and apps\n\n', 0, NULL, '2024-05-20 17:06:55'),
(4, 'general', 'Tawk Chat', 'tawk', NULL, '{\"property_id\":\"65e857468d261e1b5f6953aa\",\"widget_id\":\"1ho9p9rq8\"}', 'general/static/plugins/tawk.png', 'Free Instant Messaging system\r\n', 0, NULL, '2024-05-20 17:06:58'),
(5, 'general', 'IPinfo.io', 'ipinfo', NULL, '{\"access_token\":\"deb49413e0bc6a\"}', 'general/static/plugins/ipinfo.svg', 'The Trusted Source For IP Address Data\r\n', 1, NULL, '2025-03-18 00:28:23'),
(11, 'exchange_rate', 'Currencylayer', 'currencylayer', '_exchange_rate', '{\"api_key\":\"0778ef789e953fcde0be156459277bc5\",\"fields\":{\"auto_update_time\":\"2\",\"auto_update_time_unit\":\"1\",\"auto_update_status\":\"0\"}}', 'general/static/plugins/currencylayer.jpg', 'With over 15 exchange rate data sources, the Exchangerates API is delivering exchanging rates data for more than 170 world currencies.', 0, NULL, '2025-07-20 03:39:44'),
(12, 'exchange_rate', 'Coinmarketcap', 'coinmarketcap', '_exchange_rate', '{\"api_key\":\"8cea3244-8c3a-45d8-8061-63957aa6087b\",\"fields\":{\"auto_update_time\":\"1\",\"auto_update_time_unit\":\"1\",\"auto_update_status\":\"0\"}}', 'general/static/plugins/coinmarketcap.png', 'The world\'s cryptocurrency data authority has a professional API', 0, NULL, '2025-07-14 18:11:16'),
(13, 'notification', 'Pusher', 'pusher', NULL, '{\"pusher_app_id\":\"1881381\",\"pusher_app_key\":\"36755f88b2d2f13a9463\",\"pusher_app_secret\":\"f939de8a1ff3f564cb4d\",\"pusher_app_cluster\":\"ap2\"}', 'general/static/plugins/pusher.png', 'Leader In Realtime Technologies.Simple, scalable and reliable.Hosted realtime APIs loved by developers', 0, NULL, '2025-05-20 09:44:41'),
(14, 'notification', 'Twilto', 'twilio', NULL, '{\"account_sid\":\"ACbfdc5b6e20afc5a0290c78af2a349f1b\",\"auth_token\":\"40ebfde743d9311eb81fbd8bfd2207dc\",\"from\":\"+15413954764\"}', 'general/static/plugins/twilto.png', 'Twilio is a cloud service that allows sending and receiving SMS through simple, powerful APIs', 0, NULL, '2025-05-03 20:37:46'),
(15, 'mobile_recharge', 'Sandbox (Testing)', 'sandbox', NULL, '{\"sandbox_status\":\"completed\"}', 'general/static/plugins/sandbox-recharge.svg', 'Local sandbox driver. Use it to verify wallet flows without contacting any real provider.', 1, '2026-05-09 06:20:57', '2026-05-09 18:21:30'),
(16, 'mobile_recharge', 'Generic HTTP API', 'http', NULL, '{\"base_url\":null,\"endpoint\":\"\\/recharges\",\"token\":null,\"timeout\":15}', 'general/static/plugins/http-recharge.svg', 'Bring your own provider that exposes a REST endpoint and bearer-token auth.', 1, '2026-05-09 06:20:57', '2026-05-09 17:22:07'),
(17, 'mobile_recharge', 'Reloadly (Global Airtime)', 'reloadly', NULL, '{\"client_id\":null,\"client_secret\":null,\"sandbox\":\"1\",\"timeout\":\"20\"}', 'general/static/plugins/reloadly-recharge.svg', 'Global airtime aggregator covering 180+ countries. Supports both sandbox and production environments.', 1, '2026-05-09 06:20:57', '2026-05-09 18:21:25');


-- ----------------------------------------------------------
-- Table: project_licenses
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `project_licenses`;
CREATE TABLE `project_licenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_code` text COLLATE utf8mb4_unicode_ci,
  `license_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `support_until` timestamp NULL DEFAULT NULL,
  `activated_at` timestamp NULL DEFAULT NULL,
  `last_checked_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_licenses_product_slug_domain_unique` (`product_slug`,`domain`),
  KEY `project_licenses_product_slug_index` (`product_slug`),
  KEY `project_licenses_item_id_index` (`item_id`),
  KEY `project_licenses_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: project_updates
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `project_updates`;
CREATE TABLE `project_updates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `channel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'stable',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `package_url` text COLLATE utf8mb4_unicode_ci,
  `checksum` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature` text COLLATE utf8mb4_unicode_ci,
  `changelog` json DEFAULT NULL,
  `requirements` json DEFAULT NULL,
  `package_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `backup_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` timestamp NULL DEFAULT NULL,
  `checked_at` timestamp NULL DEFAULT NULL,
  `installed_at` timestamp NULL DEFAULT NULL,
  `error_message` longtext COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_updates_version_channel_unique` (`version`,`channel`),
  KEY `project_updates_version_index` (`version`),
  KEY `project_updates_channel_index` (`channel`),
  KEY `project_updates_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: referral_contents
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `referral_contents`;
CREATE TABLE `referral_contents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `heading` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `positive_guidelines` json DEFAULT NULL,
  `negative_guidelines` json DEFAULT NULL,
  `image_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `referral_contents` (`id`, `heading`, `positive_guidelines`, `negative_guidelines`, `image_path`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"en\":\"Share your unique referral link and earn for every successful signup.\",\"es\":\"Comparte tu enlace de referencia \\u00fanico y gana por cada registro exitoso.\"}', '{\"en\": [\"Easily share the link on social media platforms.\", \"Promote your link through any marketing channel.\", \"Share with friends and family members.\"], \"es\": [\"Comparte fácilmente el enlace en plataformas de redes sociales.\", \"Promociona tu enlace a través de cualquier canal de marketing.\", \"Comparte con amigos y familiares.\"]}', '{\"en\": [\"Multiple accounts from the same device are not allowed.\", \"Automated signups using bots are prohibited.\", \"Fake or misleading information is strictly forbidden.\"], \"es\": [\"No se permiten múltiples cuentas desde el mismo dispositivo.\", \"Los registros automatizados usando bots están prohibidos.\", \"La información falsa o engañosa está estrictamente prohibida.\"]}', 'images/2025/07/21/20250721_144406_gift_PRM5.svg', 1, '2025-07-21 14:32:15', '2025-07-21 14:44:52', NULL);


-- ----------------------------------------------------------
-- Table: referrals
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `referrals`;
CREATE TABLE `referrals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `referred_user_id` bigint unsigned DEFAULT NULL,
  `parent_referral_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `referrals_user_id_foreign` (`user_id`),
  KEY `referrals_referred_user_id_foreign` (`referred_user_id`),
  KEY `referrals_parent_referral_id_foreign` (`parent_referral_id`),
  CONSTRAINT `referrals_parent_referral_id_foreign` FOREIGN KEY (`parent_referral_id`) REFERENCES `referrals` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_referred_user_id_foreign` FOREIGN KEY (`referred_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `referrals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: rewards
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `rewards`;
CREATE TABLE `rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `level` int NOT NULL,
  `percentage` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `rewards` (`id`, `type`, `level`, `percentage`, `created_at`, `updated_at`) VALUES
(1, 'deposit', 1, '10.00', '2025-01-08 00:42:40', '2025-01-08 00:42:40'),
(4, 'payment', 1, '10.00', '2025-01-08 00:42:40', '2025-01-08 00:42:40'),
(9, 'payment', 2, '2.00', '2025-01-18 14:36:30', '2025-01-18 14:36:38'),
(10, 'payment', 3, '1.00', '2025-01-18 14:36:44', '2025-01-18 14:36:44');


-- ----------------------------------------------------------
-- Table: role_has_permissions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `role_has_permissions`;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(213, 1),
(214, 1),
(216, 1),
(217, 1),
(326, 1),
(327, 1),
(438, 1),
(439, 1),
(440, 1),
(441, 1),
(442, 1),
(673, 1),
(674, 1),
(1, 6),
(2, 6),
(3, 6),
(38, 6),
(39, 6),
(40, 6),
(43, 6),
(44, 6),
(45, 6),
(47, 6),
(49, 6),
(50, 6),
(51, 6),
(53, 6),
(55, 6),
(83, 6),
(99, 6),
(326, 6),
(327, 6),
(439, 6),
(1, 7),
(8, 7),
(10, 7),
(12, 7),
(29, 7),
(83, 7),
(89, 7),
(90, 7),
(94, 7),
(95, 7),
(96, 7),
(97, 7),
(1, 8),
(8, 8),
(23, 8),
(26, 8),
(29, 8),
(30, 8),
(31, 8),
(32, 8),
(33, 8),
(92, 8),
(65, 9),
(66, 9),
(67, 9),
(68, 9),
(70, 9),
(71, 9),
(72, 9),
(73, 9),
(74, 9),
(75, 9),
(77, 9),
(78, 9),
(79, 9),
(80, 9),
(81, 9),
(82, 9),
(98, 9),
(213, 9),
(214, 9),
(441, 9),
(442, 9),
(673, 9),
(674, 9),
(1, 10),
(2, 10),
(3, 10),
(6, 10),
(7, 10),
(8, 10),
(10, 10),
(23, 10),
(24, 10),
(26, 10),
(27, 10),
(38, 10),
(39, 10),
(43, 10),
(44, 10),
(57, 10),
(83, 10),
(101, 10),
(102, 10),
(103, 10),
(105, 10),
(438, 10),
(439, 10),
(440, 10),
(441, 10);


-- ----------------------------------------------------------
-- Table: roles
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(600) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `roles` (`id`, `name`, `description`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'Full control over the entire platform including users, roles, system settings, financial configs, and logs.\n', 'admin', '2024-07-09 05:07:24', '2024-07-09 05:07:24'),
(6, 'finance-manager', 'Controls deposits, withdrawals, gateways, wallet balances, and financial reporting.', 'admin', '2026-05-03 06:10:19', '2026-05-03 06:10:19'),
(7, 'support-executive', 'Handles customer profiles, tickets, notifications, KYC visibility, and transaction lookup.', 'admin', '2026-05-03 06:10:19', '2026-05-03 06:10:19'),
(8, 'kyc-officer', 'Reviews identity submissions, manages KYC actions, templates, and compliance notices.', 'admin', '2026-05-03 06:10:19', '2026-05-03 06:10:19'),
(9, 'content-manager', 'Maintains public pages, blog content, navigation, subscribers, social links, and SEO.', 'admin', '2026-05-03 06:10:19', '2026-05-03 06:10:19'),
(10, 'operations-manager', 'Oversees users, merchants, agents, finance queues, transactions, features, and app health.', 'admin', '2026-05-03 06:10:19', '2026-05-03 06:10:19');


-- ----------------------------------------------------------
-- Table: sessions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: settings
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `val` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`id`, `key`, `val`, `type`, `created_at`, `updated_at`) VALUES
(1, 'site_title', 'DigiKash', 'string', '2024-07-09 05:08:37', '2026-05-06 18:01:50'),
(2, 'admin_prefix', 'admin', 'string', '2024-07-09 05:08:37', '2024-07-09 05:08:37'),
(3, 'copyright_text', 'Copyright © 2024 DigiKash  | All rights reserved', 'string', '2024-07-09 05:08:37', '2024-07-13 13:19:08'),
(4, 'logo', 'images/2026/05/06/20260506_180115_2untitled_qPb7.png', 'string', '2024-07-09 05:09:57', '2026-05-06 18:01:15'),
(5, 'light_logo', 'images/2025-03-03_05-04-05_2untitled_HZ9o.png', 'string', '2024-07-09 05:09:57', '2025-03-03 05:04:05'),
(6, 'small_logo', 'images/2026/05/06/20260506_181508_envato_thumbnail_krDb.png', 'string', '2024-07-09 05:09:57', '2026-05-06 18:15:08'),
(7, 'site_favicon', 'images/2026/05/06/20260506_181432_thumbnail_yuND.png', 'string', '2024-07-09 05:09:57', '2026-05-06 18:14:32'),
(8, 'login_banner', 'images/2026/04/08/20260408_163021_chatgpt_image_apr_3_2026_11_40_32_am_1_9HXh.png', 'string', '2024-07-09 05:10:31', '2026-04-08 16:30:21'),
(9, 'screen_lock', '0', 'bool', '2024-07-14 10:32:48', '2025-03-13 11:17:08'),
(10, 'screen_lock_time', '10', 'integer', '2024-07-14 10:32:48', '2025-03-13 11:17:08'),
(11, 'site_currency_type', 'fiat', 'string', '2024-08-13 07:26:29', '2024-09-02 01:14:17'),
(12, 'site_currency', 'AED', 'string', '2024-08-13 07:27:46', '2024-09-02 01:14:17'),
(13, 'currency_symbol', 'Ξ', 'string', '2024-08-13 07:27:46', '2024-08-22 10:20:25'),
(14, 'force_https', '0', 'bool', '2024-12-02 13:18:54', '2025-03-18 01:43:22'),
(15, 'submission_lock_duration', '1', 'integer', '2024-12-02 13:18:54', '2025-05-06 12:00:11'),
(16, 'support_email', 'coevs.dev@gmail.com', 'string', '2024-12-11 17:49:44', '2025-04-14 00:44:51'),
(17, 'deposit_rewards', '1', 'bool', '2025-01-18 05:59:54', '2025-03-17 06:28:38'),
(18, 'payment_rewards', '1', 'bool', '2025-01-18 06:01:46', '2025-01-18 08:26:03'),
(19, 'secret_key', 'secret', 'string', '2025-02-15 09:41:30', '2025-02-15 09:41:30'),
(20, 'maintenance_title', 'Site is not under maintenance', 'string', '2025-02-15 09:41:30', '2025-02-15 09:41:30'),
(21, 'maintenance_text', 'Sorry for interrupt! Site will live soon.', 'string', '2025-02-15 09:41:30', '2025-02-15 09:41:30'),
(22, 'site_environment', 'local', 'string', '2025-02-15 09:41:30', '2025-08-05 10:47:04'),
(23, 'development_mode', '1', 'bool', '2025-02-15 09:41:30', '2026-05-15 17:45:03'),
(24, 'maintenance_mode', '0', 'bool', '2025-02-15 09:41:30', '2025-02-15 09:41:30'),
(25, 'email_from_name', 'Coevs', 'string', '2025-02-25 04:19:37', '2025-02-25 04:19:37'),
(26, 'email_from_address', 'coevs.dev@gmail.com', 'string', '2025-02-25 04:19:37', '2025-02-25 04:19:37'),
(27, 'mail_username', 'coevs.dev@gmail.com', 'string', '2025-02-25 04:19:37', '2025-02-25 04:19:37'),
(28, 'mail_password', 'cykf cdba oqyt fwss', 'string', '2025-02-25 04:19:37', '2025-04-14 04:52:31'),
(29, 'mail_host', 'smtp.gmail.com', 'string', '2025-02-25 04:19:37', '2025-02-25 04:19:37'),
(30, 'mail_port', '465', 'integer', '2025-02-25 04:19:37', '2025-02-25 04:19:37'),
(31, 'mail_secure', 'tls', 'string', '2025-02-25 04:19:37', '2025-02-25 13:09:32'),
(32, 'max_upload_size', '5', 'integer', '2025-04-05 18:37:49', '2025-04-05 18:37:49'),
(33, 'support_phone', '+1234567890', 'string', '2025-04-08 00:29:13', '2025-04-08 00:29:13'),
(34, 'default_breadcrumb_image', 'images/2025/04/09/20250409_095426_breadcrumb_jT3b.jpg', 'string', '2025-04-09 09:54:26', '2025-04-09 09:54:26'),
(35, 'site_timezone', 'Asia/Dhaka', 'string', '2025-04-23 05:45:54', '2025-04-23 05:45:54'),
(36, 'home_redirect', '/', 'string', '2025-04-23 05:45:54', '2025-07-20 11:15:33'),
(37, 'cookie_title', 'Cookies Consent', 'string', '2025-05-26 07:59:32', '2025-05-26 07:59:32'),
(38, 'cookie_summary', 'This website use cookies to help you have a superior and more relevant browsing experience on the website.', 'string', '2025-05-26 07:59:32', '2025-05-26 07:59:32'),
(39, 'cookie_url', '/', 'string', '2025-05-26 07:59:32', '2025-05-26 07:59:32'),
(40, 'cookie_status', '0', 'bool', '2025-05-26 07:59:32', '2025-05-26 09:52:34'),
(41, 'site_preloader', '1', 'bool', '2025-07-14 03:25:38', '2025-07-14 03:41:35'),
(42, 'preloaded_text', 's,dfgg,hf', 'string', '2025-07-14 03:25:38', '2025-07-14 03:41:10'),
(43, 'preloader_text', 'D,I,G,I,K,A,S,H', 'string', '2025-07-14 03:42:43', '2025-07-14 03:43:46'),
(44, 'site_decimal', '2', 'integer', '2026-04-08 16:30:21', '2026-04-08 16:30:21'),
(45, 'agent_program_enabled', '1', 'bool', '2026-05-01 04:02:05', '2026-05-02 17:38:16'),
(46, 'agent_self_registration', '1', 'bool', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(47, 'agent_auto_approve', '0', 'bool', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(48, 'agent_require_kyc', '0', 'bool', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(49, 'agent_max_per_user', '50', 'integer', '2026-05-01 04:02:05', '2026-05-10 17:00:05'),
(50, 'agent_code_prefix', 'AGT-', 'string', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(51, 'agent_default_commission', '0', 'string', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(52, 'agent_min_commission', '0', 'string', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(53, 'agent_max_commission', '100', 'string', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(54, 'agent_allowed_countries', NULL, 'string', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(55, 'agent_admin_email_notify', '1', 'bool', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(56, 'agent_user_email_notify', '1', 'bool', '2026-05-01 04:02:05', '2026-05-01 04:02:05'),
(57, 'pwa_enabled', '1', 'bool', '2026-05-05 10:10:29', '2026-05-13 00:01:58'),
(58, 'pwa_app_name', NULL, 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(59, 'pwa_short_name', NULL, 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(60, 'pwa_description', 'Secure mobile wallet dashboard.', 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(61, 'pwa_theme_color', '#4663EE', 'string', '2026-05-05 10:10:29', '2026-05-11 11:11:25'),
(62, 'pwa_background_color', '#f3f7fb', 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(63, 'pwa_display', 'standalone', 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(64, 'pwa_orientation', 'portrait-primary', 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(65, 'pwa_offline_message', 'A live connection is required for balances, payments, and transactions. Please reconnect and try again.', 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(66, 'pwa_cache_version', NULL, 'string', '2026-05-05 10:10:29', '2026-05-05 10:10:29'),
(67, 'notification_delivery_enabled', '1', 'bool', '2026-05-05 13:44:35', '2026-05-05 13:44:35'),
(68, 'notification_tune_sound_enabled', '1', 'bool', '2026-05-05 13:44:35', '2026-05-05 13:44:35'),
(69, 'notification_tune_default', 'glow', 'string', '2026-05-05 13:44:35', '2026-05-06 00:03:46'),
(70, 'pwa_icon_192', 'pwa/icons/icon-192.png', 'string', '2026-05-06 17:47:52', '2026-05-13 00:01:58'),
(71, 'pwa_icon_512', 'pwa/icons/icon-512.png', 'string', '2026-05-06 17:47:52', '2026-05-13 00:01:58'),
(72, 'pwa_maskable_icon', 'pwa/icons/maskable-512.png', 'string', '2026-05-06 17:47:52', '2026-05-13 00:01:58'),
(73, 'pwa_apple_touch_icon', 'pwa/icons/apple-touch-icon.png', 'string', '2026-05-06 17:47:52', '2026-05-13 00:01:58'),
(75, 'login_attempt_limit', '5', 'integer', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(76, 'login_lock_minutes', '15', 'integer', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(77, 'secure_response_headers', '0', 'bool', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(78, 'strict_transport_security', '0', 'bool', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(79, 'wallet_pin_attempt_limit', '5', 'integer', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(80, 'wallet_pin_lock_minutes', '15', 'integer', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(81, 'merchant_api_signature_required', '1', 'bool', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(82, 'merchant_api_timestamp_tolerance', '300', 'integer', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(83, 'merchant_api_rate_limit_per_minute', '120', 'integer', '2026-05-06 18:05:51', '2026-05-06 18:05:51'),
(84, 'maintenance_cover', 'images/2026/05/06/20260506_180658_medal_aOun.png', 'string', '2026-05-06 18:06:58', '2026-05-06 18:06:58'),
(85, 'mobile_recharge_provider', 'sandbox', 'string', '2026-05-09 14:01:37', '2026-05-09 18:21:30'),
(86, 'user_role_primary_color', '#4663ee', 'string', '2026-05-11 11:11:46', '2026-05-11 11:11:46'),
(87, 'user_role_accent_color', '#17a86a', 'string', '2026-05-11 11:11:46', '2026-05-11 11:11:46'),
(88, 'merchant_role_primary_color', '#16a34a', 'string', '2026-05-11 11:11:46', '2026-05-11 11:11:46'),
(89, 'merchant_role_accent_color', '#14b8a6', 'string', '2026-05-11 11:11:46', '2026-05-11 11:11:46'),
(90, 'agent_role_primary_color', '#6D28D9', 'string', '2026-05-11 11:11:46', '2026-05-11 11:42:25'),
(91, 'agent_role_accent_color', '#22D3EE', 'string', '2026-05-11 11:11:46', '2026-05-11 11:42:25'),
(92, 'signup_bonus_enabled', '1', 'bool', '2026-05-19 18:35:13', '2026-05-19 18:35:13'),
(93, 'signup_bonus_require_email_verified', '0', 'bool', '2026-05-19 18:35:13', '2026-05-19 18:36:18'),
(94, 'signup_bonus_user_amount', '5', 'string', '2026-05-19 18:35:13', '2026-05-19 18:35:13'),
(95, 'signup_bonus_merchant_amount', '10', 'string', '2026-05-19 18:35:13', '2026-05-19 18:37:57'),
(96, 'signup_bonus_agent_amount', '20', 'string', '2026-05-19 18:35:13', '2026-05-19 18:37:57'),
(97, 'active_theme', 'classic', 'string', '2026-05-19 21:43:40', '2026-05-20 10:46:14');


-- ----------------------------------------------------------
-- Table: site_seos
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `site_seos`;
CREATE TABLE `site_seos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` json DEFAULT NULL,
  `meta_description` json DEFAULT NULL,
  `meta_keywords` json DEFAULT NULL,
  `canonical_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `robots` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'index,follow',
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `site_seos` (`id`, `page_id`, `meta_title`, `meta_description`, `meta_keywords`, `canonical_url`, `robots`, `image`, `created_at`, `updated_at`) VALUES
(1, NULL, '{\"en\": \"Digikash - Secure & Fast Digital Wallet for Easy Payments\", \"es\": \"Digikash - Monedero Digital Seguro y Rápido para Pagos Fáciles\"}', '{\"en\": \"Digikash is your trusted digital wallet solution offering instant money transfers, secure online payments, and seamless transactions worldwide.\", \"es\": \"Digikash es tu solución confiable de monedero digital que ofrece transferencias de dinero instantáneas, pagos en línea seguros y transacciones sin complicaciones en todo el mundo.\"}', '\"digikash,digital wallet,send money,receive money,online payment,fast transfer,secure wallet,wallet app\"', 'https://yourdigikash.com', 'index,follow', 'images/2025/04/07/20250407_115616_2025_02_27_15_19_28_logo_t9b0_D4ap.png', '2025-04-07 03:52:10', '2025-04-07 12:00:28');


-- ----------------------------------------------------------
-- Table: socials
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `socials`;
CREATE TABLE `socials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `socials` (`id`, `name`, `icon_class`, `target`, `url`, `status`, `created_at`, `updated_at`) VALUES
(4, 'Facebook', 'fab fa-facebook-f', '_blank', 'https://www.facebook.com/yourpage', 1, '2025-04-08 02:31:11', '2025-04-09 16:30:02'),
(5, 'Twitter', 'fab fa-twitter', '_blank', 'https://www.twitter.com/yourprofile', 1, '2025-04-08 02:35:55', '2025-04-08 02:49:39'),
(6, 'LinkedIn', 'fab fa-linkedin-in', '_self', 'https://www.linkedin.com/in/yourprofile', 1, '2025-04-08 02:36:20', '2025-04-08 02:44:53');


-- ----------------------------------------------------------
-- Table: subscribers
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `subscribers`;
CREATE TABLE `subscribers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `subscribed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribers_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- (no data)


-- ----------------------------------------------------------
-- Table: subscription_plan_features
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `subscription_plan_features`;
CREATE TABLE `subscription_plan_features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_plan_id` bigint unsigned NOT NULL,
  `feature_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `feature_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'limit',
  `reset_cycle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_plan_features_plan_key_unique` (`subscription_plan_id`,`feature_key`),
  KEY `subscription_plan_features_subscription_plan_id_index` (`subscription_plan_id`),
  KEY `subscription_plan_features_feature_key_feature_type_index` (`feature_key`,`feature_type`),
  CONSTRAINT `subscription_plan_features_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subscription_plan_features` (`id`, `subscription_plan_id`, `feature_key`, `feature_label`, `feature_value`, `feature_type`, `reset_cycle`, `sort_order`, `created_at`, `updated_at`) VALUES
(38, 1, 'deposit_money', 'Deposit Money', 'enabled', 'toggle', NULL, 1, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(39, 1, 'withdraw_money', 'Withdraw Money', 'enabled', 'toggle', NULL, 2, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(40, 1, 'send_money', 'Send Money', 'enabled', 'toggle', NULL, 3, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(41, 1, 'request_money', 'Request Money', 'enabled', 'toggle', NULL, 4, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(42, 1, 'vouchers', 'Vouchers & Cashback', 'enabled', 'toggle', NULL, 5, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(43, 1, 'transaction_history', 'Transaction History', 'enabled', 'toggle', NULL, 6, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(44, 1, 'two_factor_auth', 'Two-Factor Auth (2FA)', 'enabled', 'toggle', NULL, 7, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(45, 1, 'push_notifications', 'Push Notifications', 'enabled', 'toggle', NULL, 8, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(46, 1, 'daily_transaction_limit', 'Daily Transactions', '5', 'limit', NULL, 9, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(47, 1, 'monthly_withdraw_limit', 'Monthly Withdrawal', '$500', 'limit', NULL, 10, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(48, 1, 'wallet_balance_cap', 'Max Wallet Balance', '$1,000', 'limit', NULL, 11, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(49, 1, 'support_priority', 'Customer Support', 'Standard', 'limit', NULL, 12, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(50, 2, 'deposit_money', 'Deposit Money', 'enabled', 'toggle', NULL, 1, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(51, 2, 'withdraw_money', 'Withdraw Money', 'enabled', 'toggle', NULL, 2, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(52, 2, 'send_money', 'Send Money', 'enabled', 'toggle', NULL, 3, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(53, 2, 'request_money', 'Request Money', 'enabled', 'toggle', NULL, 4, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(54, 2, 'exchange_money', 'Currency Exchange', 'enabled', 'toggle', NULL, 5, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(55, 2, 'p2p_marketplace', 'P2P Marketplace', 'enabled', 'toggle', NULL, 6, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(56, 2, 'virtual_card', 'Virtual Cards', 'enabled', 'toggle', NULL, 7, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(57, 2, 'wallet_earn', 'Wallet Earn (Staking)', 'enabled', 'toggle', NULL, 8, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(58, 2, 'referral_program', 'Referral Program', 'enabled', 'toggle', NULL, 9, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(59, 2, 'daily_transaction_limit', 'Daily Transactions', '50', 'limit', NULL, 10, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(60, 2, 'monthly_withdraw_limit', 'Monthly Withdrawal', '$10,000', 'limit', NULL, 11, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(61, 2, 'support_priority', 'Priority Support', 'Priority', 'limit', NULL, 12, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(62, 3, 'deposit_money', 'Deposit Money', 'enabled', 'toggle', NULL, 1, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(63, 3, 'withdraw_money', 'Withdraw Money', 'enabled', 'toggle', NULL, 2, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(64, 3, 'send_money', 'Send Money', 'enabled', 'toggle', NULL, 3, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(65, 3, 'exchange_money', 'Currency Exchange', 'enabled', 'toggle', NULL, 4, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(66, 3, 'p2p_marketplace', 'P2P Marketplace', 'enabled', 'toggle', NULL, 5, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(67, 3, 'virtual_card', 'Virtual Cards', 'enabled', 'toggle', NULL, 6, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(68, 3, 'wallet_earn', 'Wallet Earn (Staking)', 'enabled', 'toggle', NULL, 7, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(69, 3, 'payment_link', 'Payment Links', 'enabled', 'toggle', NULL, 8, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(70, 3, 'bank_transfer', 'Bank Transfer Payouts', 'enabled', 'toggle', NULL, 9, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(71, 3, 'api_access', 'API Access', 'enabled', 'toggle', NULL, 10, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(72, 3, 'daily_transaction_limit', 'Daily Transactions', 'unlimited', 'limit', NULL, 11, '2026-04-26 23:41:33', '2026-04-26 23:41:33'),
(73, 3, 'support_priority', 'Dedicated Support', 'Dedicated', 'limit', NULL, 12, '2026-04-26 23:41:33', '2026-04-26 23:41:33');


-- ----------------------------------------------------------
-- Table: subscription_plan_prices
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `subscription_plan_prices`;
CREATE TABLE `subscription_plan_prices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_plan_id` bigint unsigned NOT NULL,
  `billing_cycle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `discount` tinyint unsigned DEFAULT NULL COMMENT 'Discount percentage (0-100)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_price_cycle_unique` (`subscription_plan_id`,`billing_cycle`),
  KEY `subscription_plan_prices_billing_cycle_index` (`billing_cycle`),
  CONSTRAINT `subscription_plan_prices_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subscription_plan_prices` (`id`, `subscription_plan_id`, `billing_cycle`, `price`, `discount`, `created_at`, `updated_at`) VALUES
(1, 1, 'monthly', '0.00000000', NULL, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(2, 1, 'half_yearly', '0.00000000', NULL, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(3, 1, 'yearly', '0.00000000', NULL, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(4, 2, 'monthly', '9.99000000', NULL, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(5, 2, 'half_yearly', '53.95000000', 10, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(6, 2, 'yearly', '95.90000000', 20, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(7, 3, 'monthly', '29.99000000', NULL, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(8, 3, 'half_yearly', '161.95000000', 10, '2026-04-26 23:41:32', '2026-04-26 23:41:32'),
(9, 3, 'yearly', '287.90000000', 20, '2026-04-26 23:41:32', '2026-04-26 23:41:32');


-- ----------------------------------------------------------
-- Table: subscription_plans
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE `subscription_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `trial_days` int unsigned NOT NULL DEFAULT '0',
  `grace_days` int unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `plan_badge` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `auto_renew_default` tinyint(1) NOT NULL DEFAULT '0',
  `cancellation_policy` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'end_of_period',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_plans_slug_unique` (`slug`),
  KEY `subscription_plans_status_sort_order_index` (`status`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `trial_days`, `grace_days`, `is_featured`, `plan_badge`, `auto_renew_default`, `cancellation_policy`, `sort_order`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Starter', 'starter', 'Everything you need to get started. Core wallet features with sensible daily limits.', 0, 3, 0, 'FREE', 1, 'end_of_period', 1, 1, '2026-04-25 16:57:05', '2026-04-26 23:41:32'),
(2, 'Pro', 'pro', 'Unlock advanced features — exchange, P2P trading, virtual cards, and higher limits.', 7, 5, 0, 'POPULAR', 1, 'end_of_period', 2, 1, '2026-04-25 16:57:05', '2026-04-26 23:41:32'),
(3, 'Enterprise', 'enterprise', 'Full platform access with unlimited transactions, API integration, and dedicated support.', 14, 7, 1, 'BEST VALUE', 1, 'end_of_period', 3, 1, '2026-04-25 16:57:05', '2026-04-26 23:41:32');


-- ----------------------------------------------------------
-- Table: subscription_transactions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `subscription_transactions`;
CREATE TABLE `subscription_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_subscription_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `subscription_plan_id` bigint unsigned NOT NULL,
  `trx_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `amount` decimal(24,8) NOT NULL,
  `currency_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'completed',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscription_transactions_subscription_plan_id_foreign` (`subscription_plan_id`),
  KEY `subscription_transactions_user_id_type_index` (`user_id`,`type`),
  KEY `subscription_transactions_user_subscription_id_type_index` (`user_subscription_id`,`type`),
  KEY `subscription_transactions_trx_id_index` (`trx_id`),
  CONSTRAINT `subscription_transactions_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `subscription_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscription_transactions_user_subscription_id_foreign` FOREIGN KEY (`user_subscription_id`) REFERENCES `user_subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: support_categories
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `support_categories`;
CREATE TABLE `support_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `support_categories` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Genaral', 1, '2025-01-21 06:56:13', '2025-01-21 07:18:15');


-- ----------------------------------------------------------
-- Table: tickets
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `priority` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tickets_uuid_unique` (`uuid`),
  KEY `tickets_user_id_foreign` (`user_id`),
  CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: transactions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `trx_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trx_token` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `trx_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `provider` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `processing_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `amount_flow` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fee` decimal(15,2) DEFAULT NULL,
  `currency` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `net_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `payable_amount` decimal(15,2) DEFAULT NULL,
  `payable_currency` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallet_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trx_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trx_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','completed','canceled','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_trx_token_unique` (`trx_token`),
  KEY `transactions_user_id_foreign` (`user_id`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=192 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: user_features
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `user_features`;
CREATE TABLE `user_features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `feature` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_features_user_id_foreign` (`user_id`),
  CONSTRAINT `user_features_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=223 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: user_ranks
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `user_ranks`;
CREATE TABLE `user_ranks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_amount` int unsigned NOT NULL,
  `transaction_types` json DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reward` double NOT NULL DEFAULT '0',
  `features` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_ranks_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user_ranks` (`id`, `is_default`, `icon`, `name`, `transaction_amount`, `transaction_types`, `description`, `reward`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'images/2025-01-27_15-07-46_ranking_badge_lQaU.png', 'Starter', 0, '[\"deposit\", \"referral_reward\"]', 'Begin your journey with free access and basic features!', 0, '{\"wallet_create\": \"4\", \"referral_level\": \"2\"}', 1, '2025-01-27 11:39:34', '2026-04-30 14:14:08'),
(2, 0, 'images/2025-01-26_07-22-05_bronze_badge_myon.png', 'Bronze', 200, '[\"deposit\", \"send_money\", \"referral_reward\"]', 'Gain referral levels and earn rewards by completing transactions of $200', 2, '{\"wallet_create\": \"6\", \"referral_level\": \"3\"}', 1, '2025-01-26 07:22:05', '2026-04-30 14:14:13'),
(3, 0, 'images/2025-01-26_07-26-56_silver_medal_aaP2.png', 'Silver', 500, '[\"deposit\", \"send_money\", \"referral_reward\"]', 'Unlock higher referral levels and earn greater rewards by completing transactions of $500 or more!', 10, '{\"wallet_create\": \"8\", \"referral_level\": \"4\"}', 1, '2025-01-26 07:26:56', '2026-04-30 14:14:17'),
(4, 0, 'images/2025-01-26_07-28-08_gold_medal_xRTm.png', 'Gold', 1300, '[\"deposit\", \"referral_reward\"]', 'Maximize your earnings with advanced referral levels and exclusive rewards by completing transactions of $1,300 or more!', 50, '{\"wallet_create\": \"12\", \"referral_level\": \"5\"}', 1, '2025-01-26 07:28:08', '2026-04-30 14:14:22');


-- ----------------------------------------------------------
-- Table: user_subscriptions
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `user_subscriptions`;
CREATE TABLE `user_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `subscription_plan_id` bigint unsigned NOT NULL,
  `billing_cycle` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `started_at` timestamp NULL DEFAULT NULL,
  `trial_ends_at` timestamp NULL DEFAULT NULL,
  `current_period_start` timestamp NULL DEFAULT NULL,
  `current_period_end` timestamp NULL DEFAULT NULL,
  `grace_ends_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by_admin` tinyint(1) NOT NULL DEFAULT '0',
  `auto_renew` tinyint(1) NOT NULL DEFAULT '0',
  `amount_paid` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `currency_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
  `wallet_reference` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_subscriptions_user_id_status_index` (`user_id`,`status`),
  KEY `user_subscriptions_status_current_period_end_index` (`status`,`current_period_end`),
  KEY `user_subscriptions_status_trial_ends_at_index` (`status`,`trial_ends_at`),
  KEY `user_subscriptions_subscription_plan_id_index` (`subscription_plan_id`),
  CONSTRAINT `user_subscriptions_subscription_plan_id_foreign` FOREIGN KEY (`subscription_plan_id`) REFERENCES `subscription_plans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `user_subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: users
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `referral_code` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rank_id` bigint DEFAULT NULL,
  `old_ranks` json DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `first_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verification_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `state` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `signup_bonus_awarded_at` timestamp NULL DEFAULT NULL,
  `signup_bonus_seen_at` timestamp NULL DEFAULT NULL,
  `google2fa_secret` varchar(196) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_factor_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `p2p_trading_suspended_at` timestamp NULL DEFAULT NULL,
  `p2p_trading_suspend_reason` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wallet_pin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_username_unique` (`username`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_pk` (`referral_code`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: virtual_card_fee_settings
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `virtual_card_fee_settings`;
CREATE TABLE `virtual_card_fee_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `operation` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fee_amount` decimal(12,2) NOT NULL,
  `fee_percent` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `min_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `max_amount` decimal(12,2) DEFAULT NULL,
  `daily_txn_limit` int DEFAULT NULL,
  `daily_amount_limit` decimal(16,2) DEFAULT NULL,
  `approval_threshold` decimal(12,2) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_fee_setting` (`provider_id`,`currency_id`,`operation`),
  KEY `virtual_card_fee_settings_currency_id_foreign` (`currency_id`),
  CONSTRAINT `virtual_card_fee_settings_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_card_fee_settings_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `virtual_card_providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `virtual_card_fee_settings` (`id`, `provider_id`, `currency_id`, `operation`, `fee_amount`, `fee_percent`, `min_amount`, `max_amount`, `daily_txn_limit`, `daily_amount_limit`, `approval_threshold`, `active`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'topup', '2.00', '1.0000', '10.00', '1000.00', NULL, NULL, '200.00', 1, '2025-07-04 16:36:31', '2025-08-29 03:05:52');


-- ----------------------------------------------------------
-- Table: virtual_card_providers
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `virtual_card_providers`;
CREATE TABLE `virtual_card_providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_gateway_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_color` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_label` varchar(24) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supported_networks` json DEFAULT NULL,
  `supported_currencies` json DEFAULT NULL,
  `supported_countries` json DEFAULT NULL,
  `issue_fee` decimal(12,2) NOT NULL DEFAULT '0.00',
  `issue_fee_pct` decimal(5,2) DEFAULT NULL,
  `additional_issue_fee_percent` decimal(5,2) DEFAULT NULL COMMENT 'Extra percent applied on initial_load_amount at issuance',
  `min_balance` decimal(12,2) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `config` json DEFAULT NULL,
  `capabilities` json DEFAULT NULL,
  `order` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtual_card_providers_code_unique` (`code`),
  KEY `virtual_card_providers_payment_gateway_id_index` (`payment_gateway_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `virtual_card_providers` (`id`, `payment_gateway_id`, `name`, `code`, `logo`, `brand`, `brand_color`, `display_label`, `description`, `supported_networks`, `supported_currencies`, `supported_countries`, `issue_fee`, `issue_fee_pct`, `additional_issue_fee_percent`, `min_balance`, `status`, `config`, `capabilities`, `order`, `created_at`, `updated_at`) VALUES
(1, 2, 'Stripe Issuing', 'stripe', 'general/static/gateway/stripe.png', 'Multi', NULL, NULL, NULL, '[\"mastercard\", \"visa\"]', '[\"USD\", \"EUR\", \"GBP\"]', NULL, '2.00', '0.00', NULL, '10.00', 1, NULL, '{\"issue\": true, \"topup\": false, \"freeze\": true, \"limits\": true, \"controls\": true, \"withdraw\": false, \"card_details\": true}', 1, '2025-07-01 04:20:14', '2026-04-28 14:52:36'),
(2, 10, 'StroWallet Provider', 'strowallet', 'general/static/gateway/strowallet.png', 'Multi', NULL, NULL, NULL, '[\"mastercard\", \"visa\"]', '[\"USD\", \"NGN\"]', NULL, '1.50', '1.80', NULL, '5.00', 1, NULL, '{\"issue\": true, \"topup\": true, \"freeze\": false, \"limits\": false, \"controls\": false, \"withdraw\": true, \"card_details\": true}', 2, '2025-07-03 05:44:48', '2026-04-28 14:52:36'),
(3, 31, 'Bitnob', 'bitnob', 'general/static/gateway/bitnob.png', 'Visa', NULL, NULL, NULL, '[\"visa\"]', '[\"USD\"]', NULL, '1.00', NULL, NULL, '2.00', 1, NULL, '{\"issue\": true, \"topup\": true, \"freeze\": true, \"limits\": true, \"controls\": false, \"withdraw\": true, \"card_details\": true}', 3, '2026-04-28 14:52:36', '2026-04-28 14:52:36');


-- ----------------------------------------------------------
-- Table: virtual_card_requests
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `virtual_card_requests`;
CREATE TABLE `virtual_card_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `cardholder_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned DEFAULT NULL,
  `initial_load_amount` decimal(12,2) DEFAULT NULL COMMENT 'Optional base amount for percent surcharge at issuance',
  `network` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `theme` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `admin_reviewed_at` timestamp NULL DEFAULT NULL,
  `provider_issued_at` timestamp NULL DEFAULT NULL,
  `provider_response` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtual_card_requests_uuid_unique` (`uuid`),
  KEY `virtual_card_requests_wallet_id_foreign` (`wallet_id`),
  KEY `virtual_card_requests_user_id_foreign` (`user_id`),
  KEY `id` (`provider_id`),
  CONSTRAINT `virtual_card_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_card_requests_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: virtual_cards
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `virtual_cards`;
CREATE TABLE `virtual_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `virtual_card_request_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned DEFAULT NULL,
  `provider_card_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `network` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last4` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_month` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expiry_year` varchar(4) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `meta` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `virtual_cards_provider_card_id_unique` (`provider_card_id`),
  KEY `virtual_cards_virtual_card_request_id_foreign` (`virtual_card_request_id`),
  KEY `virtual_cards_wallet_id_foreign` (`wallet_id`),
  KEY `virtual_cards_user_id_foreign` (`user_id`),
  KEY `virtual_cards_provider_id_foreign` (`provider_id`),
  CONSTRAINT `virtual_cards_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `virtual_card_providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_cards_virtual_card_request_id_foreign` FOREIGN KEY (`virtual_card_request_id`) REFERENCES `virtual_card_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `virtual_cards_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: vouchers
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `vouchers`;
CREATE TABLE `vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `redeemed_by` bigint unsigned DEFAULT NULL,
  `redeemed_wallet_id` bigint unsigned DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vouchers_code_unique` (`code`),
  KEY `vouchers_user_id_foreign` (`user_id`),
  CONSTRAINT `vouchers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: wallet_earn_plans
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `wallet_earn_plans`;
CREATE TABLE `wallet_earn_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `minimum_amount` decimal(24,8) NOT NULL,
  `maximum_amount` decimal(24,8) DEFAULT NULL,
  `profit_rate` decimal(24,8) NOT NULL,
  `profit_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `duration_value` int unsigned NOT NULL DEFAULT '1',
  `duration_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'days',
  `payout_frequency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'end_of_term',
  `return_principal` tinyint(1) NOT NULL DEFAULT '1',
  `auto_approve` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `plan_badge` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_earn_plans_status_sort_order_index` (`status`,`sort_order`),
  KEY `wallet_earn_plans_currency_id_index` (`currency_id`),
  KEY `wallet_earn_plans_featured_badge_index` (`is_featured`,`plan_badge`),
  CONSTRAINT `wallet_earn_plans_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wallet_earn_plans` (`id`, `currency_id`, `name`, `description`, `icon`, `minimum_amount`, `maximum_amount`, `profit_rate`, `profit_type`, `duration_value`, `duration_unit`, `payout_frequency`, `return_principal`, `auto_approve`, `sort_order`, `is_featured`, `plan_badge`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Flexible Earn', 'Any wallet. Start earning today with zero lock-in.', 'images/2026/04/23/20260423_112026_earning_vAIw.png', '10.00000000', '5000.00000000', '1.50000000', 'percentage', 7, 'days', 'daily', 1, 1, 1, 0, 'MOST POPULAR', 1, '2026-04-23 09:43:29', '2026-04-23 11:20:26'),
(2, 5, 'Growth Stake', '30-day USDT plan with strong auto-approved returns.', 'images/2026/04/23/20260423_111948_growth_6ao0.png', '100.00000000', '10000.00000000', '3.20000000', 'percentage', 30, 'days', 'monthly', 1, 1, 2, 1, 'HIGH YIELD', 1, '2026-04-23 09:43:29', '2026-04-23 11:19:48'),
(3, 1, 'Premium BTC', 'Bitcoin staking with weekly payouts and premium yield.', NULL, '0.00100000', '1.00000000', '5.50000000', 'percentage', 90, 'days', 'weekly', 1, 0, 3, 0, 'PREMIUM PICK', 1, '2026-04-23 09:43:29', '2026-04-23 11:06:02'),
(4, 1, 'ETH Flex Stake', 'Flexible Ethereum staking with competitive 60-day returns.', NULL, '0.05000000', '10.00000000', '4.10000000', 'percentage', 60, 'days', 'weekly', 1, 1, 4, 0, '', 1, '2026-04-23 09:43:29', '2026-04-23 11:10:02');


-- ----------------------------------------------------------
-- Table: wallet_earn_rewards
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `wallet_earn_rewards`;
CREATE TABLE `wallet_earn_rewards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wallet_earn_stake_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `transaction_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(24,8) NOT NULL,
  `payout_number` int unsigned NOT NULL,
  `scheduled_at` timestamp NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'paid',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallet_earn_rewards_stake_payout_unique` (`wallet_earn_stake_id`,`payout_number`),
  KEY `wallet_earn_rewards_wallet_id_foreign` (`wallet_id`),
  KEY `wallet_earn_rewards_currency_id_foreign` (`currency_id`),
  KEY `wallet_earn_rewards_transaction_id_foreign` (`transaction_id`),
  KEY `wallet_earn_rewards_user_id_status_index` (`user_id`,`status`),
  CONSTRAINT `wallet_earn_rewards_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_earn_rewards_transaction_id_foreign` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wallet_earn_rewards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_earn_rewards_wallet_earn_stake_id_foreign` FOREIGN KEY (`wallet_earn_stake_id`) REFERENCES `wallet_earn_stakes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_earn_rewards_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: wallet_earn_stakes
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `wallet_earn_stakes`;
CREATE TABLE `wallet_earn_stakes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `wallet_earn_plan_id` bigint unsigned DEFAULT NULL,
  `wallet_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned NOT NULL,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `plan_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `principal_amount` decimal(24,8) NOT NULL,
  `profit_rate` decimal(24,8) NOT NULL,
  `profit_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `duration_value` int unsigned NOT NULL,
  `duration_unit` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payout_frequency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `return_principal` tinyint(1) NOT NULL DEFAULT '1',
  `expected_profit` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `paid_profit` decimal(24,8) NOT NULL DEFAULT '0.00000000',
  `total_payouts` int unsigned NOT NULL DEFAULT '1',
  `payouts_made` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `trx_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `review_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `starts_at` timestamp NULL DEFAULT NULL,
  `next_payout_at` timestamp NULL DEFAULT NULL,
  `matures_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `canceled_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wallet_earn_stakes_wallet_earn_plan_id_foreign` (`wallet_earn_plan_id`),
  KEY `wallet_earn_stakes_wallet_id_foreign` (`wallet_id`),
  KEY `wallet_earn_stakes_reviewed_by_foreign` (`reviewed_by`),
  KEY `wallet_earn_stakes_user_id_status_index` (`user_id`,`status`),
  KEY `wallet_earn_stakes_status_next_payout_at_index` (`status`,`next_payout_at`),
  KEY `wallet_earn_stakes_currency_id_status_index` (`currency_id`,`status`),
  CONSTRAINT `wallet_earn_stakes_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_earn_stakes_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wallet_earn_stakes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `wallet_earn_stakes_wallet_earn_plan_id_foreign` FOREIGN KEY (`wallet_earn_plan_id`) REFERENCES `wallet_earn_plans` (`id`) ON DELETE SET NULL,
  CONSTRAINT `wallet_earn_stakes_wallet_id_foreign` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: wallets
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `wallets`;
CREATE TABLE `wallets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `currency_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` double NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wallets_wallet_id_unique` (`uuid`),
  KEY `wallets_currency_id_foreign` (`currency_id`),
  KEY `wallets_user_id_foreign` (`user_id`),
  CONSTRAINT `wallets_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`),
  CONSTRAINT `wallets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: withdraw_accounts
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `withdraw_accounts`;
CREATE TABLE `withdraw_accounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `withdraw_method_id` bigint unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `credentials` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `withdraw_accounts_user_id_foreign` (`user_id`),
  KEY `withdraw_accounts_withdraw_method_id_foreign` (`withdraw_method_id`),
  CONSTRAINT `withdraw_accounts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `withdraw_accounts_withdraw_method_id_foreign` FOREIGN KEY (`withdraw_method_id`) REFERENCES `withdraw_methods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ↪ data stripped (tenant / transient table)


-- ----------------------------------------------------------
-- Table: withdraw_methods
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `withdraw_methods`;
CREATE TABLE `withdraw_methods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_gateway_id` int DEFAULT NULL COMMENT 'Payment gateway id',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icon for the withdraw method',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Name of the withdraw method',
  `type` enum('auto','manual') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'auto = automatic, manual = manual',
  `method_code` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Unique code for the withdraw method',
  `currency` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Currency of the withdrawal',
  `currency_symbol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Currency symbol',
  `min_withdraw` double NOT NULL COMMENT 'Minimum withdrawal limit',
  `max_withdraw` double NOT NULL COMMENT 'Maximum withdrawal limit',
  `conversion_rate_live` tinyint(1) DEFAULT NULL,
  `conversion_rate` double DEFAULT NULL COMMENT 'Exchange rate',
  `charge_type` enum('fixed','percent') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'fixed = fixed charge, percent = percent charge',
  `charge` double NOT NULL COMMENT 'Fee charged for withdrawals',
  `user_charge` double DEFAULT NULL COMMENT 'Charge amount for regular users',
  `user_charge_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent' COMMENT 'Charge type for regular users: fixed or percent (cast to FixPctType enum)',
  `merchant_charge` double DEFAULT NULL COMMENT 'Charge amount for merchant users',
  `merchant_charge_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percent' COMMENT 'Charge type for merchant users: fixed or percent (cast to FixPctType enum)',
  `process_time_value` int DEFAULT '0' COMMENT 'Processing time value',
  `process_time_unit` enum('minute','hour','day') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'minute' COMMENT 'Processing time unit',
  `fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT 'Additional fields required for the withdrawal method',
  `status` tinyint NOT NULL DEFAULT '1' COMMENT '1 = active, 0 = inactive',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_merchant_charges` (`user_charge`,`merchant_charge`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `withdraw_methods` (`id`, `payment_gateway_id`, `logo`, `name`, `type`, `method_code`, `currency`, `currency_symbol`, `min_withdraw`, `max_withdraw`, `conversion_rate_live`, `conversion_rate`, `charge_type`, `charge`, `user_charge`, `user_charge_type`, `merchant_charge`, `merchant_charge_type`, `process_time_value`, `process_time_unit`, `fields`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'images/2025-03-07_23-20-28_paypal_YLzE.png', 'paypal', 'auto', 'paypal-usd', 'USD', '$', 10, 1000, 0, 1, 'percent', 10, 10, 'percent', 5, 'fixed', NULL, NULL, '[{\"name\":\"email\",\"type\":\"text\",\"validation\":\"required\"}]', 1, '2024-12-29 12:28:15', '2025-07-20 05:32:14'),
(2, NULL, 'images/2025/05/19/20250519_080818_bank_transfer_7K9J.png', 'Bank Transfer', 'manual', 'swift-usd', 'USD', '$', 100, 50000, NULL, 1, 'fixed', 25, NULL, 'percent', NULL, 'percent', 5, 'day', '{\"3\":{\"name\":\"Account Holder Name\",\"type\":\"text\",\"validation\":\"required\"},\"4\":{\"name\":\"Bank Name\",\"type\":\"text\",\"validation\":\"required\"},\"5\":{\"name\":\"SWIFT\\/BIC Code\",\"type\":\"text\",\"validation\":\"required\"},\"6\":{\"name\":\"IBAN\",\"type\":\"text\",\"validation\":\"nullable\"}}', 1, '2025-05-19 08:08:18', '2025-05-19 08:08:18'),
(8, 31, 'general/static/gateway/bitnob.png', 'Bitnob Payout', 'auto', 'bitnob-usd', 'USD', '$', 10, 100000, 0, 1, 'percent', 1, 1, 'percent', 0.5, 'percent', 30, 'minute', '[{\"name\":\"destination_type\",\"type\":\"select\",\"validation\":\"required\",\"label\":\"Destination\",\"options\":{\"bank\":\"Bank Account\",\"mobile_money\":\"Mobile Money\"}},{\"name\":\"country\",\"type\":\"text\",\"validation\":\"required\",\"label\":\"Country (ISO-2)\"},{\"name\":\"bank_code\",\"type\":\"text\",\"validation\":\"required\",\"label\":\"Bank \\/ Mobile-money code\"},{\"name\":\"account_number\",\"type\":\"text\",\"validation\":\"required\",\"label\":\"Account number\"},{\"name\":\"account_name\",\"type\":\"text\",\"validation\":\"required\",\"label\":\"Account holder name\"}]', 1, '2026-04-28 14:33:22', '2026-04-30 18:46:14');


-- ----------------------------------------------------------
-- Table: withdraw_schedules
-- ----------------------------------------------------------

DROP TABLE IF EXISTS `withdraw_schedules`;
CREATE TABLE `withdraw_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `day` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `withdraw_schedules` (`id`, `day`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Sunday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20'),
(2, 'Monday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20'),
(3, 'Tuesday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20'),
(4, 'Wednesday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20'),
(5, 'Thursday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20'),
(6, 'Friday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20'),
(7, 'Saturday', 1, '2024-12-28 04:30:20', '2024-12-28 04:30:20');

SET FOREIGN_KEY_CHECKS = 1;
COMMIT;
