<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Area;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\AreaImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateAreaExport implements ShouldQueue
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
            return;
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $areas = Area::query()->orderBy('name')->get();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            $user->notify(new AreaImportExportCompleted('export', 'Unable to generate the area export file.'));

            return;
        }

        fputcsv($handle, Area::IMPORT_EXPORT_COLUMNS);

        foreach ($areas as $area) {
            fputcsv($handle, [
                $area->code,
                $area->name,
                $area->companies->pluck('name')->join(', '),
                $area->locations->pluck('name')->join(', '),
                $area->time_limit_in_min ?? 0,
                $area->dms_area_id ?? '',
                $this->normalizeStatusForExport($area->status),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('area-exports/' . $this->fileName, $contents)) {
            $user->notify(new AreaImportExportCompleted('export', 'Unable to save the area export file.'));

            return;
        }

        $user->notify(new AreaImportExportCompleted(
            'export',
            'Area export is ready for download.',
            '/company-structure/areas/export/download/' . rawurlencode($this->fileName),
        ));
    }

    private function normalizeStatusForExport(int|string|null $status): string
    {
        return $status === 1 || strtolower((string) $status) === 'active' ? 'Active' : 'Inactive';
    }
}
