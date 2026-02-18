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
            $table->string('contact_google_location')->nullable()->after('footer_brand_mobile');
            $table->string('contact_website')->nullable()->after('contact_google_location');
            $table->string('contact_email')->nullable()->after('contact_website');
            $table->string('contact_phone_no')->nullable()->after('contact_email');
            $table->string('contact_whatsapp_no')->nullable()->after('contact_phone_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            $table->dropColumn([
                'contact_google_location',
                'contact_website',
                'contact_email',
                'contact_phone_no',
                'contact_whatsapp_no',
            ]);
        });
    }
};
