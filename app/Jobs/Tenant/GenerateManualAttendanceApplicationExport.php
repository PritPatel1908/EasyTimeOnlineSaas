<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ManualAttendanceApplication;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateManualAttendanceApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the manual attendance application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $applications = ManualAttendanceApplication::query()
            ->with(['Users', 'approval_statuses'])
            ->orderBy('in_time')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to generate the manual attendance application export file.'));

            return;
        }

        fputcsv($handle, ManualAttendanceApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->Users->pluck('code')->filter()->implode(','),
                $this->formatValue($application->in_time, 'Y-m-d H:i:s'),
                $this->formatValue($application->out_time, 'Y-m-d H:i:s'),
                $application->reason ?? '',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'manual-attendance-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to save the manual attendance application export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.manual-attendance-applications.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Illuminate\Routing\Exceptions\UrlGenerationException) {
            $downloadUrl = '/time-management/manual-attendance-applications/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Manual attendance application export is ready for download.',
            $downloadUrl,
            '/time-management/manual-attendance-applications',
        ));
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }
}
