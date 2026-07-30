#!/usr/bin/env bash
# Start Ogera locally: MySQL (Homebrew or Docker) + API + Vite frontend
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

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

# Prefer the Homebrew MySQL on 3306 — that is where the seeded Ogera data lives.
# Docker on 3307 is only a fallback, and starts out empty.
if mysql_ready 3306; then
  echo "Using Homebrew MySQL on port 3306."
  ensure_api_env_port 3306
elif mysql_ready 3307; then
  echo "Using MySQL on port 3307."
  ensure_api_env_port 3307
elif command -v docker >/dev/null 2>&1; then
  echo "Homebrew MySQL unavailable — starting Docker MySQL on port 3307..."
  docker compose up -d
  for i in {1..30}; do
    if docker compose exec -T mysql mysqladmin ping -h localhost -u root -pogera_local --silent 2>/dev/null; then
      ensure_api_env_port 3307
      break
    fi
    sleep 1
  done
else
  echo ""
  echo "MySQL is not running. Choose one option:"
  echo ""
  echo "  A) Homebrew (recommended on Mac without Docker):"
  echo "     brew services start mysql"
  echo "     bash tools/setup-ogera-db.sh"
  echo ""
  echo "  B) Install Docker Desktop, then run: npm run dev:local"
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
