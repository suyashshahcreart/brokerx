# BrokerX Routes Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                      BrokerX Application                        │
│                     Dual Authentication System                   │
└─────────────────────────────────────────────────────────────────┘
                                │
                ┌───────────────┴───────────────┐
                │                               │
        ┌───────▼────────┐            ┌────────▼────────┐
        │ Standard Auth  │            │ Scheduler Auth  │
        │  (Laravel)     │            │  (Session-OTP)  │
        └───────┬────────┘            └────────┬────────┘
                │                               │
    ┌───────────┴───────────┐       ┌──────────┴─────────┐
    │   auth() middleware   │       │ scheduler.auth     │
    │   Password-based      │       │ Session: scheduler_id│
    └───────────┬───────────┘       └──────────┬─────────┘
                │                               │
    ┌───────────▼──────────────────┐  ┌────────▼─────────┐
    │ Users/Brokers Routes         │  │ Scheduler Routes │
    │ ├─ Dashboard (/)             │  │ ├─ Dashboard     │
    │ ├─ OTP Verification          │  │ ├─ Profile       │
    │ ├─ Profile Management        │  │ ├─ Appointments  │
    │ ├─ Broker CRUD               │  │ └─ Logout        │
    │ ├─ Appointments CRUD         │  └──────────────────┘
    │ ├─ Admin Panel               │
    │ │  ├─ Permissions            │
    │ │  ├─ Roles                  │
    │ │  ├─ Users                  │
    │ │  └─ Activity Log           │
    │ ├─ BrokerX Module            │
    │ └─ Theme Demo                │
    └──────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        Public Routes                            │
│ (No Authentication Required)                                    │
│ ├─ Scheduler Register                                           │
│ ├─ Scheduler Login                                              │
│ ├─ Scheduler OTP Send/Verify                                    │
│ └─ Standard Login/Register (via auth.php)                       │
└─────────────────────────────────────────────────────────────────┘
```

## Route Flow Diagram

```
┌──────────────────────────────────────────────────────────────────────┐
│                        Request Flow                                  │
└──────────────────────────────────────────────────────────────────────┘

User Request
     │
     ├─ Public Routes (/schedulers/login, /schedulers/register)
     │       │
     │       └─> No Middleware ──> SchedulerController
     │
     ├─ Standard Routes (/, /admin/*, /broker/*, /appointments/*)
     │       │
     │       └─> 'auth' Middleware ──> Check auth()->check()
     │                │
     │                ├─ Authenticated ──> Controller
     │                └─ Not Authenticated ──> Redirect to /login
     │
     └─ Scheduler Routes (/schedulers/*, /schedulers/appointments/*)
             │
             └─> 'scheduler.auth' Middleware ──> Check Session::has('scheduler_id')
                      │
                      ├─ Authenticated ──> Controller
                      └─ Not Authenticated ──> Redirect to /schedulers/login
```

## Middleware Pipeline

```
┌─────────────────────────────────────────────────────────────────┐
│                   Middleware Aliases                            │
└─────────────────────────────────────────────────────────────────┘

auth ──────────────────────> Authenticate::class (Laravel)
role ──────────────────────> RoleMiddleware::class (Spatie)
permission ────────────────> PermissionMiddleware::class (Spatie)
role_or_permission ────────> RoleOrPermissionMiddleware::class (Spatie)
scheduler.auth ────────────> SchedulerAuth::class (Custom)
```

## Authentication Check Methods

```php
┌─────────────────────────────────────────────────────────────────┐
│                 Authentication Checks                           │
└─────────────────────────────────────────────────────────────────┘

// Standard User/Broker
if (auth()->check()) {
    $user = auth()->user();
    // Access user properties
    $user->name;
    $user->email;
    $user->hasRole('admin');
}

// Scheduler
if (Session::has('scheduler_id')) {
    $schedulerId = Session::get('scheduler_id');
    $scheduler = Scheduler::find($schedulerId);
    // Access scheduler properties
    $scheduler->name;
    $scheduler->mobile;
}
```

## Route Group Structure

```
web.php
│
├─ require auth.php                    [Laravel Breeze Auth Routes]
│
├─ Scheduler Public Routes             [prefix: schedulers, name: schedulers.*]
│  ├─ OTP Send/Verify                  [POST]
│  ├─ Register                         [GET, POST]
│  └─ Login                            [GET, POST]
│
├─ Standard Auth Routes                [middleware: auth]
│  ├─ Dashboard                        [GET /]
│  ├─ OTP Routes                       [prefix: otp, email-otp]
│  ├─ User Profile                     [PUT /user/{id}]
│  ├─ Broker Management                [resource: broker]
│  ├─ Appointments                     [resource: appointments]
│  ├─ Admin Routes                     [prefix: admin, name: admin.*]
│  │  ├─ Permissions                   [resource: permissions]
│  │  ├─ Roles                         [resource: roles]
│  │  ├─ Users                         [resource: users]
│  │  └─ Activity Log                  [GET activity]
│  ├─ BrokerX Module                   [prefix: brokerx, name: brokerx.*]
│  └─ Theme Demo                       [prefix: themes, name: themes.*]
│
└─ Scheduler Auth Routes               [middleware: scheduler.auth, prefix: schedulers]
   ├─ Dashboard & CRUD                 [resource routes]
   ├─ Appointments JSON                [GET appointments/json]
   └─ Logout                           [POST logout]
```

## Security Model

```
┌─────────────────────────────────────────────────────────────────┐
│                     Security Layers                             │
└─────────────────────────────────────────────────────────────────┘

1. Web Middleware (CSRF, Session, Cookies) ─ Applied to all routes
2. RewriteImagePaths ────────────────────── Applied to web group
3. Authentication Middleware ────────────── 'auth' or 'scheduler.auth'
4. Permission Middleware ────────────────── 'permission', 'role' (optional)
5. Controller Logic ─────────────────────── Additional validation

Example:
  Request ──> Web ──> RewriteImagePaths ──> auth ──> permission:user_view
           ──> Controller ──> Response
```

## Data Flow

```
┌─────────────────────────────────────────────────────────────────┐
│            User vs Scheduler Data Flow                          │
└─────────────────────────────────────────────────────────────────┘

USER/BROKER:
Login ──> Auth::attempt() ──> Session + auth()->user()
      ──> Access via auth()->user()
      ──> Check permissions: $user->hasPermissionTo('permission_name')

SCHEDULER:
Login ──> OTP Verify ──> Session::put('scheduler_id', $id)
      ──> Access via Session::get('scheduler_id')
      ──> No permission system (mobile-based simple auth)
```

## Route Testing Commands

```powershell
# View all routes
php artisan route:list

# View specific routes
php artisan route:list | Select-String -Pattern "scheduler"
php artisan route:list | Select-String -Pattern "admin"

# Clear route cache
php artisan route:clear

# Cache routes
php artisan route:cache

# Count total routes
php artisan route:list | Measure-Object -Line
```
