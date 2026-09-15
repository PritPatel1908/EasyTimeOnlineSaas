<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    public function index(): View
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user !== null && method_exists($user, 'notifications'), 403);

        $notifications = $user->notifications()->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function show(Request $request, string $tenant, string $notification): View
    {
        $user = Auth::guard('tenant')->user();
        abort_unless($user !== null && method_exists($user, 'notifications'), 403);

        $notifications = call_user_func([$user, 'notifications']);
        $record = $notifications->whereKey($notification)->firstOrFail();
        $data = $record->data;
        $preview = null;
        $previewError = null;

        if (data_get($data, 'type') === 'export' && data_get($data, 'download_url')) {
            [$preview, $previewError] = $this->readExportPreview((string) data_get($data, 'download_url'));
        }

        $record->markAsRead();

        return view('notifications.show', [
            'notification' => $record,
            'data' => $data,
            'preview' => $preview,
            'previewError' => $previewError,
        ]);
    }

    /**
     * Read only the export files produced by the tenant export jobs.
     *
     * @return array{0: array{headers: array<int, string>, rows: array<int, array<int, string>>}|null, 1: string|null}
     */
    private function readExportPreview(string $downloadUrl): array
    {
        $path = parse_url($downloadUrl, PHP_URL_PATH);
        $fileName = is_string($path) ? basename(rawurldecode($path)) : '';
        $storagePath = match (true) {
            preg_match('/\Acompanies_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'company-exports/' . $fileName,
            preg_match('/\Alocations_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'location-exports/' . $fileName,
            preg_match('/\Adepartments_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'department-exports/' . $fileName,
            default => null,
        };

        if ($storagePath === null || ! Storage::disk('local')->exists($storagePath)) {
            return [null, 'The export file is no longer available for preview.'];
        }

        $handle = fopen(Storage::disk('local')->path($storagePath), 'rb');

        if ($handle === false) {
            return [null, 'The export file could not be opened for preview.'];
        }

        $headers = fgetcsv($handle) ?: [];
        $rows = [];

        while (count($rows) < 1000 && ($row = fgetcsv($handle)) !== false) {
            $rows[] = array_map(static fn($value): string => (string) ($value ?? ''), $row);
        }

        fclose($handle);

        return [[
            'headers' => array_map(static fn($value): string => (string) ($value ?? ''), $headers),
            'rows' => $rows,
        ], null];
    }
}
