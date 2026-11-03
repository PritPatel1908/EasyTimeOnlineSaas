<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\ShortLeaveApplication;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ShortLeaveApplicationImportExportCompleted;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateShortLeaveApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the short leave application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $applications = ShortLeaveApplication::query()
            ->with(['user', 'approval_statuses'])
            ->orderBy('date')
            ->orderBy('from_time')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ShortLeaveApplicationImportExportCompleted(
                'export',
                'Unable to generate the short leave application export file.',
                url: '/time-management/short-leave-applications',
            ));

            return;
        }

        fputcsv($handle, ShortLeaveApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->user?->code ?? '',
                $application->reason ?? '',
                $this->formatValue($application->date, 'Y-m-d'),
                $this->formatValue($application->from_time, 'H:i'),
                $this->formatValue($application->to_time, 'H:i'),
                $application->minutes ?? '',
                $application->short_leave_type ?? '',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'short-leave-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ShortLeaveApplicationImportExportCompleted(
                'export',
                'Unable to save the short leave application export file.',
                url: '/time-management/short-leave-applications',
            ));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.short-leave-applications.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/short-leave-applications/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new ShortLeaveApplicationImportExportCompleted(
            'export',
            'Short leave application export is ready for download.',
            $downloadUrl,
            '/time-management/short-leave-applications',
        ));
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }
}
