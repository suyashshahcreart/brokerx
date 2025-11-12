# BrokerX - AI Coding Agent Instructions

## Project Overview
BrokerX is a Laravel 11 real estate broker management platform with dual authentication systems (standard users/brokers and schedulers), featuring appointment scheduling, role-based permissions, and a rich UI dashboard.

## Architecture

### Core Models & Relationships
- **User**: Standard auth with Spatie roles/permissions (`HasRoles`, `LogsActivity`)
- **Broker**: One-to-one with User, stores professional details (license, commission, ratings)
- **Scheduler**: Separate mobile-based auth system (Session-based, no password), books appointments
- **Appointment**: Created by Schedulers, assigned to Users/Brokers, tracks status workflow

### Dual Authentication Pattern
Two independent auth systems coexist:
1. **Standard Auth** (`routes/auth.php`): Uses Laravel Breeze for Users/Brokers
   - Middleware: `auth`
   - Check: `auth()->check()`, `auth()->user()`
   - Routes: Protected by `auth` middleware
2. **Scheduler Auth** (`SchedulerController`): OTP-based, session-stored (`scheduler_id`), no password field
   - Middleware: `scheduler.auth` (custom middleware in `app/Http/Middleware/SchedulerAuth.php`)
   - Check: `Session::has('scheduler_id')`, `Session::get('scheduler_id')`
   - Routes: Protected by `scheduler.auth` middleware

When adding auth checks: `Session::get('scheduler_id')` for schedulers, `auth()->user()` for standard users.

### Middleware System
Registered aliases in `bootstrap/app.php`:
- `auth` - Standard Laravel authentication
- `permission` - Spatie permission check
- `role` - Spatie role check
- `role_or_permission` - Spatie role or permission check
- `scheduler.auth` - Custom scheduler authentication (checks for `scheduler_id` in session)

Apply in routes:
```php
Route::middleware(['auth'])->group(function () { /* User routes */ });
Route::middleware(['scheduler.auth'])->group(function () { /* Scheduler routes */ });
```

### Permission System
Uses Spatie Laravel Permission package:
- Models: `App\Models\Permission`, `App\Models\Role`
- Middleware aliases: `permission`, `role`, `role_or_permission` (defined in `bootstrap/app.php`)
- Apply to controllers: `$this->middleware('permission:permission_view')->only(['index'])`
- All permissions seeded in `PermissionsRolesSeeder` - update here when adding new features

## Key Conventions

### DataTables Pattern
Admin controllers use Yajra DataTables for AJAX listings:
```php
if ($request->ajax()) {
    return DataTables::of($query)
        ->addColumn('actions', fn($model) => view('admin.users.partials.actions', compact('model'))->render())
        ->rawColumns(['actions'])
        ->toJson();
}
```
Always create corresponding `partials/actions.blade.php` for row actions.

### View Layouts
- Primary layout: `layouts.vertical` (authenticated pages)
- Auth layout: `layouts.auth` (login/register)
- Pass title/subtitle: `@extends('layouts.vertical', ['title' => 'Page Title', 'subTitle' => 'Section'])`

### Asset Handling for Subdirectory Deployment
- `vite.config.js` has `base: '/lahomes/'` for subfolder deployment
- Custom `RewriteImagePaths` middleware rewrites `/images/` paths to use `asset('images')` helper
- Always use Laravel asset helpers: `asset('images/logo.png')`, never hardcode paths

### OTP Flow Pattern
See `OtpController`, `EmailOtpController`, `SchedulerController::sendOtp/verifyOtp`:
1. Generate 6-digit OTP, cache with `Cache::put("otp_{type}_{identifier}", $otp, 300)` (5 min TTL)
2. Send via Mail/SMS
3. Verify by comparing cached value
4. Update `mobile_verified_at` or `email_verified_at` timestamp

### Activity Logging
User model uses `LogsActivity` trait from Spatie. Automatic logging on model changes. View logs at `admin.activity.index`.

## Development Workflow

### Setup Commands (PowerShell)
```powershell
composer install --prefer-dist --no-interaction
Copy-Item .env.example .env -Force
php artisan key:generate
php artisan migrate --seed  # Includes PermissionsRolesSeeder
php artisan storage:link
npm install ; npm run dev
php artisan serve
```

### Frontend Build System
- **Vite** with Laravel plugin (`vite.config.js`)
- SCSS entry: `resources/scss/app.scss`, `resources/scss/icons.scss`
- JS entries: Extensive list in `vite.config.js` (dashboard, calendar, forms, charts)
- Dev: `npm run dev` (hot reload), Production: `npm run build`
- **Critical**: Node packages must be installed and built before running app

### Testing
- Run: `php artisan test` or `./vendor/bin/phpunit`
- Config: `phpunit.xml` uses array cache/session, sync queue driver for speed
- Test structure: `tests/Feature/`, `tests/Unit/`

## Common Tasks

### Adding New Permissions
1. Add to `$permissions` array in `database/seeders/PermissionsRolesSeeder.php`
2. Assign to roles in same seeder
3. Run: `php artisan db:seed --class=PermissionsRolesSeeder`
4. Apply middleware in controller: `$this->middleware('permission:new_permission')`

### Creating CRUD with DataTables
1. Controller: Extend pattern from `Admin\PermissionController`
2. Routes: `Route::resource('items', ItemController::class)->middleware('auth')`
3. Views: `admin/items/index.blade.php` with DataTable, `partials/actions.blade.php`
4. JS: Initialize DataTable with AJAX source to `route('items.index')`

### Working with Appointments
- Calendar JSON endpoint: `schedulers.appointments.json` returns FullCalendar format
- Status enum: `pending`, `confirmed`, `cancelled`, `completed`
- Multiple user tracking: `assigne_by`, `assigne_to`, `completed_by`, `updated_by`, `create_by` (scheduler)

## Dependencies
- **Backend**: Laravel 11, Spatie Permission, Spatie Activity Log, Yajra DataTables
- **Frontend**: Bootstrap 5, jQuery, FullCalendar, ApexCharts, DataTables, Flatpickr, Quill, Choices.js
- **Build**: Vite, Sass

## Gotchas
- Schedulers use session-based auth, NOT `auth()->user()` - check with `Session::get('scheduler_id')`
- Asset paths must use `asset()` helper due to subdirectory deployment middleware
- Always run `npm run build` before committing frontend changes
- Permission middleware checks happen in controller constructors, not routes
- `Property` model referenced in `Appointment` but table not yet created (commented in migration)
