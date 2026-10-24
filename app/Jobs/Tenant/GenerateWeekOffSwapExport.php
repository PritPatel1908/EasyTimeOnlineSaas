<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\WeekOffSwap;
use App\Notifications\Tenant\WeekOffSwapImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateWeekOffSwapExport implements ShouldQueue
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
        $user = $this->userId !== null ? User::query()->find($this->userId) : null;
        if ($user === null) {
            throw new RuntimeException('The user requesting the week-off swap export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $swaps = WeekOffSwap::query()->with('Users')->orderBy('week_date')->orderBy('id')->get();
        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            $user->notify(new WeekOffSwapImportExportCompleted(
                'export',
                'Unable to generate the week-off swap export file.',
            ));

            return;
        }

        fputcsv($handle, WeekOffSwap::IMPORT_EXPORT_COLUMNS);
        foreach ($swaps as $swap) {
            fputcsv($handle, [
                $swap->Users->pluck('code')->filter()->implode(','),
                $swap->week_date?->format('Y-m-d') ?? '',
                $swap->swap_date?->format('Y-m-d') ?? '',
                $swap->reason ?? '',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);
        $path = 'week-off-swap-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($path, $contents)) {
            $user->notify(new WeekOffSwapImportExportCompleted(
                'export',
                'Unable to save the week-off swap export file.',
            ));

            return;
        }

        $user->notify(new WeekOffSwapImportExportCompleted(
            'export',
            'Week-off swap export is ready for download.',
            $this->downloadUrl(),
        ));
    }

    private function downloadUrl(): string
    {
        try {
            return route('tenant.time-management.week-off-swaps.download-export', ['file' => $this->fileName]);
        } catch (\Throwable) {
            return '/time-management/week-off-swaps/export/download/'.rawurlencode($this->fileName);
        }
    }
}
