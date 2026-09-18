<?php

namespace App\Jobs\Tenant;

use App\Models\Tenant\SubDepartment;
use App\Models\Tenant\User;
use App\Notifications\Tenant\SubDepartmentImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateSubDepartmentExport implements ShouldQueue
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
        fputcsv($handle, SubDepartment::IMPORT_EXPORT_COLUMNS);
        foreach (SubDepartment::query()->orderBy('name')->get() as $subDepartment) {
            fputcsv($handle, [
                $subDepartment->name,
                $subDepartment->code,
                $subDepartment->email ?? '',
                $subDepartment->status === 1 ? 'Active' : 'Inactive',
                $subDepartment->departments->pluck('name')->join(', '),
                $subDepartment->locations()->pluck('name')->join(', '),
            ]);
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('sub-department-exports/' . $this->fileName, $contents)) return;
        $user->notify(new SubDepartmentImportExportCompleted('export', 'Sub Department export is ready for download.', '/company-structure/sub-departments/export/download/' . rawurlencode($this->fileName)));
    }
}
