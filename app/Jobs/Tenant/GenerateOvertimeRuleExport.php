<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\OvertimeRule;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\OvertimeRuleImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateOvertimeRuleExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the overtime rule export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to create a temporary overtime rule export stream.');
        }

        fputcsv($handle, OvertimeRule::IMPORT_EXPORT_COLUMNS);

        foreach (OvertimeRule::query()
            ->with(['overtime_slabs' => fn ($query) => $query->orderBy('id')])
            ->orderBy('code')
            ->get() as $rule) {
            $slabs = $rule->overtime_slabs;
            fputcsv($handle, [
                $rule->code,
                $rule->description ?? '',
                $this->formatTime($rule->ignore_early_come_minutes),
                $this->formatTime($rule->ignore_late_come_minutes),
                (int) $rule->ignore_late_going === 1 ? 'Yes' : 'No',
                $this->formatDuration($rule->ignore_overtime),
                $this->formatDuration($rule->ignore_late_going_overtime),
                (int) $rule->status === 1 ? 'Active' : 'Inactive',
                $slabs->pluck('value_from')->map(fn (?string $value): string => $this->formatTime($value))->implode(','),
                $slabs->pluck('value_to')->map(fn (?string $value): string => $this->formatTime($value))->implode(','),
                $slabs->pluck('head_value')->map(fn (?string $value): string => $this->formatTime($value))->implode(','),
                $slabs->pluck('decimal_value')->map(static fn ($value): string => $value === null ? '' : (string) $value)->implode(','),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        $filePath = 'overtime-rule-exports/'.$this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($filePath, $contents)) {
            $user->notify(new OvertimeRuleImportExportCompleted('export', 'Unable to save the overtime rule export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.overtime-rules.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/overtime-rules/export/download/'.rawurlencode($this->fileName);
        }

        $user->notify(new OvertimeRuleImportExportCompleted(
            'export',
            'Overtime rule export is ready for download.',
            $downloadUrl,
        ));
    }

    private function formatTime(?string $value): string
    {
        return $value === null ? '' : substr($value, 0, 5);
    }

    private function formatDuration(float|string|null $minutes): string
    {
        if ($minutes === null) {
            return '';
        }

        $totalMinutes = (int) round((float) $minutes);

        return sprintf('%02d:%02d', intdiv($totalMinutes, 60), $totalMinutes % 60);
    }
}
