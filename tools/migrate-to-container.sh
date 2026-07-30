#!/usr/bin/env bash
# Move Ogera's local data off the shared Homebrew MySQL and into Ogera's own container.
#
# Why: the Homebrew server on 3306 also hosts other projects' databases. Ogera's user has
# no grants on them, but sharing a server is still a shared blast radius. The container on
# 3307 is a separate MySQL instance that only ever contains ogera and ogera_laravel.
#
# The dump runs as the 'ogera' user, which by design can only read those two schemas —
# it is not capable of reading another project's data even if asked to.
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

bash tools/guard-isolation.sh >/dev/null || {
  echo "Isolation check failed — aborting. Run: npm run guard" >&2
  exit 1
}

SRC_HOST=127.0.0.1
SRC_PORT=3306
DST_PORT=3307
DB_USER=ogera
DB_PASS=ogera_local
STAMP="$(date +%Y%m%d-%H%M%S)"
OUT="backups/container-migration-${STAMP}"

mkdir -p "$OUT"

echo "==> Checking the Homebrew MySQL on ${SRC_PORT}..."
if ! mysql -u "$DB_USER" -p"$DB_PASS" -h "$SRC_HOST" -P "$SRC_PORT" -e "SELECT 1" >/dev/null 2>&1; then
  echo "    Not reachable. Nothing to migrate — the container will start empty."
  echo "    (Start it with: npm run stack:up)"
  exit 0
fi

for db in ogera ogera_laravel; do
  echo "==> Dumping ${db}..."
  mysqldump -u "$DB_USER" -p"$DB_PASS" -h "$SRC_HOST" -P "$SRC_PORT" \
    --single-transaction --routines --triggers --no-tablespaces \
    "$db" > "${OUT}/${db}.sql"
  echo "    $(wc -l < "${OUT}/${db}.sql") lines → ${OUT}/${db}.sql"
done

echo "==> Starting the Ogera container stack..."
docker compose up -d mysql

echo "==> Waiting for ogera-mysql to accept connections..."
for _ in $(seq 1 60); do
  if docker compose exec -T mysql mysqladmin ping -h localhost -u root -pogera_local --silent >/dev/null 2>&1; then
    break
  fi
  sleep 2
done

for db in ogera ogera_laravel; do
  echo "==> Loading ${db} into the container..."
  docker compose exec -T mysql mysql -u root -pogera_local \
    -e "CREATE DATABASE IF NOT EXISTS \`${db}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
  docker compose exec -T mysql mysql -u root -pogera_local "$db" < "${OUT}/${db}.sql"
done

echo "==> Pointing Ogera's env files at the container (port ${DST_PORT})..."
set_key() {
  local file="$1" key="$2" value="$3"
  [ -f "$file" ] || return 0
  cp "$file" "${file}.bak-${STAMP}"
  if grep -qE "^[[:space:]]*${key}=" "$file"; then
    /usr/bin/sed -i '' -E "s|^[[:space:]]*${key}=.*|${key}=${value}|" "$file"
  else
    printf '%s=%s\n' "$key" "$value" >> "$file"
  fi
  echo "    ${file}: ${key}=${value}"
}

set_key "apps/api/.env"    DB_PORT "$DST_PORT"
set_key "laravel-app/.env" DB_PORT "$DST_PORT"

echo ""
echo "Done. Ogera now runs on its own MySQL instance."
echo "  container   ogera-mysql on 127.0.0.1:${DST_PORT}"
echo "  databases   ogera, ogera_laravel (nothing else exists on this server)"
echo "  dumps kept  ${OUT}/"
echo "  env backups *.bak-${STAMP}"
echo ""
echo "The Homebrew MySQL on ${SRC_PORT} was only read from, never modified."
