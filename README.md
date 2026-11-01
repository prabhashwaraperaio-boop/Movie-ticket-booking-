# CINEMORA — Multi-Cinema Movie Ticket Booking Web Application

> **"Experience Movies. Reserve Your Moment."**
>
> **Currency:** Sri Lankan Rupees (`LKR`) • **Timezone:** `Asia/Colombo` • **Target Runtime:** Windows + XAMPP (Apache + PHP 8.x + MySQL/MariaDB)

---

## 1. Project Overview

**CINEMORA** is a full-stack, database-driven Multi-Cinema Movie Ticket Booking Web Application built strictly with **HTML5**, **CSS3**, **Vanilla JavaScript**, **PHP (PDO with prepared statements)**, and **SQL/MySQL (`InnoDB`, `utf8mb4`)** — with **zero** third-party frameworks, libraries, package managers, or external CDNs.

### Multi-Cinema Business Hierarchy

Unlike single-cinema booking prototypes, CINEMORA models a full multiplex chain:

```text
CINEMORA
├── Cinema Location / Branch (cinema_locations)
│   ├── Theater / Auditorium / Hall 01 (screens)
│   │   └── Independent Custom Seat Layout (seats + screen_layout_cells)
│   │       └── Scheduled Showtime (showtimes)
│   │           └── Immutable Seat & Grid Snapshots (showtime_seats + showtime_layout_cells)
│   │               └── ACID Row-Locked Customer Bookings (bookings + booking_seats)
│   └── Theater / Auditorium / Hall 02 (screens)
│       └── Independent Custom Seat Layout (seats + screen_layout_cells)
└── Additional Cinema Locations & Theaters Created by Administrator
```

Key architectural guarantees:
- **Independent Custom Seat Layouts per Theater:** Each theater (`screens`) defines its own rows, columns, `Seat` / `Aisle` / `Gap` cells, seat categories (`Standard`, `Premium`, `VIP`), category price surcharges (`price_extra`), and accessible seat flags (`is_accessible`).
- **Immutable Showtime Snapshots:** When a showtime is scheduled (`create_showtime_with_snapshots()`), all active theater seats and layout cells are snapshotted into `showtime_seats` and `showtime_layout_cells` inside an ACID transaction. Subsequent edits to a theater's template layout in the Seat Layout Designer never corrupt existing or past showtimes and bookings.
- **ACID Row-Locked Concurrent Seat Booking:** Confirming a booking locks the requested `showtime_seats` rows using `SELECT ... FOR UPDATE`, verifies every seat is `Available`, creates the `bookings` and `booking_seats` records, and marks the seats `Booked` atomically.
- **Customer & Admin Cancellation with Immediate Seat Release:** Cancelling a booking releases its `showtime_seats` rows back to `Available` while preserving the historical `bookings` and `booking_seats` rows for auditability.

---

## 2. Strict Technology Compliance

| Layer | Technology Used | External Frameworks / Libraries |
| :--- | :--- | :--- |
| Markup | **HTML5** (semantic, accessible forms & ARIA landmarks) | None |
| Styling | **CSS3** (`style.css`, `booking.css`, `admin.css`, `responsive.css`) | None (No Bootstrap, Tailwind, or external fonts/icons) |
| Client Scripting | **Vanilla JavaScript** (`main.js`, `animations.js`, `booking.js`, `seat-designer.js`) | None (No React, Vue, jQuery, or Alpine) |
| Server Backend | **PHP 8+** (`declare(strict_types=1)`, PDO prepared statements) | None (No Laravel, Symfony, or Composer packages) |
| Database | **MySQL / MariaDB** (`InnoDB`, `utf8mb4`, foreign keys & unique constraints) | None |

---

## 3. Step-by-Step XAMPP Installation Guide

### Prerequisites
- **Windows** with **XAMPP** installed (`Apache` and `MySQL` services).
- PHP 8.0+ with the `pdo_mysql` and `fileinfo` extensions enabled (enabled by default in XAMPP).

### Step 1 — Place Project in `C:\xampp\htdocs\cinemora`
1. Copy or clone this project folder into `C:\xampp\htdocs\cinemora` so that `index.php` is located at:
   ```text
   C:\xampp\htdocs\cinemora\index.php
   ```
   *(Note: Dynamic `BASE_URL` detection in `config/database.php` also allows running CINEMORA from any subdirectory name or virtual host root.)*

### Step 2 — Start Apache and MySQL
1. Open **XAMPP Control Panel**.
2. Click **Start** next to **Apache** and **Start** next to **MySQL**.

### Step 3 — Automatic Zero-Touch Database Setup & Demo Seeding
1. Open your browser and visit:
   ```text
   http://localhost/cinemora/
   ```
2. **Automatic Provisioning:** On the first localhost request (when `storage/setup.lock` does not yet exist), `config/database.php` automatically:
   - Connects to your local MySQL server (`127.0.0.1:3306`, user `root`, empty password by default).
   - Creates the `cinemora_db` database and all 14 normalized `InnoDB` tables from `database/schema.sql`.
   - Runs the idempotent demo data seeder (`scripts/seed_demo_data.php`) so the website is immediately populated with realistic Sri Lankan cinemas, custom theater seat maps, movies, future showtimes, sample bookings, and contact messages.
   - Writes `storage/setup.lock` so subsequent page loads skip setup overhead.

### Optional — Manual Setup / Verification Screen or Custom MySQL Credentials
- If your local MySQL `root` user has a password or uses a custom port:
  1. Copy `config/database.example.php` to `config/database.local.php`.
  2. Update `host`, `port`, `dbname`, `username`, and `password` in `config/database.local.php`.
- You can also inspect database health or re-run the idempotent seeder at any time from localhost by visiting:
  ```text
  http://localhost/cinemora/scripts/setup.php
  ```
- Or import `database/schema.sql` manually via **phpMyAdmin** (`http://localhost/phpmyadmin`) and then visit `http://localhost/cinemora/scripts/setup.php` to seed the dynamic future showtimes and hashed demo accounts.

---

## 4. Local-Only Demo Accounts

> **SECURITY WARNING:** The credentials below are seeded strictly for **local development and academic evaluation** on `localhost`. All passwords are stored in MySQL using PHP's `password_hash(..., PASSWORD_DEFAULT)`. **Change or remove these demo accounts before any public deployment.**

| Role | Email Address | Password | Capabilities |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@cinemora.local` | `Admin@12345` | Full access to Admin Dashboard, Cinema Locations, Theaters, Seat Layout Designer, Movies, Showtimes, Bookings & Payment Status, Customers, Contact Messages, and System Settings. |
| **Customer 1** | `customer@cinemora.local` | `Customer@12345` | Pre-populated with confirmed Paid (Demo), Unpaid, and Cancelled bookings; can select seats, book tickets, print e-tickets, and cancel eligible bookings. |
| **Customer 2** | `customer2@cinemora.local` | `Customer@12345` | Pre-populated with sample bookings; ready for concurrent seat-booking and cancellation testing. |

---

## 5. Seeded Sample Data Summary

Immediately after installation, `scripts/seed_demo_data.php` populates:
- **3 Cinema Locations:**
  1. *CINEMORA Colombo One Galle Face* (Colombo — 2 Theaters: *IMAX Laser Grand 01* & *Gold Class Luxe 02*)
  2. *CINEMORA Kandy City Centre* (Kandy — 2 Theaters: *Dolby Atmos Hall 01* & *Premier Screen 02*)
  3. *CINEMORA Galle Fort Heritage* (Galle — 2 Theaters: *Southern Coast Hall 01* & *Fort Boutique Lounge 02*)
- **6 Distinct Custom Seat Layouts:** Featuring vertical aisles, horizontal walkways, notch gaps, `Standard`, `Premium`, and `VIP` price tiers, and wheelchair-accessible (`♿`) seats.
- **8 Complete Movies:** 6 `Now Showing` and 2 `Coming Soon`, each with local SVG posters (`assets/images/movies/movie-1.svg` .. `movie-8.svg`), age ratings, durations, genres, cast, directors, and synopsis.
- **18 Dynamic Future Showtimes:** Generated relative to the current `Asia/Colombo` date (`+1` to `+5` days) with zero schedule overlaps and full `showtime_seats` / `showtime_layout_cells` snapshots.
- **6 Sample Bookings:** Including clearly labelled `DEMO DATA` Paid bookings, Unpaid bookings, and 1 Cancelled booking whose seats are verified as released back to `Available`.
- **4 Sample Contact Messages:** Distributed across `Unread`, `Read`, and `Resolved` states.

---

## 6. "Fill Demo Details" Form Helper

To accelerate evaluation, the following 6 forms include a clearly labelled **"✦ Fill Demo Details"** button handled by `assets/js/main.js`:
1. **Customer Registration** (`register.php`)
2. **Public Contact Concierge** (`contact.php`)
3. **Add Cinema Location** (`admin/cinemas/add.php`)
4. **Add Theater / Hall** (`admin/screens/add.php`)
5. **Add Movie** (`admin/movies/add.php`)
6. **Schedule New Showtime** (`admin/showtimes/add.php`)

Behavior guarantees:
- Clicking **"✦ Fill Demo Details"** populates valid realistic sample values and selects valid foreign-key options from the database.
- If the user has already typed into the form, it prompts for confirmation before overwriting existing input.
- It **never** auto-submits the form.

---

## 7. GitHub Repository vs. GitHub Pages Clarification

This workspace is structured to be pushed cleanly to a **GitHub Repository** (with `.gitignore`, `config/database.example.php`, `database/schema.sql`, and all source assets).

**Why GitHub Pages Cannot Host CINEMORA Directly:**
- **GitHub Pages** is a static file host that only serves static `.html`, `.css`, and `.js` files; it does **not** execute server-side **PHP** scripts or run a **MySQL/MariaDB** relational database.
- Because CINEMORA is a genuine server-rendered PHP + MySQL application with server-side authentication, CSRF protection, and ACID database transactions, it must be executed on a PHP + MySQL environment such as **XAMPP (`http://localhost/cinemora/`)**.

---

## 8. Key Architectural Assumptions & Design Decisions

1. **Historical Seat & Booking Preservation:** Editing a theater's layout in the Seat Layout Designer (`actions/admin-seat-designer-actions.php`) updates the theater's template (`seats` and `screen_layout_cells`) for *future* showtimes while keeping historical `seats` rows intact (soft-deactivated with `is_active = 0` when removed from the active grid) so foreign keys from `showtime_seats` and `booking_seats` are never broken.
2. **No Fake Payment Gateway:** In accordance with project specifications, CINEMORA does not fake credit card collection. New customer bookings default to `Booking Status = Confirmed` and `Payment Status = Unpaid`, and administrators can update payment status (`Unpaid`, `Paid`, `Refunded`, `Failed`) with full audit tracking (`payment_updated_by`, `payment_updated_at`, `payment_note`).
3. **Self-Cancellation Cutoff Policy:** Customers can cancel their own `Confirmed` bookings up until `cancellation_cutoff_minutes` (default `60` minutes, configurable in Admin Settings) prior to showtime start. Administrators can cancel any confirmed booking at any time.
