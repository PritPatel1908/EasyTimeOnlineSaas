<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Department;
use App\Models\Tenant\User;
use App\Notifications\Tenant\DepartmentImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateDepartmentExport implements ShouldQueue
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
        fputcsv($handle, Department::IMPORT_EXPORT_COLUMNS);
        foreach (Department::query()->orderBy('name')->get() as $department) {
            fputcsv($handle, [
                $department->name,
                $department->code,
                $department->email ?? '',
                $department->status === 1 ? 'Active' : 'Inactive',
                $department->companies->pluck('name')->join(', '),
                $department->locations->pluck('name')->join(', '),
            ]);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('department-exports/' . $this->fileName, $contents)) return;
        $user->notify(new DepartmentImportExportCompleted('export', 'Department export is ready for download.', '/company-structure/departments/export/download/' . rawurlencode($this->fileName)));
    }
}
