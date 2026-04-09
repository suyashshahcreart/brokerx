<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('states', function (Blueprint $table) {
            $table->dropUnique(['code']);
        });

        DB::statement("ALTER TABLE `states` MODIFY `code` VARCHAR(10) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `states` MODIFY `code` VARCHAR(10) NOT NULL");

        Schema::table('states', function (Blueprint $table) {
            $table->unique('code');
        });
    }
};
