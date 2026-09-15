<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTeamRequest;
use App\Http\Requests\Tenant\UpdateTeamRequest;
use App\Jobs\Tenant\GenerateTeamExport;
use App\Jobs\Tenant\ProcessTeamImport;
use App\Models\Tenant\Company;
use App\Models\Tenant\Location;
use App\Models\Tenant\Team;
use App\Models\Tenant\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeamController extends Controller
{
    private const INDEX_URL = 'company-structure/teams';

    public function index(): View
    {
        return view('company-structure.teams.index', [
            'teams' => Team::query()->latest('id')->paginate(15)->withQueryString(),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function show(Request $request): View
    {
        return view('company-structure.teams.show', [
            'team' => Team::query()->findOrFail($request->route('team')),
        ]);
    }

    public function filterStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:all,1,0'],
        ]);

        $status = $validated['status'] ?? 'all';
        $query = Team::query()->latest('id');

        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }

        $teams = $query->get();

        return response()->json([
            'html' => view('company-structure.teams.partials.rows', compact('teams'))->render(),
            'count' => $teams->count(),
        ]);
    }

    public function create(): View
    {
        return view('company-structure.teams.add', [
            'team' => null,
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTeamRequest $request): RedirectResponse
    {
        Team::query()->create($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Team created successfully.');
    }

    public function edit(Request $request): View
    {
        return view('company-structure.teams.edit', [
            'team' => Team::query()->findOrFail($request->route('team')),
            'companies' => Company::query()->where('status', 1)->orderBy('name')->get(),
            'locations' => Location::query()->where('status', 1)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTeamRequest $request): RedirectResponse
    {
        Team::query()->findOrFail($request->route('team'))->update($request->validated());

        return redirect(url(self::INDEX_URL))->with('success', 'Team updated successfully.');
    }

    public function updateStatus(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'integer', 'in:1,0'],
        ]);

        $team->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Team status updated successfully.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $team = Team::query()->findOrFail($request->route('team'));

        if ($team->hasRelatedRecords()) {
            return redirect(url(self::INDEX_URL))->with('error', $team->getRelatedRecordsMessage('Team'));
        }

        $team->delete();

        return redirect(url(self::INDEX_URL))->with('success', 'Team deleted successfully.');
    }

    public function export(): RedirectResponse
    {
        $fileName = 'teams_' . now()->format('Ymd_His') . '.csv';
        GenerateTeamExport::dispatch(Auth::guard('tenant')->id(), $fileName)->onConnection('database_tenant')->onQueue('tenant');

        return redirect(url(self::INDEX_URL))->with('success', 'Team export has started. You will receive a notification when it is ready.');
    }

    public function downloadImportSample(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            if ($handle !== false) {
                fputcsv($handle, Team::IMPORT_EXPORT_COLUMNS);
                fclose($handle);
            }
        }, 'teams_import_sample.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request): RedirectResponse
    {
        abort_unless(Auth::guard('tenant')->user() instanceof User, 403);
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'update_duplicate_records' => ['nullable', 'boolean'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('file');
        $storedPath = $file->storeAs('team-imports', 'team_import_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $file->getClientOriginalExtension());

        ProcessTeamImport::dispatch(Auth::guard('tenant')->id(), $storedPath, (bool) ($validated['update_duplicate_records'] ?? false))
            ->onConnection('database_tenant')
            ->onQueue('tenant');

        return redirect(url(self::INDEX_URL))->with('success', 'Team import has started. You will receive a notification when it finishes.');
    }

    public function downloadExport(string $tenant, ?string $file = null): BinaryFileResponse
    {
        $fileName = basename($file ?? $tenant);
        abort_unless(preg_match('/\Ateams_\d{8}_\d{6}\.csv\z/', $fileName) === 1, 404);
        $path = 'team-exports/' . $fileName;

        if (! Storage::disk('local')->exists($path)) {
            (new GenerateTeamExport(Auth::guard('tenant')->id(), $fileName))->handle();
        }

        abort_unless(Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path), 'teams.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
