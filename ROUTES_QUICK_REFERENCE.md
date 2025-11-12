# BrokerX Routes Quick Reference

## Middleware Usage

### Standard Authentication
```php
Route::middleware(['auth'])->group(function () {
    // Protected routes for Users/Brokers
});
```

### Scheduler Authentication
```php
Route::middleware(['scheduler.auth'])->group(function () {
    // Protected routes for Schedulers
});
```

### Permission-based
```php
Route::middleware(['auth', 'permission:user_view'])->group(function () {
    // Routes requiring specific permission
});
```

## Key Route Patterns

| Pattern | Example | Purpose |
|---------|---------|---------|
| `/` | Dashboard | Main dashboard for authenticated users |
| `/admin/*` | `/admin/users` | Admin panel routes |
| `/broker/*` | `/broker/create` | Broker management |
| `/appointments/*` | `/appointments/1` | Appointment CRUD |
| `/schedulers/*` | `/schedulers/login` | Scheduler system |
| `/themes/*` | `/themes/dashboard` | UI theme demo |

## Authentication Checks

### In Controllers
```php
// User/Broker
if (auth()->check()) {
    $user = auth()->user();
}

// Scheduler
if (Session::has('scheduler_id')) {
    $schedulerId = Session::get('scheduler_id');
}
```

### In Blade Views
```blade
{{-- User/Broker --}}
@auth
    <p>Welcome {{ auth()->user()->name }}</p>
@endauth

{{-- Scheduler --}}
@if(Session::has('scheduler_id'))
    <p>Scheduler logged in</p>
@endif
```

## Important Routes

### Public Routes
- `GET /schedulers/login` - Scheduler login page
- `GET /schedulers/register` - Scheduler registration
- `POST /schedulers/otp/send` - Send OTP to scheduler
- `POST /schedulers/otp/verify` - Verify scheduler OTP

### User/Broker Routes
- `GET /` - Main dashboard
- `GET /appointments` - View appointments
- `POST /appointments` - Create appointment
- `GET /broker/{id}` - View broker details

### Admin Routes
- `GET /admin/users` - User management
- `GET /admin/roles` - Role management
- `GET /admin/permissions` - Permission management
- `GET /admin/activity` - Activity log

### Scheduler Routes (Authenticated)
- `GET /schedulers` - Scheduler dashboard
- `GET /schedulers/appointments/json` - Calendar data
- `POST /schedulers/logout` - Logout scheduler

## Common Tasks

### Add New Protected Route (User/Broker)
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/new-feature', [FeatureController::class, 'index'])
        ->name('feature.index');
});
```

### Add New Scheduler Route
```php
Route::middleware(['scheduler.auth'])->group(function () {
    Route::get('/schedulers/new-feature', [SchedulerController::class, 'feature'])
        ->name('schedulers.feature');
});
```

### Add Permission-Protected Route
```php
Route::middleware(['auth', 'permission:feature_view'])->group(function () {
    Route::resource('features', FeatureController::class);
});
```

## Route Commands

```powershell
# List all routes
php artisan route:list

# Clear route cache
php artisan route:clear

# Cache routes (production)
php artisan route:cache

# Search for specific routes
php artisan route:list | Select-String -Pattern "scheduler"
```

## Debugging Tips

### Check Current Route
```php
// In controller
$currentRoute = Route::currentRouteName();
$currentUrl = url()->current();
```

### Check Authentication Status
```php
// User/Broker
dd(auth()->check(), auth()->user());

// Scheduler
dd(Session::has('scheduler_id'), Session::get('scheduler_id'));
```

### View Route Details
```powershell
# Get route by name
php artisan route:list --name=schedulers.index

# Get route by method
php artisan route:list --method=POST
```

## Security Notes

1. **Never expose scheduler session**: Don't output `scheduler_id` in responses
2. **Use CSRF protection**: All POST/PUT/DELETE routes have CSRF protection
3. **Validate permissions**: Always check permissions in controller constructors
4. **Sanitize inputs**: Use Form Requests for validation
5. **Rate limiting**: Consider adding rate limiting to OTP endpoints

## Common Errors & Solutions

| Error | Solution |
|-------|----------|
| Route [dashboard] not defined | Alias already exists at `/dashboard` |
| Route [second] not defined | Fixed - theme routes use original names (second, third, any) |
| Unauthenticated | Check middleware is applied correctly |
| Scheduler not logged in | Verify `scheduler_id` in session |

## File Locations

- **Routes**: `routes/web.php`
- **Auth Routes**: `routes/auth.php`
- **Scheduler Middleware**: `app/Http/Middleware/SchedulerAuth.php`
- **Middleware Config**: `bootstrap/app.php`
- **Controllers**: `app/Http/Controllers/`
