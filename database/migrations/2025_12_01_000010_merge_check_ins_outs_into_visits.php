<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add check-in/out columns onto photographer_visits
        Schema::table('photographer_visits', function (Blueprint $table) {
            // Check-in fields
            $table->string('check_in_photo')->nullable()->after('check_out_id');
            $table->json('check_in_metadata')->nullable()->after('check_in_photo');
            $table->timestamp('checked_in_at')->nullable()->after('check_in_metadata');
            $table->string('check_in_location')->nullable()->after('checked_in_at');
            $table->string('check_in_ip_address', 45)->nullable()->after('check_in_location');
            $table->text('check_in_device_info')->nullable()->after('check_in_ip_address');
            $table->text('check_in_remarks')->nullable()->after('check_in_device_info');

            // Check-out fields
            $table->string('check_out_photo')->nullable()->after('check_in_remarks');
            $table->json('check_out_metadata')->nullable()->after('check_out_photo');
            $table->timestamp('checked_out_at')->nullable()->after('check_out_metadata');
            $table->string('check_out_location')->nullable()->after('checked_out_at');
            $table->string('check_out_ip_address', 45)->nullable()->after('check_out_location');
            $table->text('check_out_device_info')->nullable()->after('check_out_ip_address');
            $table->text('check_out_remarks')->nullable()->after('check_out_device_info');
            $table->integer('photos_taken')->default(0)->after('check_out_remarks');
            $table->text('work_summary')->nullable()->after('photos_taken');

            // Helpful indexes
            $table->index('checked_in_at');
            $table->index('checked_out_at');
        });

        // 2) Migrate existing data from photographer_check_ins into photographer_visits
        if (Schema::hasTable('photographer_check_ins')) {
            DB::statement(
                "UPDATE photographer_visits v " .
                "JOIN photographer_check_ins ci ON ci.visit_id = v.id " .
                "SET v.check_in_photo = ci.photo, " .
                "    v.check_in_metadata = ci.metadata, " .
                "    v.checked_in_at = ci.checked_in_at, " .
                "    v.check_in_location = ci.location, " .
                "    v.check_in_ip_address = ci.ip_address, " .
                "    v.check_in_device_info = ci.device_info, " .
                "    v.check_in_remarks = ci.remarks"
            );
        }

        // 3) Migrate existing data from photographer_check_outs into photographer_visits
        if (Schema::hasTable('photographer_check_outs')) {
            DB::statement(
                "UPDATE photographer_visits v " .
                "JOIN photographer_check_outs co ON co.visit_id = v.id " .
                "SET v.check_out_photo = co.photo, " .
                "    v.check_out_metadata = co.metadata, " .
                "    v.checked_out_at = co.checked_out_at, " .
                "    v.check_out_location = co.location, " .
                "    v.check_out_ip_address = co.ip_address, " .
                "    v.check_out_device_info = co.device_info, " .
                "    v.check_out_remarks = co.remarks, " .
                "    v.photos_taken = co.photos_taken, " .
                "    v.work_summary = co.work_summary"
            );
        }

        // 4) Drop foreign keys and columns check_in_id, check_out_id from visits
        Schema::table('photographer_visits', function (Blueprint $table) {
            if (Schema::hasColumn('photographer_visits', 'check_in_id')) {
                try { $table->dropForeign(['check_in_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['check_in_id']); } catch (\Throwable $e) {}
                $table->dropColumn('check_in_id');
            }
            if (Schema::hasColumn('photographer_visits', 'check_out_id')) {
                try { $table->dropForeign(['check_out_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['check_out_id']); } catch (\Throwable $e) {}
                $table->dropColumn('check_out_id');
            }
        });

        // 5) Drop old tables
        if (Schema::hasTable('photographer_check_ins')) {
            Schema::drop('photographer_check_ins');
        }
        if (Schema::hasTable('photographer_check_outs')) {
            Schema::drop('photographer_check_outs');
        }
    }

    public function down(): void
    {
        // 1) Recreate photographer_check_ins
        if (!Schema::hasTable('photographer_check_ins')) {
            Schema::create('photographer_check_ins', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visit_id')->constrained('photographer_visits')->onDelete('cascade');
                $table->string('photo')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('checked_in_at')->useCurrent();
                $table->string('location')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('device_info')->nullable();
                $table->text('remarks')->nullable();

                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

                $table->timestamps();

                $table->index('visit_id');
                $table->index('checked_in_at');
            });
        }

        // 2) Recreate photographer_check_outs
        if (!Schema::hasTable('photographer_check_outs')) {
            Schema::create('photographer_check_outs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('visit_id')->constrained('photographer_visits')->onDelete('cascade');
                $table->string('photo')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamp('checked_out_at')->useCurrent();
                $table->string('location')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('device_info')->nullable();
                $table->text('remarks')->nullable();
                $table->integer('photos_taken')->default(0);
                $table->text('work_summary')->nullable();

                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

                $table->timestamps();

                $table->index('visit_id');
                $table->index('checked_out_at');
            });
        }

        // 3) Re-add check_in_id / check_out_id columns on visits (without FKs yet)
        Schema::table('photographer_visits', function (Blueprint $table) {
            if (!Schema::hasColumn('photographer_visits', 'check_in_id')) {
                $table->unsignedBigInteger('check_in_id')->nullable()->after('photographer_id');
                $table->index('check_in_id');
            }
            if (!Schema::hasColumn('photographer_visits', 'check_out_id')) {
                $table->unsignedBigInteger('check_out_id')->nullable()->after('check_in_id');
                $table->index('check_out_id');
            }
        });

        // 4) Move merged data back into the recreated tables and set FK ids
        //    Use chunking to avoid memory issues.
        DB::table('photographer_visits')
            ->whereNotNull('checked_in_at')
            ->orderBy('id')
            ->chunkById(500, function ($visits) {
                foreach ($visits as $v) {
                    $checkInId = DB::table('photographer_check_ins')->insertGetId([
                        'visit_id' => $v->id,
                        'photo' => $v->check_in_photo,
                        'metadata' => $v->check_in_metadata,
                        'checked_in_at' => $v->checked_in_at,
                        'location' => $v->check_in_location,
                        'ip_address' => $v->check_in_ip_address,
                        'device_info' => $v->check_in_device_info,
                        'remarks' => $v->check_in_remarks,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('photographer_visits')->where('id', $v->id)->update(['check_in_id' => $checkInId]);
                }
            });

        DB::table('photographer_visits')
            ->whereNotNull('checked_out_at')
            ->orderBy('id')
            ->chunkById(500, function ($visits) {
                foreach ($visits as $v) {
                    $checkOutId = DB::table('photographer_check_outs')->insertGetId([
                        'visit_id' => $v->id,
                        'photo' => $v->check_out_photo,
                        'metadata' => $v->check_out_metadata,
                        'checked_out_at' => $v->checked_out_at,
                        'location' => $v->check_out_location,
                        'ip_address' => $v->check_out_ip_address,
                        'device_info' => $v->check_out_device_info,
                        'remarks' => $v->check_out_remarks,
                        'photos_taken' => $v->photos_taken ?? 0,
                        'work_summary' => $v->work_summary,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    DB::table('photographer_visits')->where('id', $v->id)->update(['check_out_id' => $checkOutId]);
                }
            });

        // 5) Restore foreign keys from visits to recreated tables
        Schema::table('photographer_visits', function (Blueprint $table) {
            try { $table->foreign('check_in_id')->references('id')->on('photographer_check_ins')->onDelete('set null'); } catch (\Throwable $e) {}
            try { $table->foreign('check_out_id')->references('id')->on('photographer_check_outs')->onDelete('set null'); } catch (\Throwable $e) {}
        });

        // 6) Drop merged columns from visits
        Schema::table('photographer_visits', function (Blueprint $table) {
            foreach ([
                'check_in_photo','check_in_metadata','checked_in_at','check_in_location','check_in_ip_address','check_in_device_info','check_in_remarks',
                'check_out_photo','check_out_metadata','checked_out_at','check_out_location','check_out_ip_address','check_out_device_info','check_out_remarks','photos_taken','work_summary',
            ] as $col) {
                if (Schema::hasColumn('photographer_visits', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
