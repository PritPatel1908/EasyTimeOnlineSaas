<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\FinancialYear;
use App\Models\Tenant\Location;
use App\Models\Tenant\User;
use App\Notifications\Tenant\FinancialYearImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class GenerateFinancialYearExport implements ShouldQueue
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
        $user = $this->userId === null ? null : User::query()->find($this->userId);

        if ($user === null) {
            throw new RuntimeException('The user requesting the Financial Year export could not be found.');
        }

        Auth::shouldUse('tenant');
        Auth::guard('tenant')->setUser($user);

        $financialYears = FinancialYear::query()->orderBy('start_date')->get();
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            throw new RuntimeException('Unable to prepare the Financial Year CSV export.');
        }

        fputcsv($handle, FinancialYear::IMPORT_EXPORT_COLUMNS);

        foreach ($financialYears as $financialYear) {
            fputcsv($handle, [
                $financialYear->code,
                $financialYear->name ?? '',
                $financialYear->start_date?->format('Y-m-d') ?? '',
                $financialYear->end_date?->format('Y-m-d') ?? '',
                $this->relatedNames(Location::class, $financialYear->location_id),
                $this->relatedNames(Company::class, $financialYear->company_id),
                $financialYear->is_started ? 'Yes' : 'No',
                $financialYear->is_closed ? 'Yes' : 'No',
                (int) $financialYear->status === 1 ? 'Active' : 'Inactive',
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false
            || ! Storage::disk('local')->put('financial-year-exports/' . $this->fileName, $contents)) {
            throw new RuntimeException('Unable to save the Financial Year export file.');
        }

        $user->notify(new FinancialYearImportExportCompleted(
            'export',
            'Financial Year export is ready for download.',
            '/time-management/financial-years/export/download/' . rawurlencode($this->fileName),
        ));
    }

    private function relatedNames(string $model, mixed $ids): string
    {
        if (is_string($ids)) {
            $decoded = json_decode($ids, true);
            $ids = json_last_error() === JSON_ERROR_NONE ? $decoded : [$ids];
        }

        $ids = array_values(array_filter(
            (array) $ids,
            static fn (mixed $id): bool => is_int($id) || (is_string($id) && ctype_digit($id)),
        ));

        if ($ids === []) {
            return '';
        }

        return $model::query()->whereIn('id', $ids)->orderBy('name')->pluck('name')->implode(', ');
    }
}
