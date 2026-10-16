<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ManualLeaveBalance;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ManualLeaveBalanceImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateManualLeaveBalanceExport implements ShouldQueue
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

        $balances = ManualLeaveBalance::query()->with(['Users', 'leave_type'])->orderBy('date')->orderBy('id')->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new ManualLeaveBalanceImportExportCompleted('export', 'Unable to generate the manual leave balance export file.'));
            return;
        }

        fputcsv($handle, ManualLeaveBalance::IMPORT_EXPORT_COLUMNS);
        foreach ($balances as $balance) {
            fputcsv($handle, [
                $balance->Users->pluck('code')->filter()->implode(';'),
                $this->namesAndCodesForIds($balance->getRawOriginal('location_id'), \App\Models\Tenant\Location::class),
                $this->namesAndCodesForIds($balance->getRawOriginal('company_id'), \App\Models\Tenant\Company::class),
                $balance->leave_type?->code ?? '',
                $balance->balance ?? '',
                $balance->date?->format('Y-m-d H:i:s') ?? '',
                $balance->reason ?? '',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('manual-leave-balance-exports/' . $this->fileName, $contents)) {
            $user->notify(new ManualLeaveBalanceImportExportCompleted('export', 'Unable to save the manual leave balance export file.'));
            return;
        }

        $url = '/employee-structure/manual-leave-balances/export/download/' . rawurlencode($this->fileName);
        $user->notify(new ManualLeaveBalanceImportExportCompleted('export', 'Manual leave balance export is ready for download.', $url));
    }

    private function namesAndCodesForIds(mixed $rawIds, string $model): string
    {
        $ids = is_string($rawIds) ? json_decode($rawIds, true) : $rawIds;
        if (! is_array($ids) || $ids === []) {
            return '';
        }

        return $model::query()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get(['name', 'code'])
            ->map(static fn($record): string => $record->name . ' (' . $record->code . ')')
            ->implode(';');
    }
}
