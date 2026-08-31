# Ogera — Manual Testing Guide

How to check that ogeragency.com is working. Written so that someone who is not
a developer can follow it.

Every step has a **Pass** line. If what you see does not match the Pass line,
write it down (see [Reporting a problem](#reporting-a-problem) at the end) and
carry on to the next step — do not stop the whole run for one failure.

- **Live site:** https://ogeragency.com
- **Test on a phone as well as a laptop.** Clients open agreement and signing
  links from WhatsApp on a handset, so those pages matter most on a small screen.

---

## Before you start

**Confirm which build you are testing.** Open https://ogeragency.com/login and
look at the bottom of the page. It shows a version like `OGERA_ERP_V1.7.5`.
Quote that number in every bug you report, otherwise nobody can tell whether
the problem was already fixed.

**Only the main site is live.** The repository also contains a React app and a
Node API. Neither is deployed. If someone asks you to test "the API" or a React
screen, it is not part of ogeragency.com.

**Three modules are switched off on purpose.** Job Board, Courses (Register
Now) and Staff Permissions are hidden from the admin menu, and their public
pages (`/apply-now`, `/register-now`, `/permissions`, `/shareholders`) correctly
return **404**. That is not a bug. Do not test them.

**Some features need WhatsApp.** Most notifications go out through WasenderAPI.
If WhatsApp is not connected, logins that need an OTP and every "send to client"
button will fail. Check **Settings → Messaging** first and confirm the API key
and session are filled in. Anything marked 📱 below depends on it.

**Timed sends run on a cron job, and it is live.** Scheduled announcements,
letters, booking reminders, task reminders and contract reminders are all
dispatched by one hPanel cron running every minute:

```
/bin/bash /home/u152889834/ogera-cron.sh
```

That script lives in the account home directory, not in the app, so deploys
cannot delete it. It runs `artisan schedule:run` with the PHP 7.4 binary — the
site is Laravel 6 and fails on the account's newer PHP — and overwrites
`/home/u152889834/ogera-cron.log` each minute with the last outcome. To check
the scheduler is alive, read that file: it should show a timestamp less than a
minute old and `exit=0`.

Because cron handles this, the page-load fallback is off
(`OGERA_SCHEDULER_AUTO=false`). Turn it back on only if the cron is ever removed.

Times follow the zone in **Settings → General Setting** (currently
`Africa/Kigali`), so a reminder set for 14:30 fires at 14:30 Kigali time.

**Deep pass for letters / announcements / tasks / quotations:** use **sections
7–10A** below. Plan ~45–90 minutes with two WhatsApp phones (client + CC).

---

## 1. Quick smoke test (about 15 minutes)

Do this after every deployment. If all twelve pass, the platform is fundamentally
healthy and you can decide whether to run the full pass below.

1. [ ] https://ogeragency.com loads, images appear, no error page.
2. [ ] The version number at the bottom of `/login` is the build you expect.
3. [ ] Every header tab opens: Home, About, Services, Events, Rentals, Gallery, Contact.
4. [ ] You can log in at `/login` with your admin username and password.
5. [ ] The dashboard loads with figures, not blank boxes.
6. [ ] Every sidebar menu expands and its first page opens without an error.
7. [ ] Product List shows products; open one.
8. [ ] POS (`/pos`) loads and you can add a product to the cart.
9. [ ] Booking List (`/bookings/index`) loads and the row **Action** button shows its full menu.
10. [ ] Customer names in Booking List are names, not phone numbers.
11. [ ] 📱 Send one WhatsApp message (an announcement to yourself) and receive it.
12. [ ] Log out and back in cleanly.

---

## 2. Public website

Visit each page and check both the content and the pictures. A missing image
shows as a broken icon or an empty gap.

| Page | URL | What to check |
|---|---|---|
| Home | `/` | Hero section, logo animation, sections all populated |
| About | `/about` | Company text, leadership photos all load |
| Services | `/services` | Every service card has text and an icon/image |
| Events | `/events` | Published events listed; click one to open its detail page |
| Rentals | `/rentals` | Equipment listed with images and prices |
| Gallery | `/gallery` | All images load, none broken |
| Contact | `/contact` | Address, phone and the contact form appear |

- [ ] Each page above passes.
- [ ] The header and footer look right on a phone; the menu opens and closes.
- [ ] **Pass:** no broken images, no empty sections, no error pages.

**Contact form.** Fill in Name, Subject and Message on `/contact` and submit.
It does not save to the database — it opens WhatsApp with the message pre-filled.

- [ ] **Pass:** WhatsApp opens with your text already written.

**Rental request** (the main public lead form). On `/rentals`, submit a request
with a name, phone, equipment, start date and end date.

- [ ] You land on a confirmation page showing a reference number.
- [ ] 📱 The phone you entered receives a WhatsApp confirmation.
- [ ] In admin, the request appears under **Rental Module → Booking Request**.
- [ ] **Pass:** all three happen, and the customer is saved under the name you typed.

---

## 3. Logging in and access control

Log in at `/login` with a **username** (not an email) and password.

- [ ] Correct credentials get you in; wrong ones show an error rather than a crash.
- [ ] 📱 If OTP is switched on, the code arrives on WhatsApp and is accepted.
- [ ] "Forgot password" sends a reset code.
- [ ] Logging out returns you to the login page and you cannot go Back into the admin.

**Roles.** The account on the **Super Admin** role sees every menu including
Settings. An account on the **Admin** role sees everything *except* **Settings**
and **Site Content** — that restriction is deliberate and is worth confirming.

- [ ] Create a test user on the **Admin** role, log in as them, and confirm
      Settings and Site Content are absent.
- [ ] In **Settings → Role Permission**, create a role with only, say, Sale
      permissions. Log in as a user with that role.
- [ ] **Pass:** they see only the Sale menu, and typing another module's URL
      directly (e.g. `/bookings/index`) is refused rather than shown.

---

## 4. Products and stock

- [ ] **Category** — add a category, edit it, delete it.
- [ ] **Add Product** — create a product with a code, price, unit and an image.
      Confirm the image shows in the product list.
- [ ] **Product List** — search, filter and open the product you just made.
- [ ] **Print Barcode** — select the product and confirm a printable sheet renders.
- [ ] **Add Adjustment** — add and then subtract quantity; check the stock figure
      changes by the right amount both times.
- [ ] **Stock Count** — start a count for a warehouse and finalise it.
- [ ] **Transfer** — move stock between two warehouses and confirm both sides change.
- [ ] **Pass:** stock numbers always move in the direction and by the amount you expect.

---

## 5. POS and sales

This is the flow from your example, in full.

1. [ ] Open **Sale → POS** (`/pos`).
2. [ ] Pick a warehouse, biller and customer.
3. [ ] Add a product by clicking it, and another by scanning or typing its code.
4. [ ] Change a quantity, apply a discount, apply tax.
5. [ ] Pay by **Cash** and complete the sale.
6. [ ] The receipt appears and can be printed.
7. [ ] The sale shows in **Sale List** with the correct total.
8. [ ] Stock for the sold product went **down** by the quantity sold.

Then, from the Sale List row **Action** menu:

- [ ] **View** opens the sale detail.
- [ ] **Generate Invoice** produces a correct PDF/print view.
- [ ] **Add Payment** records a part payment and the Due figure updates.
- [ ] **View Payment** lists what you just added.
- [ ] **Add Delivery** creates a delivery note.
- [ ] **Edit** and **Delete** behave (delete should restore stock).

Also check:

- [ ] **Add Sale** (the non-POS form) creates a sale.
- [ ] **Return → Sale** returns one item and puts the stock back.
- [ ] **Gift Card** and **Coupon** can be created and applied at POS.
- [ ] Card payments only work if Stripe/PayPal keys are set in **Settings → POS
      Settings**. If they are blank, expect card options to fail — that is
      configuration, not a bug.

---

## 6. Rental module (bookings) — the most important flow

This is the module clients touch directly, so test it end to end and **do the
client half on a phone**.

### Create and send

1. [ ] **Rental Module → Booking Create**: pick a customer, warehouse, biller and
       equipment with dates. Save.
2. [ ] The booking appears in **Booking List** with the right total and a
       **Pending** status.
3. [ ] The **Customer** column shows the customer's **name**, not their phone number.
4. [ ] Open the row **Action** menu. Every item must be visible and clickable —
       Generate Invoice, View, Edit, Clone Booking, Generate Goods Delivery Note,
       Schedule Reminder, Add Payment, View Payment, Delete. Check this on the
       **last row** of the table and on a phone, where the menu used to be cut off.
5. [ ] 📱 Send the agreement for signature. The customer receives a WhatsApp with
       a `/rental-agreement/...` link.

### The client signs (do this on a phone)

6. [ ] The agreement page opens and is readable — text is not tiny, the equipment
       table is readable without scrolling sideways, and buttons are easy to tap.
7. [ ] You must scroll through the whole agreement before it lets you submit.
8. [ ] Tick the agreement checkbox.
9. [ ] **ID card, front:** attach it. Try the camera *and* a photo from the
       gallery *and* a PDF — all three must be accepted.
10. [ ] **ID card, back:** same again. Each side shows its own preview and turns
        green when attached.
11. [ ] Replace one side and confirm the other side stays attached.
12. [ ] Try to submit with only one side attached — it must tell you which side
        is missing.
13. [ ] Draw a signature with your finger; clear it and redraw.
14. [ ] Submit.
15. [ ] You land on the client portal with an "awaiting review" message.
16. [ ] 📱 The client receives a WhatsApp confirmation and a copy of the booking.
17. [ ] Opening the same signing link again says it is already signed rather than
        letting you sign twice.

### Admin reviews

18. [ ] The booking moves to **Pending Review**, and the admin gets a WhatsApp
        review link.
19. [ ] Open the contract review screen. Both sides of the ID are visible and
        labelled Front and Back, and the signature shows.
20. [ ] Approve/countersign it.
21. [ ] 📱 The client receives the final signed PDF, a QR code, and their portal
        login details.
22. [ ] The booking now appears under **Signed Contracts**.
23. [ ] Scanning the QR opens a valid verification page.

### The rest of the module

- [ ] **Booking Request** — approve a request that came from the public form.
- [ ] **Booked Products** — shows what is currently out on hire.
- [ ] **Booking Reminder** — schedule one. 📱 (Timed sending needs cron.)
- [ ] **Goods Received** — send the delivery-note signature link, sign it as the
      client, and confirm the signed PDF comes back.
- [ ] **Booking Calendar** — bookings appear on the right dates.
- [ ] Record a **Return** and confirm stock comes back.

---

## 7. Quotations (full pass) 📱

**Prep:** two phones you control — **Client** and **CC**. Both must be customers
(or users) with WhatsApp numbers in Ogera. Confirm **Settings → Messaging** is
connected. Times are **Africa/Kigali**.

### 7.1 Create and send for approval

1. [ ] **Quotations → Add Quotation**. Add ~3–4 short line items, set status to
      **Send for client approval (WhatsApp)**.
2. [ ] Select a **CC** customer (not the same phone as the client).
3. [ ] Check the **Note** default: it should be **3 bullet points** (not 4), and
      company name should say **Ogera** (or your site title), not “Beyond Tech World”.
4. [ ] Optional: paste or drop a PDF/image onto **Attach Document** — the dashed
      paste zone appears under the file input.
5. [ ] Save / send.
6. [ ] **Pass — Client WhatsApp:** approval link message arrives; body includes
      `From: *…*` branding.
7. [ ] **Pass — CC WhatsApp:** CC receives a stakeholder “sent” text (not only the
      client). Creator phone (if set) may also get a copy.
8. [ ] Open **All Tasks / Quotations list** and confirm status is awaiting approval.

### 7.2 Client approve + already-approved link + CC PDF

1. [ ] On the **client phone**, open the approval link. On a narrow phone screen,
      scroll the notes fully — sticky Approve/Reject bar must not cover the note;
      action buttons stack.
2. [ ] Items table scrolls horizontally if needed.
3. [ ] Tick agreement, draw signature, **Approve**.
4. [ ] **Pass — Client:** receives **signed PDF** (with QR if enabled).
5. [ ] **Pass — CC / creator:** receive **signed PDF** as well (same document),
      not only a text.
6. [ ] Re-open the **same** approval URL.
7. [ ] **Pass:** page says the quotation was **already approved** (not a generic
      “expired” / Hostinger skateboard 404).
8. [ ] Download / open the PDF: ~4 short lines + notes + signatures should fit on
      **one page** with letterhead footer (no orphan footer-only page 2).

### 7.3 Reject path

1. [ ] Create another quotation, send for approval, **Reject** with a comment.
2. [ ] **Pass:** admin/CC get reject notice with the comment; reopening the link
      shows **already rejected**.

### 7.4 Speed note (ops)

If WhatsApp hops feel slow (~6s between recipients), lower **Messaging** min send
interval to **2000–3000 ms** after this test. Do not set it below 1000 ms.

---

## 8. Task Manager (full pass) 📱

**Prep:** at least **3 assignees** with phones + **1 CC** person. Prefer real
phones you can read. Admin: **Task Manager** menu.

### 8.1 Pages load

1. [ ] Open **Task Dashboard** — cards link to filtered All Tasks.
2. [ ] **All Tasks** opens at `/admin/tasks/list` (HTTP 200, not a 404).
3. [ ] **Scheduled**, **Reminders**, **My Tasks**, **Pending Acceptances**,
      **Create Task**, **Task Settings** all load without errors.

### 8.2 Create now — multi-assignee + CC

1. [ ] **Create Task**: subject e.g. `QA TASK MULTI`, priority High, deadline
      tomorrow, optional PDF.
2. [ ] **Assign To:** pick **3+** people with phones. Filter Staff / Customers works.
3. [ ] **CC:** pick **1+** other person with a phone.
4. [ ] **Send now** (not schedule). Submit.
5. [ ] **Pass — Assignees:** each gets WhatsApp assignment (spaced ~Wasender interval
      apart — not all at once failing after the first).
6. [ ] **Pass — CC:** gets `TASK CC NOTIFICATION` (not silent).
7. [ ] If one number is invalid, admin logs show skip/partial; task can retry via
      cron (`notifications_sent` stays false until all reachable phones succeed).

### 8.3 Accept / pending cancel / invalid link

1. [ ] Open **Pending Acceptances**. Cards show for unaccepted assignments.
2. [ ] **Select all** (or multi-select) → **Cancel selected**.
3. [ ] **Pass:** selected cards disappear.
4. [ ] On an assignee phone, open the old `/task-invite/...` link.
5. [ ] **Pass:** **Invalid Invite** — “invalid, expired, or was cancelled by the
      administrator.”
6. [ ] Create another task, accept via invite with signature; progress updates;
      CC gets accept / progress / complete notices as you update.

### 8.4 Schedule send

1. [ ] Create a task with **Schedule** send time = **now + 3 minutes** (Kigali).
2. [ ] Open **Scheduled** — task appears with the send-at time.
3. [ ] Wait for cron (every minute). After the time passes:
4. [ ] **Pass:** assignees + CC get WhatsApp; task **leaves** the Scheduled list.
5. [ ] Optional cancel test: schedule another for +10 min, **Select** it on
      Scheduled → **Cancel selected** → it leaves the list and **never** sends.

### 8.5 Reminders (assignee + CC)

1. [ ] Create a task with a **reminder** at **now + 3 minutes**.
2. [ ] Open **Reminders** — only **Pending** rows show (no pile of “Sent”).
3. [ ] When the time fires:
4. [ ] **Pass — Assignee:** `TASK REMINDER` WhatsApp.
5. [ ] **Pass — CC:** `TASK CC — REMINDER` WhatsApp.
6. [ ] **Pass:** that reminder **disappears** from the Reminders list after send.
7. [ ] Cancel test: add a reminder +10 min, multi-select → **Delete selected** →
      it must **not** fire.

### 8.6 Login UX (related)

1. [ ] Log out. Open `/login`.
2. [ ] **Pass:** no Sign in / Sign up **tab bar**; small **Sign up** link remains
      under the form. `?tab=signup` still opens signup.

---

## 9. Announcements (manager + schedules) 📱

Use **Announcements** (WhatsApp manager), not only the legacy list module.

### 9.1 Send now + CC

1. [ ] **Compose**: subject, body, recipients **+ CC**, optional attachment.
2. [ ] Send now.
3. [ ] **Pass:** every To and every CC with a phone gets the message (and
      attachment if set). Partial failures are reflected in status if some fail.

### 9.2 Schedule + cancel

1. [ ] Compose another announcement; set **schedule** to **now + 3 minutes**.
2. [ ] Open **Scheduled** — it is listed.
3. [ ] Cancel path: select it → **Cancel selected** → leaves list, becomes draft,
      **does not** send at the old time.
4. [ ] Schedule a fresh one for +3 minutes and let it fire.
5. [ ] **Pass:** To + CC receive it; item leaves Scheduled after send.

### 9.3 Announcement reminders

1. [ ] When composing a scheduled announcement, **Add reminder** at +2 minutes
      (before send time).
2. [ ] Open **Announcement Reminders** — pending only.
3. [ ] **Pass:** To + CC get `ANNOUNCEMENT REMINDER`; row leaves the list after send.
4. [ ] Delete-selected on a future reminder prevents it from firing.

### 9.4 Legacy announcements (optional)

1. [ ] If you still use the older **Announcements** CRUD with `date_time`, schedule
      one a few minutes ahead with To + CC.
2. [ ] **Pass:** cron `announcements:send-scheduled` delivers to To and CC.

---

## 10. Letters (workflow + scheduled send) 📱

Letters move through stages; scheduled WhatsApp/PDF uses cron
`letters:send-scheduled`.

### 10.1 Happy path

1. [ ] Create **Letter Categories** / **Templates** if needed.
2. [ ] **Create Letter** to a customer (or employee) with a **CC** recipient.
3. [ ] Walk stages: editing → approval → signature → ready to send.
4. [ ] Send via WhatsApp (or queue).
5. [ ] **Pass — Primary:** PDF (and attachments) on WhatsApp.
6. [ ] **Pass — CC:** CC PDF path runs (`sendPDFToCC`) — CC phone gets the letter
      copy.

### 10.2 Scheduled letter

1. [ ] Create/approve/sign a letter with **date_time** = **now + 3 minutes**
      (Kigali), still `is_sent = 0`.
2. [ ] Wait for cron.
3. [ ] **Pass:** primary + CC receive around that time; letter marked sent.
4. [ ] Print/download: letterhead and serial look correct.

### 10.3 Reject

1. [ ] Reject a letter in the right stage.
2. [ ] **Pass:** it appears under **Rejected Letters**.

---

## 10A. Scheduler health check (do once per test day)

1. [ ] Login footer / version shows the build you expect (e.g. `OGERA_ERP_V2.2.0`).
2. [ ] Confirm timezone: Settings / app uses **Africa/Kigali** (CAT, UTC+2).
3. [ ] On the server (ops only): `~/ogera-cron.log` updates every minute with
      `exit=0` and lists at least:
      - `letters:send-scheduled`
      - `announcements:send-scheduled`
      - `announcements:process`
      - `tasks:process`
4. [ ] **Pass:** timed tests in §7–10 fire within ~1–2 minutes of the scheduled
      Kigali time (not hours late, and not a backlog dump from days ago — grace
      window is 6 hours).

---

## 11. Contracts module

Separate from booking contracts — this is the general template-based one.

- [ ] Create a **Template** and add clauses to the **Clause Library**.
- [ ] **Create Contract** from that template with a client signatory.
- [ ] Send for signature; open the `/contracts/sign/...` link, type your name,
      consent and sign.
- [ ] Countersign as admin; the contract moves to **Signed**.
- [ ] Decline a different contract with a reason.
- [ ] **Dashboard** and **Reports** show the right counts.

---

## 12. Events and Digital Invitations

- [ ] **Create Event**, publish it, and confirm it appears on the public `/events`.
- [ ] **Event Calendar** shows it on the right date.
- [ ] **Event Workforce** — add workers; send an event contract and sign it.
- [ ] **Event Timesheets** and **Labour Payments** record and total correctly.
- [ ] **Digital Invitations** — create an invitation, send it, and use
      **Check-in** to scan a guest in.
- [ ] If Invitations shows a "misconfigured" page, its separate database
      connection is not set on the server — report it as configuration.

---

## 13. HR, payroll and timesheets

- [ ] **Department** and **Employee** — create both.
- [ ] **Attendance** — record a clock-in and clock-out.
- [ ] **Payroll** — generate a payslip; check the figures and that the payslip
      verification link works.
- [ ] **Holiday** — add one and see it in My Holiday.
- [ ] **TimeSheets → Fill Time Sheet** — submit a week as an employee.
- [ ] **TimeSheet Admin → Manage All / Report / Overtime** — approve it and check
      the totals match what was submitted.

---

## 14. People

- [ ] **Add User** — create one, set a role, log in as them, then deactivate them
      and confirm they can no longer log in.
- [ ] **Add Customer** and **Add Biller** and **Add Supplier**.
- [ ] Customer names must save exactly as typed.
- [ ] **Export / Import People** — export a CSV, re-import it, confirm no
      duplicates and no mangled names.

---

## 15. Money and assets

- [ ] **Expense Category** and **Expense List** — add an expense.
- [ ] **Purchase → Add Purchase** — receive stock from a supplier; stock goes up.
- [ ] **Return → Purchase** — return some; stock goes down.
- [ ] **Payments → Awaiting Payment / All Deposits** load and total correctly.
- [ ] **Accounting** — Account List, Money Transfer, Balance Sheet.
- [ ] **Fixed Assets** — register an asset, then transfer, dispose and sell one;
      check the asset reports.

---

## 16. Reports

Open each and check the numbers are plausible and the date filter works. Cross-
check at least one against reality — for example, Sale Report for today should
match the sale you made in section 5.

- [ ] Summary (Profit & Loss), Best Seller, Product, Category
- [ ] Daily Sale, Monthly Sale, Sale Report, Payment Report
- [ ] Daily Purchase, Monthly Purchase, Purchase Report
- [ ] Warehouse Report, Warehouse Stock Chart, Product Quantity Alert
- [ ] User, Customer, Supplier, Due and Creditor's reports
- [ ] Each report exports to PDF/Excel/print without error.

---

## 17. Settings

Only a Super Admin sees these. Change one thing at a time and confirm the effect.

- [ ] **General Setting** — change the site title and confirm it appears in the
      browser tab and on documents. Change the logo and confirm it appears.
- [ ] **POS Settings** — change the default customer and warehouse; confirm POS picks them up.
- [ ] **Mail Setting** — send a test email.
- [ ] **Messaging** — confirm WhatsApp settings save and a test message sends.
- [ ] **Currency**, **Tax**, **Warehouse**, **Customer Group**, **Brand**, **Unit** — add one of each.
- [ ] **Role Permission** — covered in section 3.
- [ ] **Activity Logs** — your recent actions are listed.
- [ ] **Backup Database** — downloads a file.
- [ ] **Site Content** — reorder a menu and confirm the order changes.
- [ ] ⚠️ **Do not test Empty Database.** It erases everything.

---

## 18. Regression checks for recent fixes

### Row action menus (older)

On Booking List, Sale List and Purchase List, open the **Action** menu on the
**last row** of the table, on a laptop and on a phone.

- [ ] **Pass:** the whole menu is visible. If it is taller than the screen it
      scrolls inside itself, or it opens upwards. Nothing is cut off by the
      bottom of the table or the bottom of the window.

### Names instead of phone numbers

- [ ] Booking List **Customer** column shows names.
- [ ] Task Manager **Assign To** picker shows names.
- [ ] 📱 A task WhatsApp greets the person by name.
- [ ] **If you still see a bare number**, that person has no name saved anywhere.
      Open their customer record, type the real name, save, and confirm it now
      shows everywhere. It should not come back after that.

### Messaging schedules & CC (v2.1.6 – v2.2.0)

- [ ] Login has **no** Sign in/Sign up tab bar; small Sign up link remains.
- [ ] Task with 3+ assignees + CC: all get WhatsApp (not only the first).
- [ ] Task **reminder** WhatsApps **CC** as well as assignees; sent reminders
      leave the Reminders list.
- [ ] Pending Acceptances: multi-select **Cancel**; old invite link → Invalid.
- [ ] Quotation approve: same link shows **already approved**; CC gets signed PDF.
- [ ] New quotation default note has **3** points; PDF with ~4 lines stays one page.
- [ ] Scheduled / Reminder lists: multi-select delete or cancel stops the send.

---

## Known issues — expected, do not report

- **`/store` returns a server error.** This is the old shop front from the
  original codebase. It is not linked from any Ogera menu. Reported separately;
  it needs either fixing or removing.
- **Job Board, Courses, Staff Permissions** are hidden from the admin menu and
  their public pages return 404. Deliberate.
- **`/apply-now`, `/register-now`, `/permissions`, `/shareholders`** return 404.
  Deliberate.
- **Timed sends do nothing if cron is not set up** on the hosting account.
  Immediate sends are unaffected.
- **Digital Invitations** needs its own database connection configured on the
  server, otherwise it shows a "misconfigured" notice.

---

## Reporting a problem

For each problem, write down:

1. **Version** from the bottom of the login page, e.g. `OGERA_ERP_V1.7.5`.
2. **Where** — the exact URL, e.g. `https://ogeragency.com/bookings/index`.
3. **Device** — laptop or phone, and which browser.
4. **Who** — which login and which role.
5. **What you did** — the steps, in order, so it can be repeated.
6. **What you expected** and **what actually happened**.
7. **A screenshot.** Include the whole window, not a crop, so the URL is visible.

A problem that cannot be repeated cannot be fixed, so the steps matter more than
the description.
