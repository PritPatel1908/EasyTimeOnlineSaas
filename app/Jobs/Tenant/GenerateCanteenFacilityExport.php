<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\CanteenFacility;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CanteenFacilityImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateCanteenFacilityExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function __construct(public ?int $userId, public string $fileName, public string $search = '', public string $status = 'all') {}
    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) return;
        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) return;
        fputcsv($handle, CanteenFacility::IMPORT_EXPORT_COLUMNS);
        $query = CanteenFacility::with(['location', 'rules'])->orderBy('name');
        if ($this->search !== '') {
            $query->where(function ($query): void {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }
        if (in_array($this->status, ['0', '1'], true)) {
            $query->where('status', (int) $this->status);
        }
        foreach ($query->get() as $facility) {
            $rules = $facility->rules->isNotEmpty() ? $facility->rules : [null];

            foreach ($rules as $rule) {
                fputcsv($handle, [
                    $facility->name,
                    $facility->code,
                    $facility->total_cfa,
                    $facility->status === 1 ? 'Active' : 'Inactive',
                    $facility->location?->name ?? '',
                    $rule?->total_absent_days ?? '',
                    $rule?->company_contribution_in_percentage_wise ? '1' : '0',
                    $rule?->company_allowance_contribution_in_fixed ?? '',
                    $rule?->company_allowance_contribution_in_percentage ?? '',
                ]);
            }
        }
        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        if ($contents === false || ! Storage::disk('local')->put('canteen-facility-exports/' . $this->fileName, $contents)) return;
        $user->notify(new CanteenFacilityImportExportCompleted('export', 'Canteen Facility export is ready for download.', '/company-structure/canteen-facilities/export/download/' . rawurlencode($this->fileName)));
    }
}
