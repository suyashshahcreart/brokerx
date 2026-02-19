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
            $table->json('metadata')->nullable();
            $table->timestamp('visit_date')->nullable();
            $table->enum('status', ['pending', 'checked_in', 'checked_out', 'completed', 'cancelled'])->default('pending');
            $table->text('cancel_reason')->nullable();
            $table->text('notes')->nullable();
            
            // Check-in fields
            $table->string('check_in_photo')->nullable();
            $table->json('check_in_metadata')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->string('check_in_location')->nullable();
            $table->string('check_in_ip_address', 45)->nullable();
            $table->text('check_in_device_info')->nullable();
            $table->text('check_in_remarks')->nullable();

            // Check-out fields
            $table->string('check_out_photo')->nullable();
            $table->json('check_out_metadata')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('check_out_location')->nullable();
            $table->string('check_out_ip_address', 45)->nullable();
            $table->text('check_out_device_info')->nullable();
            $table->text('check_out_remarks')->nullable();
            $table->integer('photos_taken')->default(0);
            $table->text('work_summary')->nullable();
            
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
            $table->index('checked_in_at');
            $table->index('checked_out_at');
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
