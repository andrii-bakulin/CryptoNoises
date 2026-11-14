#!/usr/bin/env bash
#
# Loads all reference data of one database in a single mysql session:
# foreign key checks off -> every sql/data/<db>/*.sql -> checks on -> verify that all references exist.
#
# Usage:   sql/data/load.sh <db> <database> [mysql options...]
# Example: sql/data/load.sh shared cryptonoises_shared -u root -p
#
# <db> is a folder in sql/data/ (main, shared, tezos). Apply the schema first, load into empty tables only.

set -euo pipefail

if [ $# -lt 2 ]; then
    echo "Usage: $0 <db> <database> [mysql options...]" >&2
    exit 2
fi

data_dir="$(cd "$(dirname "$0")" && pwd)"
db_dir="$data_dir/$1"
database="$2"
shift 2

if ! ls "$db_dir"/*.sql >/dev/null 2>&1; then
    echo "No data files in $db_dir" >&2
    exit 2
fi

broken=$(
    {
        echo 'SET NAMES utf8mb4;'
        echo 'SET FOREIGN_KEY_CHECKS = 0;'
        cat "$db_dir"/*.sql
        echo 'SET FOREIGN_KEY_CHECKS = 1;'
        cat "$data_dir/check-foreign-keys.sql"
    } | mysql --batch --skip-column-names "$@" "$database"
)

if [ -n "$broken" ]; then
    echo "Data is loaded, but some foreign keys point to missing rows (constraint, rows):" >&2
    echo "$broken" >&2
    exit 1
fi

echo "Loaded $(ls "$db_dir"/*.sql | wc -l | tr -d ' ') file(s) from $db_dir into $database, all foreign keys OK"
