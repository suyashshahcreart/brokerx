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
        if (!Schema::hasTable('appointments')) {
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();

                // sheduler details
                $table->foreignId('scheduler_id')->constrained()->onDelete('cascade');
                //comment out for now
                // $table->foreignId('property_id')->nullable()->constrained()->onDelete('set null');

                // date and time;
                $table->date('date');
                $table->time('start_time');
                $table->time('end_time')->nullable();

                // location
                $table->string('address');
                $table->string('city');
                $table->string('state');
                $table->string('country');
                $table->string('pin_code');

                //status of processing
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');

                // assignment details
                $table->foreignId('assigne_by')->nullable()->constrained('users');
                $table->foreignId('assigne_to')->nullable()->constrained('users');

                //complete details
                $table->foreignId('completed_by')->nullable()->constrained('users');
                $table->foreignId('updated_by')->nullable()->constrained('users');

                // time-stamp
                $table->foreignId('create_by')->nullable()->constrained('schedulers');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
