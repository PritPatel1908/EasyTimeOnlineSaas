<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwapApplication;
use App\Notifications\Tenant\WeekOffSwapApplicationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateWeekOffSwapApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the week-off swap application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $applications = WeekOffSwapApplication::query()
            ->with(['Users', 'approval_statuses'])
            ->orderBy('week_date')
            ->orderBy('swap_date')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new WeekOffSwapApplicationImportExportCompleted(
                'export',
                'Unable to generate the week-off swap application export file.',
            ));

            return;
        }

        fputcsv($handle, WeekOffSwapApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->Users->pluck('code')->filter()->implode(','),
                $application->week_date?->format('Y-m-d') ?? '',
                $application->swap_date?->format('Y-m-d') ?? '',
                $application->reason ?? '',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'week-off-swap-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new WeekOffSwapApplicationImportExportCompleted(
                'export',
                'Unable to save the week-off swap application export file.',
            ));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.week-off-swap-applications.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/week-off-swap-applications/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new WeekOffSwapApplicationImportExportCompleted(
            'export',
            'Week-off swap application export is ready for download.',
            $downloadUrl,
        ));
    }
}
