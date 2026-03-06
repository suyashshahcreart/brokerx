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
        Schema::create('visitors_otp', function (Blueprint $table) {
            $table->id();
            
            // Customer Information
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->cascadeOnDelete();
            $table->foreignId('booking_id')
                ->nullable()
                ->constrained('bookings')
                ->nullOnDelete();
            $table->foreignId('tour_id')
                ->nullable()
                ->constrained('tours')
                ->nullOnDelete();
            
            // Verification Details
            $table->string('visitors_name');
            $table->string('visitors_mobile', 20);
            $table->string('visitors_email');
            $table->string('otp', 6);
            $table->string('download_link')->nullable();
            
            // Status
            $table->enum('status', ['pending', 'verified', 'expired', 'failed'])->default('pending');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            
            // Attempt Tracking
            $table->integer('attempt_count')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            
            // Notifications
            $table->boolean('otp_sent_via_sms')->default(false);
            $table->boolean('otp_sent_via_email')->default(false);
            $table->boolean('notification_sent_to_agent')->default(false);
            $table->timestamp('notification_sent_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('customer_id');
            $table->index('booking_id');
            $table->index('tour_id');
            $table->index('status');
            $table->index('otp_expires_at');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors_otp');
    }
};
