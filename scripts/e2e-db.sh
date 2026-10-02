#!/usr/bin/env bash
# (Re)creates the E2E database `luvo_e2e` as a copy of the local `luvo`
# (a production copy). Only reads from `luvo`.
set -euo pipefail
cd "$(dirname "$0")/.."

SOURCE=luvo
TARGET=${1:-luvo_e2e}
if [[ "$TARGET" == "$SOURCE" || "$TARGET" != *_e2e ]]; then
  echo "Refusing to write to '$TARGET' (must end in _e2e)." >&2
  exit 1
fi

DB_HOST=$(grep -E '^DB_HOST=' .env | cut -d= -f2-)
DB_PORT=$(grep -E '^DB_PORT=' .env | cut -d= -f2-)
DB_USERNAME=$(grep -E '^DB_USERNAME=' .env | cut -d= -f2-)
export MYSQL_PWD=$(grep -E '^DB_PASSWORD=' .env | cut -d= -f2-)
mysql_args=(-h"$DB_HOST" -P"$DB_PORT" -u"$DB_USERNAME")

mysql "${mysql_args[@]}" -e "DROP DATABASE IF EXISTS \`$TARGET\`; CREATE DATABASE \`$TARGET\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysqldump "${mysql_args[@]}" --single-transaction --routines --skip-comments "$SOURCE" | mysql "${mysql_args[@]}" "$TARGET"
# Sessions of the dev app are of no use here
mysql "${mysql_args[@]}" "$TARGET" -e "TRUNCATE sessions"

echo "$TARGET: copy of $SOURCE ($(mysql "${mysql_args[@]}" -N "$TARGET" -e 'SELECT COUNT(*) FROM team_members') team members)"
