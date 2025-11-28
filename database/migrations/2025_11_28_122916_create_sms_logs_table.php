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
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('gateway')->index(); // SMS Gateway name (e.g., MSG91, Twilio)
            $table->string('type')->default('manual')->index(); // Type: manual, cron, scheduled, etc.
            $table->string('template_key')->nullable()->index(); // Template key (e.g., login_otp)
            $table->string('template_id')->nullable(); // Gateway template ID
            $table->string('mobile', 20)->index(); // Mobile number with country code
            $table->text('message')->nullable(); // SMS message content (if available)
            $table->json('params')->nullable(); // Template parameters sent
            $table->string('status')->default('pending')->index(); // Status: pending, sent, failed, delivered
            $table->integer('status_code')->nullable(); // HTTP status code from gateway
            $table->boolean('success')->default(false)->index(); // Success flag
            $table->text('response_body')->nullable(); // Full response from gateway
            $table->json('response_json')->nullable(); // Parsed JSON response
            $table->text('error_message')->nullable(); // Error message if failed
            $table->string('gateway_message_id')->nullable()->index(); // Gateway's message ID (if provided)
            $table->decimal('cost', 10, 4)->nullable(); // SMS cost (if provided by gateway)
            $table->string('sender_id')->nullable(); // Sender ID used
            $table->unsignedBigInteger('user_id')->nullable()->index(); // User who triggered (if manual)
            $table->string('reference_type')->nullable()->index(); // Related model type (e.g., Booking, User)
            $table->unsignedBigInteger('reference_id')->nullable()->index(); // Related model ID
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamp('sent_at')->nullable(); // When SMS was actually sent
            $table->timestamp('delivered_at')->nullable(); // When SMS was delivered (if available)
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for common queries
            $table->index(['gateway', 'status']);
            $table->index(['type', 'status']);
            $table->index(['created_at', 'status']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
