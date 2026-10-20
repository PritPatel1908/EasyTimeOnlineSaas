<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\EarlyGoingRuleController;
use App\Jobs\Tenant\GenerateEarlyGoingRuleExport;
use App\Jobs\Tenant\ProcessEarlyGoingRuleImport;
use App\Models\Tenant\EarlyGoingRule;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantEarlyGoingRuleImportExportTest extends TestCase
{
    public function test_import_export_columns_match_early_going_rule_fields(): void
    {
        $this->assertSame([
            'code',
            'description',
            'ignore_early_going_minutes',
            'status',
        ], EarlyGoingRule::IMPORT_EXPORT_COLUMNS);
    }

    public function test_import_sample_matches_the_model_columns(): void
    {
        $response = app(EarlyGoingRuleController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString(
            'early_going_rules_import_sample.csv',
            $response->headers->get('Content-Disposition', ''),
        );

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(EarlyGoingRule::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(EarlyGoingRule::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_export_dispatches_the_tenant_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(EarlyGoingRuleController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString(
            'Early going rule export has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(GenerateEarlyGoingRuleExport::class, function (GenerateEarlyGoingRuleExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Aearly_going_rules_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_a_job_after_validating_csv_headers(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(User::class);
        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $headers = implode(',', EarlyGoingRule::IMPORT_EXPORT_COLUMNS);
        $file = UploadedFile::fake()->createWithContent(
            'early_going_rules_import.csv',
            $headers . "\nEarly10,Default,10,Active\n",
        );
        $request = Request::create('/time-management/early-going-rules/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], [
            'file' => $file,
        ]);

        $response = app(EarlyGoingRuleController::class)->import($request);

        $this->assertStringContainsString(
            'Early going rule import has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(ProcessEarlyGoingRuleImport::class, function (ProcessEarlyGoingRuleImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'early-going-rule-imports/');
        });
    }

    public function test_export_download_only_accepts_early_going_rule_export_names(): void
    {
        Storage::fake('local');
        $fileName = 'early_going_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'early-going-rule-exports/' . $fileName,
            "code,description,ignore_early_going_minutes,status\nEarly10,Default,10,Active\n",
        );

        $guard = Mockery::mock(\Illuminate\Contracts\Auth\Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(EarlyGoingRuleController::class)->downloadExport($fileName);

        $this->assertStringContainsString('early_going_rules.csv', $response->headers->get('Content-Disposition', ''));
    }
}
