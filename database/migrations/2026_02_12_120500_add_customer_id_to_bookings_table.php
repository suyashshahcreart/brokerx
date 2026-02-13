<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'customer_id')) {
                $table->foreignId('customer_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('customers')
                    ->cascadeOnDelete();
            }
        });

        $roleId = DB::table('roles')->where('name', 'customer')->value('id');
        if ($roleId) {
            DB::table('users')
                ->join('model_has_roles', 'model_has_roles.model_id', '=', 'users.id')
                ->where('model_has_roles.role_id', $roleId)
                ->where('model_has_roles.model_type', User::class)
                ->select(
                    'users.id as id',
                    'users.firstname',
                    'users.lastname',
                    'users.mobile',
                    'users.base_mobile',
                    'users.country_code',
                    'users.dial_code',
                    'users.country_id',
                    'users.email',
                    'users.password',
                    'users.mobile_verified_at',
                    'users.otp',
                    'users.otp_expires_at',
                    'users.created_at',
                    'users.updated_at'
                )
                ->orderBy('users.id')
                ->chunkById(500, function ($users) {
                    $rows = [];
                    $now = now();
                    foreach ($users as $user) {
                        $rows[] = [
                            'firstname' => $user->firstname,
                            'lastname' => $user->lastname,
                            'mobile' => $user->mobile,
                            'base_mobile' => $user->base_mobile,
                            'country_code' => $user->country_code,
                            'dial_code' => $user->dial_code,
                            'country_id' => $user->country_id,
                            'email' => $user->email,
                            'password' => $user->password,
                            'mobile_verified_at' => $user->mobile_verified_at,
                            'otp' => $user->otp,
                            'otp_expires_at' => $user->otp_expires_at,
                            'is_active' => true,
                            'created_at' => $user->created_at ?? $now,
                            'updated_at' => $user->updated_at ?? $now,
                        ];
                    }

                    if (!empty($rows)) {
                        DB::table('customers')->upsert(
                            $rows,
                            ['mobile'],
                            [
                                'firstname',
                                'lastname',
                                'base_mobile',
                                'country_code',
                                'dial_code',
                                'country_id',
                                'email',
                                'password',
                                'mobile_verified_at',
                                'otp',
                                'otp_expires_at',
                                'is_active',
                                'updated_at'
                            ]
                        );
                    }
                }, 'id');
        }

        DB::table('bookings as b')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->join('customers as c', 'c.mobile', '=', 'u.mobile')
            ->update(['b.customer_id' => DB::raw('c.id')]);

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'user_id')) {
                // $table->dropConstrainedForeignId('user_id');
                $table->dropIndex(['user_id', 'booking_date']);
            }
            $table->index(['customer_id', 'booking_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->cascadeOnDelete();
            }
        });

        DB::table('bookings as b')
            ->join('customers as c', 'b.customer_id', '=', 'c.id')
            ->join('users as u', 'u.mobile', '=', 'c.mobile')
            ->update(['b.user_id' => DB::raw('u.id')]);

        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'customer_id')) {
                $table->dropIndex(['customer_id', 'booking_date']);
                $table->dropConstrainedForeignId('customer_id');
            }
            $table->index(['user_id', 'booking_date']);
        });
    }
};
