<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\LeaveType;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveTypeImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateLeaveTypeExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

        $leaveTypes = LeaveType::query()->with('location')->orderBy('code')->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new LeaveTypeImportExportCompleted('export', 'Unable to generate the leave type export file.'));
            return;
        }

        fputcsv($handle, LeaveType::IMPORT_EXPORT_COLUMNS);
        foreach ($leaveTypes as $leaveType) {
            fputcsv($handle, [
                $leaveType->code ?? '',
                $leaveType->description ?? '',
                $leaveType->location?->code ?? '',
                $this->normalizeStatusForExport($leaveType->status),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('leave-type-exports/' . $this->fileName, $contents)) {
            $user->notify(new LeaveTypeImportExportCompleted('export', 'Unable to save the leave type export file.'));
            return;
        }

        $url = '/employee-structure/leave-types/export/download/' . rawurlencode($this->fileName);
        $user->notify(new LeaveTypeImportExportCompleted('export', 'Leave Type export is ready for download.', $url));
    }

    private function normalizeStatusForExport(int|string|null $status): string
    {
        return $status === 1 || strtolower((string) $status) === 'active' ? 'Active' : 'Inactive';
    }
}
