<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\ManualPunch;
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

class GenerateManualPunchExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the manual punch export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $punches = ManualPunch::query()->with('Users')->orderBy('punch_date')->orderBy('punch_time')->orderBy('id')->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to generate the manual punch export file.'));

            return;
        }

        fputcsv($handle, ManualPunch::IMPORT_EXPORT_COLUMNS);
        foreach ($punches as $punch) {
            fputcsv($handle, [
                $punch->Users->pluck('code')->filter()->implode(';'),
                $this->namesAndCodesForIds($punch->location_id, Location::class),
                $this->namesAndCodesForIds($punch->company_id, Company::class),
                $punch->punch_type,
                $this->formatValue($punch->punch_date, 'Y-m-d'),
                $this->formatValue($punch->punch_time, 'H:i:s'),
                $punch->reason ?? '',
                (int) $punch->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'manual-punch-exports/' . $this->fileName;

        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted('export', 'Unable to save the manual punch export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.manual-punches.download-export', ['file' => $this->fileName]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/manual-punches/export/download/' . rawurlencode($this->fileName);
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Manual punch export is ready for download.',
            $downloadUrl,
        ));
    }

    private function formatValue(mixed $value, string $format): string
    {
        return $value instanceof CarbonInterface
            ? $value->format($format)
            : ($value === null ? '' : (string) $value);
    }

    private function namesAndCodesForIds(mixed $value, string $model): string
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        $ids = array_values(array_filter((array) $value, static fn (mixed $id): bool => is_int($id) || ctype_digit((string) $id)));

        if ($ids === []) {
            return '';
        }

        return $model::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['name', 'code'])
            ->map(static fn ($record): string => $record->name . ($record->code ? ' (' . $record->code . ')' : ''))
            ->implode(';');
    }
}
