# SMS Logging System - Usage Guide

## Overview

All SMS sends are automatically logged to the `sms_logs` table with complete details including gateway, status, response, and more.

## Database Table: `sms_logs`

The table stores:
- **Gateway**: SMS gateway name (MSG91, Twilio, etc.)
- **Type**: manual, cron, scheduled, etc.
- **Template**: Template key and ID
- **Mobile**: Recipient mobile number
- **Status**: pending, sent, failed, delivered
- **Response**: Full gateway response
- **Reference**: Related model (Booking, User, etc.)
- **Timestamps**: sent_at, delivered_at

## Usage Examples

### 1. Manual SMS Send (from Controller/Service)

```php
use App\Services\SmsService;

$smsService = app(SmsService::class);

// Basic send (automatically logged as 'manual')
$response = $smsService->send(
    '919876543210',
    'login_otp',
    ['OTP' => '123456']
);

// With reference to a model (e.g., Booking)
$response = $smsService->send(
    $booking->user->mobile,
    'appointment_scheduled',
    ['DATE' => $booking->date->format('d-M-Y')],
    [
        'type' => 'manual',
        'reference_type' => \App\Models\Booking::class,
        'reference_id' => $booking->id,
        'notes' => 'Appointment confirmation SMS'
    ]
);
```

### 2. Cron Job SMS Send

```php
use App\Services\SmsService;
use App\Models\Booking;

// In your scheduled command or cron job
public function handle()
{
    $smsService = app(SmsService::class);
    
    // Get bookings that need reminder
    $bookings = Booking::where('date', today()->addDay())
        ->where('status', 'confirmed')
        ->get();
    
    foreach ($bookings as $booking) {
        $smsService->send(
            $booking->user->mobile,
            'photographer_visit',
            ['DATE' => $booking->date->format('d-M-Y')],
            [
                'type' => 'cron',
                'reference_type' => \App\Models\Booking::class,
                'reference_id' => $booking->id,
                'notes' => 'Automated reminder SMS'
            ]
        );
    }
}
```

### 3. Scheduled SMS Send

```php
use App\Services\SmsService;

$smsService = app(SmsService::class);

$smsService->send(
    $user->mobile,
    'work_completed_review',
    ['NAME' => $user->name],
    [
        'type' => 'scheduled',
        'reference_type' => \App\Models\User::class,
        'reference_id' => $user->id,
        'notes' => 'Scheduled review request'
    ]
);
```

## Querying SMS Logs

### Get All Logs

```php
use App\Models\SmsLog;

$logs = SmsLog::latest()->paginate(20);
```

### Filter by Status

```php
// Successful SMS
$successful = SmsLog::successful()->get();

// Failed SMS
$failed = SmsLog::failed()->get();

// Specific status
$pending = SmsLog::status('pending')->get();
```

### Filter by Type

```php
// Cron job SMS
$cronSms = SmsLog::type('cron')->get();

// Manual SMS
$manualSms = SmsLog::type('manual')->get();
```

### Filter by Gateway

```php
$msg91Logs = SmsLog::gateway('MSG91')->get();
```

### Filter by Reference

```php
// Get SMS logs for a specific booking
$bookingSms = SmsLog::where('reference_type', \App\Models\Booking::class)
    ->where('reference_id', $bookingId)
    ->get();
```

### Recent SMS

```php
// Last 7 days
$recent = SmsLog::recent(7)->get();

// Last 30 days
$monthly = SmsLog::recent(30)->get();
```

### Complex Queries

```php
// Failed MSG91 SMS in last 24 hours
$failedMsg91 = SmsLog::gateway('MSG91')
    ->failed()
    ->where('created_at', '>=', now()->subDay())
    ->get();

// Successful cron SMS for bookings
$successfulCron = SmsLog::type('cron')
    ->successful()
    ->where('reference_type', \App\Models\Booking::class)
    ->get();
```

## Log Fields

| Field | Description | Example |
|-------|-------------|---------|
| `gateway` | SMS Gateway name | MSG91 |
| `type` | Send type | manual, cron, scheduled |
| `template_key` | Template identifier | login_otp |
| `template_id` | Gateway template ID | 692962755f7a7c3df13a38b3 |
| `mobile` | Recipient number | 919876543210 |
| `params` | Template parameters | ['OTP' => '123456'] |
| `status` | Current status | pending, sent, failed, delivered |
| `success` | Success flag | true/false |
| `status_code` | HTTP status code | 200 |
| `response_body` | Full gateway response | Raw JSON string |
| `response_json` | Parsed response | Array |
| `error_message` | Error if failed | Error description |
| `gateway_message_id` | Gateway's message ID | MSG91 message ID |
| `cost` | SMS cost | 0.05 |
| `sender_id` | Sender ID used | PROPPK |
| `user_id` | User who triggered | User ID |
| `reference_type` | Related model class | App\Models\Booking |
| `reference_id` | Related model ID | Booking ID |
| `notes` | Additional notes | Custom notes |
| `sent_at` | When SMS was sent | Timestamp |
| `delivered_at` | When SMS was delivered | Timestamp |

## Automatic Logging

All SMS sends through `SmsService` are automatically logged:
- ✅ Manual sends (from controllers/services)
- ✅ Cron job sends (automated)
- ✅ Scheduled sends
- ✅ Any SMS sent through the service

No additional code needed - logging happens automatically!

