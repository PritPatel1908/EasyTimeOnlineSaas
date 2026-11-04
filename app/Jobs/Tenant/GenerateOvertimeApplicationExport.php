<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\OvertimeApplication;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\OvertimeApplicationImportExportCompleted;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateOvertimeApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the overtime application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $applications = OvertimeApplication::query()
            ->with(['user', 'approval_statuses'])
            ->orderBy('date')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new OvertimeApplicationImportExportCompleted(
                'export',
                'Unable to generate the overtime application export file.',
            ));

            return;
        }

        fputcsv($handle, OvertimeApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->user?->code ?? '',
                $this->formatValue($application->date, 'Y-m-d'),
                $application->overtime_minutes,
                $application->reason ?? '',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'overtime-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new OvertimeApplicationImportExportCompleted(
                'export',
                'Unable to save the overtime application export file.',
            ));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.overtime-applications.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/overtime-applications/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new OvertimeApplicationImportExportCompleted(
            'export',
            'Overtime application export is ready for download.',
            $downloadUrl,
            '/time-management/overtime-applications',
        ));
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }
}
