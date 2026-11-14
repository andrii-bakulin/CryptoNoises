-- CryptoNoises: find rows whose foreign keys point to missing rows, in the current database.
-- Prints one line per broken constraint (`table.constraint`, number of rows); prints nothing when all references exist.
-- Used by sql/data/load.sh; can also be run on its own: mysql <database> < sql/data/check-foreign-keys.sql
--
-- Needed because the data is loaded with FOREIGN_KEY_CHECKS = 0, and switching it back to 1 does not re-check rows.
-- Same rule as MySQL: a reference with any NULL column is not checked.

SET SESSION group_concat_max_len = 1000000;

SELECT GROUP_CONCAT(fk_query SEPARATOR ' UNION ALL ') INTO @fk_check
FROM (
    SELECT CONCAT(
        'SELECT ', QUOTE(CONCAT(TABLE_NAME, '.', CONSTRAINT_NAME)), ', COUNT(*) FROM `', TABLE_NAME, '` c WHERE ',
        GROUP_CONCAT(CONCAT('c.`', COLUMN_NAME, '` IS NOT NULL') ORDER BY ORDINAL_POSITION SEPARATOR ' AND '),
        ' AND NOT EXISTS (SELECT 1 FROM `', REFERENCED_TABLE_SCHEMA, '`.`', REFERENCED_TABLE_NAME, '` p WHERE ',
        GROUP_CONCAT(CONCAT('p.`', REFERENCED_COLUMN_NAME, '` = c.`', COLUMN_NAME, '`') ORDER BY ORDINAL_POSITION SEPARATOR ' AND '),
        ') HAVING COUNT(*) > 0'
    ) AS fk_query
    FROM information_schema.KEY_COLUMN_USAGE
    WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL
    GROUP BY TABLE_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME
) AS fks;

SET @fk_check = COALESCE(@fk_check, 'SELECT 1 FROM DUAL WHERE FALSE');   -- no foreign keys: nothing to check

PREPARE fk_check_stmt FROM @fk_check;
EXECUTE fk_check_stmt;
DEALLOCATE PREPARE fk_check_stmt;
