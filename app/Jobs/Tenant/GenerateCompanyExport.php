<?php

declare(strict_types=1);

namespace App\Jobs\Tenant;

use App\Models\Tenant\Company;
use App\Models\Tenant\Scopes\DataPolicyFilter;
use App\Models\Tenant\User;
use App\Notifications\Tenant\CompanyImportExportCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class GenerateCompanyExport implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public ?int $userId,
        public string $fileName,
    ) {}

    public function handle(): void
    {
        $user = $this->userId !== null
            ? User::query()->withoutGlobalScope(DataPolicyFilter::class)->find($this->userId)
            : null;

        if ($user === null) {
            return;
        }

        Auth::shouldUse('tenant');
        Auth::setUser($user);

        $companies = Company::query()->orderBy('name')->get();

        $headers = Company::IMPORT_EXPORT_COLUMNS;

        $filePath = 'company-exports/' . $this->fileName;
        $handle = fopen('php://temp', 'w+');

        if ($handle === false) {
            if ($user !== null) {
                $user->notify(new CompanyImportExportCompleted(
                    'export',
                    'Unable to generate the company export file.',
                ));
            }

            return;
        }

        fputcsv($handle, $headers);

        foreach ($companies as $company) {
            $locations = $company->getLocationsAttribute();

            fputcsv($handle, [
                $company->name ?? '',
                $company->code ?? '',
                $company->email ?? '',
                $this->normalizeStatusForExport($company->status),
                $locations->pluck('name')->join(', '),
            ]);
        }

        rewind($handle);
        $contents = stream_get_contents($handle);
        fclose($handle);

        if ($contents === false || ! Storage::disk('local')->put($filePath, $contents)) {
            if ($user !== null) {
                $user->notify(new CompanyImportExportCompleted(
                    'export',
                    'Unable to save the company export file.',
                ));
            }

            return;
        }

        $downloadUrl = $this->buildDownloadUrl();

        if ($user !== null) {
            $user->notify(new CompanyImportExportCompleted(
                'export',
                'Company export is ready for download.',
                $downloadUrl,
            ));
        }
    }

    /**
     * Build the notification download URL for the tenant's download route.
     * Uses route() when a request context is available (synchronous export),
     * otherwise falls back to a relative path (queued job context).
     */
    private function buildDownloadUrl(): string
    {
        try {
            return route('tenant.company-structure.companies.download-export', [
                'file' => $this->fileName,
            ]);
        } catch (\Throwable) {
            return '/company-structure/companies/export/download/' . rawurlencode($this->fileName);
        }
    }

    private function normalizeStatusForExport(int|string|null $status): string
    {
        return $status === 1 || strtolower((string) $status) === 'active' ? 'Active' : 'Inactive';
    }
}
