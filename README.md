# Ogera

Web application for **Ogera Rwanda Events**.

Forked from the Beyond Enterprise codebase, but fully independent of it.

## Isolation rule

Ogera must never read from or write to AlphaBridge or BeyondTechWorld — not their
databases, servers, PM2 processes, or nginx sites. All deploy tooling for those two
sites has been removed from this repo on purpose. Do not add it back.

| Belongs to Ogera | Never touch from here |
|---|---|
| `ogera_laravel`, `ogera` databases | `beyondtech_laravel`, `alphabridge` databases |
| MySQL user `ogera` | MySQL users `beyond`, `abt` |
| `github.com/tefumbole/ogera` | `github.com/tefumbole/BeyondTechWorld` |

## Local development

```bash
brew services start mysql
bash tools/setup-ogera-db.sh   # one time — creates the Ogera databases
npm run dev:local
```

Frontend runs on http://localhost:3000, API on http://localhost:3003.

## Local databases

Hosted by the Homebrew MySQL on this machine at `127.0.0.1:3306`.

| Purpose | Database |
|---------|----------|
| Laravel app (`laravel-app`) | `ogera_laravel` |
| Node API (`apps/api`) | `ogera` |

Credentials live in `laravel-app/.env` and `apps/api/.env`, both of which are gitignored.
The `ogera` MySQL user is granted privileges on those two databases only.

## Backups

```bash
npm run local:backup   # dumps the Ogera database + uploads into backups/
```

## Deploy

Ogera has no server yet. Add deploy tooling only when Ogera gets its own host, and
scope it to that host alone.

## Branding

Override via `.env`: `VITE_COMPANY_NAME`, `VITE_LOGO_URL`, `VITE_HERO_IMAGE_URL`, `VITE_ADMIN_PHONE_NUMBER`.
