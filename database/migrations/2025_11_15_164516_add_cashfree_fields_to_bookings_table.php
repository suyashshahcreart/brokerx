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
            $table->string('cashfree_order_id', 191)->nullable()->after('status')->unique();
            $table->string('cashfree_payment_session_id')->nullable()->after('cashfree_order_id');
            $table->string('cashfree_payment_status')->nullable()->after('cashfree_payment_session_id');
            $table->string('cashfree_payment_method')->nullable()->after('cashfree_payment_status');
            $table->unsignedBigInteger('cashfree_payment_amount')->nullable()->after('cashfree_payment_method');
            $table->string('cashfree_payment_currency', 3)->default('INR')->after('cashfree_payment_amount');
            $table->string('cashfree_reference_id')->nullable()->after('cashfree_payment_currency');
            $table->timestamp('cashfree_payment_at')->nullable()->after('cashfree_reference_id');
            $table->text('cashfree_payment_message')->nullable()->after('cashfree_payment_at');
            $table->json('cashfree_payment_meta')->nullable()->after('cashfree_payment_message');
            $table->json('cashfree_last_response')->nullable()->after('cashfree_payment_meta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'cashfree_order_id',
                'cashfree_payment_session_id',
                'cashfree_payment_status',
                'cashfree_payment_method',
                'cashfree_payment_amount',
                'cashfree_payment_currency',
                'cashfree_reference_id',
                'cashfree_payment_at',
                'cashfree_payment_message',
                'cashfree_payment_meta',
                'cashfree_last_response',
            ]);
        });
    }
};
