#!/usr/bin/env node
/**
 * Ogera ERP version bump — OGERA_ERP_Vx.y.z
 *
 * Scheme (mirrored in .githooks/pre-commit and App\Support\AppVersion):
 *   patch runs 1–9, then rolls to the next minor   1.0.9 -> 1.1.0
 *   minor runs 0–9, then rolls to the next major   1.9.9 -> 2.0.1
 *
 * Note the major roll lands on patch 1, not 0, so a new major line starts at
 * x.0.1 the way 1.0.1 did.
 *
 * laravel-app/VERSION holds the bare semver and is the single source of truth.
 * The two JS constants are kept in step with it.
 *
 * Usage:
 *   node tools/bump-version.js          bump VERSION, then sync the JS constants
 *   node tools/bump-version.js --sync   only sync the JS constants to VERSION
 */
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const ROOT = path.resolve(__dirname, '..');

const PREFIX = 'OGERA_ERP_V';
const FALLBACK = '1.0.1';

const VERSION_FILE = path.join(ROOT, 'laravel-app/VERSION');
const JS_VERSION_FILES = [
  path.join(ROOT, 'src/constants/appVersion.js'),
  path.join(ROOT, 'apps/api/src/constants/appVersion.js'),
];

// Accepts the current prefix and the two legacy ones, so old files still parse.
const ANY_PREFIX = '(?:OGERA_ERP_V\\.?|BCL_ERP_V\\.?|ABT_ERP_V\\.)';
const APP_VERSION_RE = new RegExp(
  `export const APP_VERSION = '${ANY_PREFIX}(\\d+)\\.(\\d+)\\.(\\d+)';`
);

export function nextSemver(semver) {
  const match = String(semver).trim().match(/^(\d+)\.(\d+)\.(\d+)$/);
  if (!match) throw new Error(`Invalid version: ${semver}`);

  let [major, minor, patch] = match.slice(1).map(Number);

  patch += 1;
  if (patch >= 10) {
    patch = 0;
    minor += 1;
  }
  if (minor >= 10) {
    minor = 0;
    major += 1;
    // A new major line starts at x.0.1, matching how 1.0.1 began.
    patch = 1;
  }

  return `${major}.${minor}.${patch}`;
}

function readSemver() {
  if (fs.existsSync(VERSION_FILE)) {
    const raw = fs.readFileSync(VERSION_FILE, 'utf8').trim();
    if (/^\d+\.\d+\.\d+$/.test(raw)) return raw;
  }
  // Fall back to whatever the JS constant says, so nothing is lost.
  for (const file of JS_VERSION_FILES) {
    if (!fs.existsSync(file)) continue;
    const match = fs.readFileSync(file, 'utf8').match(APP_VERSION_RE);
    if (match) return `${match[1]}.${match[2]}.${match[3]}`;
  }
  return FALLBACK;
}

function writeSemver(semver) {
  fs.writeFileSync(VERSION_FILE, `${semver}\n`);
}

function syncJsFiles(semver) {
  const label = `${PREFIX}${semver}`;
  for (const file of JS_VERSION_FILES) {
    if (!fs.existsSync(file)) continue;
    const content = fs.readFileSync(file, 'utf8');
    const updated = content.replace(
      APP_VERSION_RE,
      `export const APP_VERSION = '${label}';`
    );
    if (updated !== content) fs.writeFileSync(file, updated);
  }
  return label;
}

function main() {
  const syncOnly = process.argv.includes('--sync');
  const current = readSemver();
  const next = syncOnly ? current : nextSemver(current);

  if (!syncOnly) writeSemver(next);
  syncJsFiles(next);

  // pre-push parses field 3 of this line, so keep the "A -> B" shape.
  console.log(`${PREFIX}${current} -> ${PREFIX}${next}`);
}

if (process.argv[1] && path.resolve(process.argv[1]) === path.resolve(__filename)) {
  main();
}
