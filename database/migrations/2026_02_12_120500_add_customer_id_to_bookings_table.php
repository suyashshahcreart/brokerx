<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration {
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
                            // ✅ FIX 1: Include the original user ID so the customer
                            //    gets the exact same ID instead of a new auto-increment one.
                            'id' => $user->id,
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

            // ✅ FIX 2: Reset AUTO_INCREMENT to max(id) + 1 so future customer
            //    inserts don't collide with the manually-set IDs we just inserted.
            $maxId = DB::table('customers')->max('id') ?? 0;
            DB::statement("ALTER TABLE customers AUTO_INCREMENT = " . ($maxId + 1));
        }

        DB::table('bookings as b')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->join('customers as c', 'c.mobile', '=', 'u.mobile')
            ->update(['b.customer_id' => DB::raw('c.id')]);

        if (Schema::hasColumn('bookings', 'user_id')) {
            // Must drop foreign key first - the composite index is used by the FK constraint
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'booking_date']);
            });
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['customer_id', 'booking_date']);
        });

        // Delete users who have ONLY the customer role (migrated to customers table).
        // Do NOT delete users with multiple roles (e.g. customer + tourmanager, customer + admin).
        if ($roleId) {
            $customerOnlyUserIds = DB::table('model_has_roles as mhr')
                ->where('mhr.role_id', $roleId)
                ->where('mhr.model_type', User::class)
                ->whereRaw('(SELECT COUNT(*) FROM model_has_roles mhr2 WHERE mhr2.model_id = mhr.model_id AND mhr2.model_type = mhr.model_type) = 1')
                ->pluck('mhr.model_id');

            if ($customerOnlyUserIds->isNotEmpty()) {
                $ids = $customerOnlyUserIds->toArray();

                // Clean up related records before deleting users
                DB::table('model_has_roles')
                    ->whereIn('model_id', $ids)
                    ->where('model_type', User::class)
                    ->delete();

                DB::table('model_has_permissions')
                    ->whereIn('model_id', $ids)
                    ->where('model_type', User::class)
                    ->delete();

                if (Schema::hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')
                        ->where('tokenable_type', User::class)
                        ->whereIn('tokenable_id', $ids)
                        ->delete();
                }

                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')
                        ->whereIn('user_id', $ids)
                        ->delete();
                }

                DB::table('users')->whereIn('id', $ids)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $roleId = DB::table('roles')->where('name', 'customer')->value('id');

        // Recreate users from customers (they were deleted during up migration)
        if ($roleId && Schema::hasTable('customers')) {
            $existingMobiles = DB::table('users')->pluck('mobile')->toArray();

            $customersQuery = DB::table('customers')
                ->whereNotIn('mobile', $existingMobiles)
                ->orderBy('id');
            if (Schema::hasColumn('customers', 'deleted_at')) {
                $customersQuery->whereNull('deleted_at');
            }

            $customersQuery->chunkById(500, function ($customers) use ($roleId) {
                $now = now();
                foreach ($customers as $customer) {
                    // Restore with the original ID so referential integrity is preserved
                    DB::table('users')->insert([
                        'id' => $customer->id,
                        'firstname' => $customer->firstname,
                        'lastname' => $customer->lastname,
                        'mobile' => $customer->mobile,
                        'base_mobile' => $customer->base_mobile ?? $customer->mobile,
                        'country_code' => $customer->country_code,
                        'dial_code' => $customer->dial_code,
                        'country_id' => $customer->country_id,
                        'email' => $customer->email,
                        'password' => $customer->password,
                        'mobile_verified_at' => $customer->mobile_verified_at,
                        'otp' => $customer->otp,
                        'otp_expires_at' => $customer->otp_expires_at,
                        'created_at' => $customer->created_at ?? $now,
                        'updated_at' => $customer->updated_at ?? $now,
                    ]);

                    DB::table('model_has_roles')->insert([
                        'role_id' => $roleId,
                        'model_type' => User::class,
                        'model_id' => $customer->id,
                    ]);
                }
            });

            // Reset AUTO_INCREMENT on users table after manual ID inserts
            $maxId = DB::table('users')->max('id') ?? 0;
            DB::statement("ALTER TABLE users AUTO_INCREMENT = " . ($maxId + 1));
        }

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

        if (Schema::hasColumn('bookings', 'customer_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
            });
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropIndex(['customer_id', 'booking_date']);
            });
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('customer_id');
            });
        }

        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['user_id', 'booking_date']);
        });
    }
};  