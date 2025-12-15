# Booking System - Complete Documentation

## Overview
This document provides comprehensive documentation for the booking lifecycle management system, including booking history tracking, SMS notifications, and admin Quick Actions.

---

## Table of Contents
1. [Booking Status Lifecycle](#booking-status-lifecycle)
2. [Booking History System](#booking-history-system)
3. [SMS Notification System](#sms-notification-system)
4. [Admin Quick Actions](#admin-quick-actions)
5. [Customer Dashboard](#customer-dashboard)
6. [Database Schema](#database-schema)
7. [API Endpoints](#api-endpoints)
8. [Configuration](#configuration)

---

## Booking Status Lifecycle

### Complete Status List (19 Statuses)

| Status | Description | Color | Use Case |
|--------|-------------|-------|----------|
| `inquiry` | Initial inquiry stage | Blue (info) | Customer showed interest |
| `pending` | Awaiting payment | Yellow (warning) | Booking created, payment pending |
| `confirmed` | Payment received | Green (success) | Payment successful |
| `schedul_pending` | Schedule request pending | Yellow (warning) | Customer requested schedule |
| `schedul_accepted` | Schedule approved | Green (success) | Admin accepted schedule |
| `schedul_decline` | Schedule declined | Red (danger) | Admin declined schedule |
| `reschedul_pending` | Reschedule request pending | Yellow (warning) | Customer requested reschedule |
| `reschedul_accepted` | Reschedule approved | Green (success) | Admin accepted reschedule |
| `reschedul_decline` | Reschedule declined | Red (danger) | Admin declined reschedule |
| `reschedul_blocked` | Reschedule limit reached | Red (danger) | Max attempts exceeded |
| `schedul_assign` | Schedule assigned to agent | Blue (primary) | Agent assigned |
| `schedul_completed` | Schedule completed | Green (success) | Appointment finished |
| `tour_pending` | Tour pending | Blue (info) | Tour scheduled |
| `tour_completed` | Tour completed | Green (success) | Tour finished |
| `tour_live` | Tour in progress | Green (success) | Live tour ongoing |
| `completed` | Fully completed | Blue (primary) | All tasks done |
| `maintenance` | Under maintenance | Gray (secondary) | System maintenance |
| `cancelled` | Booking cancelled | Red (danger) | Cancelled by admin/customer |
| `expired` | Booking expired | Black (dark) | Time limit exceeded |

### Status Flow Diagram

```
Customer Creates Booking
         ↓
    [inquiry] ← Initial contact
         ↓
    [pending] ← Awaiting payment
         ↓
  Payment Gateway
         ↓
   [confirmed] ← Payment successful
         ↓
Customer Requests Schedule
         ↓
  [schedul_pending]
         ↓
    Admin Decision
         ↓
    ├─→ [schedul_accepted] ← SMS sent to customer
    └─→ [schedul_decline] ← Date & notes cleared
         ↓
  [schedul_assign] ← Assigned to agent
         ↓
  [schedul_completed] ← Appointment done
         ↓
    [tour_pending]
         ↓
    [tour_live]
         ↓
   [tour_completed]
         ↓
    [completed] ← Final stage
```

---

## Booking History System

### Overview
Every booking status change, payment status change, and major action is tracked in the `booking_histories` table with complete audit trail.

### Database Table: `booking_histories`

```sql
CREATE TABLE booking_histories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    booking_id BIGINT NOT NULL,
    from_status VARCHAR(50),
    to_status VARCHAR(50) NOT NULL,
    changed_by BIGINT,
    notes TEXT,
    metadata JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE SET NULL
);
```

### History Entry Structure

**Basic Fields:**
- `booking_id`: Related booking
- `from_status`: Previous booking status
- `to_status`: New booking status
- `changed_by`: User ID who made the change
- `notes`: Human-readable description
- `ip_address`: IP address of requester
- `user_agent`: Browser/device information
- `created_at`: Timestamp

**Metadata Field (JSON):**
Contains context-specific information based on the action type.

### Metadata Examples

#### 1. Customer Booking Creation (Stepwise)
```json
{
  "source": "customer_frontend_stepwise",
  "form_data": {
    "name": "John Doe",
    "phone": "9876543210",
    "owner_type": "Owner",
    "main_property_type": "Residential"
  },
  "booking_details": {
    "property_type_id": 1,
    "property_sub_type_id": 4,
    "bhk_id": 3,
    "furniture_type": "Semi Furnished",
    "area": 1200,
    "price": 999
  }
}
```

#### 2. Payment Callback
```json
{
  "step": "payment_callback",
  "payment_data": {
    "order_id": "bk_123_XYZ",
    "order_status": "PAID",
    "payment_status": "paid",
    "amount": 999,
    "currency": "INR"
  },
  "property_data": {
    "property_type": "Residential",
    "property_sub_type": "Apartment",
    "area": 1200,
    "price": 999
  },
  "address_data": {
    "house_no": "101",
    "building": "ABC Tower",
    "city": "Mumbai",
    "state": "Maharashtra"
  },
  "payment_gateway_response": { /* Full Cashfree response */ },
  "old_payment_status": "pending",
  "new_payment_status": "paid"
}
```

#### 3. Schedule Acceptance by Admin
```json
{
  "approved_by": "Admin User",
  "approved_at": "2025-12-04 14:30:00",
  "scheduled_date": "2025-12-10",
  "admin_notes": "Customer available on requested date"
}
```

#### 4. Payment Status Change (Quick Action)
```json
{
  "source": "admin_quick_action",
  "change_type": "payment_status",
  "old_payment_status": "pending",
  "new_payment_status": "paid",
  "changed_by_name": "Admin User",
  "admin_notes": "Payment received via bank transfer"
}
```

#### 5. Booking Status Change (Quick Action)
```json
{
  "source": "admin_quick_action",
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0..."
}
```

### When History is Created

1. **Customer Creates Booking** (Frontend)
   - Property details saved → History entry
   - Payment callback → History entry with full data

2. **Admin Actions**
   - Schedule accepted/declined → History entry
   - Status changed via Quick Actions → History entry
   - Payment status changed → History entry

3. **Customer Actions**
   - Schedule requested → History entry
   - Reschedule requested → History entry

---

## SMS Notification System

### MSG91 Integration

**Configuration File:** `config/msg91.php`

```php
'templates' => [
    'order_confirmation' => '69295ee79cb8142aae77f2a2',
    'payment_failed' => '69295eabe5d99077c61b7ac1',
    'appointment_scheduled' => '69295d82a0f6627e122a0252',
],
```

### SMS Templates

#### 1. Order Confirmation (Payment Success)
- **Template ID:** `69295ee79cb8142aae77f2a2`
- **Trigger:** Payment status = `PAID`
- **Message:** "PROPPIK: Your order is confirmed. You can now schedule your appointment on ##LINK##. – CREART"
- **Parameters:** `LINK` = https://proppik.com/

**Implementation:**
```php
// In FrontendController@syncBookingWithCashfreeOrder
if ($orderStatus === 'PAID') {
    $this->smsService->send(
        $mobile,
        'order_confirmation',
        ['LINK' => 'https://proppik.com/'],
        [
            'type' => 'manual',
            'reference_type' => 'App\Models\Booking',
            'reference_id' => $booking->id,
            'notes' => 'Order confirmation SMS'
        ]
    );
}
```

#### 2. Payment Failed
- **Template ID:** `69295eabe5d99077c61b7ac1`
- **Trigger:** Payment status = `FAILED`, `EXPIRED`, `TERMINATED`, `TERMINATION_REQUESTED`, `ACTIVE`
- **Message:** "PROPPIK: Your payment could not be processed. Please try again or use another method. – CREART"
- **Parameters:** None

**Implementation:**
```php
// In FrontendController@syncBookingWithCashfreeOrder
if (in_array($orderStatus, ['FAILED', 'EXPIRED', 'TERMINATED', 'TERMINATION_REQUESTED', 'ACTIVE'])) {
    $this->smsService->send(
        $mobile,
        'payment_failed',
        [],
        [
            'type' => 'manual',
            'reference_type' => 'App\Models\Booking',
            'reference_id' => $booking->id,
            'notes' => 'Payment failed notification'
        ]
    );
}
```

#### 3. Appointment Scheduled
- **Template ID:** `69295d82a0f6627e122a0252`
- **Trigger:** Admin accepts schedule (`schedul_accepted` or `reschedul_accepted`)
- **Message:** "PROPPIK: Your appointment is scheduled on ##DATE##. Please ensure you are available. – CREART"
- **Parameters:** `DATE` = Formatted booking date (e.g., "05 Dec 2025")

**Implementation:**
```php
// In PendingScheduleController@accept
$formattedDate = $booking->booking_date->format('d M Y');
$this->smsService->send(
    $mobile,
    'appointment_scheduled',
    ['DATE' => $formattedDate],
    [
        'type' => 'manual',
        'reference_type' => 'App\Models\Booking',
        'reference_id' => $booking->id,
        'notes' => 'Appointment scheduled SMS'
    ]
);
```

### SMS Flow Diagram

```
Payment Successful
       ↓
   SMS Sent: "Order confirmed"
       ↓
Customer Schedules
       ↓
Admin Accepts Schedule
       ↓
   SMS Sent: "Appointment scheduled on [DATE]"
       ↓
Customer Attends Appointment
```

### SMS Logging

All SMS messages are logged in `sms_logs` table:
- Gateway used (MSG91)
- Template ID
- Mobile number
- Parameters sent
- Status (sent/failed)
- Response from gateway
- Timestamp

**Query SMS logs:**
```sql
SELECT * FROM sms_logs 
WHERE reference_type = 'App\Models\Booking' 
AND reference_id = [booking_id]
ORDER BY created_at DESC;
```

---

## Admin Quick Actions

### Location
Admin Booking Show Page: `/admin/bookings/{id}`

### Features

#### 1. Payment Status Dropdown
- **Options:** Unpaid, Pending, Paid, Failed, Refunded
- **Action:** Change payment status with optional notes
- **Result:** 
  - Payment status updated
  - Booking history entry created
  - Metadata includes old/new payment status

**Implementation:**
```javascript
async function updatePaymentStatus(status) {
    // Show SweetAlert with notes field
    // Send AJAX request to /admin/bookings/{id}/update-ajax
    // Create history entry with payment_status metadata
}
```

#### 2. Booking Status Dropdown
- **Options:** All 19 lifecycle statuses
- **Action:** Change booking status with optional notes
- **Result:**
  - Booking status updated via `changeStatus()` method
  - Booking history entry created automatically
  - Timeline updated

**Implementation:**
```javascript
async function updateBookingStatus(status) {
    // Show SweetAlert with notes field
    // Send AJAX request with status and notes
    // Backend uses Booking->changeStatus() method
}
```

#### 3. Quick Status Action Buttons
Context-aware buttons based on current booking status:

- **Inquiry** → "Convert to Pending"
- **Pending** → "Mark Schedule Pending"
- **Schedule Accepted** → "Assign to Agent" or "Start Tour Process"
- **Schedule Assigned** → "Complete Schedule"
- **Tour Pending** → "Start Live Tour"
- **Tour Live** → "Complete Tour"
- **Tour/Schedule Completed** → "Maintenance"
- **Any Active Status** → "Cancel Booking"

#### 4. Schedule Acceptance
When booking has `schedul_pending` or `reschedul_pending` status:
- Accept Schedule button
- Decline Schedule button
- Display requested date and customer notes
- SMS notification sent on acceptance

---

## Customer Dashboard

### URL
`/booking-dashboard`

### Behavior

1. **No Bookings:**
   - Redirect to `/setup` (booking creation page)

2. **Has Bookings:**
   - Display booking list
   - Show schedule button for confirmed bookings
   - Allow scheduling/rescheduling

### Schedule Attempt Limits

**Configuration:**
Admin Settings → Booking Schedule Date tab
- `customer_attempt`: Maximum number of accepted schedules allowed
- `customer_attempt_note`: Message to show when limit reached

**Counting Logic:**
Only counts **ACCEPTED** schedules:
- `schedul_accepted`
- `reschedul_accepted`

Does NOT count:
- `schedul_pending`
- `schedul_decline`
- `reschedul_pending`
- `reschedul_decline`

**When Limit Reached:**
- Status set to `reschedul_blocked`
- Customer sees dynamic message from `customer_attempt_note`
- History entry created

---

## Database Schema

### Bookings Table (Updated)

```sql
ALTER TABLE bookings 
MODIFY COLUMN status ENUM(
    'inquiry',
    'pending',
    'confirmed',
    'schedul_pending',
    'schedul_accepted',
    'schedul_decline',
    'reschedul_pending',
    'reschedul_accepted',
    'reschedul_decline',
    'reschedul_blocked',
    'schedul_assign',
    'schedul_completed',
    'tour_pending',
    'tour_completed',
    'tour_live',
    'completed',
    'maintenance',
    'cancelled',
    'expired'
) DEFAULT 'pending';
```

### Booking Histories Table

See [Booking History System](#booking-history-system) section above.

### Relationships

```php
// Booking Model
public function histories() {
    return $this->hasMany(BookingHistory::class);
}

// BookingHistory Model
public function booking() {
    return $this->belongsTo(Booking::class);
}

public function changedBy() {
    return $this->belongsTo(User::class, 'changed_by');
}
```

---

## API Endpoints

### Schedule Management

#### Accept Schedule
```
POST /admin/pending-schedules/{booking}/accept
```

**Request Body:**
```json
{
  "notes": "Customer available on requested date"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Schedule approved successfully",
  "booking": { /* updated booking data */ }
}
```

**Actions:**
- Changes status to `schedul_accepted` or `reschedul_accepted`
- Creates history entry
- Sends SMS to customer

#### Decline Schedule
```
POST /admin/pending-schedules/{booking}/decline
```

**Request Body:**
```json
{
  "notes": "Date not available"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Schedule declined successfully"
}
```

**Actions:**
- Changes status to `schedul_decline` or `reschedul_decline`
- Clears `booking_date` and `booking_notes`
- Creates history entry

### Quick Actions

#### Update Booking (AJAX)
```
POST /admin/bookings/{booking}/update-ajax
```

**Request Body (Status Change):**
```json
{
  "status": "completed",
  "notes": "All tasks completed"
}
```

**Request Body (Payment Status Change):**
```json
{
  "payment_status": "paid",
  "notes": "Payment received via bank transfer"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Booking updated successfully",
  "booking": { /* updated booking with histories */ },
  "updated_fields": ["status"]
}
```

---

## Configuration

### Environment Variables

```env
# MSG91 SMS Gateway
MSG91_AUTH_KEY=your_auth_key_here
MSG91_SENDER_ID=PROPPK
```

### Admin Settings

Navigate to: `/admin/settings` → "Booking Schedule Date" tab

**Settings:**
1. **Customer Attempt** (Integer)
   - Maximum number of accepted schedule attempts
   - Default: 3

2. **Customer Attempt Note** (Text)
   - Message shown when limit exceeded
   - Example: "You have reached the maximum number of schedule attempts. Please contact support."

---

## Timeline Display

### Features

1. **Interactive Timeline**
   - Smooth scroll animations
   - Hover effects on cards
   - Color-coded status indicators

2. **Left/Right Layout**
   - Customer actions on left (👤 icon)
   - Admin/System actions on right (🛡️ icon)
   - Center vertical line

3. **Metadata Display**
   - Collapsible JSON view
   - Syntax highlighted
   - Scrollable for large data

4. **Information Display**
   - Status badges with colors
   - User name with role
   - Timestamp (absolute + relative)
   - IP address
   - Notes/description

### CSS Classes

```css
.timeline-center          /* Main container */
.timeline-item-wrapper    /* Individual entry */
.timeline-icon            /* Center icon */
.timeline-card            /* Content card */
.timeline-card-left       /* Customer action card */
.timeline-card-right      /* Admin action card */
```

---

## Testing

### Test Scenarios

#### 1. Complete Booking Flow
```
1. Customer creates booking → Check history entry
2. Payment successful → Check history + SMS
3. Customer schedules → Check history
4. Admin accepts → Check history + SMS
5. Admin changes status → Check history
6. Admin changes payment → Check history
```

#### 2. SMS Testing
```sql
-- Check SMS logs
SELECT * FROM sms_logs 
WHERE template_key IN ('order_confirmation', 'payment_failed', 'appointment_scheduled')
ORDER BY created_at DESC 
LIMIT 10;
```

#### 3. History Verification
```sql
-- Check booking histories
SELECT 
    bh.id,
    b.id as booking_id,
    bh.from_status,
    bh.to_status,
    bh.notes,
    u.firstname as changed_by,
    bh.created_at
FROM booking_histories bh
JOIN bookings b ON bh.booking_id = b.id
LEFT JOIN users u ON bh.changed_by = u.id
WHERE b.id = [booking_id]
ORDER BY bh.created_at DESC;
```

---

## Troubleshooting

### Common Issues

#### 1. History Not Created
**Check:**
- Is `changeStatus()` method being used?
- Is `BookingHistory` model imported?
- Are validation rules correct?

#### 2. SMS Not Sent
**Check:**
- Is MSG91 configured in `.env`?
- Is SMS gateway enabled in admin panel?
- Check `sms_logs` table for errors
- Verify template ID in `config/msg91.php`

#### 3. Payment Status Not Updating
**Check:**
- Is payment_status in validation rules?
- Is history entry being created?
- Check `updateAjax()` method implementation

### Debug Logs

```bash
# View Laravel logs
tail -f storage/logs/laravel.log

# Filter for specific events
tail -f storage/logs/laravel.log | grep "Appointment scheduled SMS"
tail -f storage/logs/laravel.log | grep "Order confirmation SMS"
tail -f storage/logs/laravel.log | grep "Payment failed SMS"
```

---

## Maintenance

### Database Maintenance

```sql
-- Clean old history entries (optional)
DELETE FROM booking_histories 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);

-- Check history table size
SELECT 
    COUNT(*) as total_entries,
    DATE(created_at) as date,
    COUNT(*) as entries_per_day
FROM booking_histories
GROUP BY DATE(created_at)
ORDER BY date DESC
LIMIT 30;
```

### Performance Optimization

1. **Indexes**
   - `booking_id` (already indexed via FK)
   - `created_at` for time-based queries
   - `to_status` for status-based queries

2. **Eager Loading**
   ```php
   $booking = Booking::with('histories.changedBy.roles')->find($id);
   ```

3. **Pagination**
   - Limit history entries displayed
   - Use lazy loading for metadata

---

## Future Enhancements

### Suggested Features

1. **Bulk Status Updates**
   - Select multiple bookings
   - Change status in bulk
   - Create history for each

2. **Automated Status Transitions**
   - Auto-expire after X days
   - Auto-complete tours
   - Scheduled status changes

3. **Email Notifications**
   - Mirror SMS notifications via email
   - PDF attachments for confirmations

4. **Analytics Dashboard**
   - Status distribution charts
   - Conversion funnel
   - Average time per status

5. **History Filters**
   - Filter by status
   - Filter by date range
   - Filter by user

---

## Support

### Contact Information
- **Project:** BrokerX
- **Company:** PROPPIK (https://proppik.com)
- **Documentation Version:** 1.0
- **Last Updated:** December 4, 2025

---

## Changelog

### Version 1.0 (December 4, 2025)
- ✅ Implemented 19 booking lifecycle statuses
- ✅ Created booking history tracking system
- ✅ Integrated MSG91 SMS notifications
- ✅ Added admin Quick Actions
- ✅ Implemented schedule acceptance flow
- ✅ Added payment status history tracking
- ✅ Created interactive timeline display
- ✅ Implemented customer schedule attempt limits

---

**End of Documentation**

