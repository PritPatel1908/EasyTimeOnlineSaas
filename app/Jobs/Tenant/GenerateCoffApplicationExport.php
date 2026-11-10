<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\CoffApplication;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CoffApplicationImportExportCompleted;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateCoffApplicationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the COFF application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $applications = CoffApplication::query()
            ->with(['user', 'approval_statuses'])
            ->orderBy('coff_against_date')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to generate the COFF application export file.');
        }

        fputcsv($handle, CoffApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->user?->code ?? '',
                $this->formatValue($application->coff_against_date, 'Y-m-d'),
                $this->formatValue($application->coff_from_date, 'Y-m-d'),
                $this->formatValue($application->coff_to_date, 'Y-m-d'),
                $application->is_half_day ? 'Yes' : 'No',
                $application->is_first_half ? 'Yes' : 'No',
                $application->is_second_half ? 'Yes' : 'No',
                $application->overallApprovalStatus(),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'coff-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('Unable to save the COFF application export file.');
        }

        $downloadUrl = url('/time-management/coff-applications/export/download/'.rawurlencode($this->fileName));

        $user->notify(new CoffApplicationImportExportCompleted(
            'export',
            'COFF application export is ready for download.',
            $downloadUrl,
            '/time-management/coff-applications',
        ));
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }
}
