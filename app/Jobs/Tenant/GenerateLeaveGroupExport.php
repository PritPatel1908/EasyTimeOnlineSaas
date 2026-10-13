<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\LeaveGroup;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveGroupImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateLeaveGroupExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the Leave Group export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to prepare the Leave Group export.');
        }

        fputcsv($handle, LeaveGroup::IMPORT_EXPORT_COLUMNS);
        foreach (LeaveGroup::query()->with('financial_year')->orderBy('name')->get() as $leaveGroup) {
            fputcsv($handle, [
                $leaveGroup->name,
                $leaveGroup->financial_year_id ?? '',
                $leaveGroup->is_monthly ? 'Yes' : 'No',
                $leaveGroup->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('leave-group-exports/'.$this->fileName, $contents)) {
            throw new RuntimeException('Unable to write the Leave Group export file.');
        }

        $user->notify(new LeaveGroupImportExportCompleted(
            'export',
            'Leave Group export is ready for download.',
            '/employee-structure/leave-groups/export/download/'.rawurlencode($this->fileName),
        ));
    }
}
