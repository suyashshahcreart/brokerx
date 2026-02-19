<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // ZIP processing status for tour uploads
            $table->string('tour_zip_status')->default('pending')->after('base_url'); // pending|processing|done|failed
            $table->unsignedTinyInteger('tour_zip_progress')->default(0)->after('tour_zip_status'); // 0-100
            $table->text('tour_zip_message')->nullable()->after('tour_zip_progress');
            $table->timestamp('tour_zip_started_at')->nullable()->after('tour_zip_message');
            $table->timestamp('tour_zip_finished_at')->nullable()->after('tour_zip_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'tour_zip_status',
                'tour_zip_progress',
                'tour_zip_message',
                'tour_zip_started_at',
                'tour_zip_finished_at',
            ]);
        });
    }
};

