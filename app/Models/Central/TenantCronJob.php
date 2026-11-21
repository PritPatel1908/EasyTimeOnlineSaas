<?php

declare(strict_types=1);

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class TenantCronJob extends Model
{
    public const TASK_LEAVE_MUSTER = 'create_leave_muster';

    public const TASK_PUNCH_PROCESS = 'process_punches';

    public const TASKS = [
        self::TASK_LEAVE_MUSTER => 'Leave Muster',
        self::TASK_PUNCH_PROCESS => 'Punch Process',
    ];

    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'task_key',
        'name',
        'schedule_type',
        'schedule_time',
        'interval_minutes',
        'enabled',
        'next_run_at',
        'last_run_at',
        'last_status',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'interval_minutes' => 'integer',
            'next_run_at' => 'datetime',
            'last_run_at' => 'datetime',
        ];
    }

    public static function ensureForTenant(string $tenantId): self
    {
        return self::query()->firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'task_key' => self::TASK_LEAVE_MUSTER,
            ],
            [
                'name' => self::TASKS[self::TASK_LEAVE_MUSTER],
                'schedule_type' => 'daily',
                'schedule_time' => '00:00:00',
                'interval_minutes' => null,
                'enabled' => true,
                'next_run_at' => self::nextRunAt('daily', '00:00:00'),
                'last_status' => 'never',
            ],
        );
    }

    public static function nextRunAt(
        string $scheduleType,
        ?string $scheduleTime = null,
        ?int $intervalMinutes = null,
        ?Carbon $reference = null,
    ): Carbon {
        return match ($scheduleType) {
            'daily' => self::nextDailyRunAt($scheduleTime ?? '00:00:00', $reference),
            'interval' => self::nextIntervalRunAt($intervalMinutes, $reference),
            default => throw new InvalidArgumentException('Unsupported cron schedule type.'),
        };
    }

    public static function nextDailyRunAt(string $time, ?Carbon $reference = null): Carbon
    {
        $reference = $reference?->copy() ?? now();
        $time = strlen($time) === 5 ? $time.':00' : $time;
        $nextRun = $reference->copy()->startOfDay()->setTimeFromTimeString($time);

        return $nextRun->gt($reference) ? $nextRun : $nextRun->addDay();
    }

    private static function nextIntervalRunAt(?int $intervalMinutes, ?Carbon $reference): Carbon
    {
        if ($intervalMinutes === null || $intervalMinutes < 1) {
            throw new InvalidArgumentException('Interval schedules require a positive interval in minutes.');
        }

        return ($reference?->copy() ?? now())->addMinutes($intervalMinutes);
    }
}
