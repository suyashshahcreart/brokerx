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
        // Change working_json from JSON to LONGTEXT
        DB::statement('ALTER TABLE tours MODIFY working_json LONGTEXT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to JSON type
        DB::statement('ALTER TABLE tours MODIFY working_json JSON NULL');
    }
};
