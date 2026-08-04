# Help screenshot capture

Captures live screenshots from ogeragency.com for the in-app User Guide
(`laravel-app/resources/docs/help/` → `public/help/screenshots/`).

```bash
cd tools/help-screenshots
npm install
# Optional: OGERA_HELP_USER / OGERA_HELP_PASS
node capture.js
```

Staff login requires WhatsApp OTP. After password login, read the current
admin OTP from the production DB (SSH) and set `OGERA_HELP_OTP`, or use the
inline flow in `capture.js` that fetches it automatically when SSH keys are
available.
