const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const ROOT = path.resolve(__dirname, '../..');
const SHOTS = path.join(ROOT, 'laravel-app/public/help/screenshots');
const WORK = path.join(__dirname, '.work');
const OUT = path.join(ROOT, 'laravel-app/public/help/videos/ogera-overview.mp4');
const m4a = path.join(WORK, 'narration.m4a');

const duration = parseFloat(
  execSync(
    `ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ${JSON.stringify(m4a)}`,
    { encoding: 'utf8' }
  ).trim()
);

const order = [
  '01-login.png',
  '01b-otp.png',
  '02-dashboard.png',
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

const per = duration / order.length;
const list = path.join(WORK, 'slides.txt');
const lines = [];
for (const name of order) {
  const p = path.join(SHOTS, name);
  lines.push(`file '${p.replace(/'/g, "'\\''")}'`);
  lines.push(`duration ${per.toFixed(3)}`);
}
const last = path.join(SHOTS, order[order.length - 1]);
lines.push(`file '${last.replace(/'/g, "'\\''")}'`);
fs.writeFileSync(list, lines.join('\n'));

const slide = path.join(WORK, 'slideshow.mp4');
execSync(
  `ffmpeg -y -f concat -safe 0 -i ${JSON.stringify(list)} ` +
    `-vf "scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2:color=0x033d2e,format=yuv420p" ` +
    `-r 30 -c:v libx264 -pix_fmt yuv420p ${JSON.stringify(slide)}`,
  { stdio: 'inherit' }
);

execSync(
  `ffmpeg -y -stream_loop -1 -i ${JSON.stringify(slide)} -i ${JSON.stringify(m4a)} ` +
    `-map 0:v:0 -map 1:a:0 -c:v libx264 -preset medium -crf 20 -c:a aac -b:a 192k ` +
    `-t ${duration.toFixed(2)} -shortest -movflags +faststart ${JSON.stringify(OUT)}`,
  { stdio: 'inherit' }
);

console.log('OK', (fs.statSync(OUT).size / 1024 / 1024).toFixed(1) + 'MB', duration.toFixed(1) + 's');
