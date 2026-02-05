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
        // Add composite index on bookings for property type filtering
        // Note: property_type_id and property_sub_type_id already have indexes from foreign keys
        // This composite index helps with the WHERE IN queries used in portfolio API filtering
        Schema::table('bookings', function (Blueprint $table) {
            if (!$this->indexExists('bookings', 'bookings_property_type_sub_type_index')) {
                $table->index(['property_type_id', 'property_sub_type_id'], 'bookings_property_type_sub_type_index');
            }
        });

        // tours.booking_id already has an index from foreign key, but we can add a composite
        // index with status for better filtering performance
        Schema::table('tours', function (Blueprint $table) {
            if (!$this->indexExists('tours', 'tours_booking_status_index')) {
                $table->index(['booking_id', 'is_active'], 'tours_booking_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_property_type_sub_type_index');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropIndex('tours_booking_status_index');
        });
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();
        $indexes = $connection->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $index]
        );
        return $indexes[0]->count > 0;
    }
};
