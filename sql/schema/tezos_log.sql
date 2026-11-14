-- CryptoNoises: Tezos indexer log database (dbTezosLog, kSECRET_TEZOS_MYSQL_HOST_LOG)
-- Use any database name and set it in config/consts-secrets.php.
-- Apply to an empty database: mysql <db_name> < sql/schema/tezos_log.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;   -- tables are in alphabetical order, foreign keys are checked at the end

CREATE TABLE IF NOT EXISTS `ipfs_cache_0` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_1` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_2` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_3` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_4` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_5` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_6` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_7` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_8` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_9` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_a` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_b` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_c` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_d` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_e` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ipfs_cache_f` (
  `ipfs_id` varbinary(1024) NOT NULL DEFAULT '',
  `ipfs_data` blob NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`ipfs_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `log_errors` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ts_event` timestamp NOT NULL DEFAULT current_timestamp(),
  `level` enum('fatal','error','warning','notice') DEFAULT NULL,
  `scope` varchar(48) NOT NULL DEFAULT '',
  `action` varchar(48) NOT NULL DEFAULT '',
  `message` varchar(4096) NOT NULL DEFAULT '',
  `data` longblob DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ts_event` (`ts_event`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tzprofiles_cache` (
  `profile_address` varbinary(256) NOT NULL DEFAULT '',
  `content` mediumtext NOT NULL,
  `ts_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`profile_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
