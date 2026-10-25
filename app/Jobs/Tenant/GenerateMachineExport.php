<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Machine;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\MachineImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateMachineExport implements ShouldQueue
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
            return;
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $machines = Machine::query()->with(['company', 'location', 'area'])->orderBy('name')->get();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            $user->notify(new MachineImportExportCompleted('export', 'Unable to generate the machine export file.'));

            return;
        }

        fputcsv($handle, Machine::IMPORT_EXPORT_COLUMNS);

        foreach ($machines as $machine) {
            fputcsv($handle, [
                $machine->name,
                $machine->machine_ip ?? '',
                $machine->machine_serial_number ?? '',
                $machine->dms_device_id ?? '',
                $machine->dms_area_id ?? '',
                $machine->sync_dms,
                $machine->network_status,
                $machine->access_direction ?? '',
                $machine->location?->name ?? '',
                $machine->company?->name ?? '',
                $machine->area?->name ?? '',
                $machine->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('machine-exports/' . $this->fileName, $contents)) {
            $user->notify(new MachineImportExportCompleted('export', 'Unable to save the machine export file.'));

            return;
        }

        $user->notify(new MachineImportExportCompleted(
            'export',
            'Machine export is ready for download.',
            '/company-structure/machines/export/download/' . rawurlencode($this->fileName),
        ));
    }
}
