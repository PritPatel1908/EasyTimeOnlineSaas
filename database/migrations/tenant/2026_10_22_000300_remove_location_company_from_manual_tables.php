<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('manual_attendances')
            ->where(function ($query): void {
                $query->where(function ($query): void {
                    $query->whereNull('location_ids')->whereNotNull('location_id');
                })->orWhere(function ($query): void {
                    $query->whereNull('company_ids')->whereNotNull('company_id');
                });
            })
            ->orderBy('id')
            ->chunkById(500, function ($attendances): void {
                foreach ($attendances as $attendance) {
                    $updates = [];
                    if ($attendance->location_ids === null && $attendance->location_id !== null) {
                        $updates['location_ids'] = json_encode([(int) $attendance->location_id], JSON_THROW_ON_ERROR);
                    }
                    if ($attendance->company_ids === null && $attendance->company_id !== null) {
                        $updates['company_ids'] = json_encode([(int) $attendance->company_id], JSON_THROW_ON_ERROR);
                    }
                    if ($updates !== []) {
                        DB::table('manual_attendances')
                            ->where('id', $attendance->id)
                            ->update($updates);
                    }
                }
            });

        foreach (['manual_punches', 'manual_attendances'] as $tableName) {
            $columns = array_values(array_filter(
                ['location_id', 'company_id'],
                static fn (string $column): bool => Schema::hasColumn($tableName, $column),
            ));
            $foreignKeys = DB::getDriverName() === 'sqlite'
                ? []
                : array_filter(
                    Schema::getForeignKeys($tableName),
                    static fn (array $foreignKey): bool => count(array_intersect($foreignKey['columns'], $columns)) > 0,
                );

            Schema::table($tableName, function (Blueprint $table) use ($columns, $foreignKeys): void {
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign($foreignKey['name']);
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        Schema::table('shift_changes', function (Blueprint $table): void {
            $table->dropColumn(['location_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::table('manual_punches', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('punch_time')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->after('location_id')->constrained('companies')->cascadeOnDelete();
        });

        Schema::table('manual_attendances', function (Blueprint $table): void {
            $table->foreignId('location_id')->nullable()->after('id')->constrained('locations')->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->after('location_id')->constrained('companies')->cascadeOnDelete();
        });

        Schema::table('shift_changes', function (Blueprint $table): void {
            $table->json('location_id')->nullable()->after('shift_rotation_id');
            $table->json('company_id')->nullable()->after('location_id');
        });
    }
};
