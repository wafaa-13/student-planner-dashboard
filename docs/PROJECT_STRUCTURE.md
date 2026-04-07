# Student Performance & Study Planner – Beginner Project Plan

## 1) How a PHP + MySQL project is organized

A simple PHP + MySQL app is usually split into a few clear parts:

- **Configuration**: database connection settings.
- **Pages**: PHP files users open in the browser.
- **Reusable includes**: shared header/footer/navigation to avoid repeating code.
- **Assets**: CSS and JavaScript files.
- **Database scripts**: SQL files to create tables.

## 2) Beginner-friendly folder structure

- `config/` → app configuration (like DB connection)
- `public/` → main web pages (entry points)
- `includes/` → reusable components (header/footer)
- `assets/css/` → stylesheets
- `assets/js/` → JavaScript (optional now, useful later)
- `database/` → SQL schema/migration files
- `docs/` → planning notes and architecture docs

## 3) Exact files to create

- `config/db.php`
- `public/index.php`
- `public/dashboard.php`
- `public/assignments.php`
- `public/add-assignment.php`
- `includes/header.php`
- `includes/footer.php`
- `assets/css/style.css`
- `assets/js/app.js`
- `database/schema.sql`

## 4) Database planning (simple + scalable)

### A) `assignments` table

Purpose: store assignments/tasks students need to finish.

Suggested columns:

- `id` (primary key)
- `title` (assignment name)
- `subject` (Math, English, etc.)
- `description` (optional details)
- `due_date` (deadline)
- `status` (pending/in_progress/completed)
- `priority` (low/medium/high)
- `estimated_hours` (optional planning value)
- `created_at`, `updated_at` (timestamps)

Why this works:

- Easy for beginners.
- Supports dashboard filtering (status/priority/date).
- Can grow later without breaking basics.

### B) `gpa_records` table (future GPA feature)

Purpose: keep a simple history of GPA/term performance.

Suggested columns:

- `id` (primary key)
- `term_name` (e.g., "Fall 2026")
- `credits_attempted`
- `credits_earned`
- `gpa`
- `notes` (optional)
- `recorded_at` (when this GPA entry was stored)

Why this works:

- Keeps GPA history by term.
- Leaves room to add detailed course-level tables later.
