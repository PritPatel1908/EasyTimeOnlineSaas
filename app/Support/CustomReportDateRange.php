<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use InvalidArgumentException;

final class CustomReportDateRange
{
    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    public function resolve(
        string $preset,
        ?DateTimeInterface $customFromDate = null,
        ?DateTimeInterface $customToDate = null,
    ): array {
        $today = CarbonImmutable::today();

        return match ($preset) {
            'today' => ['from' => $today, 'to' => $today],
            'yesterday' => ['from' => $today->subDay(), 'to' => $today->subDay()],
            'this_week' => ['from' => $today->startOfWeek(), 'to' => $today],
            'last_week' => [
                'from' => $today->startOfWeek()->subWeek(),
                'to' => $today->startOfWeek()->subWeek()->endOfWeek(),
            ],
            'this_month' => ['from' => $today->startOfMonth(), 'to' => $today],
            'last_month' => [
                'from' => $today->startOfMonth()->subMonth(),
                'to' => $today->startOfMonth()->subMonth()->endOfMonth(),
            ],
            'this_quarter' => ['from' => $today->startOfQuarter(), 'to' => $today],
            'last_quarter' => [
                'from' => $today->startOfQuarter()->subQuarter(),
                'to' => $today->startOfQuarter()->subQuarter()->endOfQuarter(),
            ],
            'this_year' => ['from' => $today->startOfYear(), 'to' => $today],
            'last_year' => [
                'from' => $today->startOfYear()->subYear(),
                'to' => $today->startOfYear()->subYear()->endOfYear(),
            ],
            'custom' => $this->customRange($customFromDate, $customToDate),
            default => throw new InvalidArgumentException('Unsupported custom report date range preset.'),
        };
    }

    /**
     * @return array{from: CarbonImmutable, to: CarbonImmutable}
     */
    private function customRange(?DateTimeInterface $fromDate, ?DateTimeInterface $toDate): array
    {
        if ($fromDate === null || $toDate === null) {
            throw new InvalidArgumentException('Custom report date ranges require both a from date and a to date.');
        }

        return [
            'from' => CarbonImmutable::instance($fromDate)->startOfDay(),
            'to' => CarbonImmutable::instance($toDate)->startOfDay(),
        ];
    }
}
