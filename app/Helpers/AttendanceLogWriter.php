<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\Tenant\AttendanceLog;
use Illuminate\Support\Facades\DB;

class AttendanceLogWriter
{
    /**
     * @param  array<string, mixed>  $identity
     * @param  array<string, mixed>  $attributes
     */
    public static function updateUnlessLocked(array $identity, array $attributes): bool
    {
        return DB::transaction(function () use ($identity, $attributes): bool {
            $log = AttendanceLog::query()
                ->where($identity)
                ->lockForUpdate()
                ->first();

            if ($log?->is_locked) {
                return false;
            }

            if ($log === null) {
                AttendanceLog::query()->create($identity + $attributes);

                return true;
            }

            $log->fill($attributes)->save();

            return true;
        });
    }
}
