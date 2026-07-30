#!/usr/bin/env bash
# Ogera isolation guard.
#
# Ogera was forked from the codebase behind AlphaBridge and BeyondTechWorld, which are
# separate live systems. This script is the tripwire: it refuses to let Ogera start,
# migrate, build, or push if any configuration has drifted toward those systems.
#
# Run directly:  bash tools/guard-isolation.sh
# It also runs automatically before dev / build / db / push via npm pre-hooks and git hooks.
set -uo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

FAILED=0

fail() {
  printf '  BLOCKED  %s\n' "$1" >&2
  FAILED=1
}

ok() {
  printf '  ok       %s\n' "$1"
}

# Databases, MySQL users, filesystem roots, process names and remotes that belong to the
# other projects. Ogera must never reference any of them.
FORBIDDEN_DATABASES='beyondtech_laravel|alphabridge|beyondtechworld'
FORBIDDEN_DB_USERS='^(beyond|abt)$'
FORBIDDEN_PATHS='/var/www/beyondtechworld|/var/www/alphabridge'
FORBIDDEN_PROCESSES='beyondtechworld-api|alphabridge-api'
FORBIDDEN_REMOTE='BeyondTechWorld'

ALLOWED_DB_HOSTS='127.0.0.1|localhost|::1|mysql|ogera-mysql'
ALLOWED_DATABASES='ogera|ogera_laravel'

echo "Ogera isolation guard"
echo "---------------------"

# ---------------------------------------------------------------------------
# 1. Environment files must point only at Ogera's own databases, on a local host.
# ---------------------------------------------------------------------------
read_key() {
  # Last non-commented assignment wins, matching dotenv behaviour.
  grep -E "^[[:space:]]*${2}=" "$1" 2>/dev/null | tail -n1 | cut -d= -f2- | tr -d '"'"'"' \r'
}

check_env_file() {
  local file="$1" name_key="$2" user_key="$3" host_key="$4"
  [ -f "$file" ] || return 0

  local db user host
  db="$(read_key "$file" "$name_key")"
  user="$(read_key "$file" "$user_key")"
  host="$(read_key "$file" "$host_key")"

  if [ -n "$db" ]; then
    if echo "$db" | grep -Eiq "$FORBIDDEN_DATABASES"; then
      fail "$file → ${name_key}=${db} belongs to another project."
    elif ! echo "$db" | grep -Eq "^(${ALLOWED_DATABASES})$"; then
      fail "$file → ${name_key}=${db} is not an Ogera database (expected: ${ALLOWED_DATABASES//|/, })."
    else
      ok "$file → ${name_key}=${db}"
    fi
  fi

  if [ -n "$user" ]; then
    if echo "$user" | grep -Eiq "$FORBIDDEN_DB_USERS"; then
      fail "$file → ${user_key}=${user} is another project's MySQL user."
    else
      ok "$file → ${user_key}=${user}"
    fi
  fi

  if [ -n "$host" ] && ! echo "$host" | grep -Eq "^(${ALLOWED_DB_HOSTS})$"; then
    fail "$file → ${host_key}=${host} is not local. Ogera must not reach a remote database."
  fi
}

check_env_file "apps/api/.env"      DB_NAME     DB_USER     DB_HOST
check_env_file "laravel-app/.env"   DB_DATABASE DB_USERNAME DB_HOST

# The Laravel secondary connection must stay unset or Ogera-local.
if [ -f "laravel-app/.env" ]; then
  secondary="$(read_key "laravel-app/.env" "BEYOND_DATA_DB_DATABASE")"
  if [ -n "$secondary" ] && ! echo "$secondary" | grep -Eq "^(${ALLOWED_DATABASES})$"; then
    fail "laravel-app/.env → BEYOND_DATA_DB_DATABASE=${secondary} must be blank or an Ogera database."
  fi
fi

# ---------------------------------------------------------------------------
# 2. The git remote must be Ogera's own repository.
# ---------------------------------------------------------------------------
if command -v git >/dev/null 2>&1 && git rev-parse --git-dir >/dev/null 2>&1; then
  bad_remote=0
  while read -r remote url _; do
    [ -z "${url:-}" ] && continue
    if echo "$url" | grep -qi "$FORBIDDEN_REMOTE"; then
      fail "git remote '${remote}' points at ${url} — that is another project's repository."
      bad_remote=1
    elif ! echo "$url" | grep -qi 'tefumbole/ogera'; then
      fail "git remote '${remote}' points at ${url}, which is not github.com/tefumbole/ogera."
      bad_remote=1
    fi
  done < <(git remote -v | sort -u -k1,1)
  [ "$bad_remote" -eq 0 ] && ok "git remotes point only at github.com/tefumbole/ogera"
fi

# ---------------------------------------------------------------------------
# 3. Deploy tooling for the other projects must stay deleted.
# ---------------------------------------------------------------------------
resurrected="$(git ls-files 2>/dev/null | grep -Ei 'tools/(nginx|env)/.*(beyondtechworld|alphabridge|manukeza|newvision|okusoma)|deploy-(beyondtechworld|alphabridge)|vps-(apply-nginx|check-ports|fix-alphabridge)|push-local-to-vps|pull-production-db|migrate-hostinger|ecosystem.*\.cjs' || true)"
if [ -n "$resurrected" ]; then
  while IFS= read -r f; do
    fail "$f re-introduces deploy tooling for another project. It was deliberately deleted."
  done <<< "$resurrected"
else
  ok "no cross-project deploy tooling present"
fi

# ---------------------------------------------------------------------------
# 4. No tracked, executable script may target the other projects' hosts or paths.
# ---------------------------------------------------------------------------
scripts="$(git ls-files -- '*.sh' '*.bash' '*.zsh' 'tools/*' 2>/dev/null | grep -v 'guard-isolation.sh' || true)"
if [ -n "$scripts" ]; then
  offenders=""
  while IFS= read -r f; do
    [ -f "$f" ] || continue
    if grep -Eq "$FORBIDDEN_PATHS|$FORBIDDEN_PROCESSES" "$f" 2>/dev/null; then
      offenders="${offenders}${f}\n"
    fi
  done <<< "$scripts"
  if [ -n "$offenders" ]; then
    printf '%b' "$offenders" | while IFS= read -r f; do
      [ -n "$f" ] && fail "$f references another project's server path or process name."
    done
    FAILED=1
  else
    ok "no script references /var/www/beyondtechworld, /var/www/alphabridge, or their PM2 processes"
  fi
fi

echo "---------------------"
if [ "$FAILED" -ne 0 ]; then
  cat >&2 <<'MSG'

Isolation check FAILED. Nothing was started.

Ogera may only use:
  databases  ogera, ogera_laravel
  MySQL user ogera
  host       127.0.0.1 (Homebrew) or the ogera-mysql container on 3307
  remote     github.com/tefumbole/ogera

Fix the item(s) above and re-run. Do not bypass this check.
MSG
  exit 1
fi

echo "Isolation OK — Ogera is using only its own resources."
