# WorkForce Pro

**WorkForce Pro** is a production-quality, enterprise-grade HR Management System built with **Core PHP 8+** and **MySQL** — no frameworks, no Laravel, no CodeIgniter. Designed to demonstrate full-stack engineering skills in a technical interview setting.

## Live Demo

> Deploy to Render using the guide below. Local setup takes under 2 minutes.

- **URL (local):** `http://localhost:8000/login.php`
- **Email:** `admin@workforce.test`
- **Password:** `password`

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Core PHP 8+, PDO, Prepared Statements |
| Database | MySQL 8+ (normalized, FK, indexed) |
| Frontend | HTML5, CSS3, Bootstrap 5, ES6 |
| Libraries | DataTables, Chart.js, SweetAlert2, Font Awesome |
| Security | CSRF, XSS escaping, bcrypt, session auth, rate-safe |

---

## Features

### Authentication
- Secure login / logout with `password_hash` / `password_verify`
- Session regeneration on login
- Change password from profile
- CSRF protection on all forms

### Dashboard
- Live statistics: Total Employees, Present Today, Pending Leaves, Pending Payroll
- Attendance Trend chart (14-day line chart)
- Department Distribution (doughnut chart)
- Monthly Payroll overview (bar chart)
- Recent hires table

### Employee Management (Full CRUD via AJAX)
- Add, Edit, Delete, Toggle Status, Bulk Delete
- Avatar upload with preview
- CSV Export, Print
- Server-side DataTables (search, sort, pagination)
- Department and Designation cascade dropdowns
- Status filter (Active / Inactive / Terminated)

### Department Management
- Add, Edit, Delete, Toggle Status, Bulk Delete
- Shows live employee count per department
- Server-side DataTables

### Designation Management
- Linked to departments
- Level classification: Junior → C-Level
- Server-side DataTables

### Salary & Payroll
- Monthly salary records with Basic, Allowances, Deductions
- Auto-calculated Net Salary (MySQL generated column)
- Status: Pending / Paid / Cancelled
- One-click "Mark as Paid"
- Filter by pay month

### Attendance
- Mark per-employee daily attendance
- Statuses: Present, Absent, Half-Day, Leave, Holiday
- Check-in / Check-out time tracking
- Filter by date

### Leave Management
- Submit leave requests (Annual, Sick, Casual, Maternity, Paternity, Unpaid)
- One-click Approve / Reject from table row
- Auto-calculated leave days (MySQL generated column)
- Filter by status

### Profile
- Edit name, phone, bio
- Upload and preview avatar
- Change password (current + new + confirm validation)

### Settings
- Company name, email, timezone, currency, pagination
- One-click database backup (generates `.sql` file to `database/backups/`)
- Application log viewer

### Audit Logs
- Immutable activity history
- Records every create, update, delete, login, logout
- Server-side DataTables with search

### Notifications
- Per-user notification system
- Unread badge in topbar
- Mark individual or all as read

---

## UI Design

The interface is inspired by **Linear**, **Vercel**, **Stripe**, and **Apple**:

- **Dark mode by default** with Light mode toggle (persisted via `localStorage`)
- Glassmorphism stat cards with colored accent borders
- Smooth hover animations on all interactive elements
- Responsive sidebar with mobile hamburger toggle
- Toast notifications (success, error, warning, info)
- SweetAlert2 confirmation dialogs before delete
- Skeleton-style loading states

---

## Folder Structure

```
WorkForcePro/
├── ajax/                  # JSON endpoints (one per module)
│   ├── employees.php
│   ├── departments.php
│   ├── designations.php
│   ├── salaries.php
│   ├── attendance.php
│   ├── leaves.php
│   ├── profile.php
│   ├── settings.php
│   ├── dashboard.php
│   └── audit_logs.php
├── assets/
│   ├── css/app.css        # Full custom design system
│   └── js/
│       ├── app.js         # Toast, API helper, theme, sidebar
│       └── resource.js    # Reusable CRUD resource manager
├── config/
│   └── database.php       # Singleton PDO (env-variable driven)
├── controllers/           # Business logic layer
├── models/                # PDO data access layer (BaseModel + 10 models)
├── views/
│   ├── layout.php         # Master layout (sidebar + topbar)
│   └── pages/             # One file per page
├── includes/
│   └── bootstrap.php      # Session, autoload, helpers, CSRF
├── database/
│   └── backups/           # Auto-generated SQL backups
├── uploads/               # Avatar storage (write-protected via .htaccess)
├── logs/                  # app.log
├── database.sql           # Full schema + seed data
├── index.php              # Router
├── login.php
├── logout.php
├── 404.php
├── Dockerfile
├── .env.example
└── .gitignore
```

---

## Database Schema

| Table | Description |
|---|---|
| `users` | Admin / HR accounts |
| `departments` | Company departments |
| `designations` | Job titles linked to departments |
| `employees` | Full employee records |
| `salaries` | Monthly salary records with generated `net_salary` |
| `attendance` | Daily check-in/out per employee |
| `leave_requests` | Leave applications with generated `days` column |
| `notifications` | Per-user notification records |
| `audit_logs` | Immutable activity history |
| `settings` | Key-value application configuration |

All tables use **InnoDB**, **Foreign Keys**, **Indexes** on searchable columns, and `utf8mb4` charset.

---

## Security Implementation

| Concern | Solution |
|---|---|
| SQL Injection | PDO prepared statements everywhere — zero raw queries |
| XSS | `htmlspecialchars()` on all output via `e()` helper |
| CSRF | Token in session, verified on every POST and AJAX call |
| Passwords | `password_hash(PASSWORD_DEFAULT)` / `password_verify()` |
| Sessions | HTTP-only, SameSite Lax, strict mode, regenerated on login |
| File Uploads | MIME type + size validation, randomized filename, `.htaccess` blocks PHP execution |
| Route Protection | `requireAuth()` called on every protected page and AJAX endpoint |

---

## API Reference

All endpoints require an authenticated session and a valid `csrf_token` (POST body or `X-CSRF-TOKEN` header).

| Module | Endpoint | Actions |
|---|---|---|
| Employees | `ajax/employees.php` | `list`, `get`, `save`, `delete`, `bulk_delete`, `toggle`, `options`, `export` |
| Departments | `ajax/departments.php` | `list`, `get`, `save`, `delete`, `bulk_delete`, `toggle`, `options` |
| Designations | `ajax/designations.php` | `list`, `get`, `save`, `delete`, `bulk_delete`, `toggle`, `options` |
| Salaries | `ajax/salaries.php` | `list`, `get`, `save`, `delete`, `bulk_delete`, `mark_paid` |
| Attendance | `ajax/attendance.php` | `list`, `get`, `save`, `delete`, `bulk_delete`, `today_summary` |
| Leaves | `ajax/leaves.php` | `list`, `get`, `save`, `delete`, `bulk_delete`, `approve`, `reject` |
| Profile | `ajax/profile.php` | `get`, `update`, `change_password` |
| Settings | `ajax/settings.php` | `get`, `save`, `backup`, `logs`, `notifications`, `mark_notification_read` |
| Dashboard | `ajax/dashboard.php` | — (returns all stats) |
| Audit Logs | `ajax/audit_logs.php` | `list` |

**Success response:**
```json
{ "success": true, "message": "Employee saved successfully." }
```

**Validation error:**
```json
{ "success": false, "errors": { "email": "Valid email required." } }
```

---

## Local Installation

### Requirements
- PHP 8.0+
- MySQL 8.0+

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/harichopper/workforcepro.git
cd workforcepro

# 2. Create the database and import schema
mysql -u root -e "CREATE DATABASE workforcepro;"
mysql -u root workforcepro < database.sql

# 3. (Optional) Configure DB credentials if not using defaults
#    Edit config/database.php  OR  set environment variables:
export DB_HOST=127.0.0.1
export DB_PORT=3306
export DB_NAME=workforcepro
export DB_USER=root
export DB_PASS=

# 4. Start the PHP development server
php -S localhost:8000

# 5. Open in browser
# http://localhost:8000/login.php
```

---

## ☁️ Cloud Deployment (Render)

WorkForce Pro ships with a `Dockerfile` for instant cloud deployment.

1. **Push** this repository to your GitHub account.
2. Go to **[Render Dashboard](https://dashboard.render.com/)** → **New → Web Service**
3. Connect your GitHub repo. Render auto-detects the `Dockerfile`.
4. Create a free MySQL database on **[Aiven](https://aiven.io/mysql)** or **[PlanetScale](https://planetscale.com/)**, then import `database.sql`.
5. Add **Environment Variables** in Render:

| Variable | Value |
|---|---|
| `DB_HOST` | Your cloud DB host |
| `DB_PORT` | Your cloud DB port |
| `DB_NAME` | `workforcepro` |
| `DB_USER` | Your DB user |
| `DB_PASS` | Your DB password |

6. Click **Deploy** — done!

> **Note:** Render's free tier uses ephemeral storage. For avatar uploads to persist, mount a **Persistent Disk** at `/var/www/html/uploads`.

---

## Interview Notes

WorkForce Pro was built to demonstrate professional, framework-free PHP engineering:

- **MVC-inspired architecture** cleanly separates Models, Controllers, Views, and AJAX endpoints without a framework.
- **`BaseModel`** provides a reusable PDO abstraction — insert, update, delete, bulk operations, toggle — so each model stays focused on business logic.
- **`Resource.js`** is a reusable JavaScript class that wires up a DataTable, modal form, and CRUD operations for any module in ~15 lines of config.
- **Server-side DataTables** support search, sort, pagination, and filters without loading all rows into the browser.
- **Security** is explicit and layered: prepared statements, CSRF, XSS escaping, secure sessions, bcrypt, upload validation, and route guards.
- **Cloud-ready** from day one: environment-variable driven configuration, a production `Dockerfile`, and deployment instructions.

---

## Maintenance

- DB credentials: set via environment variables (see `.env.example`)
- SQL backups: auto-generated to `database/backups/` via the Settings page
- Application logs: appended to `logs/app.log`
- `uploads/` must be writable by the web server (`chmod 755`)
