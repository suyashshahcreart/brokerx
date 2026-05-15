# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Laravel 11** application for managing tour bookings and property showcases. The system includes QR code generation, multi-language support (English, Gujarati, Hindi), and rich media handling for info modals.

## Running the Application

```bash
# Start the development server
php artisan serve

# Build frontend assets (Vite)
npm run dev

# Build for production
npm run build

# Run tests
php artisan test
```

## Architecture

- **Backend**: Laravel 11 with controllers under `app/Http/Controllers/`
- **Frontend**: Blade templates + JavaScript modules in `resources/js/pages/`
- **Database**: MySQL with Eloquent ORM
- **Storage**: AWS S3 for media files

## Key JavaScript Files

The main JS files are in `resources/js/pages/`:
- `booking_infomodal_nodes.js` - manages info point editing modal (the file you asked about)
- `booking_tour_iconLib.js` - icon selection library
- `edit-booking-tour.js` - main tour editing page
- `tour-manager.js` - tour management interface

## Important Notes on booking_infomodal_nodes.js

This file handles editing info modals with these key functions:
- **openEditModal()** - Opens the edit form with existing data
- **renderSelectedInfoPointForm()** - Populates the form
- **getFormState()** - Collects form data on submit
- **pushUpdatedInfoPoint()** - Stores updated data before API call
- Uses TinyMCE editors for rich text
- Supports image/video/audio uploads
- Multi-language fields use format: `name[en]`, `name[gu]`, `name[hi]`

**Data Flow**: Edit button → openEditModal → form populated → submit → pushUpdatedInfoPoint → fetch PATCH request to server

The file uses a global `UpdatedInfoPoint` Map to track edits before sending to the server via the `UpdateNodesButton` click handler.

## Routes

- Web routes: `routes/web.php` (admin routes under `/admin`)
- API routes: `routes/api.php`
- Auth routes: `routes/auth.php`

## Dependencies

Key packages:
- `barryvdh/laravel-dompdf` - PDF generation
- `simplesoftwareio/simple-qrcode` - QR code generation
- `maatwebsite/excel` - Excel import/export
- `spatie/laravel-permission` - Role-based permissions
- `yajra/laravel-datatables-oracle` - DataTables