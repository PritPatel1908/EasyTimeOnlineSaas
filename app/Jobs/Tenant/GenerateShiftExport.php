<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\Shift;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ShiftImportExportCompleted;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateShiftExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $fileName,
    ) {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId !== null
            ? User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId)
            : null;

        if ($user === null) {
            throw new RuntimeException('The user requesting the shift export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $shifts = Shift::query()->orderBy('name')->get();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            $user->notify(new ShiftImportExportCompleted('export', 'Unable to generate the shift export file.'));

            return;
        }

        $headers = Shift::IMPORT_EXPORT_COLUMNS;
        fputcsv($handle, $headers);

        foreach ($shifts as $shift) {
            $locations = $this->resolveNames(Location::class, $shift->location_id);
            $companies = $this->resolveNames(Company::class, $shift->company_id);
            $row = [];

            foreach ($headers as $column) {
                $row[] = match ($column) {
                    'location' => implode(', ', $locations),
                    'company' => implode(', ', $companies),
                    'is_flaxible', 'is_night_shift', 'set_cutoff' => $shift->{$column} ? 'Yes' : 'No',
                    'status' => (int) $shift->status === 1 ? 'Active' : 'Inactive',
                    default => $this->formatValue($shift->{$column}),
                };
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        $filePath = 'shift-exports/' . $this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($filePath, $contents)) {
            $user->notify(new ShiftImportExportCompleted('export', 'Unable to save the shift export file.'));

            return;
        }

        $user->notify(new ShiftImportExportCompleted(
            'export',
            'Shift export is ready for download.',
            $this->buildDownloadUrl(),
        ));
    }

    private function buildDownloadUrl(): string
    {
        try {
            return route('tenant.time-management.shifts.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            return '/time-management/shifts/export/download/' . rawurlencode($this->fileName);
        }
    }

    private function formatValue(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('H:i:s');
        }

        return $value === null ? '' : (string) $value;
    }

    /**
     * @return array<int, string>
     */
    private function resolveNames(string $model, mixed $value): array
    {
        $ids = $this->idsFrom($value);

        if ($ids === []) {
            return [];
        }

        return $model::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }

    /**
     * @return array<int, int>
     */
    private function idsFrom(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : [$value];
        }

        return array_values(array_filter(
            (array) $value,
            static fn(mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
        ));
    }
}
