<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'tour_zip_phase')) {
                $table->string('tour_zip_phase', 64)->nullable()->after('tour_zip_progress');
            }
            if (! Schema::hasColumn('bookings', 'tour_zip_current_item')) {
                $table->string('tour_zip_current_item', 255)->nullable()->after('tour_zip_phase');
            }
            if (! Schema::hasColumn('bookings', 'tour_zip_items_done')) {
                $table->unsignedInteger('tour_zip_items_done')->default(0)->after('tour_zip_current_item');
            }
            if (! Schema::hasColumn('bookings', 'tour_zip_items_total')) {
                $table->unsignedInteger('tour_zip_items_total')->default(0)->after('tour_zip_items_done');
            }
            if (! Schema::hasColumn('bookings', 'tour_zip_eta_seconds')) {
                $table->unsignedInteger('tour_zip_eta_seconds')->nullable()->after('tour_zip_items_total');
            }
        });

        // unsignedTinyInteger -> decimal (no doctrine/dbal required)
        if (Schema::hasColumn('bookings', 'tour_zip_progress')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('ALTER TABLE bookings MODIFY tour_zip_progress DECIMAL(5,2) NOT NULL DEFAULT 0');
            } else {
                Schema::table('bookings', function (Blueprint $table) {
                    $table->decimal('tour_zip_progress', 5, 2)->default(0)->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bookings', 'tour_zip_progress')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql' || $driver === 'mariadb') {
                DB::statement('ALTER TABLE bookings MODIFY tour_zip_progress TINYINT UNSIGNED NOT NULL DEFAULT 0');
            }
        }

        Schema::table('bookings', function (Blueprint $table) {
            foreach (['tour_zip_eta_seconds', 'tour_zip_items_total', 'tour_zip_items_done', 'tour_zip_current_item', 'tour_zip_phase'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
