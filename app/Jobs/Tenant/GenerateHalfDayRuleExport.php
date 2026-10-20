<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\HalfDayRule;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\HalfDayRuleImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateHalfDayRuleExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $fileName,
    ) {
        $this->onConnection('database_tenant')->onQueue('export');
    }

    public function handle(): void
    {
        $user = $this->userId !== null
            ? User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId)
            : null;

        if ($user === null) {
            throw new RuntimeException('The user requesting the half-day rule export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to create a temporary half-day rule export stream.');
        }

        $columns = HalfDayRule::IMPORT_EXPORT_COLUMNS;
        fputcsv($handle, $columns);

        foreach (HalfDayRule::query()->orderBy('code')->get() as $rule) {
            fputcsv($handle, [
                $rule->code,
                $rule->description ?? '',
                (int) $rule->single_punch_allowed === 1 ? 'Yes' : 'No',
                $rule->work_hr_less_than_minutes,
                $rule->late_coming_minutes,
                $rule->no_of_late,
                $rule->consecutive_late_coming,
                (int) $rule->ignore_month_end_late === 1 ? 'Yes' : 'No',
                $rule->early_going_minutes,
                $rule->no_of_early,
                $rule->consecutive_early_going,
                $rule->ignore_month_end_early ? 'Yes' : 'No',
                (int) $rule->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        $filePath = 'half-day-rule-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($filePath, $contents)) {
            $user->notify(new HalfDayRuleImportExportCompleted('export', 'Unable to save the half-day rule export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.half-day-rules.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/half-day-rules/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new HalfDayRuleImportExportCompleted(
            'export',
            'Half-day rule export is ready for download.',
            $downloadUrl,
        ));
    }
}
