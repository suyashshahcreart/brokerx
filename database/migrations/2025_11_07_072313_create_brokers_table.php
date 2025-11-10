<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brokers', function (Blueprint $table) {
            $table->id();
            // Relationship to user
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Contact info
            $table->string('phone_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('pin_code')->nullable();

            // Professional info
            $table->string('license_number')->unique();
            $table->string('company_name')->nullable();
            $table->string('position_title')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->boolean('license_verified')->default(false);
            $table->decimal('commission_rate', 5, 2)->default(0.00);
            $table->text('bio')->nullable();

            // Media
            $table->string('profile_image')->nullable();
            $table->string('cover_image')->nullable();

            // Optional social links (JSON)
            $table->json('social_links')->nullable();

            $table->enum('status', ['pending', 'approved', 'suspended'])->default('pending');
            $table->boolean('working_status')->default(0);

            // Metrics
            $table->integer('total_sales')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0.00);
            
            // range code.
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brokers');
    }
};
