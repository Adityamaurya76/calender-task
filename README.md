# Laravel Calendar Task

**Project:** A simple Laravel application for creating and managing tasks on a calendar-style UI.

**Short description:** This repo provides a Laravel backend and minimal frontend for creating, viewing, and updating tasks that are displayed on a calendar. It includes models, migrations, simple views, and JavaScript for client interactions.

**Requirements:**
- PHP 8.1+ (or the version supported by this Laravel install)
- Composer
- Node.js 16+ and npm (for frontend build)
- A database supported by Laravel (MySQL, MariaDB, PostgreSQL, SQLite)

**Quick Setup**

1. Install PHP dependencies:

```bash
composer install
```

2. Install Node dependencies and build assets (development):

```bash
npm install
npm run dev
```

3. Copy the `.env` file and configure environment values (database, app key, etc.):

```bash
cp .env.example .env
php artisan key:generate
# Edit .env to set DB_* values
```

4. Run migrations and (optional) seeders:

```bash
php artisan migrate
php artisan db:seed
```

5. Serve the application locally:

```bash
php artisan serve
# Visit http://127.0.0.1:8000
```

**Database**
- Migrations are in the `database/migrations` folder. The tasks table migration is `2025_11_23_130349_create_tasks_table.php`.
- The `app/Models/Task.php` model represents tasks.

**Testing**
- Run PHPUnit tests:

```bash
./vendor/bin/phpunit
```

**Frontend**
- Minimal frontend assets live in `resources/js` and `resources/views`.
- The calendar views are in `resources/views/calender.blade.php`, `resources/views/home.blade.php`, and `resources/views/task-modal.blade.php`.
- Frontend interactivity for tasks is implemented in `public/js/tasks.js` and `resources/js/app.js` (see `vite.config.js` for bundling).

**Key Files & Locations**
- `routes/web.php` — application routes and endpoints.
- `app/Http/Controllers/` — controllers handling HTTP requests.
- `app/Models/Task.php` — Task Eloquent model.
- `database/migrations/2025_11_23_130349_create_tasks_table.php` — migration for tasks.
- `resources/views/` — Blade templates for the calendar and modals.
- `public/js/tasks.js` — client-side calendar task logic.

**Features**
- Create, edit and delete tasks.
- Tasks appear on a calendar UI.
- Simple modal for task creation and update.

**Development Notes & Recommendations**
- Configure caching, queueing, and mail settings in `.env` for production.
- Consider using Laravel Sanctum or auth scaffolding if adding user-specific calendars.
- For richer calendar UI, integrate a library like FullCalendar (already compatible with existing endpoints).

**Common Commands**

```bash
# Install PHP deps
composer install

# Install JS deps and build
npm install
npm run build      # production build

# Run migrations
php artisan migrate

# Run tests
./vendor/bin/phpunit

# Serve locally
php artisan serve
```
