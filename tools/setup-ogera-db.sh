#!/usr/bin/env bash
# Creates Ogera's own local MySQL databases, seeded from the dumps in backups/ogera-init.
# Ogera keeps separate databases so local work never touches BeyondTechWorld data.
# Requires: brew services start mysql
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=ogera
DB_PASS=ogera_local
LARAVEL_DB=ogera_laravel
API_DB=ogera
SEED_DIR=backups/ogera-init

echo "==> Creating databases '${LARAVEL_DB}' and '${API_DB}' with user '${DB_USER}'."
echo "    Enter your MySQL root password when prompted."
echo ""

mysql -h "$DB_HOST" -P "$DB_PORT" -u root -p <<SQL
CREATE DATABASE IF NOT EXISTS \`${LARAVEL_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS \`${API_DB}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${LARAVEL_DB}\`.* TO '${DB_USER}'@'localhost';
GRANT ALL PRIVILEGES ON \`${API_DB}\`.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
SQL

# Only seed a database that has no tables yet, so re-running never clobbers Ogera data.
seed_if_empty() {
  local db="$1" dump="$2" tables
  tables=$(mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" -N -B \
    -e "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='${db}'")

  if [[ "$tables" -gt 0 ]]; then
    echo "==> '${db}' already has ${tables} tables — leaving it untouched."
    return
  fi
  if [[ ! -f "$dump" ]]; then
    echo "==> No seed file at ${dump} — '${db}' left empty."
    return
  fi

  echo "==> Seeding '${db}' from ${dump}..."
  mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" -p"$DB_PASS" "$db" < "$dump"
  echo "    done."
}

seed_if_empty "$LARAVEL_DB" "${SEED_DIR}/ogera_laravel.sql"
seed_if_empty "$API_DB" "${SEED_DIR}/ogera_api.sql"

echo ""
echo "Ogera databases ready:"
echo "  Laravel  ${LARAVEL_DB}  (laravel-app/.env)"
echo "  Node API ${API_DB}      (apps/api/.env)"
echo "  User     ${DB_USER} / ${DB_PASS}  @ ${DB_HOST}:${DB_PORT}"
