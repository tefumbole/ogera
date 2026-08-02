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

## 7. Quotations

- [ ] **Quotation → Add Quotation**: build a quote and save it.
- [ ] 📱 Send it to the client on WhatsApp.
- [ ] Open the `/quotation-approval/...` link as the client, tick the agreement,
      draw a signature and approve.
- [ ] 📱 The client receives the signed quotation PDF with a verification QR.
- [ ] The quotation shows as Approved in admin, and the link cannot be reused.
- [ ] Repeat with **Reject** and a comment; confirm the comment reaches admin.
- [ ] Convert an approved quotation into a sale.
- [ ] **Pass:** the signed quotation stays on one page and shows the signature.

---

## 8. Task Manager

- [ ] **Create Task**: title, description, priority, start and deadline, colour,
      an optional PDF, and at least one assignee.
- [ ] In the **Assign To** picker, people are listed by **name**, not by phone
      number. (You will see some people listed twice, once as *Portal* and once
      as *User* — that is a known duplication, not a name bug.)
- [ ] Try to save with no assignee — it must refuse.
- [ ] Save. 📱 The assignee receives a WhatsApp that greets them **by name** and
      contains a `/task-invite/...` link.
- [ ] Open the invite link as the assignee. Accept it with a signature.
- [ ] The task appears in their list at `/user/tasks`; update the progress.
- [ ] Decline a second task and confirm admin is notified.
- [ ] **Pending Acceptances**, **Scheduled**, **Reminders** and **All Tasks**
      pages all load and show the right tasks.
- [ ] **Pass:** no phone numbers appear where a person's name should be.

---

## 9. Announcements and messaging 📱

- [ ] **Compose**: write a message, choose recipients, attach a file, send now.
- [ ] The recipients receive it, with the attachment.
- [ ] The announcement is listed with a serial number.
- [ ] Schedule one for a few minutes ahead — this only arrives if cron is running.
- [ ] **Templates** and **Categories** can be created and reused.
- [ ] **Settings** (company name, header, footer) affect the message that goes out.
- [ ] **Create SMS** works if an SMS gateway is configured; skip if not.

---

## 10. Letters

Letters move through stages, and each stage belongs to a different role.

- [ ] **Letter Categories** and **Templates Letter** can be created.
- [ ] **Create Letter** from a template, addressed to a customer or employee.
- [ ] Walk it through: Awaiting Editing → Awaiting Approval → Awaiting Signature
      → Ready To Send → Sent. Confirm it appears in the correct list at each step
      and that a user without that stage's role cannot move it.
- [ ] Print and download a sent letter; check the letterhead and serial number.
- [ ] Reject a letter and confirm it lands in **Rejected Letters**.

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

These two were fixed in v1.7.5. Confirm they stayed fixed.

**Row action menus.** On Booking List, Sale List and Purchase List, open the
**Action** menu on the **last row** of the table, on a laptop and on a phone.

- [ ] **Pass:** the whole menu is visible. If it is taller than the screen it
      scrolls inside itself, or it opens upwards. Nothing is cut off by the
      bottom of the table or the bottom of the window.

**Names instead of phone numbers.**

- [ ] Booking List **Customer** column shows names.
- [ ] Task Manager **Assign To** picker shows names.
- [ ] 📱 A task WhatsApp greets the person by name.
- [ ] **If you still see a bare number**, that person has no name saved anywhere.
      Open their customer record, type the real name, save, and confirm it now
      shows everywhere. It should not come back after that.

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
