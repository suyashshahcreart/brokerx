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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('mobile', 20)->unique();
            $table->string('base_mobile', 20)->nullable();
            $table->char('country_code', 2)->nullable();
            $table->char('dial_code', 6)->nullable();
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('otp', 6)->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->string('cover_photo')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('company_name')->nullable();
            $table->string('tag_line')->nullable();
            $table->boolean('is_active')->default(true)->index();

            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
