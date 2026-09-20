<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Designation;
use App\Models\Tenant\User;
use App\Notifications\Tenant\DesignationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateDesignationExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $fileName)
    {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) {
            return;
        }
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            return;
        }
        fputcsv($handle, Designation::IMPORT_EXPORT_COLUMNS);
        foreach (Designation::query()->orderBy('name')->get() as $designation) {
            fputcsv($handle, [
                $designation->name,
                $designation->code,
                $designation->status === 1 ? 'Active' : 'Inactive',
                $designation->companies()->pluck('name')->join(', '),
                $designation->locations()->pluck('name')->join(', '),
                $designation->categories()->pluck('name')->join(', '),
            ]);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('designation-exports/' . $this->fileName, $contents)) {
            return;
        }
        $user->notify(new DesignationImportExportCompleted(
            'export',
            'Designation export is ready for download.',
            '/employee-structure/designations/export/download/' . rawurlencode($this->fileName),
        ));
    }
}
