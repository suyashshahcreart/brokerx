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
        Schema::table('users', function (Blueprint $table) {
            $table->string('base_mobile', 20)->nullable()->after('mobile');
            $table->char('country_code', 2)->nullable()->after('base_mobile');
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete()
                ->after('country_code')
                ->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn(['country_code', 'base_mobile']);
        });
    }
};
