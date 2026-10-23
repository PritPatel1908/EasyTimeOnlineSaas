<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChange;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateWeekOffChangeExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId !== null
            ? User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId)
            : null;
        if ($user === null) {
            throw new RuntimeException('The user requesting the week-off change export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $changes = WeekOffChange::query()
            ->with(['Users', 'WeekOffChangeDetails'])
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted(
                'export',
                'Unable to generate the week-off change export file.',
                url: '/time-management/week-off-changes',
            ));

            return;
        }

        fputcsv($handle, WeekOffChange::IMPORT_EXPORT_COLUMNS);
        foreach ($changes as $change) {
            $details = $change->WeekOffChangeDetails;
            fputcsv($handle, [
                $change->Users->pluck('code')->filter()->implode(','),
                $change->from_date?->format('Y-m-d') ?? '',
                $change->is_forever ? '1' : '0',
                $change->to_date?->format('Y-m-d') ?? '',
                $change->reason ?? '',
                $details->pluck('week_days')->implode(','),
                $details->map(static fn ($detail): string => (string) ($detail->wo_type?->value ?? $detail->getRawOriginal('wo_type')))->implode(','),
                $details->pluck('first_week')->map(static fn (bool $value): int => (int) $value)->implode(','),
                $details->pluck('second_week')->map(static fn (bool $value): int => (int) $value)->implode(','),
                $details->pluck('third_week')->map(static fn (bool $value): int => (int) $value)->implode(','),
                $details->pluck('fourth_week')->map(static fn (bool $value): int => (int) $value)->implode(','),
                $details->pluck('fifth_week')->map(static fn (bool $value): int => (int) $value)->implode(','),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'week-off-change-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted(
                'export',
                'Unable to save the week-off change export file.',
                url: '/time-management/week-off-changes',
            ));

            return;
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Week-off change export is ready for download.',
            $this->downloadUrl(),
            '/time-management/week-off-changes',
        ));
    }

    private function downloadUrl(): string
    {
        try {
            return route('tenant.time-management.week-off-changes.download-export', ['file' => $this->fileName]);
        } catch (\Throwable) {
            return '/time-management/week-off-changes/export/download/'.rawurlencode($this->fileName);
        }
    }
}