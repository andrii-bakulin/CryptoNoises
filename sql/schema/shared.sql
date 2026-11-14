-- CryptoNoises: Shared reference database (dbShared, kSECRET_MYSQL_HOST_SHARED)
-- Use any database name and set it in config/consts-secrets.php.
-- Apply to an empty database: mysql <db_name> < sql/schema/shared.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;   -- tables are in alphabetical order, foreign keys are checked at the end

CREATE TABLE IF NOT EXISTS `market_urls` (
  `market_url_id` varchar(32) NOT NULL DEFAULT '',
  `button_caption` varchar(32) NOT NULL DEFAULT '',
  `internal_url_suffix` varchar(255) NOT NULL DEFAULT '',
  `external_url_pattern` varchar(255) NOT NULL DEFAULT '',
  `notice` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`market_url_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `marketplaces` (
  `marketplace_address` varchar(64) NOT NULL DEFAULT '',
  `collection_address` varchar(64) NOT NULL,
  `create_type` enum('manual','auto') NOT NULL DEFAULT 'manual',
  `marketplace_name` varchar(255) DEFAULT NULL,
  `token_market_url_id` varchar(32) DEFAULT NULL,
  `token_extra1_market_url_id` varchar(32) DEFAULT NULL,
  `token_extra2_market_url_id` varchar(32) DEFAULT NULL,
  `token_extra3_market_url_id` varchar(32) DEFAULT NULL,
  `token_extra4_market_url_id` varchar(32) DEFAULT NULL,
  `user_market_url_id` varchar(32) DEFAULT NULL,
  `collection_market_url_id` varchar(32) DEFAULT NULL,
  `hits` bigint unsigned DEFAULT 0,
  `notice` varchar(255) NOT NULL DEFAULT '',
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`marketplace_address`,`collection_address`),
  KEY `token_markeplace_url_id` (`token_market_url_id`),
  KEY `user_markeplace_url_id` (`user_market_url_id`),
  KEY `token_extra1_market_url_id` (`token_extra1_market_url_id`),
  KEY `token_extra2_market_url_id` (`token_extra2_market_url_id`),
  KEY `token_extra3_market_url_id` (`token_extra3_market_url_id`),
  KEY `token_extra4_market_url_id` (`token_extra4_market_url_id`),
  KEY `collection_market_url_id` (`collection_market_url_id`),
  CONSTRAINT `marketplaces_ibfk_1` FOREIGN KEY (`token_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE,
  CONSTRAINT `marketplaces_ibfk_2` FOREIGN KEY (`user_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE,
  CONSTRAINT `marketplaces_ibfk_3` FOREIGN KEY (`token_extra1_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE,
  CONSTRAINT `marketplaces_ibfk_4` FOREIGN KEY (`token_extra2_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE,
  CONSTRAINT `marketplaces_ibfk_5` FOREIGN KEY (`token_extra3_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE,
  CONSTRAINT `marketplaces_ibfk_6` FOREIGN KEY (`token_extra4_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE,
  CONSTRAINT `marketplaces_ibfk_7` FOREIGN KEY (`collection_market_url_id`) REFERENCES `market_urls` (`market_url_id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
