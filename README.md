# Task Management API (Laravel + MySQL)

Laravel Engineer Intern Take-Home Assignment implementation.

## Features

1. Create task
2. List tasks
3. Update task status
4. Delete task
5. Daily task report (bonus)

## Tech Stack

- PHP 8.3+
- Laravel 13
- MySQL

## Database Schema

Table: `tasks`

- `id` (bigint, primary key)
- `title` (string)
- `due_date` (date)
- `priority` (enum: `low`, `medium`, `high`)
- `status` (enum: `pending`, `in_progress`, `done`)
- `created_at`
- `updated_at`

Business-level uniqueness rule is also enforced at DB level with a unique index on `title + due_date`.

## API Endpoints

### 1) Create Task

- Method: `POST`
- URL: `/api/tasks`
- Rules:
	- `title` cannot duplicate another task with the same `due_date`
	- `priority` must be one of `low`, `medium`, `high`
	- `due_date` must be today or later

Example:

```bash
curl -X POST http://localhost:8000/api/tasks \
	-H "Content-Type: application/json" \
	-d '{
		"title": "Prepare API docs",
		"due_date": "2026-03-28",
		"priority": "high"
	}'
```

### 2) List Tasks

- Method: `GET`
- URL: `/api/tasks`
- Optional query param: `status`
- Rules:
	- Sort by priority (`high` -> `medium` -> `low`)
	- Then sort by `due_date` ascending
	- Returns meaningful JSON when no tasks exist

Examples:

```bash
curl http://localhost:8000/api/tasks
curl http://localhost:8000/api/tasks?status=in_progress
```

### 3) Update Task Status

- Method: `PATCH`
- URL: `/api/tasks/{id}/status`
- Rules:
	- Allowed progression only:
		- `pending` -> `in_progress`
		- `in_progress` -> `done`
	- Cannot skip or revert status

Example:

```bash
curl -X PATCH http://localhost:8000/api/tasks/1/status \
	-H "Content-Type: application/json" \
	-d '{"status":"in_progress"}'
```

### 4) Delete Task

- Method: `DELETE`
- URL: `/api/tasks/{id}`
- Rule:
	- Only tasks with `done` status can be deleted
	- Otherwise returns `403 Forbidden`

Example:

```bash
curl -X DELETE http://localhost:8000/api/tasks/1
```

### 5) Daily Report (Bonus)

- Method: `GET`
- URL: `/api/tasks/report?date=YYYY-MM-DD`
- Returns counts per priority and status for that date (`due_date`)

Example:

```bash
curl "http://localhost:8000/api/tasks/report?date=2026-03-28"
```

Sample response:

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

## Local Setup (MySQL)

1. Install dependencies:

```bash
composer install
```

2. Copy environment file:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure MySQL in `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_manager
DB_USERNAME=root
DB_PASSWORD=your_password
```

4. Create database and run migrations + seeders:

```bash
php artisan migrate --seed
```

5. Start server:

```bash
php artisan serve
```

## Running Tests

```bash
php artisan test
```

## Deploy Online (MySQL)

### Option A: Railway

1. Push this project to GitHub.
2. Create a new Railway project from the repo.
3. Add a MySQL service in Railway.
4. Set environment variables in Railway:
	 - `APP_ENV=production`
	 - `APP_DEBUG=false`
	 - `APP_KEY` (generate locally with `php artisan key:generate --show`)
	 - `DB_CONNECTION=mysql`
	 - `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from Railway MySQL service
5. Configure start command:

```bash
php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT}
```

6. Use Railway-provided public URL for API testing.

### Option B: Render

1. Push repo to GitHub.
2. Create a new Web Service on Render from the repo.
3. Create a MySQL database (Render or external managed MySQL).
4. Set environment variables (same as Railway).
5. Build command:

```bash
composer install --no-dev --optimize-autoloader
```

6. Start command:

```bash
php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT}
```

7. Test via Render URL.

## Notes

- Feature tests cover all required assignment rules.
- API routes are available under `/api/tasks`.
