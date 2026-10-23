<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\ShiftChange;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualPunchImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateManualShiftChangeExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the manual shift change export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $changes = ShiftChange::query()
            ->with(['Users', 'shifts', 'shift_rotation'])
            ->orderBy('from_date')
            ->orderBy('id')
            ->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualPunchImportExportCompleted(
                'export',
                'Unable to generate the manual shift change export file.',
                url: '/time-management/manual-shift-changes',
            ));

            return;
        }

        fputcsv($handle, ShiftChange::IMPORT_EXPORT_COLUMNS);
        foreach ($changes as $change) {
            fputcsv($handle, [
                $change->Users->pluck('code')->filter()->implode(','),
                $change->shift_type ?? '',
                $change->shifts->pluck('code')->filter()->implode(','),
                $change->shift_rotation?->code ?? '',
                $change->from_date?->format('Y-m-d') ?? '',
                $change->is_forever ? '1' : '0',
                $change->to_date?->format('Y-m-d') ?? '',
                $change->reason ?? '',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'manual-shift-change-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new ManualPunchImportExportCompleted(
                'export',
                'Unable to save the manual shift change export file.',
                url: '/time-management/manual-shift-changes',
            ));

            return;
        }

        $user->notify(new ManualPunchImportExportCompleted(
            'export',
            'Manual shift change export is ready for download.',
            $this->downloadUrl(),
            '/time-management/manual-shift-changes',
        ));
    }

    private function downloadUrl(): string
    {
        try {
            return route('tenant.time-management.manual-shift-changes.download-export', ['file' => $this->fileName]);
        } catch (\Throwable) {
            return '/time-management/manual-shift-changes/export/download/'.rawurlencode($this->fileName);
        }
    }
}