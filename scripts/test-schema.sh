#!/usr/bin/env bash
# Structure-only dump of the local `luvo` DB for the PHPUnit database
# `luvo_test`. The migrations don't reproduce it (no ON DELETE CASCADE,
# coords precision), see .rewrite/08-test-plan.md (F9). Reads only.
set -euo pipefail
cd "$(dirname "$0")/.."

DB_HOST=${DB_HOST:-$(grep -E '^DB_HOST=' .env | cut -d= -f2-)}
DB_PORT=${DB_PORT:-$(grep -E '^DB_PORT=' .env | cut -d= -f2-)}
DB_USERNAME=${DB_USERNAME:-$(grep -E '^DB_USERNAME=' .env | cut -d= -f2-)}
DB_PASSWORD=${DB_PASSWORD:-$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2-)}

MYSQL_PWD="$DB_PASSWORD" mysqldump -h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME" \
  --no-data --skip-comments --skip-dump-date --compact luvo \
  | sed -E 's/ AUTO_INCREMENT=[0-9]+//' > tests/fixtures/schema.sql

echo "tests/fixtures/schema.sql: $(grep -c 'CREATE TABLE' tests/fixtures/schema.sql) tables"
