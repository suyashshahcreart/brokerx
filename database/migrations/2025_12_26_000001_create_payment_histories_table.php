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
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            // Gateway information (gateway-agnostic)
            $table->string('gateway', 50)->default('cashfree'); // cashfree, payu, razorpay, etc.
            $table->string('gateway_order_id', 191)->nullable()->index(); // Order ID from gateway
            $table->string('gateway_payment_id')->nullable()->index(); // Payment ID/reference from gateway
            $table->string('gateway_session_id')->nullable(); // Session ID if applicable
            
            // Payment details
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded'])
                ->default('pending')
                ->index();
            
            $table->unsignedBigInteger('amount'); // Amount in paise (smallest currency unit)
            $table->string('currency', 3)->default('INR');
            $table->string('payment_method')->nullable(); // UPI, card, netbanking, wallet, etc.
            
            // Payment metadata (gateway-specific data stored as JSON)
            $table->json('gateway_response')->nullable(); // Full response from gateway
            $table->json('gateway_meta')->nullable(); // Additional gateway-specific metadata
            $table->text('gateway_message')->nullable(); // Success/error message from gateway
            
            // Timestamps
            $table->timestamp('initiated_at')->nullable(); // When payment was initiated
            $table->timestamp('completed_at')->nullable(); // When payment was completed
            $table->timestamp('failed_at')->nullable(); // When payment failed
            
            // Additional tracking
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable(); // Additional notes about this payment attempt
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['booking_id', 'status']);
            $table->index(['booking_id', 'gateway']);
            $table->index(['gateway', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};


