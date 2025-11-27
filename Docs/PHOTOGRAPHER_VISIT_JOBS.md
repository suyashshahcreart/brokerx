# Photographer Visit Jobs System

## Overview
The Photographer Visit Jobs system is an automated job assignment and tracking module that integrates with the booking system. When a new booking is created, a photographer visit job is automatically generated to ensure proper workflow management.

## Features

### 1. Auto Job Creation
- **Trigger**: Automatically creates a photographer visit job when a new booking is made
- **Initial Status**: Jobs start with 'pending' status
- **Linked Data**: Jobs are linked to both booking and tour records

### 2. Job Management (Admin)
- **Create Jobs**: Manual job creation with booking selection
- **Assign Photographers**: Assign photographers to pending jobs
- **Edit Jobs**: Update job details, priority, and schedule
- **Delete Jobs**: Remove jobs (soft delete)
- **View Jobs**: DataTable view with filtering by status, priority, photographer, date

### 3. Photographer Interface
- **View Jobs**: Photographers can see jobs assigned to them
- **Accept Jobs**: Change status from 'assigned' to 'in_progress'
- **Complete Jobs**: Mark jobs as completed with notes
- **Job Details**: View full booking and property information
- **Visit History**: See all visits associated with a job

## Job Workflow

```
┌─────────────────┐
│  Booking Created │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  Job Created    │
│  (Pending)      │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  Admin Assigns  │
│  Photographer   │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  Job Assigned   │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  Photographer   │
│  Accepts Job    │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  In Progress    │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  Photographer   │
│  Completes Job  │
└────────┬─────────┘
         │
         ▼
┌─────────────────┐
│  Completed      │
└─────────────────┘
```

## Database Structure

### Table: photographer_visit_jobs

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| booking_id | bigint | Foreign key to bookings |
| tour_id | bigint | Foreign key to tours (nullable) |
| photographer_id | bigint | Foreign key to users (photographer) |
| job_code | string | Unique job identifier (e.g., PVJ-ABC12345) |
| status | enum | pending, assigned, in_progress, completed, cancelled |
| priority | enum | low, normal, high, urgent |
| scheduled_date | timestamp | When the job should be done |
| assigned_at | timestamp | When photographer was assigned |
| started_at | timestamp | When job started |
| completed_at | timestamp | When job completed |
| instructions | text | Job instructions |
| special_requirements | text | Special requirements |
| estimated_duration | integer | Estimated minutes |
| metadata | json | Additional data |
| cancellation_reason | text | Reason if cancelled |
| notes | text | General notes |
| created_by | bigint | User who created |
| updated_by | bigint | User who last updated |
| deleted_by | bigint | User who deleted |
| assigned_by | bigint | User who assigned photographer |

### Relationships
- **booking**: BelongsTo Booking
- **tour**: BelongsTo Tour
- **photographer**: BelongsTo User
- **visits**: HasMany PhotographerVisit
- **creator**: BelongsTo User (created_by)
- **assigner**: BelongsTo User (assigned_by)

## Routes

### Admin Routes (prefix: admin)
```php
GET    /admin/photographer-visit-jobs              # List jobs
GET    /admin/photographer-visit-jobs/create       # Create job form
POST   /admin/photographer-visit-jobs              # Store new job
GET    /admin/photographer-visit-jobs/{id}         # Show job details
GET    /admin/photographer-visit-jobs/{id}/edit    # Edit job form
PUT    /admin/photographer-visit-jobs/{id}         # Update job
DELETE /admin/photographer-visit-jobs/{id}         # Delete job
POST   /admin/photographer-visit-jobs/{id}/assign  # Assign photographer
```

### Photographer Routes (prefix: photographer)
```php
GET    /photographer/jobs              # List my jobs
GET    /photographer/jobs/{id}         # View job details
POST   /photographer/jobs/{id}/accept  # Accept job
POST   /photographer/jobs/{id}/complete # Mark as completed
GET    /photographer/jobs/upcoming     # Upcoming jobs
```

## Permissions

### Admin Permissions
- `photographer_visit_job_view`
- `photographer_visit_job_create`
- `photographer_visit_job_edit`
- `photographer_visit_job_delete`
- `photographer_visit_job_assign`

### Photographer Permissions
- `photographer_visit_job_view` (own jobs only)
- `photographer_visit_job_edit` (own jobs only)

## Models

### PhotographerVisitJob Model
**Location**: `app/Models/PhotographerVisitJob.php`

**Key Methods**:
- `assignPhotographer($photographerId, $assignedBy)` - Assign photographer
- `markAsInProgress()` - Change status to in_progress
- `markAsCompleted()` - Change status to completed
- `cancel($reason)` - Cancel job with reason
- `isAssigned()` - Check if assigned
- `isInProgress()` - Check if in progress
- `isCompleted()` - Check if completed
- `isOverdue()` - Check if overdue

**Scopes**:
- `byStatus($status)` - Filter by status
- `forPhotographer($photographerId)` - Jobs for specific photographer
- `pending()` - Only pending jobs
- `assigned()` - Only assigned jobs
- `byPriority($priority)` - Filter by priority
- `upcoming()` - Future scheduled jobs

**Attributes**:
- `priority_color` - Bootstrap color for priority badge
- `status_color` - Bootstrap color for status badge

## Controllers

### Admin\PhotographerVisitJobController
**Location**: `app/Http/Controllers/Admin/PhotographerVisitJobController.php`

**Methods**:
- `index()` - List with DataTables AJAX
- `create()` - Show create form
- `store()` - Create new job
- `show()` - Show job details
- `edit()` - Show edit form
- `update()` - Update job
- `assign()` - Assign photographer to job
- `destroy()` - Soft delete job

### Photographer\JobController
**Location**: `app/Http/Controllers/Photographer/JobController.php`

**Methods**:
- `index()` - List photographer's jobs
- `show()` - View job details
- `accept()` - Accept assigned job
- `complete()` - Mark job as completed
- `upcoming()` - Get upcoming jobs

## Views

### Admin Views
- `resources/views/admin/photographer-visit-jobs/index.blade.php` - Job listing with DataTables
- (Additional views needed: create.blade.php, edit.blade.php, show.blade.php)

### Photographer Views
- `resources/views/photographer/jobs/index.blade.php` - My jobs list
- `resources/views/photographer/jobs/show.blade.php` - Job details

## Integration with Booking System

The system automatically creates a photographer visit job when a booking is created:

**File**: `app/Http/Controllers/Admin/BookingController.php`

```php
public function store(Request $request)
{
    // ... create booking
    // ... create tour
    
    // Auto-create photographer visit job
    $job = PhotographerVisitJob::create([
        'booking_id' => $booking->id,
        'tour_id' => $tour->id,
        'photographer_id' => null, // Will be assigned later
        'status' => 'pending',
        'priority' => 'normal',
        'scheduled_date' => $booking->booking_date ?? now()->addDays(1),
        'instructions' => 'Complete photography for property booking #' . $booking->id,
        'created_by' => $request->user()->id ?? null,
    ]);
    
    // ... activity log
}
```

## Usage Examples

### Admin: Creating and Assigning a Job
1. Admin creates a booking → Job auto-created
2. Admin navigates to "Photographer Jobs"
3. Filters for pending jobs
4. Clicks edit on a job
5. Assigns photographer and saves
6. Job status changes to "assigned"

### Photographer: Working on a Job
1. Photographer logs in
2. Goes to "My Jobs" menu
3. Sees list of assigned jobs
4. Clicks on a job to view details
5. Clicks "Accept Job" button
6. Job status changes to "in_progress"
7. Completes work and visits
8. Clicks "Mark as Completed"
9. Adds completion notes
10. Job status changes to "completed"

## Future Enhancements
- Email notifications to photographers when assigned
- Push notifications for job updates
- Calendar view for scheduled jobs
- Job analytics and reporting
- Time tracking integration
- Photo upload directly from job interface
- Mobile app for photographers
