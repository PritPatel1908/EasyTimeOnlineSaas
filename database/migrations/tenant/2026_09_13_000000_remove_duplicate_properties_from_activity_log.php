<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection(config('activitylog.database_connection'));
        $table = config('activitylog.table_name');

        $connection->table($table)
            ->whereNotNull('properties')
            ->orderBy('id')
            ->chunkById(100, function ($logs) use ($connection, $table): void {
                foreach ($logs as $log) {
                    $properties = json_decode((string) $log->properties, true);

                    if (! is_array($properties)) {
                        continue;
                    }

                    $changed = false;
                    foreach (['old_value', 'new_value'] as $key) {
                        if (array_key_exists($key, $properties)) {
                            unset($properties[$key]);
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        $connection->table($table)
                            ->where('id', $log->id)
                            ->update(['properties' => json_encode($properties, JSON_THROW_ON_ERROR)]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Removed audit properties cannot be restored reliably.
    }
};
