<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['deleted_at', 'id'], 'bookings_deleted_at_id_index');
            $table->index(['status', 'created_at'], 'bookings_status_created_at_index');
            $table->index('booking_date', 'bookings_booking_date_index');
            $table->index(['country_id', 'state_id', 'city_id'], 'bookings_location_index');
            $table->index(['property_type_id', 'property_sub_type_id'], 'bookings_property_type_index');
            $table->index('bhk_id', 'bookings_bhk_id_index');
            $table->index('customer_id', 'bookings_customer_id_index');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->index(['booking_id', 'deleted_at', 'id'], 'tours_booking_latest_index');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_deleted_at_id_index');
            $table->dropIndex('bookings_status_created_at_index');
            $table->dropIndex('bookings_booking_date_index');
            $table->dropIndex('bookings_location_index');
            $table->dropIndex('bookings_property_type_index');
            $table->dropIndex('bookings_bhk_id_index');
            $table->dropIndex('bookings_customer_id_index');
        });

        Schema::table('tours', function (Blueprint $table) {
            $table->dropIndex('tours_booking_latest_index');
        });
    }
};
