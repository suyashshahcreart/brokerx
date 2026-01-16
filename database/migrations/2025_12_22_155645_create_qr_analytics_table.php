<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qr_analytics', function (Blueprint $table) {
            $table->id();
            
            // QR Code and Booking Information
            $table->string('tour_code')->nullable()->index()->comment('Tour code from URL (e.g., e4GzGJC1z)');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete()->index()->comment('Linked booking via tour_code');
            $table->string('page_url')->nullable()->comment('Full page URL accessed');
            $table->string('page_type')->default('welcome')->comment('welcome, tour_code, analytics');
            
            // User & Device Information
            $table->string('user_ip', 45)->index()->comment('User IP address (IPv4 or IPv6)');
            $table->text('user_agent')->nullable()->comment('User browser and device information');
            $table->string('browser_name', 100)->nullable()->index()->comment('Browser name (Chrome, Firefox, Safari, etc.)');
            $table->string('browser_version', 50)->nullable()->comment('Browser version');
            $table->string('os_name', 100)->nullable()->index()->comment('Operating system name');
            $table->string('os_version', 50)->nullable()->comment('Operating system version');
            $table->string('device_type', 50)->nullable()->index()->comment('Device type (mobile, tablet, desktop)');
            $table->string('screen_resolution', 50)->nullable()->comment('Screen resolution (width x height)');
            $table->string('language', 10)->nullable()->comment('User language preference');
            
            // Geolocation Information
            $table->string('country', 100)->nullable()->index()->comment('Country based on IP');
            $table->string('city', 100)->nullable()->index()->comment('City based on IP');
            $table->string('region', 100)->nullable()->comment('Region/State based on IP');
            $table->decimal('latitude', 10, 8)->nullable()->comment('Latitude coordinate');
            $table->decimal('longitude', 11, 8)->nullable()->comment('Longitude coordinate');
            $table->string('timezone', 100)->nullable()->comment('User timezone');
            
            // Marketing & UTM Parameters
            $table->text('referrer')->nullable()->comment('Referrer URL if any');
            $table->string('utm_source', 100)->nullable()->index()->comment('UTM source parameter');
            $table->string('utm_medium', 100)->nullable()->index()->comment('UTM medium parameter');
            $table->string('utm_campaign', 100)->nullable()->index()->comment('UTM campaign parameter');
            $table->string('utm_term', 100)->nullable()->comment('UTM term parameter');
            $table->string('utm_content', 100)->nullable()->comment('UTM content parameter');
            
            // Session & User Information
            $table->string('session_id', 255)->nullable()->index()->comment('Session identifier');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Laravel user ID if logged in');
            
            // Tracking Metadata
            $table->datetime('scan_date')->useCurrent()->index()->comment('Date and time of scan/visit');
            $table->string('tracking_status', 20)->default('success')->index()->comment('Tracking status (success, error, invalid_tour_code)');
            $table->text('error_message')->nullable()->comment('Error message if tracking failed');
            $table->decimal('load_time', 10, 4)->nullable()->comment('Page load time in seconds');
            
            // Additional metadata for future analytics
            $table->json('metadata')->nullable()->comment('Additional metadata for future use');
            
            $table->timestamps();
            
            // Additional indexes for analytics queries
            $table->index(['tour_code', 'scan_date']);
            $table->index(['booking_id', 'scan_date']);
            $table->index(['country', 'city']);
            $table->index(['device_type', 'os_name']);
            $table->index(['utm_source', 'utm_campaign']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_analytics');
    }
};
