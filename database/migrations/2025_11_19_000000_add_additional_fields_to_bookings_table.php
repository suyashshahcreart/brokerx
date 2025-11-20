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
        Schema::table('bookings', function (Blueprint $table) {
            $table->text('other_option_details')->nullable()->after('furniture_type');
            $table->string('firm_name')->nullable()->after('other_option_details');
            $table->string('gst_no')->nullable()->after('firm_name');
            $table->string('tour_final_link')->nullable()->after('gst_no');
            $table->string('tour_code')->nullable()->after('tour_final_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'other_option_details',
                'firm_name',
                'gst_no',
                'tour_final_link',
                'tour_code'
            ]);
        });
    }
};

