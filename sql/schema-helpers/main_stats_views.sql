-- Bots + API database (dbMain): monitoring views
-- OPTIONAL: monitoring views for manual inspection (not used by the code).
-- Apply after the main schema of the same database.

CREATE OR REPLACE VIEW `stats_fixer_tezos_transactions_analyze` AS
    SELECT
        `level`,
        `state`,
        `items_count`,
        `notice`,
        `ts_scan_started`,
        `ts_scan_finished`,
        timestampdiff(SECOND,`ts_scan_started`,`ts_scan_finished`) AS `time_spend`,
        timestampdiff(SECOND,`ts_scan_started`,`ts_scan_finished`) / `items_count` AS `seconds_per_item`,
        `items_count` / timestampdiff(SECOND,`ts_scan_started`,`ts_scan_finished`) AS `items_per_second`
    FROM `fixer_tezos_transactions_analyze`;

CREATE OR REPLACE VIEW `stats_queue_domains_expiry` AS
    SELECT
        'In Queue Total' AS `In Queue`,
        NULL AS `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_domains_expiry`
    UNION ALL
    SELECT
        'In Queue' AS `In Queue`,
        `iteration`,
        count(0) AS `tasks_count`
    FROM `queue_domains_expiry`
    GROUP BY 'In Queue', `iteration`
    UNION ALL
    SELECT
        'Done' AS `In Queue`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_domains_expiry__done`
    UNION ALL
    SELECT
        'Failed' AS `Failed`,
        NULL AS `NULL`,
        count(0) AS `tasks_count`
    FROM `queue_domains_expiry__failed`;
