<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ManualAttendance;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Routing\Exceptions\UrlGenerationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateManualAttendanceExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the manual attendance export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $attendances = ManualAttendance::query()
            ->with('Users')
            ->orderBy('in_time')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to generate the manual attendance export file.'));

            return;
        }

        fputcsv($handle, ManualAttendance::IMPORT_EXPORT_COLUMNS);
        foreach ($attendances as $attendance) {
            fputcsv($handle, [
                $attendance->Users->pluck('code')->filter()->implode(','),
                $this->formatValue($attendance->in_time, 'Y-m-d H:i:s'),
                $this->formatValue($attendance->out_time, 'Y-m-d H:i:s'),
                $attendance->reason ?? '',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'manual-attendance-exports/' . $this->fileName;

        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to save the manual attendance export file.'));

            return;
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Manual attendance export is ready for download.',
            $this->downloadUrl(),
            '/time-management/manual-attendances',
        ));
    }

    private function downloadUrl(): string
    {
        try {
            return route('tenant.time-management.manual-attendances.download-export', ['file' => $this->fileName]);
        } catch (UrlGenerationException) {
            return '/time-management/manual-attendances/export/download/' . rawurlencode($this->fileName);
        }
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }

}
