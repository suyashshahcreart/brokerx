# Scheduler Middleware Implementation

## Overview
This document describes the implementation of the `SchedulerAuth` middleware and the reorganization of the `web.php` routes file for better structure and maintainability.

## Changes Made

### 1. Created Scheduler Authentication Middleware
**File**: `app/Http/Middleware/SchedulerAuth.php`

The middleware protects routes that require scheduler authentication. Key features:
- Checks for `scheduler_id` in session (session-based auth, not Laravel's standard auth)
- Redirects unauthenticated users to scheduler login page
- Returns JSON response for API requests
- Provides appropriate error messages

**Usage Pattern**:
```php
Route::middleware(['scheduler.auth'])->group(function () {
    // Protected scheduler routes
});
```

### 2. Registered Middleware Alias
**File**: `bootstrap/app.php`

Added `scheduler.auth` alias to the middleware configuration:
```php
'scheduler.auth' => \App\Http\Middleware\SchedulerAuth::class,
```

This allows using the middleware as `scheduler.auth` in routes instead of the full class name.

### 3. Reorganized Routes Structure
**File**: `routes/web.php`

Completely restructured the routes file with:

#### A. Clear Section Comments
- Authentication Routes
- Public Routes
- Standard User/Broker Routes (Laravel Auth)
- Admin Routes
- BrokerX Module Routes
- Theme Demo Routes
- Scheduler Routes (Session-based Auth)

#### B. Logical Route Grouping
All routes are now properly grouped by:
- Authentication type (public, auth, scheduler.auth)
- Functionality (admin, broker, appointments)
- Prefix and naming conventions

#### C. Consistent Naming Conventions
- All route names follow Laravel best practices
- Prefixes match route group purposes
- Clear separation between user and scheduler routes

## Route Structure

### Public Routes (No Authentication)
```
POST   schedulers/otp/send           - schedulers.otp.send
POST   schedulers/otp/verify         - schedulers.otp.verify
GET    schedulers/register           - schedulers.register
POST   schedulers/register           - schedulers.register.store
GET    schedulers/login              - schedulers.login
POST   schedulers/login              - schedulers.login.attempt
```

### Standard Auth Routes (Users/Brokers)
```
GET    /                             - root (Dashboard)
GET    /dashboard                    - dashboard
POST   /otp/send                     - otp.send
POST   /otp/verify                   - otp.verify
POST   /email-otp/send               - email_otp.send
POST   /email-otp/verify             - email_otp.verify
PUT    /user/{id}                    - user.update
CRUD   /broker                       - broker.*
CRUD   /appointments                 - appointments.*
GET    /appointments/json            - appointments.json
```

### Admin Routes (Standard Auth)
```
CRUD   /admin/permissions            - admin.permissions.*
CRUD   /admin/roles                  - admin.roles.*
CRUD   /admin/users                  - admin.users.*
GET    /admin/activity               - admin.activity.index
```

### BrokerX Routes (Standard Auth)
```
GET    /brokerx                      - brokerx.index
```

### Theme Routes (Standard Auth)
```
GET    /themes/{any}                 - any
GET    /themes/{first}/{second}      - second
GET    /themes/{first}/{second}/{third} - third
```

### Scheduler Routes (Scheduler Auth)
```
GET    /schedulers                   - schedulers.index
GET    /schedulers/create            - schedulers.create
POST   /schedulers                   - schedulers.store
GET    /schedulers/{scheduler}       - schedulers.show
GET    /schedulers/{scheduler}/edit  - schedulers.edit
PUT    /schedulers/{scheduler}       - schedulers.update
DELETE /schedulers/{scheduler}       - schedulers.destroy
GET    /schedulers/appointments/json - schedulers.appointments.json
POST   /schedulers/logout            - schedulers.logout
```

## Dual Authentication System

### Standard Authentication
- Uses Laravel Breeze
- For Users and Brokers
- Password-based login
- Check: `auth()->user()`
- Middleware: `auth`

### Scheduler Authentication
- Session-based (OTP)
- No password field
- Mobile-first approach
- Check: `Session::get('scheduler_id')`
- Middleware: `scheduler.auth`

## Benefits of Changes

1. **Clear Separation of Concerns**: Each authentication system has its own clearly defined routes
2. **Better Maintainability**: Well-organized structure makes it easy to find and modify routes
3. **Consistent Middleware Usage**: All scheduler routes now properly protected
4. **Comprehensive Documentation**: Inline comments explain each section
5. **Scalability**: Easy to add new route groups as the application grows

## Testing

After implementation:
1. ✅ Route cache cleared successfully
2. ✅ All 83 routes properly registered
3. ✅ Middleware aliases working correctly
4. ✅ Route naming conventions consistent

## Next Steps

To use the scheduler middleware in your controllers:
```php
public function __construct()
{
    $this->middleware('scheduler.auth')->except(['login', 'register']);
}
```

Or protect individual methods:
```php
public function __construct()
{
    $this->middleware('scheduler.auth')->only(['index', 'store', 'update']);
}
```

## Related Files
- `app/Http/Middleware/SchedulerAuth.php` - Middleware class
- `bootstrap/app.php` - Middleware registration
- `routes/web.php` - Route definitions
- `app/Http/Controllers/SchedulerController.php` - Scheduler controller
