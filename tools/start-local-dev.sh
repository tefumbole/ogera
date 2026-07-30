#!/usr/bin/env bash
# Start Ogera locally: MySQL (Homebrew or Docker) + API + Vite frontend
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

bash tools/guard-isolation.sh || exit 1
echo ""

if [ ! -f .env ]; then
  cp .env.local.example .env
  echo "Created .env from .env.local.example"
fi

if [ ! -f apps/api/.env ]; then
  cp apps/api/.env.local.example apps/api/.env
  echo "Created apps/api/.env from apps/api/.env.local.example"
fi

mysql_ready() {
  local port="$1"
  mysql -u ogera -pogera_local -h 127.0.0.1 -P "$port" -e "SELECT 1" >/dev/null 2>&1
}

ensure_api_env_port() {
  local port="$1"
  if grep -q '^DB_PORT=' apps/api/.env; then
    sed -i '' "s/^DB_PORT=.*/DB_PORT=${port}/" apps/api/.env
  else
    echo "DB_PORT=${port}" >> apps/api/.env
  fi
}

echo "Checking MySQL..."

# Prefer Ogera's own container on 3307. It is a separate MySQL *server* that contains
# nothing but ogera and ogera_laravel, so no other project is even reachable from it.
# The Homebrew server on 3306 is shared with other projects; Ogera's user has no grants
# there, but it is still a shared instance, so it is only a fallback.
if mysql_ready 3307; then
  echo "Using Ogera's own MySQL container on port 3307 (fully isolated)."
  ensure_api_env_port 3307
elif command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
  echo "Starting Ogera's MySQL container on port 3307..."
  docker compose up -d mysql
  for _ in $(seq 1 60); do
    if docker compose exec -T mysql mysqladmin ping -h localhost -u root -pogera_local --silent 2>/dev/null; then
      break
    fi
    sleep 2
  done
  if mysql_ready 3307; then
    ensure_api_env_port 3307
    echo "Container ready on 3307."
  else
    echo "Container did not become ready in time. Check: npm run stack:logs"
    exit 1
  fi
elif mysql_ready 3306; then
  echo "Ogera's container is unavailable — falling back to Homebrew MySQL on 3306."
  echo "Note: 3306 is shared with other projects. Ogera's user has access only to"
  echo "      'ogera' and 'ogera_laravel', but for full isolation start Docker and run:"
  echo "        npm run stack:migrate-data"
  ensure_api_env_port 3306
else
  echo ""
  echo "MySQL is not running. Choose one option:"
  echo ""
  echo "  A) Ogera's own container (recommended — fully isolated):"
  echo "     start Docker Desktop, then: npm run stack:up"
  echo ""
  echo "  B) Homebrew MySQL (shared instance):"
  echo "     brew services start mysql"
  echo "     bash tools/setup-ogera-db.sh"
  echo ""
  exit 1
fi

if ! mysql_ready "$(grep '^DB_PORT=' apps/api/.env | cut -d= -f2)"; then
  echo "ERROR: Cannot connect to MySQL. Check apps/api/.env DB_HOST/DB_PORT/DB_USER/DB_PASSWORD."
  exit 1
fi

echo "Running database migration..."
npm run db:migrate

echo ""
echo "============================================"
echo "  Ogera local dev"
echo "  Frontend: http://localhost:3000"
echo "  API:      http://localhost:3003"
echo "  OTP:      skipped locally (VITE_DEV_SKIP_OTP=true)"
echo "============================================"
echo ""

npm run dev:api &
API_PID=$!
npm run dev &
VITE_PID=$!

cleanup() {
  kill "$API_PID" "$VITE_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

wait
