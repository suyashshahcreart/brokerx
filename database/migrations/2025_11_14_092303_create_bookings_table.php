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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('property_type_id')
                ->constrained('property_types')
                ->cascadeOnDelete();

            $table->foreignId('property_sub_type_id')
                ->constrained('property_sub_types')
                ->cascadeOnDelete();

            $table->foreignId('bhk_id')
                ->nullable()
                ->constrained('b_h_k_s')
                ->nullOnDelete();

            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();

            $table->foreignId('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();

            // Property details
            $table->string('furniture_type')->nullable();
            $table->unsignedInteger('area');
            $table->unsignedBigInteger('price')->nullable();

            // Address fields
            $table->string('house_no')->nullable();
            $table->string('building')->nullable();
            $table->string('society_name')->nullable();
            $table->string('address_area')->nullable();
            $table->string('landmark')->nullable();
            $table->text('full_address')->nullable();
            $table->string('pin_code')->nullable();

            // Booking metadata
            $table->date('booking_date')->nullable();
            $table->enum('payment_status', ['unpaid', 'pending', 'paid', 'failed', 'refunded'])->default('unpaid');
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');

            // Audit fields
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('deleted_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'booking_date']);
            $table->index('status');
            $table->index('payment_status');
            $table->index(['city_id', 'state_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
