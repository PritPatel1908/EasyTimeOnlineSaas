<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\ShiftRotation;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ShiftRotationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateShiftRotationExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the shift rotation export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $rotations = ShiftRotation::query()
            ->with(['rotation_values.shift'])
            ->orderBy('code')
            ->get();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            $user->notify(new ShiftRotationImportExportCompleted('export', 'Unable to generate the shift rotation export file.'));

            return;
        }

        $headers = ShiftRotation::IMPORT_EXPORT_COLUMNS;
        fputcsv($handle, $headers);

        foreach ($rotations as $rotation) {
            $row = [
                'code' => $rotation->code,
                'start_date' => $rotation->start_date?->format('Y-m-d') ?? '',
                'end_date' => $rotation->end_date?->format('Y-m-d') ?? '',
                'is_forever' => $rotation->is_forever ? 'Yes' : 'No',
                'skip_days_on_month_end' => $rotation->skip_days_on_month_end ? 'Yes' : 'No',
                'skip_days_on_year_end' => $rotation->skip_days_on_year_end ? 'Yes' : 'No',
                'shift_status' => $rotation->shift_status ? 'Yes' : 'No',
                'status' => (int) $rotation->status === 1 ? 'Active' : 'Inactive',
                'shift_code' => $rotation->rotation_values
                    ->map(static fn ($value): string => $value->shift?->code ?? '')
                    ->implode(','),
                'days' => $rotation->rotation_values
                    ->map(static fn ($value): string => (string) $value->days)
                    ->implode(','),
            ];

            fputcsv($handle, array_map(static fn (string $column): string => $row[$column], $headers));
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        $filePath = 'shift-rotation-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($filePath, $contents)) {
            $user->notify(new ShiftRotationImportExportCompleted('export', 'Unable to save the shift rotation export file.'));

            return;
        }

        $user->notify(new ShiftRotationImportExportCompleted(
            'export',
            'Shift rotation export is ready for download.',
            $this->buildDownloadUrl(),
        ));
    }

    private function buildDownloadUrl(): string
    {
        try {
            return route('tenant.time-management.shift-rotations.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            return '/time-management/shift-rotations/export/download/'.rawurlencode($this->fileName);
        }
    }
}
