<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we need to modify the enum column
        // First, update existing status values that need to be changed
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM(
            'inquiry',
            'pending',
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
            'maintenance',
            'expired',
            'confirmed',
            'cancelled',
            'completed',
            'scheduled'
        ) DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE bookings MODIFY COLUMN status ENUM(
            'pending',
            'confirmed',
            'cancelled',
            'completed',
            'scheduled'
        ) DEFAULT 'pending'");
    }
};

