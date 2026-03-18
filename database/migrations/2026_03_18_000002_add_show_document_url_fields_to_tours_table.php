<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            
            // document related fields
            if (!Schema::hasColumn('tours', 'document_auth_required')) {
                $table->boolean('document_auth_required')->default(true)->after('show_contact_whatsapp_no');
            }

            if (!Schema::hasColumn('tours', 'show_document_url')) {
                $table->boolean('show_document_url')->default(true)->after('document_auth_required');
            }

            if (!Schema::hasColumn('tours', 'show_document_url2')) {
                $table->boolean('show_document_url2')->default(true)->after('show_document_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tours', function (Blueprint $table) {
            if (Schema::hasColumn('tours', 'document_auth_required')) {
                $table->dropColumn('document_auth_required');
            }
            
            if (Schema::hasColumn('tours', 'show_document_url2')) {
                $table->dropColumn('show_document_url2');
            }

            if (Schema::hasColumn('tours', 'show_document_url')) {
                $table->dropColumn('show_document_url');
            }
        });
    }
};
