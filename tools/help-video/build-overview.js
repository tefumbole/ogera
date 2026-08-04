/**
 * Build one overview walkthrough video with macOS TTS voiceover.
 *
 * 1) Generate narration audio via `say`
 * 2) Record a live browser tour (Playwright/Chrome) — or fall back to
 *    a timed slideshow of the Help screenshots
 * 3) Mux audio + video with ffmpeg into public/help/videos/ogera-overview.mp4
 */
const fs = require('fs');
const path = require('path');
const { execSync, spawnSync } = require('child_process');

const ROOT = path.resolve(__dirname, '../..');
const OUT_DIR = path.join(ROOT, 'laravel-app/public/help/videos');
const SHOTS = path.join(ROOT, 'laravel-app/public/help/screenshots');
const WORK = path.join(__dirname, '.work');
const NARRATION = path.join(__dirname, 'narration.txt');
const VOICE = process.env.OGERA_HELP_VOICE || 'Reed (English (US))';
const BASE = process.env.OGERA_HELP_BASE || 'https://ogeragency.com';
const USER = process.env.OGERA_HELP_USER || 'admin';
const PASS = process.env.OGERA_HELP_PASS || 'system';
const SSH_KEY = process.env.OGERA_SSH_KEY || path.join(process.env.HOME, '.ssh/indatwa_deploy');
const CHROME =
  process.env.CHROME_PATH ||
  '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';

fs.mkdirSync(OUT_DIR, { recursive: true });
fs.mkdirSync(WORK, { recursive: true });

function sh(cmd, opts = {}) {
  console.log('>', cmd);
  return execSync(cmd, { stdio: 'inherit', ...opts });
}

function shOut(cmd) {
  return execSync(cmd, { encoding: 'utf8' }).trim();
}

function generateVoiceover() {
  const aiff = path.join(WORK, 'narration.aiff');
  const m4a = path.join(WORK, 'narration.m4a');
  const text = fs.readFileSync(NARRATION, 'utf8').replace(/\s+/g, ' ').trim();
  // Write a clean text file for say (avoids shell quoting issues).
  const sayFile = path.join(WORK, 'narration-say.txt');
  fs.writeFileSync(sayFile, text);

  console.log('Generating voiceover with voice:', VOICE);
  sh(`say -v ${JSON.stringify(VOICE)} -f ${JSON.stringify(sayFile)} -o ${JSON.stringify(aiff)}`);
  sh(
    `ffmpeg -y -i ${JSON.stringify(aiff)} -c:a aac -b:a 192k ${JSON.stringify(m4a)}`
  );
  const dur = parseFloat(
    shOut(
      `ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ${JSON.stringify(m4a)}`
    )
  );
  console.log('Voiceover duration:', dur.toFixed(1), 's');
  return { m4a, duration: dur };
}

function readOtp() {
  const cmd = `ssh -p 65002 -i ${JSON.stringify(SSH_KEY)} -o StrictHostKeyChecking=accept-new -o ConnectTimeout=20 u152889834@193.203.189.131 'cd ~/domains/ogeragency.com/public_html && /opt/alt/php74/usr/bin/php -r "require \\"vendor/autoload.php\\"; \\$app=require \\"bootstrap/app.php\\"; \\$app->make(Illuminate\\\\Contracts\\\\Console\\\\Kernel::class)->bootstrap(); echo DB::table(\\"users\\")->where(\\"name\\",\\"admin\\")->value(\\"otp\\");"'`;
  return shOut(cmd).replace(/\D/g, '').slice(-6);
}

async function recordLiveTour(targetSeconds) {
  // Prefer playwright if available; otherwise install it locally in this folder.
  let playwright;
  try {
    playwright = require('playwright');
  } catch (_) {
    console.log('Installing playwright…');
    sh('npm install playwright@1.49 --no-fund --no-audit', { cwd: __dirname });
    playwright = require('playwright');
  }

  const videoDir = path.join(WORK, 'pw-video');
  fs.rmSync(videoDir, { recursive: true, force: true });
  fs.mkdirSync(videoDir, { recursive: true });

  const { chromium } = playwright;
  const browser = await chromium.launch({
    channel: 'chrome',
    headless: true,
    args: ['--window-size=1280,720'],
  });
  const context = await browser.newContext({
    viewport: { width: 1280, height: 720 },
    recordVideo: { dir: videoDir, size: { width: 1280, height: 720 } },
  });
  const page = await context.newPage();
  const sleep = (ms) => page.waitForTimeout(ms);

  async function go(urlPath, waitMs = 2200) {
    await page.goto(BASE + urlPath, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await sleep(waitMs);
    await page.evaluate(() => {
      const l = document.getElementById('loader');
      if (l) l.style.display = 'none';
    }).catch(() => {});
  }

  // Login
  await go('/login', 1500);
  await page.fill('input[name="identifier"]', USER);
  await page.fill('input[name="password"]', PASS);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}),
    page.click('button[type="submit"]'),
  ]);
  await sleep(1500);

  if (/otp/i.test(page.url())) {
    await sleep(800);
    const otp = readOtp();
    console.log('OTP for recording:', otp);
    await page.fill('#otp-code, input[name="otp"]', otp);
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle', timeout: 60000 }).catch(() => {}),
      page.click('button[type="submit"]'),
    ]);
    await sleep(1500);
  }

  // Tour — dwell times roughly match narration beats
  const stops = [
    ['/admin', 5000],
    ['/help', 4500],
    ['/products', 4000],
    ['/products/print_barcode', 3500],
    ['/stock-count', 3000],
    ['/pos', 5000],
    ['/sales', 3500],
    ['/bookings/create', 5000],
    ['/bookings/index', 4000],
    ['/customer', 3500],
    ['/admin/announcements/compose', 4000],
    ['/setting/general_setting', 4000],
    ['/setting/messaging', 3500],
    ['/help', 4000],
  ];

  let elapsed = 0;
  for (const [p, ms] of stops) {
    console.log('tour', p);
    await go(p, ms);
    elapsed += ms + 800;
  }

  // Pad to cover voiceover if still short
  while (elapsed / 1000 < targetSeconds - 2) {
    await sleep(2000);
    elapsed += 2000;
  }

  await context.close();
  await browser.close();

  const files = fs.readdirSync(videoDir).filter((f) => f.endsWith('.webm'));
  if (!files.length) throw new Error('Playwright produced no video');
  const webm = path.join(videoDir, files[0]);
  console.log('Recorded', webm);
  return webm;
}

function buildSlideshow(targetSeconds) {
  // Fallback: Ken-Burns-ish slideshow from existing Help screenshots.
  const order = [
    '01-login.png',
    '01b-otp.png',
    '02-dashboard.png',
    '16-sidebar.png',
    '03-products-list.png',
    '05-print-barcode.png',
    '06-stock-count.png',
    '07-pos.png',
    '08-sales-list.png',
    '09-booking-create.png',
    '10-booking-list.png',
    '11-customers.png',
    '12-announcements-compose.png',
    '14-settings-general.png',
    '15-settings-messaging.png',
    '02-dashboard.png',
  ];
  const listFile = path.join(WORK, 'slides.txt');
  const per = Math.max(2.5, targetSeconds / order.length);
  const lines = [];
  for (const name of order) {
    const p = path.join(SHOTS, name);
    if (!fs.existsSync(p)) continue;
    lines.push(`file '${p.replace(/'/g, "'\\''")}'`);
    lines.push(`duration ${per.toFixed(2)}`);
  }
  // Last image must be repeated for ffmpeg concat demuxer.
  const last = order.filter((n) => fs.existsSync(path.join(SHOTS, n))).pop();
  lines.push(`file '${path.join(SHOTS, last).replace(/'/g, "'\\''")}'`);
  fs.writeFileSync(listFile, lines.join('\n'));

  const mp4 = path.join(WORK, 'slideshow.mp4');
  sh(
    `ffmpeg -y -f concat -safe 0 -i ${JSON.stringify(listFile)} ` +
      `-vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2:color=0x033d2e,format=yuv420p" ` +
      `-r 30 -c:v libx264 -pix_fmt yuv420p ${JSON.stringify(mp4)}`
  );
  return mp4;
}

function mux(videoPath, audioPath, outPath, audioDuration) {
  // Loop/trim video to audio length, replace audio with narration.
  sh(
    `ffmpeg -y -stream_loop -1 -i ${JSON.stringify(videoPath)} -i ${JSON.stringify(audioPath)} ` +
      `-map 0:v:0 -map 1:a:0 -c:v libx264 -preset medium -crf 23 -c:a aac -b:a 192k ` +
      `-t ${audioDuration.toFixed(2)} -shortest -movflags +faststart ${JSON.stringify(outPath)}`
  );
}

(async () => {
  const { m4a, duration } = generateVoiceover();

  // Slideshow of curated Help screenshots is the default: it stays in sync with
  // the voiceover and avoids bot-checks / loaders that spoil a live capture.
  // Set OGERA_HELP_LIVE=1 to attempt a Playwright browser tour instead.
  let videoPath;
  if (process.env.OGERA_HELP_LIVE === '1') {
    try {
      videoPath = await recordLiveTour(duration);
    } catch (e) {
      console.warn('Live tour failed, using screenshot slideshow:', e.message);
      videoPath = buildSlideshow(duration);
    }
  } else {
    videoPath = buildSlideshow(duration);
  }

  const out = path.join(OUT_DIR, 'ogera-overview.mp4');
  mux(videoPath, m4a, out, duration);
  const sizeMb = (fs.statSync(out).size / (1024 * 1024)).toFixed(1);
  console.log('Wrote', out, `(${sizeMb} MB), ${duration.toFixed(1)}s`);
})().catch((e) => {
  console.error(e);
  process.exit(1);
});
