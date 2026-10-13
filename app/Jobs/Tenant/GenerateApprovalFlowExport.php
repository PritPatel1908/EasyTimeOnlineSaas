<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ApprovalFlowImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateApprovalFlowExport implements ShouldQueue
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
            throw new RuntimeException('The approval flow export requester could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('The approval flow export could not be initialized.');
        }

        fputcsv($handle, ApprovalFlow::IMPORT_EXPORT_COLUMNS);
        $flows = ApprovalFlow::query()->with(['approval_flow_details.users'])->orderBy('approval_flow_code')->get();
        foreach ($flows as $flow) {
            $details = $flow->approval_flow_details->sortBy('level');
            if ($details->isEmpty()) {
                fputcsv($handle, [$flow->approval_flow_code, '', '']);
                continue;
            }

            foreach ($details as $detail) {
                fputcsv($handle, [
                    $flow->approval_flow_code,
                    $detail->level,
                    $detail->users->sortBy('id')->map(fn(User $approver): string => $approver->email ?: ($approver->code ?: (string) $approver->id))->join('|'),
                ]);
            }
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('approval-flow-exports/' . $this->fileName, $contents)) {
            throw new RuntimeException('The approval flow export file could not be saved.');
        }

        $user->notify(new ApprovalFlowImportExportCompleted(
            'export',
            'Approval flow export is ready for download.',
            '/employee-structure/approval-flows/export/download/' . rawurlencode($this->fileName),
        ));
    }
}
