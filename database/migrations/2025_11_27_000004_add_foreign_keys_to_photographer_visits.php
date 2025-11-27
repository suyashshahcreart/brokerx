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
        Schema::table('photographer_visits', function (Blueprint $table) {
            $table->foreign('check_in_id')->references('id')->on('photographer_check_ins')->onDelete('set null');
            $table->foreign('check_out_id')->references('id')->on('photographer_check_outs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographer_visits', function (Blueprint $table) {
            $table->dropForeign(['check_in_id']);
            $table->dropForeign(['check_out_id']);
        });
    }
};
