# Task Management API

A RESTful API for managing tasks with priority levels, due dates, and status tracking. Built with Laravel 13 and MySQL.

**Status**: ✅ Complete with all required features and bonus functionality

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Running Tests](#running-tests)
- [Deployment](#deployment)
- [Project Structure](#project-structure)
- [Notes](#notes)

## Features

0. ✅ **Authentication & Authorization** - Session login/register with per-user task access control
1. ✅ **Create Task** - Add new tasks with title, due date, and priority
2. ✅ **List Tasks** - Retrieve tasks with optional status filtering, sorted by priority and due date
3. ✅ **Update Task Status** - Progress tasks through defined state transitions (pending → in_progress → done)
4. ✅ **Delete Task** - Remove completed tasks with status protection
5. ✅ **Daily Report** (Bonus) - Generate summary reports of tasks grouped by priority and status for specific dates

## Tech Stack

- **Framework**: Laravel 13
- **Language**: PHP 8.3+
- **Database**: MySQL 8.0+
- **Build Tools**: Vite

## Requirements

- PHP 8.3 or higher
- Composer
- MySQL 8.0 or higher
- Node.js 18+ (for Vite)
- Git

## Database Schema

### Table: `tasks`

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | `bigint unsigned` | PRIMARY KEY, AUTO_INCREMENT | Unique task identifier |
| `title` | `varchar(255)` | NOT NULL | Task title/description |
| `due_date` | `date` | NOT NULL | Expected completion date |
| `priority` | `enum('low','medium','high')` | NOT NULL | Task priority level |
| `status` | `enum('pending','in_progress','done')` | NOT NULL, DEFAULT 'pending' | Current task status |
| `created_at` | `timestamp` | NULL | Creation timestamp |
| `updated_at` | `timestamp` | NULL | Last update timestamp |

**Unique Constraint**: `title` + `due_date` (prevents duplicate tasks on the same date)

## API Endpoints

All endpoints require `Content-Type: application/json` headers where applicable.

### 1. Create Task

**Endpoint**: `POST /api/tasks`

**Request Body**:
```json
{
  "title": "Task title (required)",
  "due_date": "YYYY-MM-DD (required, must be today or later)",
  "priority": "low|medium|high (required)"
}
```

**Validation Rules**:
- `title` cannot duplicate another task with the same `due_date`
- `priority` must be one of: `low`, `medium`, `high`
- `due_date` must be today or later

**Example:**

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Prepare API documentation",
    "due_date": "2026-04-15",
    "priority": "high"
  }'
```

---

### 2. List Tasks

**Endpoint**: `GET /api/tasks`

**Query Parameters**:
- `status` (optional): Filter by `pending`, `in_progress`, or `done`

**Response Sorting**:
- Primary: By priority (`high` → `medium` → `low`)
- Secondary: By `due_date` (ascending)

**Examples:**

```bash
# List all tasks
curl http://localhost:8000/api/tasks

# Filter by status
curl http://localhost:8000/api/tasks?status=in_progress
```

---

### 3. Update Task Status

**Endpoint**: `PATCH /api/tasks/{id}/status`

**Request Body**:
```json
{
  "status": "pending|in_progress|done (required)"
}
```

**Status Transitions** (only these are allowed):
- `pending` → `in_progress`
- `in_progress` → `done`

Cannot skip or revert status changes.

**Example:**

```bash
curl -X PATCH http://localhost:8000/api/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'
```

---

### 4. Delete Task

**Endpoint**: `DELETE /api/tasks/{id}`

**Restrictions**:
- Only tasks with `done` status can be deleted
- Returns `403 Forbidden` for non-completed tasks

**Example:**

```bash
curl -X DELETE http://localhost:8000/api/tasks/1
```

---

### 5. Daily Report (Bonus)

**Endpoint**: `GET /api/tasks/report`

**Query Parameters**:
- `date` (required): Report date in `YYYY-MM-DD` format

**Returns**: Task counts grouped by priority and status for tasks due on that date.

**Response Format**:
```json
{
  "date": "2026-03-28",
  "summary": {
    "high": {"pending": 2, "in_progress": 1, "done": 0},
    "medium": {"pending": 1, "in_progress": 0, "done": 3},
    "low": {"pending": 0, "in_progress": 0, "done": 1}
  }
}
```

**Example:**

```bash
curl "http://localhost:8000/api/tasks/report?date=2026-03-28"
```

## Installation

### 1. Clone & Setup Dependencies

```bash
git clone <repository-url>
cd <project-directory>
composer install
npm install  # Optional: if using JS assets
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Database Configuration

Edit `.env` and configure your MySQL connection:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 4. Setup Database

```bash
php artisan migrate --seed
```

This will:
- Create all necessary tables (users, tasks, cache, jobs, migrations)
- Run database seeders to populate sample data

### 5. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api/tasks`

## Authentication

- Protected routes now require an authenticated session.
- Register at `/register` or use seeded credentials:
  - Email: `demo@example.com`
  - Password: `password123`

## Running Tests

Execute the test suite with PHPUnit:

```bash
php artisan test
```

**Test Coverage**:
- Feature tests for all API endpoints
- Validation rule testing
- Status transition constraints
- Error handling and edge cases

## Deployment

### Deploy to Railway (Recommended)

Railway simplifies deployment with automatic scaling and data persistence.

#### Steps:

1. **Push to GitHub**:
   ```bash
   git add .
   git commit -m "Ready for deployment"
   git push origin main
   ```

2. **Create Railway Project**:
   - Go to [Railway.app](https://railway.app)
   - Create new project from GitHub repo

3. **Add MySQL Service**:
   - Add MySQL service from Railway marketplace
   - Note the database credentials

4. **Configure Environment Variables**:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_KEY` (run `php artisan key:generate --show` locally)
   - Database variables:
     - `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` from Railway MySQL
     - `DB_DATABASE=task_management`

5. **Set Start Command**:
   ```bash
   php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT}
   ```

6. **Deploy & Test**:
   - Railway will deploy automatically
   - Use the provided Railway URL for API testing
   - Example: `https://your-railway-app.up.railway.app/api/tasks`

---

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/          # API controllers
│   │   └── Requests/             # Form request validation
│   └── Models/
│       ├── Task.php              # Task model
│       └── User.php              # User model
├── config/                        # Configuration files
├── database/
│   ├── migrations/               # Database migrations
│   ├── seeders/                  # Database seeders
│   └── task_management_dump.sql  # Database snapshot
├── routes/
│   ├── api.php                   # API routes
│   ├── web.php                   # Web routes
│   └── console.php               # Console commands
├── tests/
│   ├── Feature/                  # Feature tests
│   │   └── TaskApiTest.php       # Task API tests
│   └── Unit/                     # Unit tests
├── .env.example                  # Environment template
├── composer.json                 # PHP dependencies
└── README.md                      # This file
```

## Notes

- ✅ All required features implemented and tested
- ✅ Bonus daily report endpoint included
- ✅ Database-level constraints enforce business rules
- ✅ Comprehensive feature tests cover all requirements
- ✅ API uses RESTful principles
- ✅ Proper HTTP status codes and error responses
- 🔒 Data validation at both application and database levels
- 📝 All routes use the `/api` prefix for organization
