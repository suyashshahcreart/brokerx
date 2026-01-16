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
        Schema::table('tours', function (Blueprint $table) {
            $table->string('custom_logo_sidebar')->nullable();
            $table->string('custom_logo_footer')->nullable();
            $table->string('custom_name')->nullable();
            $table->string('custom_email')->nullable();
            $table->string('custom_mobile')->nullable();
            $table->string('custom_type')->nullable();
            $table->text('custom_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'custom_logo_sidebar',
                'custom_logo_footer',
                'custom_name',
                'custom_email',
                'custom_mobile',
                'custom_type',
                'custom_description',
            ]);
        });
    }
};
