<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\EarlyGoingRule;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\EarlyGoingRuleImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateEarlyGoingRuleExport implements ShouldQueue
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
            throw new RuntimeException('The user requesting the early going rule export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            throw new RuntimeException('Unable to create a temporary early going rule export stream.');
        }

        $columns = EarlyGoingRule::IMPORT_EXPORT_COLUMNS;
        fputcsv($handle, $columns);

        foreach (EarlyGoingRule::query()->orderBy('code')->get() as $rule) {
            fputcsv($handle, [
                $rule->code,
                $rule->description ?? '',
                $rule->ignore_early_going_minutes,
                (int) $rule->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        $filePath = 'early-going-rule-exports/' . $this->fileName;
        if ($contents === false || ! Storage::disk('local')->put($filePath, $contents)) {
            $user->notify(new EarlyGoingRuleImportExportCompleted('export', 'Unable to save the early going rule export file.'));

            return;
        }

        try {
            $downloadUrl = route('tenant.time-management.early-going-rules.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            $downloadUrl = '/time-management/early-going-rules/export/download/' . rawurlencode($this->fileName);
        }

        $user->notify(new EarlyGoingRuleImportExportCompleted(
            'export',
            'Early going rule export is ready for download.',
            $downloadUrl,
        ));
    }
}
