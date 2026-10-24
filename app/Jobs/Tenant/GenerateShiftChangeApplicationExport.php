<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\ShiftChangeApplication;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateShiftChangeApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the shift change application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $applications = ShiftChangeApplication::query()
            ->with(['Users', 'shifts', 'shift_rotation', 'approval_statuses'])
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted(
                'export',
                'Unable to generate the shift change application export file.',
                url: '/time-management/shift-change-applications',
            ));

            return;
        }

        fputcsv($handle, ShiftChangeApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->Users->pluck('code')->filter()->implode(','),
                $application->shift_type ?? '',
                $application->shifts->pluck('code')->filter()->implode(','),
                $application->shift_rotation?->code ?? '',
                $application->from_date?->format('Y-m-d') ?? '',
                $application->is_forever ? '1' : '0',
                $application->to_date?->format('Y-m-d') ?? '',
                $application->reason ?? '',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'shift-change-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted(
                'export',
                'Unable to save the shift change application export file.',
                url: '/time-management/shift-change-applications',
            ));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.shift-change-applications.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/shift-change-applications/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Shift change application export is ready for download.',
            $downloadUrl,
            '/time-management/shift-change-applications',
        ));
    }
}
