<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ManualPunch;
use App\Models\Tenant\ManualPunchApplication;
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

class GenerateManualPunchApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the manual punch application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $applications = ManualPunchApplication::query()
            ->with(['Users', 'approval_statuses'])
            ->orderBy('punch_date')
            ->orderBy('punch_time')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to generate the manual punch application export file.'));

            return;
        }

        fputcsv($handle, ManualPunchApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->Users->pluck('code')->filter()->implode(','),
                ManualPunch::punchTypeLabel($application->punch_type),
                $this->formatValue($application->punch_date, 'Y-m-d'),
                $this->formatValue($application->punch_time, 'H:i:s'),
                $application->reason ?? '',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'manual-punch-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to save the manual punch application export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.manual-punch-applications.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/manual-punch-applications/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Manual punch application export is ready for download.',
            $downloadUrl,
            '/time-management/manual-punch-applications',
        ));
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }
}
