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
        Schema::table('qr_analytics', function (Blueprint $table) {
            $table->text('full_address')->nullable()->after('region')->comment('Full address from reverse geocoding');
            $table->string('pincode', 20)->nullable()->after('full_address')->index()->comment('PIN/ZIP code from reverse geocoding');
            $table->string('location_source', 20)->nullable()->after('pincode')->comment('GPS or IP - source of location data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qr_analytics', function (Blueprint $table) {
            $table->dropColumn(['full_address', 'pincode', 'location_source']);
        });
    }
};
