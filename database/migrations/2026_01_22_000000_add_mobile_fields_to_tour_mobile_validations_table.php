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
        Schema::table('tour_mobile_validations', function (Blueprint $table) {
            $table->string('base_mobile')->nullable()->after('mobile');
            $table->string('country_code', 10)->nullable()->after('base_mobile');
            $table->string('country_name', 100)->nullable()->after('country_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_mobile_validations', function (Blueprint $table) {
            $table->dropColumn(['base_mobile', 'country_code', 'country_name']);
        });
    }
};

