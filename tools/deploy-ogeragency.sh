#!/usr/bin/env bash
#
# Deploy the Ogera ERP (laravel-app) to ogeragency.com.
#
# This is the ONLY host Ogera deploys to. The account it lives on also hosts
# alpha-bridge.net and other unrelated sites, so every path below is pinned to
# ogeragency.com and the script refuses to run if that is tampered with.
#
# Usage:
#   tools/deploy-ogeragency.sh              bump version, then deploy
#   tools/deploy-ogeragency.sh --no-bump    deploy the current version
#   tools/deploy-ogeragency.sh --dry-run    show what would transfer
#
set -euo pipefail

readonly DOMAIN="ogeragency.com"
readonly SSH_HOST="${OGERA_SSH_HOST:-193.203.189.131}"
readonly SSH_USER="${OGERA_SSH_USER:-u152889834}"
readonly SSH_PORT="${OGERA_SSH_PORT:-65002}"
readonly SSH_KEY="${OGERA_SSH_KEY:-$HOME/.ssh/indatwa_deploy}"
readonly REMOTE_ROOT="domains/${DOMAIN}/public_html"

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

BUMP=1
RSYNC_EXTRA=()
for arg in "$@"; do
    case "$arg" in
        --no-bump) BUMP=0 ;;
        --dry-run) RSYNC_EXTRA+=(--dry-run) ; BUMP=0 ;;
        *) echo "Unknown option: $arg" >&2 ; exit 1 ;;
    esac
done

# --- Guardrails ------------------------------------------------------------
# Ogera must never write into another project's site, even by accident.
case "$REMOTE_ROOT" in
    *beyondtechworld*|*alphabridge*|*alpha-bridge*|*beyondcompany*)
        echo "Refusing to deploy: target '$REMOTE_ROOT' belongs to another project." >&2
        exit 1
        ;;
esac
if [[ "$REMOTE_ROOT" != "domains/${DOMAIN}/public_html" ]]; then
    echo "Refusing to deploy: remote root was altered." >&2
    exit 1
fi

if [[ -x tools/guard-isolation.sh ]]; then
    echo "==> Isolation guard"
    bash tools/guard-isolation.sh
fi

if [[ ! -f laravel-app/index.php ]]; then
    echo "laravel-app/index.php missing - wrong directory?" >&2
    exit 1
fi

# --- Version ---------------------------------------------------------------
if [[ "$BUMP" == "1" ]]; then
    echo "==> Version"
    node tools/bump-version.js
fi
VERSION="$(tr -d '[:space:]' < laravel-app/VERSION)"
echo "    deploying OGERA_ERP_V${VERSION}"

SSH_OPTS=(-p "$SSH_PORT" -i "$SSH_KEY" -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20)
remote() { ssh "${SSH_OPTS[@]}" "${SSH_USER}@${SSH_HOST}" "$@"; }

# --- Transfer --------------------------------------------------------------
# .env, uploads and storage hold live state and are never overwritten. vendor/
# is rebuilt on the host so the archive stays small.
echo "==> Sync to ${DOMAIN}"
rsync -az --delete --human-readable ${RSYNC_EXTRA[@]+"${RSYNC_EXTRA[@]}"} \
    -e "ssh -p ${SSH_PORT} -i ${SSH_KEY} -o StrictHostKeyChecking=accept-new" \
    --exclude='/vendor/' \
    --exclude='/node_modules/' \
    --exclude='/.git/' \
    --exclude='/.env' \
    --exclude='/storage/logs/*' \
    --exclude='/storage/framework/cache/data/*' \
    --exclude='/storage/framework/sessions/*' \
    --exclude='/storage/framework/views/*' \
    --exclude='/public/uploads/' \
    --exclude='/public/logo/' \
    --exclude='/bootstrap/cache/*.php' \
    --exclude='/.phpunit.result.cache' \
    laravel-app/ "${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}/"

if [[ " ${RSYNC_EXTRA[*]-} " == *--dry-run* ]]; then
    echo "Dry run complete - nothing changed."
    exit 0
fi

# --- Build on host ---------------------------------------------------------
echo "==> Install dependencies and migrate"
# The repo checkout is mode 700, which the web server cannot read, so modes are
# normalised after every transfer. PHP is pinned to 7.4 by the app's .htaccess:
# Laravel 6.20 fatals on 8.1+, and the host defaults to 8.3.
remote "
set -e
cd ~/${REMOTE_ROOT}
export COMPOSER_MEMORY_LIMIT=-1
PHP=/opt/alt/php74/usr/bin/php
[ -x \"\$PHP\" ] || PHP=php
\$PHP /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction --no-progress -q 2>/dev/null \
  || composer install --no-dev --optimize-autoloader --no-interaction --no-progress -q
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
find . -type d -not -path './storage/*' -exec chmod 755 {} +
find . -type f -not -path './storage/*' -exec chmod 644 {} +
chmod -R 775 storage bootstrap/cache
chmod 755 artisan
\$PHP artisan migrate --force
\$PHP artisan config:cache
\$PHP artisan route:cache || \$PHP artisan route:clear
\$PHP artisan view:cache
\$PHP artisan cache:clear || true
"

echo "==> Verify"
code="$(curl -s -o /dev/null -w '%{http_code}' --max-time 25 "https://${DOMAIN}/" || echo 000)"
echo "    https://${DOMAIN}/ -> HTTP ${code}"
if [[ "$code" != "200" && "$code" != "302" ]]; then
    echo "    WARNING: unexpected status. Check storage/logs/laravel.log on the host." >&2
    exit 1
fi

echo "Deployed OGERA_ERP_V${VERSION} to https://${DOMAIN}/"
