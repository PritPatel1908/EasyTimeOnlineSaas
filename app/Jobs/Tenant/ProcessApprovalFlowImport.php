<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\ApprovalFlow;
use App\Models\Tenant\User;
use App\Notifications\Tenant\ApprovalFlowImportExportCompleted;
use App\Support\CsvImportTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProcessApprovalFlowImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $filePath, public bool $updateDuplicateRecords = false)
    {
        $this->onConnection('database_tenant')->onQueue('import');
    }

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null || ! Storage::disk('local')->exists($this->filePath)) {
            throw new RuntimeException('The approval flow import requester or file could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);
        $handle = fopen(Storage::disk('local')->path($this->filePath), 'r');
        if ($handle === false) {
            throw new RuntimeException('The approval flow import file could not be opened.');
        }

        $headers = array_map(
            static fn($header): string => CsvImportTemplate::normalizeHeader((string) $header),
            fgetcsv($handle) ?: [],
        );
        $groups = [];
        $errors = [];
        $rowNumber = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (CsvImportTemplate::isHintRow($values)) {
                continue;
            }

            $row = array_combine($headers, array_pad(array_slice($values, 0, count($headers)), count($headers), ''));
            if (! is_array($row)) {
                $errors[] = "Row {$rowNumber} could not be read.";
                continue;
            }

            $code = trim((string) ($row['approval_flow_code'] ?? ''));
            if ($code === '') {
                $errors[] = "Row {$rowNumber} is missing an approval flow code.";
                continue;
            }
            if (mb_strlen($code) > 255) {
                $errors[] = "Row {$rowNumber} has an approval flow code longer than 255 characters.";
                continue;
            }

            $groups[$code] ??= ['details' => [], 'invalid' => false];
            $levelValue = trim((string) ($row['level'] ?? ''));
            $usersValue = trim((string) ($row['users'] ?? ''));
            if ($levelValue === '') {
                if ($usersValue !== '') {
                    $errors[] = "Row {$rowNumber} has approvers but no level.";
                    $groups[$code]['invalid'] = true;
                }
                continue;
            }

            $levelAssignments = $this->parseLevelAssignments($levelValue, $usersValue);
            if ($levelAssignments === null) {
                $errors[] = "Row {$rowNumber} requires a positive level and at least one approver.";
                $groups[$code]['invalid'] = true;
                continue;
            }

            foreach ($levelAssignments as $assignment) {
                $userIds = [];
                foreach ($assignment['users'] as $token) {
                    $approver = User::query()
                        ->whereRaw('LOWER(email) = ?', [mb_strtolower($token)])
                        ->first()
                        ?? User::query()->where('code', $token)->first();

                    if ($approver === null && ctype_digit($token)) {
                        $approver = User::query()->find((int) $token);
                    }

                    if ($approver === null) {
                        $errors[] = "Row {$rowNumber} references an unknown approver: {$token}.";
                        $groups[$code]['invalid'] = true;
                        continue;
                    }
                    $userIds[] = (int) $approver->id;
                }

                if ($userIds !== []) {
                    $level = $assignment['level'];
                    $groups[$code]['details'][$level] = array_values(array_unique(array_merge(
                        $groups[$code]['details'][$level] ?? [],
                        $userIds,
                    )));
                }
            }
        }
        fclose($handle);

        $created = 0;
        $updated = 0;
        foreach ($groups as $code => $group) {
            if ($group['invalid']) {
                continue;
            }

            $flow = ApprovalFlow::query()->where('approval_flow_code', $code)->first();
            if ($flow !== null && ! $this->updateDuplicateRecords) {
                $errors[] = "Duplicate approval flow code {$code}.";
                continue;
            }

            DB::transaction(function () use ($code, $group, $flow, &$created, &$updated): void {
                $flow ??= ApprovalFlow::query()->create(['approval_flow_code' => $code]);
                if ($flow->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                    $flow->approval_flow_details()->delete();
                }

                ksort($group['details']);
                foreach ($group['details'] as $level => $userIds) {
                    $detail = $flow->approval_flow_details()->create(['level' => $level]);
                    $detail->users()->sync($userIds);
                }
            });
        }

        Storage::disk('local')->delete($this->filePath);
        $message = $errors === []
            ? "Imported {$created} new approval flow(s) and updated {$updated} existing flow(s)."
            : "Import completed with {$created} new approval flow(s), {$updated} updated flow(s), and errors: " . implode('; ', array_slice($errors, 0, 5));
        $user->notify(new ApprovalFlowImportExportCompleted('import', $message));
    }

    /**
     * @return array<int, array{level: int, users: array<int, string>}>|null
     */
    private function parseLevelAssignments(string $levelValue, string $usersValue): ?array
    {
        $levels = array_map('trim', explode(',', $levelValue));
        $approverGroups = count($levels) > 1
            ? array_map('trim', explode(',', $usersValue))
            : [$usersValue];

        if (
            count($levels) !== count($approverGroups)
            || collect($levels)->contains(static fn(string $level): bool => ! ctype_digit($level) || (int) $level < 1)
        ) {
            return null;
        }

        $assignments = [];
        foreach ($levels as $index => $level) {
            $users = array_values(array_filter(
                array_map('trim', explode('|', $approverGroups[$index])),
                static fn(string $token): bool => $token !== '',
            ));
            if ($users === []) {
                return null;
            }

            $assignments[] = ['level' => (int) $level, 'users' => $users];
        }

        return $assignments;
    }
}
