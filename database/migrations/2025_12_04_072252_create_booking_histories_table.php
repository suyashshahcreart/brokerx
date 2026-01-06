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
        Schema::create('booking_histories', function (Blueprint $table) {
            $table->id();
            
            // Foreign key to booking
            $table->foreignId('booking_id')
                ->constrained('bookings')
                ->cascadeOnDelete();
            
            // Status tracking
            $table->enum('from_status', [
                'inquiry',
                'pending',
                'schedul_pending',
                'schedul_accepted',
                'schedul_decline',
                'reschedul_pending',
                'reschedul_accepted',
                'schedul_inprogress',
                'reschedul_decline',
                'reschedul_blocked',
                'schedul_assign',
                'schedul_completed',
                'tour_pending',
                'tour_completed',
                'tour_live',
                'maintenance',
                'expired',
                'confirmed',
                'cancelled',
                'completed',
                'scheduled'
            ])->nullable();
            
            $table->enum('to_status', [
                'inquiry',
                'pending',
                'schedul_pending',
                'schedul_accepted',
                'schedul_inprogress',
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
                'maintenance',
                'expired',
                'confirmed',
                'cancelled',
                'completed',
                'scheduled'
            ]);
            
            // Who made the change
            $table->foreignId('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            
            // Additional metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['booking_id', 'created_at']);
            $table->index('to_status');
            $table->index('changed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_histories');
    }
};
