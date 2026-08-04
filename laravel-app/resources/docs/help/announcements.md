# Announcements & WhatsApp

Use **Announcements** to compose WhatsApp messages, schedule them, and send reminders. Letters and other modules use the same messaging settings.

![Compose announcement](/public/help/screenshots/12-announcements-compose.png)

## Compose and send

1. Sidebar → **Announcements** → **Compose**.
2. Write the **title** and **message**.
3. Choose recipients (users / groups / numbers as the form allows).
4. Attach a file if needed (PDF, image).
5. Either:
   - **Send now**, or
   - Turn on **Schedule** and pick date & time (uses the company timezone in Settings).

## All announcements & scheduled

![Announcement list](/public/help/screenshots/13-announcements-list.png)

- **All Announcements** — history and delivery results.
- **Scheduled** — waiting for their send time.
- **Reminders** — follow-up nudges linked to an announcement.

## Templates & categories

- **Templates** — reusable message bodies.
- **Categories** — organise announcements for reporting.

## How scheduling behaves

Cron (or the host scheduler) runs every minute and sends items whose time has arrived.

- Messages due within about **6 hours** still go out after a short outage (deploy, reboot).
- Messages that are **much older** are closed without sending, so customers are not flooded with stale reminders. That is intentional.

## Reviews link

Outbound letters, invoices, and many WhatsApp messages can include a **Review us** link to the public Reviews page. Low-star reviews are held in **Site Content → Reviews** until you publish them.

## Messaging settings

WhatsApp provider keys live under **Settings → Messaging** (see the Settings chapter). Announcement module options (defaults, serial numbers) are under **Announcements → Settings**.
