<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'show_contact_google_location')) {
                $table->boolean('show_contact_google_location')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_email')) {
                $table->boolean('show_contact_email')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_website')) {
                $table->boolean('show_contact_website')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_phone_no')) {
                $table->boolean('show_contact_phone_no')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_whatsapp_no')) {
                $table->boolean('show_contact_whatsapp_no')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'document_auth_required')) {
                $table->boolean('document_auth_required')->nullable()->change();
            }

            if (Schema::hasColumn('tours', 'show_user_details_button')) {
                $table->boolean('show_user_details_button')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'show_contact_google_location')) {
                $table->boolean('show_contact_google_location')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_email')) {
                $table->boolean('show_contact_email')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_website')) {
                $table->boolean('show_contact_website')->nullable(false)->default(false)->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_phone_no')) {
                $table->boolean('show_contact_phone_no')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'show_contact_whatsapp_no')) {
                $table->boolean('show_contact_whatsapp_no')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'document_auth_required')) {
                $table->boolean('document_auth_required')->nullable(false)->default(true)->change();
            }

            if (Schema::hasColumn('tours', 'show_user_details_button')) {
                $table->boolean('show_user_details_button')->nullable(false)->default(true)->change();
            }
        });
    }
};
