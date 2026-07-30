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

### How the rule is enforced

The rule is not just documentation — three checks enforce it and none of them should be
disabled or bypassed:

| Check | What it blocks |
|---|---|
| `tools/guard-isolation.sh` | Runs automatically before every dev, build, database and deploy script. Fails if an env file names a foreign database, user or remote host, if a git remote is not Ogera's, or if cross-project deploy tooling reappears. Run it yourself with `npm run guard`. |
| `.githooks/pre-push` | Refuses to push anywhere except `github.com/tefumbole/ogera`. |
| `.githooks/pre-commit` | Refuses to commit new lines referencing the other projects' databases, MySQL users, `/var/www` paths or PM2 processes. |

Enable the hooks once per clone with `npm run hooks:install`.

## Local development

Ogera runs its own MySQL server in a container, so the other projects' databases do not
exist in it at all. This is the recommended setup:

```bash
npm run stack:up      # starts ogera-mysql (3307) and ogera-api (3003)
npm run dev           # frontend on http://localhost:3000
```

Moving data from an earlier Homebrew-based setup into the container:

```bash
npm run stack:migrate-data
```

Other stack commands: `npm run stack:down`, `npm run stack:logs`, `npm run stack:reset`
(the last one deletes the container's data volume).

`npm run dev:local` also works and picks the container automatically when it is running.

## Local databases

Preferably the `ogera-mysql` container at `127.0.0.1:3307` — a MySQL server dedicated to
Ogera. The Homebrew MySQL at `127.0.0.1:3306` is a supported fallback, but it is shared
with other projects, so isolation there rests on user grants rather than on a separate
server.

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
