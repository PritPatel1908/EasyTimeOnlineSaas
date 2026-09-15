<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Team;
use App\Models\Tenant\User;
use App\Notifications\Tenant\TeamImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateTeamExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ?int $userId, public string $fileName) {}

    public function handle(): void
    {
        $user = $this->userId === null ? null : User::withoutGlobalScopes()->find($this->userId);
        if ($user === null) {
            return;
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $handle = fopen('php://temp', 'w+');
        if ($handle === false) {
            return;
        }

        fputcsv($handle, Team::IMPORT_EXPORT_COLUMNS);
        foreach (Team::query()->orderBy('name')->get() as $team) {
            fputcsv($handle, [
                $team->name,
                $team->code,
                $team->email ?? '',
                $team->status === 1 ? 'Active' : 'Inactive',
                $team->companies->pluck('name')->join(', '),
                $team->locations->pluck('name')->join(', '),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put('team-exports/' . $this->fileName, $contents)) {
            return;
        }

        $user->notify(new TeamImportExportCompleted('export', 'Team export is ready for download.', '/company-structure/teams/export/download/' . rawurlencode($this->fileName)));
    }
}
