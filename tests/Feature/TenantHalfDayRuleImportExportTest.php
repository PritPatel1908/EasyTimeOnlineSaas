<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\Tenant\HalfDayRuleController;
use App\Jobs\Tenant\GenerateHalfDayRuleExport;
use App\Jobs\Tenant\ProcessHalfDayRuleImport;
use App\Models\Tenant\HalfDayRule;
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

class TenantHalfDayRuleImportExportTest extends TestCase
{
    public function test_import_export_columns_match_half_day_rule_fields(): void
    {
        $this->assertSame([
            'code',
            'description',
            'single_punch_allowed',
            'work_hr_less_than_minutes',
            'late_coming_minutes',
            'no_of_late',
            'consecutive_late_coming',
            'ignore_month_end_late',
            'early_going_minutes',
            'no_of_early',
            'consecutive_early_going',
            'ignore_month_end_early',
            'status',
        ], HalfDayRule::IMPORT_EXPORT_COLUMNS);
    }

    public function test_import_sample_matches_the_model_columns(): void
    {
        $response = app(HalfDayRuleController::class)->downloadImportSample();

        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString(
            'half_day_rules_import_sample.csv',
            $response->headers->get('Content-Disposition', ''),
        );

        ob_start();
        ($response->getCallback())();
        $csv = ob_get_clean();
        $rows = array_map('str_getcsv', explode("\n", trim($csv)));

        $this->assertSame(HalfDayRule::IMPORT_EXPORT_COLUMNS, $rows[0]);
        $this->assertCount(count(HalfDayRule::IMPORT_EXPORT_COLUMNS), $rows[1]);
    }

    public function test_export_dispatches_the_tenant_queue_job(): void
    {
        Bus::fake();
        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('id')->andReturn(17);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(HalfDayRuleController::class)->export();

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringContainsString(
            'Half-day rule export has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(GenerateHalfDayRuleExport::class, function (GenerateHalfDayRuleExport $job): bool {
            return $job->userId === 17
                && $job->connection === 'database_tenant'
                && $job->queue === 'export'
                && preg_match('/\Ahalf_day_rules_\d{8}_\d{6}\.csv\z/', $job->fileName) === 1;
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

        $headers = implode(',', HalfDayRule::IMPORT_EXPORT_COLUMNS);
        $file = UploadedFile::fake()->createWithContent(
            'half_day_rules_import.csv',
            $headers."\nDefault,Sample,Yes,300,10,3,3,No,10,3,3,0,Active\n",
        );
        $request = Request::create('/time-management/half-day-rules/import', 'POST', [
            'update_duplicate_records' => '1',
        ], [], [
            'file' => $file,
        ]);

        $response = app(HalfDayRuleController::class)->import($request);

        $this->assertStringContainsString(
            'Half-day rule import has started',
            $response->getSession()->get('success', ''),
        );
        Bus::assertDispatched(ProcessHalfDayRuleImport::class, function (ProcessHalfDayRuleImport $job): bool {
            return $job->userId === 17
                && $job->updateDuplicateRecords
                && str_contains($job->filePath, 'half-day-rule-imports/');
        });
    }

    public function test_export_download_only_accepts_half_day_rule_export_names(): void
    {
        Storage::fake('local');
        $fileName = 'half_day_rules_20261020_123456.csv';
        Storage::disk('local')->put(
            'half-day-rule-exports/'.$fileName,
            "code,description,single_punch_allowed,work_hr_less_than_minutes,late_coming_minutes,no_of_late,consecutive_late_coming,ignore_month_end_late,early_going_minutes,no_of_early,consecutive_early_going,ignore_month_end_early,status\nDefault,Sample,Yes,300,10,3,3,No,10,3,3,0,Active\n",
        );

        $guard = Mockery::mock(Guard::class);
        $guard->shouldReceive('user')->andReturn(null);
        Auth::shouldReceive('guard')->with('tenant')->andReturn($guard);

        $response = app(HalfDayRuleController::class)->downloadExport($fileName);

        $this->assertStringContainsString('half_day_rules.csv', $response->headers->get('Content-Disposition', ''));
    }
}
