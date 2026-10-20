<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\AbsentRuleController;
use App\Jobs\Tenant\GenerateAbsentRuleExport;
use App\Jobs\Tenant\ProcessAbsentRuleImport;
use App\Models\Tenant\AbsentRule;
use App\Models\Tenant\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class TenantAbsentRuleImportExportTest extends TestCase
{
    public function test_import_export_columns_match_absent_rule_fields(): void
    {
        $this->assertSame([
            'code',
            'description',
            'work_hr_less_than_minutes',
            'allow_single_punch',
            'late_coming_minutes',
            'no_of_late',
            'consecutive_late_coming',
            'ignore_month_end_late',
            'early_going_minutes',
            'no_of_early',
            'consecutive_early_going',
            'ignore_month_end_early',
            'status',
        ], AbsentRule::IMPORT_EXPORT_COLUMNS);
    }

    public function test_import_sample_matches_the_model_columns(): void
    {
        $response = app(AbsentRuleController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString(
            'absent_rules_import_sample.csv',
            $response->headers->get('Content-Disposition', ''),
        );

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(AbsentRule::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(AbsentRule::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_export_dispatches_the_tenant_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(AbsentRuleController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString(
            'Absent rule export has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(GenerateAbsentRuleExport::class, function (GenerateAbsentRuleExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Aabsent_rules_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
        });
    }

    public function test_import_dispatches_a_job_after_validating_csv_headers(): void
    {
        Bus::fake();
        Storage::fake('local');
        $user = Mockery::mock(User::class);
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn($user);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $headers = implode(',', AbsentRule::IMPORT_EXPORT_COLUMNS);
        $file = UploadedFile::fake()->createWithContent(
            'absent_rules_import.csv',
            $headers."\nDefault,Sample,300,Yes,10,3,3,No,10,3,3,No,Active\n",
        );
        $request = Request::create('/time-management/absent-rules/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], [
            'file' => $file,
        ]);

        $response = app(AbsentRuleController::class)->import($request);

        $this->assertStringContainsString(
            'Absent rule import has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(ProcessAbsentRuleImport::class, function (ProcessAbsentRuleImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'absent-rule-imports/');
        });
    }

    public function test_export_download_only_accepts_absent_rule_export_names(): void
    {
        Storage::fake('local');
        $fileName = 'absent_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'absent-rule-exports/'.$fileName,
            "code,description,work_hr_less_than_minutes,allow_single_punch,late_coming_minutes,no_of_late,consecutive_late_coming,ignore_month_end_late,early_going_minutes,no_of_early,consecutive_early_going,ignore_month_end_early,status\nDefault,Sample,300,Yes,10,3,3,No,10,3,3,No,Active\n",
        );

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(AbsentRuleController::class)->downloadExport($fileName);

        $this->assertStringContainsString('absent_rules.csv', $response->headers->get('Content-Disposition', ''));
    }
}
