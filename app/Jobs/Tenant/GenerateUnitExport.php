<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Unit;
use App\Models\Tenant\User;
use App\Notifications\Tenant\UnitImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateUnitExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) return;
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) return;
        fputcsv($handle, Unit::IMPORT_EXPORT_COLUMNS);
        foreach (Unit::query()->orderBy('name')->get() as $unit) {
            fputcsv($handle, [
                $unit->name,
                $unit->code,
                $unit->status === 1 ? 'Active' : 'Inactive',
                $unit->locations->pluck('name')->join(', '),
            ]);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('unit-exports/' . $this->fileName, $contents)) return;
        $user->notify(new UnitImportExportCompleted('export', 'Unit export is ready for download.', '/company-structure/units/export/download/' . rawurlencode($this->fileName)));
    }
}
