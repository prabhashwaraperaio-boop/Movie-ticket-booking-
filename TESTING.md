# CINEMORA — Quality Assurance, Verification & Test Report

## 1. Execution Environment Disclosure

- **Workspace Path:** `e:\University\NIBM\Lecture Notes\Y1 Sem2 SEF\Webapplication`
- **Target Deployment Path:** `C:\xampp\htdocs\cinemora` (`http://localhost/cinemora/`)
- **Host Environment Check:** Inspected system `PATH` and drives `C:\`, `D:\`, `E:\` using `where.exe php mysql xampp-control`. Neither PHP CLI, MySQL/MariaDB server, nor XAMPP is installed in this automated agent environment.
- **Verification Methodology:**
  - **Static Code, Link, Security & Schema Audits (`Passed`):** Executed automated PowerShell static verification across all **79 PHP files**, **4 JavaScript files**, **4 CSS stylesheets**, **1 SQL schema file**, and **15 SVG vector assets** to verify PHP block tags, `declare(strict_types=1)`, zero `TODO`/`FIXME` placeholders, 100% resolved `require_once` paths, 100% resolved `url()`/`redirect()`/`asset_url()` targets, 100% POST `<form>` `csrf_field()` coverage, and SQL schema/column consistency.
  - **Runtime Database & Browser Execution Tests (`Not Run`):** Because Apache, PHP, and MySQL/XAMPP are not installed on this host machine, live HTTP/MySQL runtime tests are honestly recorded as **`Not Run`** below, accompanied by exact step-by-step manual verification instructions for XAMPP.

---

## 2. Automated Static Audit Results (Executed in Workspace)

| Audit Check | Scope | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- | :--- |
| **ST-01: Technology Stack Purity** | Entire repository | Only HTML5, CSS3, Vanilla JS, PHP, and SQL/MySQL files exist; zero external frameworks, npm/Composer packages, or CDN URLs. | Verified 79 `.php`, 4 `.js`, 4 `.css`, 1 `.sql`, 15 `.svg` files; 0 external dependencies or CDN links. | **Passed** |
| **ST-02: PHP Opening Tags & Strict Types** | All 79 `.php` files | Every PHP script starts with `<?php` and `declare(strict_types=1);` with balanced PHP tags and zero `TODO`/`FIXME` placeholders. | 79/79 PHP files verified with 0 syntax tag imbalances and 0 `TODO`/`FIXME` strings. | **Passed** |
| **ST-03: Internal Link & Redirect Resolution** | All `url()`, `redirect()`, `asset_url()` calls | Every internal route and asset reference resolves to an existing file in the project workspace. | All route, redirect, and asset references across all 79 PHP files resolved on disk with 0 broken links. | **Passed** |
| **ST-04: PHP Include Path Verification** | All `require_once` statements | Every `require_once __DIR__`, `dirname(__DIR__)`, and `dirname(__DIR__, 2)` expression resolves to an existing PHP file. | 100% of `require_once` targets verified on disk with 0 missing files. | **Passed** |
| **ST-05: CSRF Protection Coverage** | All HTML `<form method="post">` elements & `actions/*.php` handlers | Every POST form embeds `<?= csrf_field() ?>` and every POST action endpoint calls `verify_csrf_or_abort()`. | 100% of POST forms and action scripts verified. | **Passed** |
| **ST-06: PDO Prepared Statements & SQL Schema Alignment** | All database queries vs `database/schema.sql` | All 14 normalized `InnoDB` tables (`users`, `cinema_locations`, `screens`, `seats`, `screen_layout_cells`, `movies`, `showtimes`, `showtime_layout_cells`, `showtime_seats`, `bookings`, `booking_seats`, `contact_messages`, `system_settings`, `admin_activity_logs`) match PHP queries and use PDO prepared statements. | Verified all 14 tables, foreign keys, unique constraints, and prepared statements across all models and actions. | **Passed** |

---

## 3. Comprehensive Test Cases Across All 9 Functional Categories

### Category 1 — Environment & Database Setup

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **ENV-01: First-Run Auto-Provisioning (`config/database.php`)** | Visiting `http://localhost/cinemora/` on a fresh XAMPP setup automatically creates `cinemora_db`, executes `database/schema.sql`, runs `scripts/seed_demo_data.php`, and creates `storage/setup.lock`. | Code path implemented in `config/database.php` (`maybe_auto_provision_local_database()`); requires XAMPP MySQL runtime to execute. | **Not Run** |
| **ENV-02: Idempotent Demo Data Seeding (`scripts/seed_demo_data.php`)** | Running the seeder multiple times via `http://localhost/cinemora/scripts/setup.php` does not duplicate users, cinemas, theaters, seats, movies, showtimes, bookings, or contact messages. | Implemented with `seed_key` unique constraints, `email` checks, and `ON DUPLICATE KEY UPDATE`; requires XAMPP runtime to execute. | **Not Run** |
| **ENV-03: Seeded Minimum Counts Verification** | Database contains 3 cinemas, 6 theaters, 8 movies, 18 future showtimes, 3 users (1 Admin, 2 Customers), 6 sample bookings (including 1 Cancelled with released seats), and 4 contact messages. | Seed arrays and transactional loops verified in `scripts/seed_demo_data.php`; requires XAMPP MySQL runtime to execute. | **Not Run** |

### Category 2 — Public Website & Navigation

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **PUB-01: Homepage (`index.php`) & Quick Booking Finder** | Displays hero banner, Quick Booking Finder (Movie, Cinema, Date), stats strip, Now Showing grid, Cinema Locations showcase, and Coming Soon strip with LKR prices. | Implemented in `index.php` with live MySQL queries and local SVG assets; requires XAMPP runtime to inspect in browser. | **Not Run** |
| **PUB-02: Movies Catalogue (`movies.php`, `now-showing.php`, `coming-soon.php`)** | Supports filtering by search keyword, genre, language, cinema location, status, and showtime date, retaining filter values in inputs. | Implemented with parameterized PDO queries in `movies.php`, `now-showing.php`, and `coming-soon.php`; requires XAMPP runtime. | **Not Run** |
| **PUB-03: Movie Details (`movie-details.php`) & Cinema Details (`cinema-details.php`)** | Groups upcoming showtimes by Cinema Location -> Theater Hall -> Date with direct "Select Seats" buttons and displays custom theater seat statistics. | Implemented in `movie-details.php` and `cinema-details.php`; requires XAMPP runtime. | **Not Run** |
| **PUB-04: Contact Concierge (`contact.php` & `actions/contact-actions.php`)** | Validates name, email, phone, subject, and message; stores submission in `contact_messages` as `Unread`; "Fill Demo Details" populates fields without auto-submitting. | Implemented in `contact.php`, `actions/contact-actions.php`, and `assets/js/main.js`; requires XAMPP runtime. | **Not Run** |

### Category 3 — Authentication & Role Authorization

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **AUTH-01: Customer Registration (`register.php`)** | Validates full name, unique email, phone, and strong password (`>= 8` chars, upper, lower, digit); hashes password with `password_hash()`; logs user in and regenerates session ID. | Implemented in `register.php` and `actions/auth-actions.php`; requires XAMPP runtime. | **Not Run** |
| **AUTH-02: Admin & Customer Sign-In (`login.php`)** | Signing in with `admin@cinemora.local` / `Admin@12345` redirects to `admin/dashboard.php`; signing in with `customer@cinemora.local` / `Customer@12345` redirects to `customer/dashboard.php` (or saved return URL). | Implemented in `login.php`, `actions/auth-actions.php`, and `includes/auth.php`; requires XAMPP runtime. | **Not Run** |
| **AUTH-03: Brute-Force Login Throttling** | 5 consecutive failed sign-in attempts for the same email/IP lock out login attempts for 60 seconds (`is_login_throttled()`). | Implemented in `includes/auth.php` (`record_failed_login()` / `is_login_throttled()`); requires XAMPP runtime. | **Not Run** |
| **AUTH-04: Role Boundary Enforcement (`require_admin()`, `require_customer()`)** | Guests and Customers attempting to access `/admin/*` are blocked and redirected; deactivated users (`account_status = 'Inactive'`) are immediately logged out on their next request. | Verified in `includes/auth.php` (`current_user()`, `require_admin()`, `require_customer()`); requires XAMPP runtime. | **Not Run** |

### Category 4 — Multi-Cinema & Theater Hierarchy Management

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **CIN-01: Add & Edit Cinema Location (`admin/cinemas/*`)** | Admin can create/update a cinema branch with unique `cinema_name`, city, address, phone, email, opening hours, facilities, status, and optional banner upload. | Implemented in `admin/cinemas/add.php`, `edit.php`, `view.php`, and `actions/admin-cinema-actions.php`; requires XAMPP runtime. | **Not Run** |
| **CIN-02: Cinema Deactivation Guard** | Setting a cinema location to `Inactive` is blocked if any theater inside that cinema has upcoming `Scheduled` showtimes. | Enforced in `actions/admin-cinema-actions.php`; requires XAMPP runtime. | **Not Run** |
| **SCR-01: Add & Edit Theater / Hall (`admin/screens/*`)** | Admin can create/update a theater linked to a parent cinema (`cinema_location_id`) with unique `(cinema_location_id, screen_name)` and initialize its default seat layout. | Implemented in `admin/screens/add.php`, `edit.php`, and `actions/admin-screen-actions.php`; requires XAMPP runtime. | **Not Run** |

### Category 5 — Custom Seat Layout Designer

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **SEAT-01: Interactive Theater Seat Designer (`admin/seat-designer/index.php`)** | Admin can select any theater across any cinema, apply quick presets (`standard`, `twin_aisle`, `luxury_vip`, `wide_imax`), paint `Standard`, `Premium`, `VIP`, `Accessible`, `Aisle`, or `Gap` cells, and auto-renumber seats per row (`A1, A2...`). | Implemented in `admin/seat-designer/index.php` and `assets/js/seat-designer.js`; requires XAMPP runtime. | **Not Run** |
| **SEAT-02: Historical Seat Preservation on Save (`actions/admin-seat-designer-actions.php`)** | Saving a modified theater layout updates `seats`, `screen_layout_cells`, and `screens.total_seats` inside a transaction without deleting historical `seats` rows referenced by `showtime_seats` or modifying existing showtime snapshots. | Implemented in `actions/admin-seat-designer-actions.php`; requires XAMPP runtime. | **Not Run** |

### Category 6 — Movie & Showtime Scheduling

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **MOV-01: Add, Edit & Deactivate Movies (`admin/movies/*`)** | Admin can add/edit movies with poster upload validation (`finfo_file`, max 3 MB) and cannot deactivate a movie that has upcoming `Scheduled` showtimes. | Implemented in `admin/movies/*` and `actions/admin-movie-actions.php`; requires XAMPP runtime. | **Not Run** |
| **SHW-01: Schedule Showtime with Overlap & Cleaning Buffer Validation (`create_showtime_with_snapshots()`)** | Scheduling a showtime checks that the cinema, theater, and movie are `Active`, verifies the theater has active seats, enforces `end_datetime >= start_datetime + duration_minutes`, prevents overlapping showtimes in the same theater including `cleaning_interval_minutes` (default 15m), and snapshots all seats/cells into `showtime_seats` and `showtime_layout_cells`. | Implemented in `includes/functions.php` (`create_showtime_with_snapshots()`) and `actions/admin-showtime-actions.php`; requires XAMPP runtime. | **Not Run** |
| **SHW-02: Showtime Edit Lock When Bookings Exist** | Once a showtime has confirmed bookings, its movie, theater, start/end time, and base price are locked from destructive edits; cancelling the showtime automatically cancels confirmed bookings and releases all booked seats. | Implemented in `admin/showtimes/edit.php` and `actions/admin-showtime-actions.php`; requires XAMPP runtime. | **Not Run** |

### Category 7 — Customer Seat Selection, Booking & Cancellation Flow

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **BKG-01: Live Seat Map & Category Pricing (`booking/seat-selection.php`)** | Renders the exact custom grid (`showtime_layout_cells` + `showtime_seats`) for the chosen theater/showtime, displaying `Standard`, `Premium`, `VIP`, `Accessible (♿)`, `Booked (✕)`, `Aisle`, and `Gap` cells with live LKR total calculation and max-seat limit enforcement. | Implemented in `booking/seat-selection.php`, `booking/seat-status-api.php`, and `assets/js/booking.js`; requires XAMPP runtime. | **Not Run** |
| **BKG-02: ACID Row-Locked Booking Confirmation (`actions/booking-actions.php`)** | Confirming a booking locks the selected `showtime_seats` rows via `SELECT ... FOR UPDATE`, verifies all seats are `Available`, prevents duplicate submission via `idempotency_token`, inserts `bookings` (`Confirmed`, `Unpaid`) and `booking_seats`, and marks seats `Booked`. | Implemented in `actions/booking-actions.php`; requires XAMPP runtime. | **Not Run** |
| **BKG-03: Printable E-Ticket (`booking/ticket.php`)** | Displays booking reference code (`CNM-YYYYMMDD-XXXXXX`), cinema branch, theater hall, showtime date/time, itemized seat badges (`Standard`/`Premium`/`VIP`), payment status, and print stylesheet (`window.print()`). | Implemented in `booking/ticket.php`; requires XAMPP runtime. | **Not Run** |
| **BKG-04: Customer Self-Cancellation & Seat Release** | Customer can cancel a `Confirmed` booking before the `cancellation_cutoff_minutes` window (default 60m prior to showtime); cancelling sets `booking_status = 'Cancelled'`, sets `payment_status = 'Refunded'` if previously `Paid`, and releases `showtime_seats` back to `Available` so another customer can re-book those exact seats. | Implemented in `actions/booking-actions.php` and `database/schema.sql` (composite unique key `(booking_id, showtime_seat_id)` on `booking_seats`); requires XAMPP runtime. | **Not Run** |

### Category 8 — Administrator Operations & Payment Status Governance

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **ADM-01: Payment Status Update & Audit Trail (`admin/bookings/view.php`)** | Admin can update a booking's payment status (`Unpaid`, `Paid`, `Refunded`, `Failed`) with an optional note; updates `payment_updated_by`, `payment_updated_at`, and writes an entry to `admin_activity_logs`. | Implemented in `admin/bookings/view.php` and `actions/admin-booking-actions.php`; requires XAMPP runtime. | **Not Run** |
| **ADM-02: Customer Account Activation / Deactivation (`admin/customers/*`)** | Admin can view customer booking/spending history and toggle `Active` / `Inactive` status. | Implemented in `admin/customers/index.php`, `view.php`, and `actions/admin-customer-actions.php`; requires XAMPP runtime. | **Not Run** |
| **ADM-03: Contact Message Workflow (`admin/messages/*`)** | Viewing an `Unread` message automatically transitions it to `Read`; admin can save internal reply notes, change status (`Unread`, `Read`, `Resolved`), or delete a message. | Implemented in `admin/messages/index.php`, `view.php`, and `actions/admin-message-actions.php`; requires XAMPP runtime. | **Not Run** |
| **ADM-04: System Settings & Admin Profile (`admin/settings/index.php`)** | Admin can configure `cleaning_interval_minutes`, `cancellation_cutoff_minutes`, `max_seats_per_booking`, and public support contacts, update admin profile details, rotate admin password, and inspect `admin_activity_logs`. | Implemented in `admin/settings/index.php` and `actions/admin-settings-actions.php`; requires XAMPP runtime. | **Not Run** |

### Category 9 — Security, Validation & Responsive Design

| Test Case | Expected Result | Actual Result | Status |
| :--- | :--- | :--- | :--- |
| **SEC-01: XSS & SQL Injection Prevention** | All user-supplied output is escaped via `e()` (`htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`) and all database queries use PDO prepared statements with `ATTR_EMULATE_PREPARES => false`. | Verified across all 79 PHP files (`Passed` static code audit); live browser fuzzing requires XAMPP runtime. | **Passed (Static) / Not Run (Runtime)** |
| **SEC-02: File Upload Hardening (`.htaccess` & `finfo_file`)** | Uploading non-image files or files `> 3 MB` is rejected; `uploads/.htaccess` and `storage/.htaccess` block direct script or log access. | Verified in `actions/admin-movie-actions.php`, `actions/admin-cinema-actions.php`, `uploads/.htaccess`, and `storage/.htaccess`. | **Passed (Static) / Not Run (Runtime)** |
| **UI-01: Responsive Layouts (`assets/css/responsive.css`)** | Public pages, interactive seat map (with horizontal scroll container), printable ticket, and collapsible admin sidebar adapt cleanly across desktop (`1440px`), tablet (`768px`), and mobile (`375px`) viewports. | Implemented in `assets/css/style.css`, `booking.css`, `admin.css`, and `responsive.css`; visual browser inspection requires XAMPP runtime. | **Not Run** |

---

## 4. Step-by-Step Manual XAMPP Verification Checklist

To execute all runtime tests above on your local Windows machine with XAMPP:

1. **Deploy & Start Services:**
   - Ensure the project is placed in `C:\xampp\htdocs\cinemora`.
   - Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. **Verify Zero-Touch Setup & Seeding:**
   - Open `http://localhost/cinemora/` in Chrome/Edge/Firefox.
   - Confirm that the homepage loads with 6 "Now Showing" movies, 3 Cinema Locations (Colombo, Kandy, Galle), and "Coming Soon" releases.
   - Optionally open `http://localhost/cinemora/scripts/setup.php` to inspect row counts for all 14 database tables and click **"Re-Run Idempotent Demo Seeder"** to verify zero duplicate rows are created.
3. **Test Public Filtering & "Fill Demo Details":**
   - Visit **Movies** (`http://localhost/cinemora/movies.php`), filter by *Cinema Location = CINEMORA Colombo One Galle Face*, and verify results.
   - Visit **Contact** (`http://localhost/cinemora/contact.php`), click **"✦ Fill Demo Details"**, verify fields populate without submitting, and then click **Send Message**.
4. **Test Customer Seat Selection, Booking & Cancellation:**
   - Sign in at `http://localhost/cinemora/login.php` as `customer@cinemora.local` / `Customer@12345`.
   - Pick any upcoming showtime, select 2 available seats (e.g., 1 `Premium` and 1 `VIP`), verify the live LKR total updates in the summary sidebar, and proceed through **Booking Summary** -> **Confirm Booking**.
   - Click **View / Print E-Ticket** on the confirmation screen and test **Print E-Ticket**.
   - Visit **My Bookings** (`http://localhost/cinemora/customer/my-bookings.php`), cancel the newly created booking, and re-open the same showtime's seat map to verify the cancelled seats are immediately `Available` again.
5. **Test Administrator Portal & Seat Layout Designer:**
   - Sign out and sign in at `http://localhost/cinemora/login.php` as `admin@cinemora.local` / `Admin@12345`.
   - Open **Seat Layout Designer** (`http://localhost/cinemora/admin/seat-designer/index.php`), select a theater hall, paint custom `VIP` / `Aisle` / `Gap` cells, click **Auto-Renumber Seats**, and save.
   - Open **Bookings** (`http://localhost/cinemora/admin/bookings/index.php`), inspect a booking, update its **Payment Status** to `Paid` with a note, and verify the audit trail in **Admin Profile & Settings** (`http://localhost/cinemora/admin/settings/index.php`).
