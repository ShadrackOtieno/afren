# AFREN — Africa Researcher & Enumerator Network

Laravel 12 · Blade · Alpine.js · Tailwind (CDN) · MySQL

Two complementary commercial pathways on one platform, **kept logically separate**:

| | Monthly subscription (Option A) | Direct recruitment request (Option B) |
|---|---|---|
| Entry point | `/packages` → `/packages/{slug}/subscribe` | `/request-recruitment` |
| Account / login | Organization account created at subscription checkout; sign in to the portal | **None.** No account, login, subscription or payment to submit |
| Billing | Monthly invoices (`SUB-YYYY-#####`), renewals tracked | Manual invoice prepared by an admin from the reviewed scope (`DIR-YYYY-#####`) |
| Pricing | Admin-defined packages (price, currency, limits, benefits) | Admin-entered line items — **never auto-priced** |
| Records | `subscriptions`, `subscription_events`, `invoices.type=subscription` | `recruitment_requests (source=public)`, `invoices.type=direct` |

## 1. Requirements
PHP ≥ 8.2 (extensions: mbstring, xml, curl, pdo_mysql, fileinfo, zip), Composer 2, MySQL 8 / MariaDB 10.6+. Node is **not** required (Tailwind and Alpine load from CDN; see "Production notes").

## Accounts & sign-in
One sign-in page (`/login`) for everyone; the role decides where you land.

| Who | How they get an account | After sign-in | Area |
|---|---|---|---|
| Researcher / enumerator | Registration form (includes a password) | `/my/profile` | Edit all profile fields, replace CV, add/remove documents, score breakdown, pause visibility |
| Subscribing organization | Subscription checkout (creates the account) | `/portal` | Subscription & billing tab; **My recruitment requests** tab (status tracker, shortlist, invoices, edit while New/Under review) |
| AFREN staff | Created by a super-admin (Admin → Staff users) | `/admin` | Admin dashboard |
| Organization with **no** subscription | **No account** | n/a | Public request form + reference tracking |

* Passwords: min 8 chars with upper/lower case and a number; "Forgot password?" sends a reset link (`/forgot-password`). Login is throttled; deactivated accounts cannot sign in.
* A signed-in subscriber's request counts toward package limits; they can still choose "Submit as a one-off direct request instead" (invoiced separately).
* The professional's login email is not editable from the profile (contact AFREN to change it).

## What's in the latest revision
* Request form is a **5-step wizard** with per-step validation and a review screen.
* **Contact form** with admin **Messages** inbox.
* Redesign: cool neutrals + dark blue + orange (configurable), solid colours only, flat SVG illustrations (templates in `resources/brand/`).

## 2. Setup (local)
```bash
unzip afren.zip && cd afren
mysql -u root -p -e "CREATE DATABASE afren CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cp .env.example .env            # then edit DB_USERNAME / DB_PASSWORD, AFREN_ADMIN_* values
composer install
php artisan key:generate
php artisan migrate --seed      # countries, scoring criteria, first super-admin
php artisan serve               # http://localhost:8000
```
(or `./setup.sh`). Windows: use Laragon or XAMPP (enable PHP ≥ 8.2), create the DB in phpMyAdmin, then run the same `composer`/`php artisan` commands in the project folder.

* Public site: `http://localhost:8000`
* Admin: `http://localhost:8000/admin/login` — credentials from `.env` (`AFREN_ADMIN_EMAIL` / `AFREN_ADMIN_PASSWORD`, default `admin@afren.test` / `ChangeMe!12345`). **Change immediately.**
* Optional demo data (sample packages with *placeholder* prices + 4 professionals): `php artisan db:seed --class=DemoSeeder` (demo professionals can sign in as `demo1@example.org` … `demo4@example.org`, password `DemoPass!123`)
* Mail defaults to `MAIL_MAILER=log` (emails, including password-reset links, appear in `storage/logs/laravel.log`). Set SMTP in `.env` for real delivery.

## Brand colours
Default brand: **dark blue `#12355b`** (primary) and **orange `#bf5410`** (accent). Change them in **Admin → Settings & tax → Brand colours**: pick or type hex values, preview live, save. Lighter/darker shades are generated automatically and applied to the public site, admin area, favicon and illustrations (`resources/brand/*.svg` are templates recoloured on request). Colours must keep white button text readable (primary ≥ 4.5:1 contrast, accent ≥ 3:1 with a warning below 4.5:1). "Reset to defaults" restores the original blue and orange. Tailwind class names: `brand-*` (primary) and `accent-*`.

## Step-by-step forms
Every long form is a wizard (one section at a time, **Next / Back**, clickable progress bar, per-step validation, final **Review** screen with "Edit" links, server-side errors reopen the step that has them): the **recruitment request** (5 steps), **researcher/enumerator registration** (5), **organization subscription checkout** (3) and the **professional profile editor** (5; sections are freely navigable when editing). They share one script and three partials: `partials/wizard-script`, `wizard-stepper`, `wizard-review`, `wizard-buttons`. To make another form a wizard, wrap each section in `<section x-ref="sN" data-step="N" x-show="step === N">`, set `x-data="wizard(start, ['Title 1', …, 'Review'], false)"` with `novalidate` and `@submit="submitGuard($event)"`.

## Look & feel
Two-tier header: a dark **top bar** (phone, email, social icons) above a **coloured main header** with dropdown menus and a call-to-action. Sections alternate tinted backgrounds (light brand, light accent, white, solid brand bands) with subtle dot patterns (SVG, no gradients); no coloured decorative borders (enforced by a test); page headers are solid brand bands with an accent underline. Phone, public email, WhatsApp and social links are edited in **Admin → Settings & tax → Contact details & social links**; empty fields are hidden. Social URLs must be `http(s)` links.

## 3. Run the tests
```bash
php artisan test
```
Tests use in-memory SQLite (already configured in `phpunit.xml`; needs the `pdo_sqlite` PHP extension). They cover: guest direct request (no account/subscription created); subscription pending → paid → active; renewal invoicing & expiry; upgrade/downgrade/cancel; package request & shortlist limits; direct request → admin invoice → partial/full payment → request "Paid"; both models side by side with separate reporting; scoring, weights and recalculation; job-specific matching; duplicate registration checks; role-based access.

## 4. Roles
`super_admin` (everything, staff users, audit log) · `admin` (operations, packages, scoring, settings, finance) · `recruiter` (professionals, requests, matching, shortlists) · `finance` (subscriptions, invoices, payments, reports).

## 5. Where things are
| Concern | Code |
|---|---|
| Professional registration | `ProfessionalRegistrationController`, `Professional` |
| Scoring (10 configurable criteria, max 100) | `Services/ScoringService`, `scoring_criteria` table, admin → *Scoring rules* |
| Job-specific matching | `Services/MatchingService` (category, location, languages, skills, tools, experience, education, availability) |
| Subscriptions lifecycle | `Services/SubscriptionService`, command `afren:process-subscriptions` |
| Invoices / tax | `Services/InvoiceService`, `tax_rules` (country rule → default rule → 0%) |
| Payments (admin-recorded only) | `Services/PaymentService` |
| Audit trail | `Services/Audit`, `audit_logs` |
| Admin UI | `app/Http/Controllers/Admin/*`, `resources/views/admin/*` |
| Public UI | `resources/views/public/*`, `resources/views/components/*` |

## 6. Business rules implemented
* **Subscriptions:** checkout creates a *pending* subscription + first invoice; it becomes *active* only when an admin records full payment. Renewal invoices are issued `renewal_notice_days` (7) before renewal; unpaid → *past_due* → *expired* after `grace_days` (7) (`config/afren.php`). **Upgrade** = immediate with prorated difference invoice; **downgrade** = at next renewal; **cancel** = end of paid period (resume possible). Packages: price, currency, `max_requests_per_month`, `max_candidates_per_request`, free-text benefits — all admin-defined; empty limit = not capped.
* **Direct requests:** saved with a unique `REQ-YYYY-XXXXXX` reference, admins notified, requester gets confirmation and can track by reference + email. Status flow: New → Under review → Invoiced → Paid / In progress → Shortlisting → Completed (or Declined). Sending the invoice moves it to *Invoiced*; full payment moves it to *Paid*.
* **Matching** uses each request's own requirements; the general **profile score** (completeness/quality) is shown separately from the **job fit** score, with per-criterion explanation.
* **Multi-country / currency:** 54 African countries with dial codes and currencies seeded (`CountrySeeder`); packages and invoices have their own currency; no currency conversion in reports (grouped per currency); tax rules per country.
* **Security:** hashed passwords, session regeneration on login,  server-side validation everywhere, honeypot + throttling on public forms, admin login throttling, CSRF, role middleware, private file storage (CVs/briefs served only to authorised staff via controller), upload type/size limits, CSV formula-injection guard, audit log, password rules for staff.

## 7. Production notes
1. `APP_ENV=production`, `APP_DEBUG=false`, strong DB credentials, HTTPS.
2. **Scheduler (required for renewals):** cron `* * * * * cd /path/afren && php artisan schedule:run >> /dev/null 2>&1`.
3. `php artisan config:cache route:cache view:cache` and `composer install --no-dev -o`.
4. Tailwind/Alpine come from CDNs for zero-build setup. For a fully self-hosted build, install them via Vite (`npm i`) and replace the CDN tags in `resources/views/layouts/*.blade.php`.
5. Payment confirmation is deliberately manual (bank/mobile-money reconciliation by finance staff). To add a gateway (Paystack, Flutterwave, Stripe, M-Pesa), call `PaymentService::record()` from a **signature-verified webhook** — nothing else needs to change.
6. Privacy Policy and Terms pages are **templates**; get them legally reviewed for each jurisdiction you operate in.
7. Back up the database **and** `storage/app/private` (CVs, briefs).

## 8. Known limits / next steps
Admin cannot yet edit a professional's profile fields (only verification, notes and active status). No email-verification step at sign-up and no two-factor authentication yet (recommended for staff accounts). Invoices are emailed as plain text (no PDF yet); mail is sent synchronously (queue it if volume grows).