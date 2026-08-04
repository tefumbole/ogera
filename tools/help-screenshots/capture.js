/**
 * Capture live ogeragency.com screenshots for the in-app User Guide.
 *
 * Usage:
 *   node capture.js
 */
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer-core');

const BASE = process.env.OGERA_HELP_BASE || 'https://ogeragency.com';
const USER = process.env.OGERA_HELP_USER || 'admin';
const PASS = process.env.OGERA_HELP_PASS || 'system';
const CHROME =
  process.env.CHROME_PATH ||
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

const OUT = path.resolve(
  __dirname,
  '../../laravel-app/public/help/screenshots'
);

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

const shots = [
  { file: '01-login.png', path: '/login', wait: 'input[name="identifier"], input[name="name"], input[name="email"]', beforeLogin: true },
  { file: '02-dashboard.png', path: '/admin', wait: '.side-navbar, .content-inner' },
  { file: '03-products-list.png', path: '/products', wait: 'table, .dataTables_wrapper, .container-fluid' },
  { file: '04-products-create.png', path: '/products/create', wait: 'form, .container-fluid' },
  { file: '05-print-barcode.png', path: '/products/print_barcode', wait: '.container-fluid' },
  { file: '06-stock-count.png', path: '/stock-count', wait: '.container-fluid' },
  { file: '07-pos.png', path: '/pos', wait: '.container-fluid, #lims_productcodeSearch' },
  { file: '08-sales-list.png', path: '/sales', wait: '.container-fluid' },
  { file: '09-booking-create.png', path: '/bookings/create', wait: '.container-fluid' },
  { file: '10-booking-list.png', path: '/bookings/index', wait: '.container-fluid' },
  { file: '11-customers.png', path: '/customer', wait: '.container-fluid' },
  { file: '12-announcements-compose.png', path: '/admin/announcements/compose', wait: '.container-fluid' },
  { file: '13-announcements-list.png', path: '/admin/announcements/list', wait: '.container-fluid' },
  { file: '14-settings-general.png', path: '/setting/general_setting', wait: '.container-fluid' },
  { file: '15-settings-messaging.png', path: '/setting/messaging', wait: '.container-fluid' },
  { file: '16-sidebar.png', path: '/admin', wait: '.side-navbar' },
];

async function dismissOverlays(page) {
  for (const sel of ['.swal2-confirm', '.close[data-dismiss="alert"]', 'button[data-dismiss="modal"]', '.iziToast-close']) {
    try {
      const el = await page.$(sel);
      if (el) await el.click({ delay: 20 }).catch(() => {});
    } catch (_) {}
  }
}

async function fetchOtpFromDb() {
  // Optional: OGERA_HELP_OTP=123456  — or read from DB via SSH helper script.
  if (process.env.OGERA_HELP_OTP) return String(process.env.OGERA_HELP_OTP).trim();
  return null;
}

async function completeOtp(page) {
  if (!/\/otp\//.test(page.url())) return;

  let otp = await fetchOtpFromDb();
  if (!otp) {
    // Last resort: ask production DB over the capture helper (set by shell).
    throw new Error('Landed on OTP screen but OGERA_HELP_OTP is not set');
  }

  // Pad / trim to 6 digits.
  otp = otp.replace(/\D/g, '').padStart(6, '0').slice(-6);
  console.log('  submitting OTP…');

  // Prefer discrete digit inputs, else a single field.
  const digitInputs = await page.$$('input[maxlength="1"], .otp-input input, input.otp-digit');
  if (digitInputs.length >= 6) {
    for (let i = 0; i < 6; i++) {
      await digitInputs[i].click({ clickCount: 3 });
      await digitInputs[i].type(otp[i], { delay: 30 });
    }
  } else {
    const single =
      (await page.$('input[name="otp"]')) ||
      (await page.$('#otp')) ||
      (await page.$('input[type="tel"]')) ||
      (await page.$('input[type="text"]'));
    if (!single) throw new Error('No OTP input found');
    await single.click({ clickCount: 3 });
    await single.type(otp, { delay: 40 });
  }

  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }).catch(() => {}),
    page.click('button[type="submit"], .btn-primary, button'),
  ]);
  await sleep(1200);
  console.log('  after OTP →', page.url());
}

async function login(page) {
  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
  await page.waitForSelector('input[name="identifier"], input[name="name"], input[name="email"]', {
    timeout: 30000,
  });

  const userSel = (await page.$('input[name="identifier"]'))
    ? 'input[name="identifier"]'
    : (await page.$('input[name="name"]'))
      ? 'input[name="name"]'
      : 'input[name="email"]';

  await page.click(userSel, { clickCount: 3 });
  await page.type(userSel, USER, { delay: 20 });
  await page.click('input[name="password"]', { clickCount: 3 });
  await page.type('input[name="password"]', PASS, { delay: 20 });

  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }).catch(() => {}),
    page.click('button[type="submit"]'),
  ]);

  await sleep(1500);
  const url = page.url();
  if (/\/login/.test(url)) {
    throw new Error('Login failed — still on /login');
  }
  console.log('  logged in →', url);
  await completeOtp(page);
}

async function shot(page, item) {
  const dest = path.join(OUT, item.file);
  console.log('→', item.file, item.path);
  try {
    await page.goto(`${BASE}${item.path}`, {
      waitUntil: 'domcontentloaded',
      timeout: 60000,
    });
    if (item.wait) {
      await page.waitForSelector(item.wait, { timeout: 25000 }).catch(() => {});
    }
    await sleep(1200);
    await dismissOverlays(page);
    await sleep(400);

    await page.evaluate(() => {
      const loader = document.getElementById('loader');
      if (loader) loader.style.display = 'none';
      document.body.style.overflow = 'auto';
    });

    // Auth pages may bounce unauthenticated hits to /login — skip those shots.
    if (!item.beforeLogin && /\/login/.test(page.url())) {
      console.error('  FAIL redirected to login');
      return;
    }

    await page.screenshot({ path: dest, fullPage: false, type: 'png' });
    console.log('  ok', Math.round(fs.statSync(dest).size / 1024) + 'KB');
  } catch (e) {
    console.error('  FAIL', e.message);
  }
}

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  console.log('Capturing from', BASE, 'as', USER);
  console.log('Output →', OUT);

  const browser = await puppeteer.launch({
    executablePath: CHROME,
    headless: true,
    defaultViewport: { width: 1440, height: 900, deviceScaleFactor: 1 },
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1440,900'],
  });

  const page = await browser.newPage();
  page.setDefaultTimeout(45000);

  const loginShot = shots.find((s) => s.beforeLogin);
  if (loginShot) await shot(page, loginShot);

  await login(page);

  for (const item of shots.filter((s) => !s.beforeLogin)) {
    await shot(page, item);
  }

  await browser.close();
  console.log('Done.');
  const files = fs.readdirSync(OUT).filter((f) => f.endsWith('.png'));
  console.log('PNG count:', files.length);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
