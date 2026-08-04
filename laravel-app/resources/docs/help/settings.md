# Settings Essentials

Administrators use **Settings** for company identity, timezone, messaging, and access control. Change these carefully — they affect every invoice and WhatsApp message.

![General settings](/public/help/screenshots/14-settings-general.png)

## General setting

1. Sidebar → **Settings** → **General Setting**.
2. Set **Site Title**, logo, currency, and **Timezone** (e.g. Africa/Kigali).
3. Save. Scheduled announcements, letters, and reminders use this timezone.

> After changing timezone, confirm a test schedule fires at the wall-clock time you expect.

## Messaging (WhatsApp)

![Messaging settings](/public/help/screenshots/15-settings-messaging.png)

1. Sidebar → **Settings** → **Messaging** (or WhatsApp / SMS settings, depending on your build).
2. Choose the active provider (WasenderAPI, Twilio, etc.).
3. Paste API key / session / tokens from your provider dashboard.
4. Save, then send a short test message to your own phone.

Without a working provider, POS / booking / announcement “send WhatsApp” actions will fail silently or show an error.

## Role permission

**Settings → Role Permission** controls which menus each role sees. Super Admin sees everything. Other roles only see what you tick.

Typical pattern:

| Role | Access |
|------|--------|
| Admin | Full |
| Sales | POS, sales, customers |
| Store | Products, stock, bookings |
| Accountant | Reports, payments |

## Users and signatures

Covered in **People & Customers**. From Settings you can also open **User Profile** (your own password and details).

## Backup

**Settings → Backup Database** downloads or stores a dump. Prefer regular backups before big imports or Empty Database.

## Testing Guide vs User Guide

| Guide | Audience |
|-------|----------|
| **Help** (this guide) | Everyday staff — how to use screens |
| **Settings → Testing Guide** | QA / go-live — checklists to prove the build works |

## Site Content

Staff with the Site Content permission manage the public website (gallery, leaders, reviews). That is separate from ERP sales/bookings and will get its own Help chapter later.
