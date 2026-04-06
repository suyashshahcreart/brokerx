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
            $table->boolean('show_user_details_button')->default(true);
            $table->string('user_details_button_icon')->nullable();
            $table->string('user_details_button_tooltip')->nullable();
            $table->json('user_details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn(['show_user_details_button', 'user_details_button_icon', 'user_details_button_tooltip', 'user_details']);
        });
    }
};
