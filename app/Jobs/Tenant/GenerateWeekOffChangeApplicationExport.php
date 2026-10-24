<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffChangeApplication;
use App\Notifications\Tenant\WeekOffChangeApplicationImportExportCompleted;
use App\Support\WeekOffChangeCsv;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateWeekOffChangeApplicationExport implements ShouldQueue
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
        $user = $this->userId === null
            ? null
            : User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the week-off change application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $applications = WeekOffChangeApplication::query()
            ->with(['Users', 'WeekOffChangeDetails', 'approval_statuses'])
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new WeekOffChangeApplicationImportExportCompleted('export', 'Unable to generate the week-off change application export file.'));

            return;
        }

        fputcsv($handle, WeekOffChangeApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            $details = $application->WeekOffChangeDetails;
            fputcsv($handle, [
                $application->Users->pluck('code')->filter()->implode(','),
                $application->from_date?->format('Y-m-d') ?? '',
                $application->is_forever ? '1' : '0',
                $application->to_date?->format('Y-m-d') ?? '',
                $application->reason ?? '',
                $details->map(static fn ($detail): string => WeekOffChangeCsv::formatWeekDay((int) $detail->week_days))->implode(','),
                $details->map(static fn ($detail): string => WeekOffChangeCsv::formatWeekOffType((int) ($detail->wo_type?->value ?? $detail->getRawOriginal('wo_type'))))->implode(','),
                $details->pluck('first_week')->map(static fn (bool $value): string => WeekOffChangeCsv::formatWeekFlag($value))->implode(','),
                $details->pluck('second_week')->map(static fn (bool $value): string => WeekOffChangeCsv::formatWeekFlag($value))->implode(','),
                $details->pluck('third_week')->map(static fn (bool $value): string => WeekOffChangeCsv::formatWeekFlag($value))->implode(','),
                $details->pluck('fourth_week')->map(static fn (bool $value): string => WeekOffChangeCsv::formatWeekFlag($value))->implode(','),
                $details->pluck('fifth_week')->map(static fn (bool $value): string => WeekOffChangeCsv::formatWeekFlag($value))->implode(','),
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'week-off-change-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new WeekOffChangeApplicationImportExportCompleted('export', 'Unable to save the week-off change application export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.week-off-change-applications.download-export', ['file' => $this->fileName]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/week-off-change-applications/export/download/'.rawurlencode($this->fileName);
        }
        $user->notify(new WeekOffChangeApplicationImportExportCompleted(
            'export',
            'Week-off change application export is ready for download.',
            $downloadUrl,
        ));
    }
}
