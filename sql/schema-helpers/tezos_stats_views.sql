-- Tezos indexer database (dbTezos): monitoring views
-- OPTIONAL: monitoring views for manual inspection (not used by the code).
-- Apply after the main schema of the same database.

CREATE OR REPLACE VIEW `stats_queue_contracts` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_contracts`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_contracts`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_contracts__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_contracts__failed`;

CREATE OR REPLACE VIEW `stats_queue_ipfs_primary` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_primary`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_primary`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_primary__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_primary__failed`;

CREATE OR REPLACE VIEW `stats_queue_ipfs_secondary` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_secondary`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_secondary`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_secondary__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_ipfs_secondary__failed`;

CREATE OR REPLACE VIEW `stats_queue_profiles` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        `profile_type`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_profiles`
    GROUP BY 'In Queue Total', `profile_type`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `profile_type`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_profiles`
    GROUP BY 'In Queue', `profile_type`, `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        `profile_type`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_profiles__done`
    GROUP BY 'Done', `profile_type`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        `profile_type`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_profiles__failed`
    GROUP BY 'Failed', `profile_type`;

CREATE OR REPLACE VIEW `stats_queue_tokens_detected` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_detected`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_detected`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_detected__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_detected__failed`
    UNION ALL
    SELECT
        `last_notice`,
        NULL AS `NULL`,
        count(0) AS `count(*)`
    FROM `queue_tokens_detected__failed`
    GROUP BY `last_notice`
    ORDER BY `tasks_count` desc;

CREATE OR REPLACE VIEW `stats_queue_tokens_events` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_events`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_events`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_events__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_events__failed`;

CREATE OR REPLACE VIEW `stats_queue_tokens_metadata` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata__failed`;

CREATE OR REPLACE VIEW `stats_queue_tokens_metadata_merge` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata_merge`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata_merge`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata_merge__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_tokens_metadata_merge__failed`;

CREATE OR REPLACE VIEW `stats_tokens_metadata_states` AS
    SELECT
        `metadata_state`,
        count(0) AS `count(*)`
    FROM `tokens`
    GROUP BY `metadata_state`;

CREATE OR REPLACE VIEW `stats_tokens_preview_states` AS
    SELECT
        `preview_state`,
        count(0) AS `count(*)`
    FROM `tokens`
    GROUP BY `preview_state`;
