<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\LeaveApplication;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\LeaveApplicationImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateLeaveApplicationExport implements ShouldQueue
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
        $user = $this->userId === null
            ? null
            : User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId);
        if ($user === null) {
            throw new RuntimeException('The user requesting the leave application export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $applications = LeaveApplication::query()
            ->with(['users', 'leave_type', 'leave_reason'])
            ->orderBy('application_date')
            ->orderBy('id')
            ->get();

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new LeaveApplicationImportExportCompleted('export', 'Unable to generate the leave application export file.'));

            return;
        }

        fputcsv($handle, LeaveApplication::IMPORT_EXPORT_COLUMNS);
        foreach ($applications as $application) {
            fputcsv($handle, [
                $application->users->pluck('code')->filter()->implode(','),
                $application->leave_type?->code ?? '',
                $application->leave_reason?->leave_reason ?? '',
                $application->reason_explanation ?? '',
                $application->is_only_half ? 'Yes' : 'No',
                $application->from_date?->format('Y-m-d') ?? '',
                $application->is_second_half ? 'Yes' : 'No',
                $application->application_date?->format('Y-m-d H:i:s') ?? '',
                $application->to_date?->format('Y-m-d') ?? '',
                $application->is_first_half ? 'Yes' : 'No',
                $application->leave_count ?? '',
                (int) $application->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'leave-application-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new LeaveApplicationImportExportCompleted('export', 'Unable to save the leave application export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.leave-applications.download-export', ['file' => $this->fileName]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/leave-applications/export/download/'.rawurlencode($this->fileName);
        }
        $user->notify(new LeaveApplicationImportExportCompleted(
            'export',
            'Leave application export is ready for download.',
            $downloadUrl,
        ));
    }
}
