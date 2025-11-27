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
        Schema::create('photographer_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->onDelete('cascade');
            $table->unsignedBigInteger('tour_id')->nullable();
            $table->foreignId('photographer_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('check_in_id')->nullable();
            $table->unsignedBigInteger('check_out_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('visit_date')->nullable();
            $table->enum('status', ['pending', 'checked_in', 'checked_out', 'completed', 'cancelled'])->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Indexes for better query performance
            $table->index('photographer_id');
            $table->index('booking_id');
            $table->index('tour_id');
            $table->index('status');
            $table->index('visit_date');
            $table->index('check_in_id');
            $table->index('check_out_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographer_visits');
    }
};
