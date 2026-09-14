<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Location;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LocationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateLocationExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $fileName) {}

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

        $locations = Location::query()->orderBy('name')->get();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            $user?->notify(new LocationImportExportCompleted('export', 'Unable to generate the location export file.'));
            return;
        }

        fputcsv($handle, Location::IMPORT_EXPORT_COLUMNS);
        foreach ($locations as $location) {
            fputcsv($handle, [
                $location->name ?? '',
                $location->code ?? '',
                $location->email ?? '',
                $location->latitude ?? '',
                $location->longitude ?? '',
                $this->normalizeStatusForExport($location->status),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('location-exports/' . $this->fileName, $contents)) {
            $user?->notify(new LocationImportExportCompleted('export', 'Unable to save the location export file.'));
            return;
        }

        $url = '/company-structure/locations/export/download/' . rawurlencode($this->fileName);
        $user?->notify(new LocationImportExportCompleted('export', 'Location export is ready for download.', $url));
    }

    private function normalizeStatusForExport(int|string|null $status): string
    {
        return $status === 1 || strtolower((string) $status) === 'active' ? 'Active' : 'Inactive';
    }
}
