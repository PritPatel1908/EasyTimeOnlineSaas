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
            preg_match('/\Afinancial_years_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'financial-year-exports/' . $fileName,
            preg_match('/\Acompanies_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'company-exports/' . $fileName,
            preg_match('/\Acanteen_facilities_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'canteen-facility-exports/' . $fileName,
            preg_match('/\Alocations_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'location-exports/' . $fileName,
            preg_match('/\Adepartments_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'department-exports/' . $fileName,
            preg_match('/\Asub_departments_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'sub-department-exports/' . $fileName,
            preg_match('/\Ateams_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'team-exports/' . $fileName,
            preg_match('/\Acategories_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'category-exports/' . $fileName,
            preg_match('/\Adesignations_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'designation-exports/' . $fileName,
            preg_match('/\Aemployees_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'employee-exports/' . $fileName,
            preg_match('/\Aapproval_flows_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'approval-flow-exports/' . $fileName,
            preg_match('/\Aunits_\d+_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'unit-exports/' . $fileName,
            preg_match('/\Aleave_types_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'leave-type-exports/' . $fileName,
            preg_match('/\Aleave_groups_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'leave-group-exports/' . $fileName,
            preg_match('/\Amanual_leave_balances_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'manual-leave-balance-exports/' . $fileName,
            preg_match('/\Ashifts_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'shift-exports/' . $fileName,
            preg_match('/\Amanual_punches_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'manual-punch-exports/' . $fileName,
            preg_match('/\Amanual_attendances_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'manual-attendance-exports/' . $fileName,
            preg_match('/\Amanual_shift_changes_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'manual-shift-change-exports/' . $fileName,
            preg_match('/\Aweek_off_changes_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'week-off-change-exports/' . $fileName,
            preg_match('/\Ashift_rotations_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'shift-rotation-exports/' . $fileName,
            preg_match('/\Alate_coming_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'late-coming-rule-exports/' . $fileName,
            preg_match('/\Aearly_going_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'early-going-rule-exports/' . $fileName,
            preg_match('/\Ahalf_day_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'half-day-rule-exports/' . $fileName,
            preg_match('/\Aovertime_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'overtime-rule-exports/' . $fileName,
            preg_match('/\Aabsent_rules_\d{8}_\d{6}\.csv\z/', $fileName) === 1 => 'absent-rule-exports/' . $fileName,
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
